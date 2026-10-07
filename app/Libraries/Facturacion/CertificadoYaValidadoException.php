<?php

namespace App\Libraries\Facturacion;

/** La empresa ya tiene un certificado AFIP validado: sus datos no se pueden corregir por API. */
class CertificadoYaValidadoException extends \RuntimeException
{
}
