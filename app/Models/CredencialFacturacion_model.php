<?php

namespace App\Models;

use CodeIgniter\Model;
use Config\Services;

/**
 * Credenciales de la API de facturación. Una sola fila (id = 1).
 *
 * api_key y webhook_secret se guardan cifrados con encryption.key del .env;
 * usar guardar() / obtener() en lugar de insert()/find() directos.
 */
class CredencialFacturacion_model extends Model
{
    public const ESTADO_PENDIENTE_ONBOARDING = 'pendiente_onboarding';
    public const ESTADO_ACTIVA = 'activa';

    private const CAMPOS_CIFRADOS = ['api_key', 'webhook_secret'];

    protected $table = 'credencial_facturacion';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['id', 'ambiente', 'punto_venta', 'api_key', 'webhook_secret', 'estado', 'onboarding_id', 'empresa_externa_id'];

    /** Credencial con los secretos ya descifrados, o null si nunca se inició el alta. */
    public function obtener(): ?array
    {
        $credencial = $this->find(1);

        if (!$credencial) {
            return null;
        }

        foreach (self::CAMPOS_CIFRADOS as $campo) {
            $credencial[$campo] = $this->descifrar($credencial[$campo]);
        }

        return $credencial;
    }

    /** Crea o actualiza la fila única, cifrando los secretos que vengan en $datos. */
    public function guardar(array $datos): void
    {
        foreach (self::CAMPOS_CIFRADOS as $campo) {
            if (array_key_exists($campo, $datos)) {
                $datos[$campo] = $this->cifrar($datos[$campo]);
            }
        }

        if ($this->find(1)) {
            $this->update(1, $datos);
        } else {
            $this->insert(['id' => 1] + $datos + ['ambiente' => 'homologacion']);
        }
    }

    public static function estaActiva(?array $credencial): bool
    {
        return $credencial !== null
            && $credencial['estado'] === self::ESTADO_ACTIVA
            && !empty($credencial['api_key'])
            && $credencial['punto_venta'] !== null;
    }

    private function cifrar(?string $valor): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        return base64_encode(Services::encrypter()->encrypt($valor));
    }

    private function descifrar(?string $valor): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        try {
            return Services::encrypter()->decrypt(base64_decode($valor));
        } catch (\Throwable $e) {
            log_message('error', 'No se pudo descifrar la credencial de facturación (¿cambió encryption.key?): ' . $e->getMessage());
            return null;
        }
    }
}
