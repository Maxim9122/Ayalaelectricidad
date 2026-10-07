<?php

namespace App\Libraries\Facturacion;

/** Status HTTP + cuerpo JSON decodificado de una llamada a la API de facturación. */
class RespuestaApi
{
    /** @var int */
    public $status;

    /** @var array */
    public $json;

    public function __construct(int $status, array $json)
    {
        $this->status = $status;
        $this->json = $json;
    }

    public function exitosa(): bool
    {
        return $this->status >= 200 && $this->status < 300;
    }

    public function mensaje(string $porDefecto = ''): string
    {
        return (string) ($this->json['message'] ?? $porDefecto);
    }
}
