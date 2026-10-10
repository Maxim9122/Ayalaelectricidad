<?php

namespace App\Libraries\Facturacion;

use App\Models\Cabecera_model;
use App\Models\CredencialFacturacion_model;
use App\Models\Factura_model;

/**
 * Paso 3: emitir la Factura de una venta.
 *
 * Contrato no negociable: emitir() NUNCA tira excepción. Una venta ya cobrada es dinero
 * real — la facturación es un paso posterior best-effort. Cualquier falla queda grabada
 * en la propia factura (estado + error_mensaje) y en el log; nunca revierte la venta.
 */
class EmisionComprobanteService
{
    use AplicaRespuestaComprobante;

    private const MAPA_TIPO_COMPROBANTE = ['A' => 1, 'B' => 6, 'C' => 11];

    /** @var FacturacionApiClient */
    private $client;

    /** @var CalculadoraIva */
    private $iva;

    /** @var Factura_model */
    private $facturas;

    public function __construct(?FacturacionApiClient $client = null, ?CalculadoraIva $iva = null)
    {
        $this->client = $client ?? new FacturacionApiClient();
        $this->iva = $iva ?? new CalculadoraIva();
        $this->facturas = new Factura_model();
    }

    public function emitir(int $facturaId): void
    {
        try {
            $this->emitirSinCapturar($facturaId);
        } catch (\Throwable $e) {
            log_message('error', "Error inesperado emitiendo la factura #{$facturaId}: " . $e->getMessage());
            $this->facturas->update($facturaId, [
                'estado'        => Factura_model::ESTADO_ERROR,
                'error_mensaje' => 'Error inesperado al facturar. Podés reintentar.',
            ]);
        }
    }

    private function emitirSinCapturar(int $facturaId): void
    {
        $factura = $this->facturas->find($facturaId);
        if (!$factura || !empty($factura['cae'])) {
            return; // ya emitida, nada que hacer
        }

        if (!$this->client->configurada()) {
            log_message('info', "Facturación no configurada: la factura #{$facturaId} queda pendiente.");
            return;
        }

        $credencial = (new CredencialFacturacion_model())->obtener();

        // Un 202 ya fue aceptado por la API: se consulta su estado en vez de re-emitir.
        if ($factura['estado'] === Factura_model::ESTADO_PENDIENTE_AFIP && $factura['comprobante_externo_id'] && CredencialFacturacion_model::estaActiva($credencial)) {
            $this->consultarPendiente($factura, $credencial);
            return;
        }

        if (!CredencialFacturacion_model::estaActiva($credencial)) {
            $this->facturas->update($facturaId, [
                'estado'        => Factura_model::ESTADO_ERROR,
                'error_mensaje' => 'Todavía no se terminó de configurar la facturación electrónica (onboarding).',
            ]);
            return;
        }

        $tipoComprobante = self::MAPA_TIPO_COMPROBANTE[$factura['tipo_factura']] ?? null;
        if ($tipoComprobante === null) {
            $this->facturas->update($facturaId, [
                'estado'        => Factura_model::ESTADO_ERROR,
                'error_mensaje' => "Tipo de factura desconocido: \"{$factura['tipo_factura']}\".",
            ]);
            return;
        }

        [$docTipo, $docNro] = Factura_model::documentoReceptor($factura);

        $body = [
            'punto_venta'      => (int) $credencial['punto_venta'],
            'tipo_comprobante' => $tipoComprobante,
            'concepto'         => 1, // Productos
            'cliente_doc_tipo' => $docTipo, // 80 CUIT, 96 DNI, 99 Consumidor Final
            'cliente_doc_nro'  => $docNro,
            'moneda'           => 'PES',
            'cotizacion'       => 1,
        ] + $this->iva->importesParaApi($factura['tipo_factura'], (float) $factura['importe_total'])
          + Factura_model::condicionIvaParaApi($factura);

        try {
            // Idempotency-Key estable por venta e intento: un reintento nunca duplica el comprobante.
            $key = self::idempotencyKey('venta-' . $factura['venta_id'], (int) $factura['intento']);
            $respuesta = $this->client->emitirComprobante($credencial['api_key'], $key, $body);
        } catch (\Throwable $e) {
            log_message('error', "Error de red emitiendo la factura #{$facturaId}: " . $e->getMessage());
            $this->facturas->update($facturaId, [
                'estado'        => Factura_model::ESTADO_ERROR,
                'error_mensaje' => 'Error de comunicación con el servicio de facturación. Podés reintentar.',
            ]);
            return;
        }

        $this->aplicarRespuestaComun($this->facturas, $facturaId, $respuesta, (int) $credencial['punto_venta']);
        $this->sincronizarVenta($facturaId);
    }

    /** Aplica el resultado que llega por el webhook de comprobantes (un 202 que se resolvió). */
    public function aplicarResultadoWebhook(array $factura, array $payload): void
    {
        $credencial = (new CredencialFacturacion_model())->find(1);
        $this->aplicarResultadoAsincrono($this->facturas, $factura, $payload, (int) ($credencial['punto_venta'] ?? 0));
        $this->sincronizarVenta((int) $factura['id']);
    }

    private function consultarPendiente(array $factura, array $credencial): void
    {
        try {
            $respuesta = $this->client->consultarComprobante($credencial['api_key'], $factura['comprobante_externo_id']);
        } catch (\Throwable $e) {
            log_message('error', "Error de red consultando la factura #{$factura['id']}: " . $e->getMessage());
            return; // sigue pendiente; el webhook la va a resolver
        }

        if ($respuesta->status === 200) {
            $this->aplicarResultadoWebhook($factura, $respuesta->json);
        }
    }

    /** La venta pasa a "Facturada" cuando su factura queda aprobada. */
    private function sincronizarVenta(int $facturaId): void
    {
        $factura = $this->facturas->find($facturaId);
        if (!$factura || !Factura_model::estaAprobada($factura) || !$factura['venta_id']) {
            return;
        }

        $ventas = new Cabecera_model();
        $venta = $ventas->find($factura['venta_id']);
        if ($venta && in_array($venta['estado'], ['Sin_Facturar', 'Error_factura'], true)) {
            $ventas->update($venta['id'], ['estado' => 'Facturada']);
        }
    }
}
