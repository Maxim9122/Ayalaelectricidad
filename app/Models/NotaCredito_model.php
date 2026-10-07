<?php

namespace App\Models;

use CodeIgniter\Model;

class NotaCredito_model extends Model
{
    public const ESTADO_PENDIENTE = 'pendiente';
    public const ESTADO_PENDIENTE_AFIP = 'pendiente_afip';
    public const ESTADO_APROBADA = 'aprobada';
    public const ESTADO_RECHAZADA = 'rechazada';
    public const ESTADO_ERROR = 'error';

    protected $table = 'notas_credito';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'factura_id', 'creado_por', 'importe_acreditado', 'motivo',
        'numero_comprobante', 'comprobante_externo_id', 'cae', 'cae_vencimiento', 'estado', 'error_mensaje', 'intento',
    ];

    public static function fallo(array $notaCredito): bool
    {
        return in_array($notaCredito['estado'], [self::ESTADO_RECHAZADA, self::ESTADO_ERROR], true);
    }

    public static function estaAprobada(array $notaCredito): bool
    {
        return $notaCredito['estado'] === self::ESTADO_APROBADA && !empty($notaCredito['cae']);
    }
}
