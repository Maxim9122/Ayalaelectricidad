<?php

namespace App\Libraries\Facturacion;

use App\Models\Cabecera_model;
use App\Models\ConfiguracionFacturacion_model;
use App\Models\CredencialFacturacion_model;
use App\Models\Factura_model;
use App\Models\NotaCredito_model;
use App\Models\Productos_model;
use App\Models\VentaDetalle_model;
use App\Models\Vendedores_model;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

/**
 * PDF de una Factura o Nota de Crédito aprobada (con CAE), con el QR oficial de AFIP (RG 4892).
 * Formato ticket (80mm) o A4 según la configuración de facturación.
 */
class ComprobantePdf
{
    private const CODIGOS_FACTURA = ['A' => 1, 'B' => 6, 'C' => 11];
    private const CODIGOS_NOTA_CREDITO = ['A' => 3, 'B' => 8, 'C' => 13];

    /** Devuelve [nombreArchivo, contenidoPdf]. */
    public function factura(array $factura): array
    {
        return $this->generar($factura, null);
    }

    /** Devuelve [nombreArchivo, contenidoPdf]. */
    public function notaCredito(array $nota): array
    {
        $factura = (new Factura_model())->find($nota['factura_id']);

        return $this->generar($factura, $nota);
    }

    private function generar(array $factura, ?array $nota): array
    {
        $comprobante = $nota ?? $factura;
        $config = (new ConfiguracionFacturacion_model())->obtener();
        $credencial = (new CredencialFacturacion_model())->find(1);

        $venta = $factura['venta_id'] ? (new Cabecera_model())->find($factura['venta_id']) : null;
        $items = $venta ? $this->items((int) $venta['id']) : [];
        $vendedor = $venta ? (new Vendedores_model())->find($venta['id_usuario']) : null;

        $letra = $factura['tipo_factura'];
        $codigo = $nota ? self::CODIGOS_NOTA_CREDITO[$letra] : self::CODIGOS_FACTURA[$letra];
        $importe = (float) ($nota ? $nota['importe_acreditado'] : $factura['importe_total']);
        $partes = explode('-', (string) $comprobante['numero_comprobante']);
        $puntoVenta = count($partes) > 1 ? (int) $partes[0] : (int) ($credencial['punto_venta'] ?? 0);
        // created_at ya está en hora argentina (appTimezone), que es la fecha que usa AFIP.
        $fechaEmision = substr((string) $comprobante['created_at'], 0, 10);

        $datos = [
            'config'       => $config,
            'esA4'         => ConfiguracionFacturacion_model::usaFormatoA4($config),
            'titulo'       => $nota ? 'NOTA DE CRÉDITO' : 'FACTURA',
            'letra'        => $letra,
            'codigo'       => $codigo,
            'comprobante'  => $comprobante,
            'factura'      => $factura,
            'nota'         => $nota,
            'venta'        => $venta,
            'items'        => $nota ? [] : $items,
            'vendedor'     => $vendedor ? $vendedor['nombre'] : null,
            'importe'      => $importe,
            'iva'          => (new CalculadoraIva())->calcular($importe),
            'fechaEmision' => $fechaEmision,
            'qr'           => $this->qrAfip($config, $codigo, $puntoVenta, $comprobante, $fechaEmision, $importe, Factura_model::documentoReceptor($factura)),
        ];

        require_once APPPATH . 'Libraries/dompdf/autoload.inc.php';
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml(view('facturacion/comprobante_pdf', $datos));
        if ($datos['esA4']) {
            $dompdf->setPaper('A4');
        }
        $dompdf->render();

        $nombre = ($nota ? 'NotaCredito' : 'Factura') . "_{$letra}_" . preg_replace('/[^0-9\-]/', '', (string) $comprobante['numero_comprobante']) . '.pdf';

        return [$nombre, $dompdf->output()];
    }

    private function items(int $ventaId): array
    {
        $productos = new Productos_model();
        $items = [];

        foreach ((new VentaDetalle_model())->where('venta_id', $ventaId)->findAll() as $detalle) {
            $producto = $productos->find($detalle['producto_id']);
            $items[] = [
                'nombre'   => $producto ? $producto['nombre'] : 'Producto #' . $detalle['producto_id'],
                'cantidad' => $detalle['cantidad'],
                'precio'   => (float) $detalle['precio'],
                'total'    => (float) $detalle['total'],
            ];
        }

        return $items;
    }

    /** QR oficial de AFIP como data URI PNG (https://www.afip.gob.ar/fe/qr/especificaciones.asp). */
    private function qrAfip(array $config, int $tipoComprobante, int $puntoVenta, array $comprobante, string $fecha, float $importe, array $receptor): string
    {
        // numero_comprobante llega como "PPPP-NNNNNNNN": nroCmp es solo la segunda parte.
        $partes = explode('-', (string) $comprobante['numero_comprobante']);

        $payload = [
            'ver'        => 1,
            'fecha'      => $fecha,
            'cuit'       => (int) $config['cuit'],
            'ptoVta'     => $puntoVenta,
            'tipoCmp'    => $tipoComprobante,
            'nroCmp'     => (int) end($partes),
            'importe'    => round($importe, 2),
            'moneda'     => 'PES',
            'ctz'        => 1,
            'tipoDocRec' => $receptor[0], // 80 CUIT, 96 DNI, 99 Consumidor Final
            'nroDocRec'  => (int) ($receptor[1] ?? 0),
            'tipoCodAut' => 'E',
            'codAut'     => (int) $comprobante['cae'],
        ];

        $url = 'https://www.afip.gob.ar/fe/qr/?p=' . base64_encode(json_encode($payload));

        $opciones = new QROptions([
            'outputType'  => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel'    => QRCode::ECC_M,
            'scale'       => 4,
            'imageBase64' => true,
        ]);

        return (new QRCode($opciones))->render($url);
    }
}
