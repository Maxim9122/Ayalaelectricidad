<?php

namespace App\Libraries\Facturacion;

use CodeIgniter\Model;

/**
 * Mapea la respuesta de la API a estado. Compartido por Factura y Nota de Crédito:
 * ambas emiten contra el mismo endpoint, guardan los mismos campos y usan los mismos
 * valores de estado (pendiente_afip, aprobada, rechazada, error).
 *
 * Idempotencia: la API devuelve SIEMPRE el mismo comprobante para la misma Idempotency-Key,
 * incluso si quedó rechazado. Por eso, cuando un comprobante termina en rechazo o error
 * definitivo del lado de la API, se incrementa `intento`: el próximo reintento sale con
 * una key nueva. Los errores de red/HTTP inesperados NO lo incrementan (no se sabe si la
 * API llegó a crear el comprobante, y reusar la key evita duplicarlo).
 */
trait AplicaRespuestaComprobante
{
    /** Idempotency-Key estable por intento: "venta-123", y "venta-123-2" después de un rechazo. */
    private static function idempotencyKey(string $prefijo, int $intento): string
    {
        return $intento > 1 ? "{$prefijo}-{$intento}" : $prefijo;
    }

    /** Respuesta síncrona de POST /api/v1/comprobantes. */
    private function aplicarRespuestaComun(Model $modelo, int $id, RespuestaApi $respuesta, int $puntoVenta): void
    {
        $json = $respuesta->json;

        switch ($respuesta->status) {
            case 200:
            case 201:
                $modelo->update($id, [
                    'estado'                 => 'aprobada',
                    'cae'                    => $json['cae'] ?? null,
                    'cae_vencimiento'        => self::normalizarFecha($json['cae_vencimiento'] ?? null),
                    'numero_comprobante'     => self::numeroComprobante($json, $puntoVenta),
                    'comprobante_externo_id' => self::idExterno($json),
                    'error_mensaje'          => null,
                ]);
                break;
            case 202:
                $modelo->update($id, [
                    'estado'                 => 'pendiente_afip',
                    'comprobante_externo_id' => self::idExterno($json),
                    'error_mensaje'          => null,
                ]);
                break;
            case 422:
                // Con "id" es un rechazo de AFIP (el comprobante quedó creado con esa key);
                // sin "id" es un error de validación del body (no se creó nada).
                $datos = [
                    'estado'        => 'rechazada',
                    'error_mensaje' => ($json['error_mensaje'] ?? null) ?: ($json['message'] ?? null) ?: 'Rechazado por AFIP.',
                ];
                if (isset($json['id'])) {
                    $datos['intento'] = $this->intentoSiguiente($modelo, $id);
                }
                $modelo->update($id, $datos);
                break;
            case 401:
                $modelo->update($id, [
                    'estado'        => 'error',
                    'error_mensaje' => 'La credencial de facturación fue rechazada. Contactá a soporte.',
                ]);
                break;
            default:
                $modelo->update($id, [
                    'estado'        => 'error',
                    'error_mensaje' => "Respuesta inesperada del servicio de facturación (HTTP {$respuesta->status}).",
                ]);
        }
    }

    /**
     * Resultado asíncrono (webhook de comprobantes o GET de consulta) de un 202 pendiente.
     * Estados de la API: pendiente | aprobado | rechazado | error (definitivo tras reintentar).
     * Devuelve true si el comprobante quedó aprobado.
     */
    private function aplicarResultadoAsincrono(Model $modelo, array $comprobante, array $payload, int $puntoVenta): bool
    {
        $estado = $payload['estado'] ?? null;

        if ($estado === 'aprobado' || $estado === 'aprobada' || !empty($payload['cae'])) {
            $modelo->update($comprobante['id'], [
                'estado'                 => 'aprobada',
                'cae'                    => $payload['cae'] ?? $comprobante['cae'],
                'cae_vencimiento'        => self::normalizarFecha($payload['cae_vencimiento'] ?? null) ?? $comprobante['cae_vencimiento'],
                'numero_comprobante'     => self::numeroComprobante($payload, $puntoVenta) ?? $comprobante['numero_comprobante'],
                'comprobante_externo_id' => self::idExterno($payload) ?? $comprobante['comprobante_externo_id'],
                'error_mensaje'          => null,
            ]);
            return true;
        }

        if (in_array($estado, ['rechazado', 'rechazada', 'error'], true)) {
            $modelo->update($comprobante['id'], [
                'estado'        => $estado === 'error' ? 'error' : 'rechazada',
                'error_mensaje' => ($payload['error_mensaje'] ?? null) ?: ($estado === 'error' ? 'El servicio de facturación no pudo emitir el comprobante.' : 'Rechazado por AFIP.'),
                'intento'       => (int) ($comprobante['intento'] ?? 1) + 1,
            ]);
            return false;
        }

        if ($estado === 'pendiente') {
            return false; // sigue en curso del lado de la API
        }

        log_message('warning', 'Resultado de comprobante con estado no reconocido. {modelo} id {id}: {payload}', [
            'modelo'  => get_class($modelo),
            'id'      => $comprobante['id'],
            'payload' => json_encode($payload),
        ]);
        return false;
    }

    private function intentoSiguiente(Model $modelo, int $id): int
    {
        $actual = $modelo->find($id);

        return (int) ($actual['intento'] ?? 1) + 1;
    }

    private static function idExterno(array $json): ?string
    {
        $id = $json['id'] ?? $json['comprobante_id'] ?? null;

        return $id === null ? null : (string) $id;
    }

    /**
     * La API devuelve punto_venta y numero por separado; se guarda como "PPPP-NNNNNNNN".
     * Si el punto de venta no viene en la respuesta, se usa el de la credencial.
     */
    private static function numeroComprobante(array $json, int $puntoVenta): ?string
    {
        if (!empty($json['numero_comprobante'])) {
            return (string) $json['numero_comprobante'];
        }
        if (!isset($json['numero']) || $json['numero'] === '') {
            return null;
        }

        return sprintf('%04d-%08d', (int) ($json['punto_venta'] ?? $puntoVenta), (int) $json['numero']);
    }

    /** Acepta "2026-10-17", "20261017" o un datetime ISO; devuelve Y-m-d o null. */
    private static function normalizarFecha($valor): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        $valor = (string) $valor;
        if (preg_match('/^\d{8}$/', $valor)) {
            return substr($valor, 0, 4) . '-' . substr($valor, 4, 2) . '-' . substr($valor, 6, 2);
        }

        $timestamp = strtotime($valor);

        return $timestamp ? date('Y-m-d', $timestamp) : null;
    }
}
