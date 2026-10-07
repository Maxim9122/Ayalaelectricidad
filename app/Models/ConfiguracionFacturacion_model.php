<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Configuración de facturación del negocio. Una sola fila (id = 1).
 */
class ConfiguracionFacturacion_model extends Model
{
    public const RESPONSABLE_INSCRIPTO = 'responsable_inscripto';
    public const MONOTRIBUTISTA = 'monotributista';

    protected $table = 'configuracion_facturacion';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'id',
        'razon_social', 'cuit', 'email_contacto', 'domicilio', 'ingresos_brutos', 'inicio_actividades',
        'condicion_fiscal', 'factura_habilitada', 'comprobante_predeterminado', 'formato_comprobante',
        'codigo_autorizacion_hash', 'monto_identificar_consumidor',
    ];

    /** Devuelve la fila única, creándola si todavía no existe. */
    public function obtener(): array
    {
        $config = $this->find(1);

        if (!$config) {
            $this->insert([
                'id' => 1,
                'factura_habilitada' => 1,
                'comprobante_predeterminado' => 'remito',
                'formato_comprobante' => 'ticket',
            ]);
            $config = $this->find(1);
        }

        return $config;
    }

    public static function puedeFacturar(array $config): bool
    {
        return in_array($config['condicion_fiscal'] ?? null, [self::RESPONSABLE_INSCRIPTO, self::MONOTRIBUTISTA], true);
    }

    /** Puede facturar y además no está pausada la facturación. */
    public static function facturacionHabilitada(array $config): bool
    {
        return self::puedeFacturar($config) && (int) ($config['factura_habilitada'] ?? 0) === 1;
    }

    public static function esResponsableInscripto(array $config): bool
    {
        return ($config['condicion_fiscal'] ?? null) === self::RESPONSABLE_INSCRIPTO;
    }

    public static function usaFormatoA4(array $config): bool
    {
        return ($config['formato_comprobante'] ?? null) === 'a4';
    }

    /**
     * Letras de factura que puede emitir el negocio según su condición fiscal.
     * Sin FACTURACION_API_URL no hay facturación posible: todo va con Remito.
     */
    public static function letrasPermitidas(array $config): array
    {
        if (!self::facturacionHabilitada($config) || trim((string) config('Facturacion')->url) === '') {
            return [];
        }

        return self::esResponsableInscripto($config) ? ['A', 'B'] : ['C'];
    }

    /**
     * Comprobante que viene preseleccionado al cobrar, validado contra el estado actual
     * (un valor guardado que dejó de ser válido nunca rompe el formulario de cobro).
     */
    public static function comprobantePredeterminadoEfectivo(array $config): string
    {
        $predeterminado = $config['comprobante_predeterminado'] ?? 'remito';

        if (in_array($predeterminado, self::letrasPermitidas($config), true)) {
            return $predeterminado;
        }

        return 'remito';
    }

    /**
     * Código de autorización para cancelar/modificar ventas cobradas y anular facturas.
     * Es uno solo, compartido, y se guarda como hash: solo se puede verificar o reemplazar.
     */
    public function verificarCodigoAutorizacion(?string $codigo): bool
    {
        $hash = $this->obtener()['codigo_autorizacion_hash'] ?? null;

        return $hash && $codigo !== null && $codigo !== '' && password_verify($codigo, $hash);
    }

    /** Desde este importe ARCA exige identificar al consumidor final (DNI). */
    public static function exigeIdentificarConsumidor(array $config, float $importe): bool
    {
        $tope = (float) ($config['monto_identificar_consumidor'] ?? 0);

        return $tope > 0 && $importe >= $tope;
    }
}
