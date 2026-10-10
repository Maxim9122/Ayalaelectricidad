<?php

namespace App\Libraries\Facturacion;

/**
 * Condición frente al IVA del receptor (tabla de ARCA, RG 5616 — FEParamGetCondicionIvaReceptor).
 * Se envía a la API como `cliente_condicion_iva`. Desde el 30/11/2026 ARCA la exige siempre.
 */
class CondicionIva
{
    public const RESPONSABLE_INSCRIPTO = 1;
    public const CONSUMIDOR_FINAL = 5;

    public const OPCIONES = [
        1  => 'IVA Responsable Inscripto',
        4  => 'IVA Sujeto Exento',
        5  => 'Consumidor Final',
        6  => 'Responsable Monotributo',
        7  => 'Sujeto No Categorizado',
        8  => 'Proveedor del Exterior',
        9  => 'Cliente del Exterior',
        10 => 'IVA Liberado - Ley N° 19.640',
        13 => 'Monotributista Social',
        15 => 'IVA No Alcanzado',
        16 => 'Monotributo Trabajador Independiente Promovido',
    ];

    /** A quiénes se les puede emitir Factura A (RG 5616: Responsables Inscriptos y Monotributistas). */
    public const PERMITEN_FACTURA_A = [1, 6, 13, 16];

    public static function valida($codigo): bool
    {
        return $codigo !== null && $codigo !== '' && isset(self::OPCIONES[(int) $codigo]);
    }

    public static function nombre($codigo): ?string
    {
        return self::valida($codigo) ? self::OPCIONES[(int) $codigo] : null;
    }

    /** Normaliza lo que llega de un formulario: código válido o null. */
    public static function desdeFormulario($valor): ?int
    {
        return self::valida($valor) ? (int) $valor : null;
    }
}
