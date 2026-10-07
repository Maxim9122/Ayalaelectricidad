<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Conexión con la API externa de facturación electrónica (AFIP/ARCA).
 *
 * Se configura desde el .env:
 *   FACTURACION_API_URL=
 *   FACTURACION_PLATFORM_KEY=
 *   FACTURACION_ONBOARDING_WEBHOOK_SECRET=
 *
 * Mientras FACTURACION_API_URL esté vacía, el módulo no hace nada (no factura, no rompe).
 */
class Facturacion extends BaseConfig
{
    /** URL base de la API de facturación. */
    public $url = '';

    /** Platform key (fpk_...): solo server-to-server, nunca al navegador. */
    public $platformKey = '';

    /** Secreto para verificar la firma del webhook de onboarding. */
    public $onboardingWebhookSecret = '';

    /** Timeout en segundos para las llamadas a la API. */
    public $timeout = 15;

    public function __construct()
    {
        parent::__construct();

        $this->url                     = $this->url ?: (string) env('FACTURACION_API_URL', '');
        $this->platformKey             = $this->platformKey ?: (string) env('FACTURACION_PLATFORM_KEY', '');
        $this->onboardingWebhookSecret = $this->onboardingWebhookSecret ?: (string) env('FACTURACION_ONBOARDING_WEBHOOK_SECRET', '');
    }
}
