<?php

namespace App\Models;

use CodeIgniter\Model;

class Factura_model extends Model
{
    public const ESTADO_PENDIENTE = 'pendiente';
    public const ESTADO_PENDIENTE_AFIP = 'pendiente_afip'; // esperando que se resuelva un 202 (async)
    public const ESTADO_APROBADA = 'aprobada';
    public const ESTADO_RECHAZADA = 'rechazada';
    public const ESTADO_ERROR = 'error'; // error de ESTE lado (red, config, etc.) — no es rechazo de AFIP

    protected $table = 'facturas';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'venta_id', 'cliente_id', 'cliente_nombre', 'cliente_cuit', 'cliente_dni', 'cliente_condicion_iva', 'tipo_factura', 'importe_total',
        'numero_comprobante', 'comprobante_externo_id', 'cae', 'cae_vencimiento', 'estado', 'error_mensaje', 'intento',
    ];

    public static function fallo(array $factura): bool
    {
        return in_array($factura['estado'], [self::ESTADO_RECHAZADA, self::ESTADO_ERROR], true);
    }

    public static function estaAprobada(array $factura): bool
    {
        return $factura['estado'] === self::ESTADO_APROBADA && !empty($factura['cae']);
    }

    /**
     * Campo `cliente_condicion_iva` para la API. Si la factura no lo tiene (facturas viejas),
     * no se manda y la API usa su default (A: Responsable Inscripto, B/C: Consumidor Final).
     */
    public static function condicionIvaParaApi(array $factura): array
    {
        return empty($factura['cliente_condicion_iva'])
            ? []
            : ['cliente_condicion_iva' => (int) $factura['cliente_condicion_iva']];
    }

    /**
     * Cómo se identifica al receptor ante AFIP: [tipo de documento, número].
     * 80 = CUIT, 96 = DNI, 99 = Consumidor Final sin identificar (número null).
     */
    public static function documentoReceptor(array $factura): array
    {
        if (!empty($factura['cliente_cuit'])) {
            return [80, $factura['cliente_cuit']];
        }
        if (!empty($factura['cliente_dni'])) {
            return [96, $factura['cliente_dni']];
        }

        return [99, null];
    }
}
