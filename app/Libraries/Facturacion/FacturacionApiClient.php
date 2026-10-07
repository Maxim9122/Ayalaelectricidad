<?php

namespace App\Libraries\Facturacion;

use Config\Facturacion;
use Config\Services;

/**
 * Capa HTTP pura contra la API externa de facturación. Sin lógica de negocio.
 */
class FacturacionApiClient
{
    /** @var Facturacion */
    private $config;

    public function __construct(?Facturacion $config = null)
    {
        $this->config = $config ?? config('Facturacion');
    }

    public function configurada(): bool
    {
        return trim($this->config->url) !== '';
    }

    /**
     * Paso 1: dar de alta la empresa y pedir el link de onboarding.
     * Devuelve {empresa_id, onboarding_id, ambiente, onboarding_url, expira_en}.
     */
    public function altaEmpresa(array $datos): array
    {
        $respuesta = $this->enviar('POST', '/api/platform/v1/empresas', $this->config->platformKey, $datos);

        if ($respuesta->status === 409) {
            throw new CuitYaRegistradoException('Este CUIT ya está registrado con otra integración o cargado a mano del lado de la API de facturación. Contactá al administrador de esa API para resolverlo.');
        }

        $this->exigirExito($respuesta);

        return $respuesta->json;
    }

    /** Corrige razón social / CUIT / email antes de que se suba el certificado. */
    public function corregirEmpresa($empresaExternaId, array $datos): array
    {
        $respuesta = $this->enviar('PATCH', '/api/platform/v1/empresas/' . rawurlencode((string) $empresaExternaId), $this->config->platformKey, $datos);

        if ($respuesta->status === 409) {
            $mensaje = $respuesta->mensaje();
            // Los dos casos de 409 se distinguen SOLO por el texto del mensaje.
            if (strpos(mb_strtolower($mensaje), 'certificado') !== false) {
                throw new CertificadoYaValidadoException($mensaje ?: 'Esta empresa ya tiene un certificado AFIP validado — no se puede corregir por acá.');
            }
            throw new CuitYaRegistradoException($mensaje ?: 'Ese CUIT ya pertenece a otra empresa registrada.');
        }
        if ($respuesta->status === 404) {
            throw new \RuntimeException('No se encontró esa empresa en la API de facturación.');
        }
        if ($respuesta->status === 422) {
            throw new \RuntimeException($respuesta->mensaje('Datos inválidos.'));
        }

        $this->exigirExito($respuesta);

        return $respuesta->json;
    }

    /**
     * Paso 2.5: sumar un punto de venta a una empresa que ya tiene certificado validado.
     * No repite el onboarding ni toca la API Key. 201 nuevo / 200 ya existía (idempotente).
     */
    public function agregarPuntoVenta($empresaExternaId, string $ambiente, int $numero): array
    {
        $respuesta = $this->enviar('POST', '/api/platform/v1/empresas/' . rawurlencode((string) $empresaExternaId) . '/puntos-venta', $this->config->platformKey, [
            'ambiente' => $ambiente,
            'numero'   => $numero,
        ]);

        if ($respuesta->status === 409) {
            throw new \RuntimeException($respuesta->mensaje('La empresa todavía no tiene un certificado validado para ese ambiente: primero hay que completar el alta.'));
        }
        if ($respuesta->status === 404) {
            throw new \RuntimeException('No se encontró esta empresa en el servicio de facturación.');
        }
        if ($respuesta->status === 422) {
            throw new \RuntimeException($respuesta->mensaje('Datos inválidos.'));
        }

        $this->exigirExito($respuesta);

        return $respuesta->json;
    }

    /** Verifica la API Key: devuelve {empresa, ambiente, api_key} o el error de la API. */
    public function ping(string $apiKey): RespuestaApi
    {
        return $this->enviar('GET', '/api/v1/ping', $apiKey);
    }

    /** Paso 3: emitir. NO tira excepción por 4xx/5xx — el que llama mapea el status. */
    public function emitirComprobante(string $apiKey, string $idempotencyKey, array $body): RespuestaApi
    {
        return $this->enviar('POST', '/api/v1/comprobantes', $apiKey, $body, ['Idempotency-Key' => $idempotencyKey]);
    }

    public function consultarComprobante(string $apiKey, $comprobanteId): RespuestaApi
    {
        return $this->enviar('GET', '/api/v1/comprobantes/' . rawurlencode((string) $comprobanteId), $apiKey);
    }

    private function enviar(string $metodo, string $path, string $token, ?array $body = null, array $headers = []): RespuestaApi
    {
        if (!$this->configurada()) {
            throw new \RuntimeException('FACTURACION_API_URL no está configurada.');
        }

        // Instancia nueva por llamada: el cliente compartido de CI4 arrastra headers entre requests.
        $client = Services::curlrequest([
            'timeout'     => $this->config->timeout,
            'http_errors' => false,
        ], null, null, false);

        $opciones = [
            'headers' => array_merge([
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json',
            ], $headers),
        ];
        if ($body !== null) {
            $opciones['json'] = $body;
        }

        $response = $client->request($metodo, rtrim($this->config->url, '/') . $path, $opciones);
        $json = json_decode((string) $response->getBody(), true);

        return new RespuestaApi($response->getStatusCode(), is_array($json) ? $json : []);
    }

    private function exigirExito(RespuestaApi $respuesta): void
    {
        if (!$respuesta->exitosa()) {
            throw new \RuntimeException("La API de facturación respondió HTTP {$respuesta->status}: " . $respuesta->mensaje('sin detalle'));
        }
    }
}
