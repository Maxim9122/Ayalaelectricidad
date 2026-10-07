<?php

namespace App\Libraries\Facturacion;

/**
 * ⚠️ Asume alícuota única 21% para todo. Si se venden productos exentos o con otra
 * alícuota, esto no alcanza — hay que discriminar por ítem.
 */
class CalculadoraIva
{
    private const ID_ALICUOTA_GENERAL = 5; // 21%

    /** Desarma un total que YA incluye IVA. */
    public function calcular(float $importeTotal): array
    {
        $importeNeto = round($importeTotal / 1.21, 2);
        $importeIva = round($importeTotal - $importeNeto, 2);

        return ['importe_neto' => $importeNeto, 'importe_iva' => $importeIva, 'importe_total' => round($importeTotal, 2)];
    }

    public function detalleParaComprobante(float $importeTotal): array
    {
        $c = $this->calcular($importeTotal);

        return [['Id' => self::ID_ALICUOTA_GENERAL, 'BaseImp' => $c['importe_neto'], 'Importe' => $c['importe_iva']]];
    }

    /**
     * Un Monotributista NO discrimina IVA ante AFIP (va incluido en la cuota fija).
     * Factura C: neto = total, iva = 0, sin detalle.
     */
    public function sinDiscriminar(float $importeTotal): array
    {
        return ['importe_neto' => round($importeTotal, 2), 'importe_iva' => 0.0, 'importe_total' => round($importeTotal, 2)];
    }

    /** Arma los importes del body para la API según la letra del comprobante. */
    public function importesParaApi(string $letra, float $importeTotal): array
    {
        $esMonotributo = $letra === 'C';
        $calculo = $esMonotributo ? $this->sinDiscriminar($importeTotal) : $this->calcular($importeTotal);

        return [
            'importe_neto'  => $calculo['importe_neto'],
            'importe_iva'   => $calculo['importe_iva'],
            'importe_total' => $calculo['importe_total'],
            'iva_detalle'   => $esMonotributo ? [] : $this->detalleParaComprobante($importeTotal),
        ];
    }
}
