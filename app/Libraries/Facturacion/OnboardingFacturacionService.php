<?php

namespace App\Libraries\Facturacion;

use App\Models\CredencialFacturacion_model;

/**
 * Paso 1: dar de alta la empresa en la API y obtener el link de onboarding
 * (donde el dueño sube su certificado AFIP .crt/.key).
 *
 * Esto es "dar de alta" Y "renovar certificado" a la vez: volver a llamar a iniciar()
 * con la credencial ya activa es la forma soportada de renovar. La API Key anterior
 * sigue funcionando hasta que se completa el onboarding nuevo.
 */
class OnboardingFacturacionService
{
    /** @var FacturacionApiClient */
    private $client;

    public function __construct(?FacturacionApiClient $client = null)
    {
        $this->client = $client ?? new FacturacionApiClient();
    }

    /**
     * Devuelve el onboarding_url. Vence en 30 minutos y es de un solo uso:
     * pedirlo y redirigir en el mismo request, nunca guardarlo.
     */
    public function iniciar(string $razonSocial, string $cuit, string $emailContacto, string $ambiente): string
    {
        $respuesta = $this->client->altaEmpresa([
            'razon_social'   => $razonSocial,
            'cuit'           => $cuit,
            'email_contacto' => $emailContacto,
            'ambiente'       => $ambiente,
            'webhook_url'    => base_url('api/webhooks/facturacion/comprobantes'),
        ]);

        $credenciales = new CredencialFacturacion_model();
        $actual = $credenciales->find(1);

        $datos = [
            'empresa_externa_id' => $respuesta['empresa_id'] ?? null,
            'onboarding_id'      => isset($respuesta['onboarding_id']) ? (string) $respuesta['onboarding_id'] : null,
        ];
        // En una renovación la credencial vieja sigue activa hasta que llegue el webhook.
        if (!$actual || $actual['estado'] !== CredencialFacturacion_model::ESTADO_ACTIVA) {
            $datos['ambiente'] = $ambiente;
            $datos['estado'] = CredencialFacturacion_model::ESTADO_PENDIENTE_ONBOARDING;
        }
        $credenciales->guardar($datos);

        if (empty($respuesta['onboarding_url'])) {
            throw new \RuntimeException('La API de facturación no devolvió el link de onboarding.');
        }

        return $respuesta['onboarding_url'];
    }

    public function corregirDatos(array $credencial, array $datos): void
    {
        if (empty($credencial['empresa_externa_id'])) {
            throw new \RuntimeException('Todavía no se inició el alta en la API de facturación.');
        }

        $this->client->corregirEmpresa($credencial['empresa_externa_id'], $datos);
    }
}
