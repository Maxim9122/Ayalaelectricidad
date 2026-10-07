<?php

namespace App\Controllers;

use App\Libraries\Facturacion\EmisionComprobanteService;
use App\Libraries\Facturacion\EmisionNotaCreditoService;
use App\Models\Cabecera_model;
use App\Models\CredencialFacturacion_model;
use App\Models\Factura_model;
use App\Models\NotaCredito_model;
use CodeIgniter\Controller;

/**
 * Webhooks que nos manda la API de facturación. Públicos (sin sesión):
 * la autenticidad la garantiza la firma HMAC del header X-Signature.
 */
class FacturacionWebhook_controller extends Controller
{
    /**
     * Paso 2: la API avisa que el dueño terminó de subir el certificado AFIP.
     * Trae la api_key y el secreto del webhook de comprobantes. También llega al renovar.
     */
    public function onboarding()
    {
        $secret = config('Facturacion')->onboardingWebhookSecret;
        if (!$secret || !$this->firmaValida($secret)) {
            return $this->response->setStatusCode(401);
        }

        $payload = json_decode((string) $this->request->getBody(), true);
        $faltantes = [];
        foreach (['onboarding_id', 'empresa_id', 'cuit', 'ambiente', 'punto_venta', 'api_key'] as $campo) {
            if (!is_array($payload) || !isset($payload[$campo]) || $payload[$campo] === '') {
                $faltantes[] = $campo;
            }
        }
        if ($faltantes || !ctype_digit((string) $payload['punto_venta'])) {
            return $this->response->setStatusCode(422)->setJSON(['message' => 'Payload inválido.', 'faltantes' => $faltantes]);
        }

        // Una sola empresa: siempre es la fila única. Al renovar, la key nueva reemplaza a la vieja.
        (new CredencialFacturacion_model())->guardar([
            'ambiente'           => (string) $payload['ambiente'],
            'punto_venta'        => (int) $payload['punto_venta'],
            'api_key'            => (string) $payload['api_key'],
            'webhook_secret'     => isset($payload['comprobantes_webhook_secret']) ? (string) $payload['comprobantes_webhook_secret'] : null,
            'estado'             => CredencialFacturacion_model::ESTADO_ACTIVA,
            'onboarding_id'      => (string) $payload['onboarding_id'],
            'empresa_externa_id' => $payload['empresa_id'],
        ]);

        log_message('info', !empty($payload['renovacion'])
            ? 'Facturación electrónica: certificado renovado.'
            : 'Facturación electrónica: onboarding completado, credencial activa.');

        return $this->response->setStatusCode(204);
    }

    /**
     * Avisa cuando se resuelve un comprobante que había quedado pendiente (202).
     * La API reintenta con backoff si no respondemos 2xx: responder rápido y siempre 204,
     * salvo que la firma falle.
     */
    public function comprobantes()
    {
        $payload = json_decode((string) $this->request->getBody(), true);
        if (!is_array($payload)) {
            $payload = [];
        }

        $comprobanteId = $payload['comprobante_id'] ?? $payload['id'] ?? null;
        $idempotencyKey = isset($payload['idempotency_key']) ? (string) $payload['idempotency_key'] : '';

        $factura = $this->localizarFactura($comprobanteId, $idempotencyKey);
        $nota = $factura ? null : $this->localizarNotaCredito($comprobanteId, $idempotencyKey);

        if (!$factura && !$nota) {
            log_message('warning', 'Webhook de comprobantes: no se pudo identificar el comprobante. ' . json_encode($payload));
            return $this->response->setStatusCode(204);
        }

        // La firma se verifica con el webhook_secret de la credencial (distinto del de onboarding).
        $credencial = (new CredencialFacturacion_model())->obtener();
        $secret = $credencial['webhook_secret'] ?? null;
        if (!$secret || !$this->firmaValida($secret)) {
            return $this->response->setStatusCode(401);
        }

        if ($factura) {
            (new EmisionComprobanteService())->aplicarResultadoWebhook($factura, $payload);
        } else {
            (new EmisionNotaCreditoService())->aplicarResultadoWebhook($nota, $payload);
        }

        return $this->response->setStatusCode(204);
    }

    private function localizarFactura($comprobanteExternoId, string $idempotencyKey): ?array
    {
        $facturas = new Factura_model();

        if ($comprobanteExternoId !== null && $comprobanteExternoId !== '') {
            $factura = $facturas->where('comprobante_externo_id', (string) $comprobanteExternoId)->first();
            if ($factura) {
                return $factura;
            }
        }

        // "venta-123" o "venta-123-2" (reintento después de un rechazo)
        if (preg_match('/^venta-(\d+)(-\d+)?$/', $idempotencyKey, $m)) {
            $venta = (new Cabecera_model())->find((int) $m[1]);
            if ($venta && $venta['factura_id']) {
                return $facturas->find($venta['factura_id']);
            }
        }

        return null;
    }

    private function localizarNotaCredito($comprobanteExternoId, string $idempotencyKey): ?array
    {
        $notas = new NotaCredito_model();

        if ($comprobanteExternoId !== null && $comprobanteExternoId !== '') {
            $nota = $notas->where('comprobante_externo_id', (string) $comprobanteExternoId)->first();
            if ($nota) {
                return $nota;
            }
        }

        if (preg_match('/^nc-(\d+)(-\d+)?$/', $idempotencyKey, $m)) {
            return $notas->find((int) $m[1]);
        }

        return null;
    }

    private function firmaValida(string $secret): bool
    {
        $firmaEsperada = hash_hmac('sha256', (string) $this->request->getBody(), $secret);
        $firmaRecibida = $this->request->getHeaderLine('X-Signature');

        return $firmaRecibida !== '' && hash_equals($firmaEsperada, $firmaRecibida);
    }
}
