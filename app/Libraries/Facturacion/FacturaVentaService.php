<?php

namespace App\Libraries\Facturacion;

use App\Models\Cabecera_model;
use App\Models\Clientes_model;
use App\Models\ConfiguracionFacturacion_model;
use App\Models\Factura_model;
use App\Models\NotaCredito_model;
use App\Models\Productos_model;
use App\Models\VentaDetalle_model;

/**
 * Reglas de negocio entre una venta y sus comprobantes fiscales.
 * No habla con la API: eso lo hacen EmisionComprobanteService / EmisionNotaCreditoService.
 */
class FacturaVentaService
{
    private const ID_CLIENTE_ANONIMO = 1;

    /**
     * Valida que se pueda emitir esa letra para ese cliente ANTES de guardar la venta.
     * Devuelve el mensaje de error, o null si está todo bien.
     */
    public function validarComprobante(string $comprobante, $idCliente, float $importe = 0.0, string $dni = ''): ?string
    {
        if ($comprobante === 'remito') {
            return null;
        }

        $config = (new ConfiguracionFacturacion_model())->obtener();
        if (!in_array($comprobante, ConfiguracionFacturacion_model::letrasPermitidas($config), true)) {
            return "La Factura {$comprobante} no está habilitada para este negocio. Revisá la configuración de facturación.";
        }

        if ($comprobante === 'A' && $this->cuitDelCliente($idCliente) === null) {
            return 'La Factura A requiere un cliente registrado con CUIT válido (11 dígitos).';
        }

        // Consumidor final (sin CUIT) desde el tope de ARCA: hay que identificarlo con DNI.
        if ($this->cuitDelCliente($idCliente) === null && ConfiguracionFacturacion_model::exigeIdentificarConsumidor($config, $importe)) {
            if (!self::dniValido($dni)) {
                $tope = number_format((float) $config['monto_identificar_consumidor'], 0, ',', '.');
                return "Para facturar $ {$tope} o más a un consumidor final, ARCA exige el DNI del comprador (7 u 8 dígitos).";
            }
        }

        return null;
    }

    /**
     * Crea la factura (estado pendiente) para una venta ya guardada y la vincula a la venta.
     * Devuelve el id de la factura. La emisión ante AFIP se hace después, por separado.
     */
    public function crearFactura(int $ventaId, string $letra, string $dni = ''): int
    {
        $ventas = new Cabecera_model();
        $venta = $ventas->find($ventaId);

        $cliente = (int) $venta['id_cliente'] > self::ID_CLIENTE_ANONIMO
            ? (new Clientes_model())->find($venta['id_cliente'])
            : null;

        $nombre = $cliente ? $cliente['nombre'] : ($venta['nombre_prov_client'] ?: 'Consumidor Final');

        $cuit = $this->cuitDelCliente($venta['id_cliente']);

        $facturas = new Factura_model();
        $facturaId = (int) $facturas->insert([
            'venta_id'       => $ventaId,
            'cliente_id'     => $cliente ? $cliente['id_cliente'] : null,
            'cliente_nombre' => $nombre,
            'cliente_cuit'   => $cuit,
            // El DNI solo se usa si no hay CUIT (consumidor final identificado).
            'cliente_dni'    => $cuit === null && self::dniValido($dni) ? $dni : null,
            'tipo_factura'   => $letra,
            // Se factura lo que paga el cliente (con descuento por efectivo / recargo por tarjeta).
            'importe_total'  => round((float) ($venta['total_bonificado'] ?: $venta['total_venta']), 2),
            'estado'         => Factura_model::ESTADO_PENDIENTE,
        ]);

        // Si la venta ya tenía una factura fallida, esta la reemplaza; la vieja queda como histórico.
        $ventas->update($ventaId, ['tipo_comprobante' => 'factura', 'factura_id' => $facturaId]);

        return $facturaId;
    }

    public function marcarRemito(int $ventaId): void
    {
        (new Cabecera_model())->update($ventaId, ['tipo_comprobante' => 'remito']);
    }

    /**
     * Anulación LOCAL de una venta facturada: devuelve el stock, cancela la venta y crea la
     * Nota de Crédito pendiente por el 100% de lo facturado. Todo en una transacción.
     * La emisión de la NC ante AFIP se hace DESPUÉS, fuera de la transacción.
     *
     * @throws \RuntimeException si la venta no se puede anular (no se modifica nada).
     */
    public function anularVenta(int $ventaId, int $usuarioId, string $motivo): int
    {
        $db = db_connect();
        $db->transBegin();

        try {
            $venta = $db->query('SELECT * FROM ventas_cabecera WHERE id = ? FOR UPDATE', [$ventaId])->getRowArray();
            if (!$venta) {
                throw new \RuntimeException('La venta no existe.');
            }
            if ($venta['estado'] === 'Cancelado') {
                throw new \RuntimeException('La venta ya está cancelada.');
            }

            $factura = $venta['factura_id'] ? (new Factura_model())->find($venta['factura_id']) : null;
            if (!$factura || !Factura_model::estaAprobada($factura)) {
                throw new \RuntimeException('Solo se puede anular una venta con factura aprobada (con CAE).');
            }

            $notas = new NotaCredito_model();
            if ($notas->where('factura_id', $factura['id'])->first()) {
                throw new \RuntimeException('Esta factura ya tiene una nota de crédito.');
            }

            $this->devolverStock($ventaId);
            (new Cabecera_model())->update($ventaId, ['estado' => 'Cancelado', 'motivo' => mb_substr('Anulada con NC: ' . $motivo, 0, 300)]);

            $notaId = (int) $notas->insert([
                'factura_id'         => $factura['id'],
                'creado_por'         => $usuarioId,
                'importe_acreditado' => $factura['importe_total'], // el total FACTURADO
                'motivo'             => $motivo,
                'estado'             => NotaCredito_model::ESTADO_PENDIENTE,
            ]);

            // Con DBDebug apagado (producción) un error de SQL no tira excepción: se chequea acá.
            if ($db->transStatus() === false) {
                throw new \RuntimeException('No se pudo anular la venta (error de base de datos).');
            }
            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e instanceof \RuntimeException ? $e : new \RuntimeException('No se pudo anular la venta.', 0, $e);
        }

        return $notaId;
    }

    /** Mensaje flash [tipo, texto] según cómo quedó la factura. */
    public function mensajeFactura(int $facturaId): array
    {
        $factura = (new Factura_model())->find($facturaId);

        switch ($factura['estado']) {
            case Factura_model::ESTADO_APROBADA:
                return ['msg', "Factura {$factura['tipo_factura']} {$factura['numero_comprobante']} emitida (CAE {$factura['cae']})."];
            case Factura_model::ESTADO_PENDIENTE_AFIP:
                return ['msg', 'La factura quedó pendiente de AFIP; se va a completar sola en unos minutos.'];
            case Factura_model::ESTADO_PENDIENTE:
                return ['msgEr', 'La facturación electrónica no está configurada: la venta se guardó y la factura quedó pendiente.'];
            default:
                return ['msgEr', 'La venta se guardó pero la factura no se emitió: ' . esc($factura['error_mensaje']) . ' Podés reintentar desde Ventas > Acciones.'];
        }
    }

    public static function dniValido(string $dni): bool
    {
        return (bool) preg_match('/^\d{7,8}$/', $dni);
    }

    /** CUIT del cliente registrado (solo dígitos, 11 de largo) o null si es consumidor final. */
    public function cuitDelCliente($idCliente): ?string
    {
        if ((int) $idCliente <= self::ID_CLIENTE_ANONIMO) {
            return null;
        }

        $cliente = (new Clientes_model())->find($idCliente);
        $cuit = preg_replace('/\D/', '', (string) ($cliente['cuil'] ?? ''));

        return strlen($cuit) === 11 ? $cuit : null;
    }

    private function devolverStock(int $ventaId): void
    {
        $productos = new Productos_model();
        $detalles = (new VentaDetalle_model())->where('venta_id', $ventaId)->findAll();

        foreach ($detalles as $detalle) {
            $producto = $productos->find($detalle['producto_id']);
            if ($producto) {
                $productos->update($detalle['producto_id'], ['stock' => $producto['stock'] + $detalle['cantidad']]);
            }
        }
    }
}
