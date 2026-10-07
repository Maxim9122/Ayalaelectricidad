<?php

namespace App\Libraries\Facturacion;

use App\Models\CredencialFacturacion_model;
use App\Models\Factura_model;
use App\Models\NotaCredito_model;

/**
 * Paso 3: emitir la Nota de Crédito (100% de la factura). Mismo endpoint y mismo
 * contrato que la factura: emitir() NUNCA tira excepción.
 */
class EmisionNotaCreditoService
{
    use AplicaRespuestaComprobante;

    // La letra tiene que coincidir con la de la factura: AFIP rechaza una NC A contra una Factura B.
    private const MAPA_TIPO_NOTA_CREDITO = ['A' => 3, 'B' => 8, 'C' => 13];

    /** @var FacturacionApiClient */
    private $client;

    /** @var CalculadoraIva */
    private $iva;

    /** @var NotaCredito_model */
    private $notas;

    public function __construct(?FacturacionApiClient $client = null, ?CalculadoraIva $iva = null)
    {
        $this->client = $client ?? new FacturacionApiClient();
        $this->iva = $iva ?? new CalculadoraIva();
        $this->notas = new NotaCredito_model();
    }

    public function emitir(int $notaCreditoId): void
    {
        try {
            $this->emitirSinCapturar($notaCreditoId);
        } catch (\Throwable $e) {
            log_message('error', "Error inesperado emitiendo la nota de crédito #{$notaCreditoId}: " . $e->getMessage());
            $this->marcarError($notaCreditoId, 'Error inesperado al emitir la nota de crédito. Podés reintentar.');
        }
    }

    private function emitirSinCapturar(int $notaCreditoId): void
    {
        $nota = $this->notas->find($notaCreditoId);
        if (!$nota || !empty($nota['cae'])) {
            return;
        }

        if (!$this->client->configurada()) {
            log_message('info', "Facturación no configurada: la nota de crédito #{$notaCreditoId} queda pendiente.");
            return;
        }

        $factura = (new Factura_model())->find($nota['factura_id']);
        $credencial = (new CredencialFacturacion_model())->obtener();

        if (!CredencialFacturacion_model::estaActiva($credencial)) {
            $this->marcarError($notaCreditoId, 'Todavía no se terminó de configurar la facturación electrónica (onboarding).');
            return;
        }

        if ($nota['estado'] === NotaCredito_model::ESTADO_PENDIENTE_AFIP && $nota['comprobante_externo_id']) {
            $this->consultarPendiente($nota, $credencial);
            return;
        }

        if (!$factura || empty($factura['comprobante_externo_id'])) {
            $this->marcarError($notaCreditoId, 'La factura original no tiene comprobante_externo_id — no se puede acreditar.');
            return;
        }
        // (int) truncaría en silencio un id no numérico a 0: mejor fallar acá con un error claro.
        if (!is_numeric($factura['comprobante_externo_id'])) {
            $this->marcarError($notaCreditoId, 'comprobante_externo_id no numérico — no se puede acreditar.');
            return;
        }

        $tipoComprobante = self::MAPA_TIPO_NOTA_CREDITO[$factura['tipo_factura']] ?? null;
        if ($tipoComprobante === null) {
            $this->marcarError($notaCreditoId, "Tipo de factura desconocido: \"{$factura['tipo_factura']}\".");
            return;
        }

        // Mismo receptor que la factura original (80 CUIT, 96 DNI, 99 Consumidor Final).
        [$docTipo, $docNro] = Factura_model::documentoReceptor($factura);

        $body = [
            'punto_venta'      => (int) $credencial['punto_venta'],
            'tipo_comprobante' => $tipoComprobante,
            'concepto'         => 1,
            'cliente_doc_tipo' => $docTipo,
            'cliente_doc_nro'  => $docNro,
            'moneda'           => 'PES',
            'cotizacion'       => 1,
        ] + $this->iva->importesParaApi($factura['tipo_factura'], (float) $nota['importe_acreditado']) + [
            // El id INTERNO que devolvió la API al emitir la factura — NO el número, NO el CAE.
            'comprobante_asociado_id' => (int) $factura['comprobante_externo_id'],
        ];

        try {
            $key = self::idempotencyKey('nc-' . $nota['id'], (int) $nota['intento']);
            $respuesta = $this->client->emitirComprobante($credencial['api_key'], $key, $body);
        } catch (\Throwable $e) {
            log_message('error', "Error de red emitiendo la nota de crédito #{$notaCreditoId}: " . $e->getMessage());
            $this->marcarError($notaCreditoId, 'Error de comunicación con el servicio de facturación. Podés reintentar.');
            return;
        }

        $this->aplicarRespuestaComun($this->notas, $notaCreditoId, $respuesta, (int) $credencial['punto_venta']);
    }

    public function aplicarResultadoWebhook(array $nota, array $payload): void
    {
        $credencial = (new CredencialFacturacion_model())->find(1);
        $this->aplicarResultadoAsincrono($this->notas, $nota, $payload, (int) ($credencial['punto_venta'] ?? 0));
    }

    private function consultarPendiente(array $nota, array $credencial): void
    {
        try {
            $respuesta = $this->client->consultarComprobante($credencial['api_key'], $nota['comprobante_externo_id']);
        } catch (\Throwable $e) {
            log_message('error', "Error de red consultando la nota de crédito #{$nota['id']}: " . $e->getMessage());
            return;
        }

        if ($respuesta->status === 200) {
            $this->aplicarResultadoWebhook($nota, $respuesta->json);
        }
    }

    private function marcarError(int $notaCreditoId, string $mensaje): void
    {
        $this->notas->update($notaCreditoId, ['estado' => NotaCredito_model::ESTADO_ERROR, 'error_mensaje' => $mensaje]);
    }
}
