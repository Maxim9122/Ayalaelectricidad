<?php
/**
 * PDF de Factura / Nota de Crédito con CAE. Lo renderiza App\Libraries\Facturacion\ComprobantePdf.
 *
 * @var array $config @var bool $esA4 @var string $titulo @var string $letra @var int $codigo
 * @var array $comprobante @var array $factura @var array|null $nota @var array|null $venta
 * @var array $items @var string|null $vendedor @var float $importe @var array $iva
 * @var string $fechaEmision @var string $qr
 */
$pesos = function ($valor) {
    return '$&nbsp;' . number_format((float) $valor, 2, ',', '.');
};
$esRI = ($config['condicion_fiscal'] ?? null) === 'responsable_inscripto';
$cuitFormateado = function ($cuit) {
    $cuit = preg_replace('/\D/', '', (string) $cuit);
    return strlen($cuit) === 11 ? substr($cuit, 0, 2) . '-' . substr($cuit, 2, 8) . '-' . substr($cuit, 10) : $cuit;
};
?>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: DejaVu Sans, Arial, sans-serif; margin: 0; padding: 0; <?= $esA4 ? 'font-size: 12px;' : 'width: 220px; font-size: 9px;' ?> }
    .centro { text-align: center; }
    .derecha { text-align: right; white-space: nowrap; }
    .negrita { font-weight: bold; }
    hr { border: 0; border-top: 1px dashed #000; margin: 5px 0; }
    p { margin: 2px 0; }
    .letra { display: inline-block; border: 2px solid #000; padding: 2px 10px; font-size: <?= $esA4 ? '34px' : '26px' ?>; font-weight: bold; line-height: 1; }
    .cod { font-size: <?= $esA4 ? '10px' : '8px' ?>; }
    table { width: 100%; border-collapse: collapse; }
    th { border-bottom: 1px solid #000; text-align: left; }
    td, th { padding: 1px 2px; vertical-align: top; }
    .totales td { padding: 1px 2px; }
    .qr { width: <?= $esA4 ? '120px' : '110px' ?>; }
</style>
</head>
<body>
    <div class="centro">
        <span class="letra"><?= esc($letra) ?></span><br>
        <span class="cod">COD. <?= str_pad((string) $codigo, 3, '0', STR_PAD_LEFT) ?></span>
        <p class="negrita" style="font-size: <?= $esA4 ? '16px' : '12px' ?>;"><?= esc($titulo) ?></p>
    </div>

    <p class="negrita" style="font-size: <?= $esA4 ? '15px' : '11px' ?>;"><?= esc($config['razon_social']) ?></p>
    <p>CUIT: <?= esc($cuitFormateado($config['cuit'])) ?></p>
    <?php if (!empty($config['domicilio'])): ?><p>Domicilio: <?= esc($config['domicilio']) ?></p><?php endif; ?>
    <?php if (!empty($config['ingresos_brutos'])): ?><p>Ingresos Brutos: <?= esc($config['ingresos_brutos']) ?></p><?php endif; ?>
    <?php if (!empty($config['inicio_actividades'])): ?><p>Inicio de actividades: <?= date('d/m/Y', strtotime($config['inicio_actividades'])) ?></p><?php endif; ?>
    <p><?= $esRI ? 'IVA Responsable Inscripto' : 'Responsable Monotributo' ?></p>
    <hr>

    <p class="negrita">Nro: <?= esc($comprobante['numero_comprobante']) ?></p>
    <p>Fecha: <?= date('d/m/Y', strtotime($fechaEmision)) ?></p>
    <?php if ($nota): ?>
        <p>Comprobante asociado: Factura <?= esc($letra) ?> <?= esc($factura['numero_comprobante']) ?></p>
        <p>Motivo: <?= esc($nota['motivo']) ?></p>
    <?php endif; ?>
    <hr>

    <?php
    // Condición frente al IVA informada en la factura; las facturas anteriores a este dato
    // muestran lo que se mostraba siempre (A: Responsable Inscripto, B/C: Consumidor Final).
    $condicionTexto = \App\Libraries\Facturacion\CondicionIva::nombre($factura['cliente_condicion_iva'] ?? null)
        ?? ($letra === 'A' ? 'IVA Responsable Inscripto' : 'Consumidor Final');
    ?>
    <p>Cliente: <?= esc($factura['cliente_nombre']) ?></p>
    <?php if (!empty($factura['cliente_cuit'])): ?>
        <p>CUIT: <?= esc($cuitFormateado($factura['cliente_cuit'])) ?></p>
    <?php elseif (!empty($factura['cliente_dni'])): ?>
        <p>DNI: <?= esc($factura['cliente_dni']) ?></p>
    <?php endif; ?>
    <p>Condición IVA: <?= esc($condicionTexto) ?></p>
    <?php if ($vendedor): ?><p>Atendido por: <?= esc($vendedor) ?></p><?php endif; ?>
    <?php if ($venta): ?><p>Venta Nro: <?= (int) $venta['id'] ?></p><?php endif; ?>
    <hr>

    <?php if ($items): ?>
        <table>
            <thead>
                <tr><th>Cant.</th><th>Descripción</th><th class="derecha">P.Unit</th><th class="derecha">Subtotal</th></tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= esc($item['cantidad']) ?></td>
                    <td><?= esc($item['nombre']) ?></td>
                    <td class="derecha"><?= $pesos($item['precio']) ?></td>
                    <td class="derecha"><?= $pesos($item['total']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php $ajuste = round($importe - array_sum(array_column($items, 'total')), 2); ?>
        <?php if (abs($ajuste) >= 0.01): ?>
            <p class="derecha"><?= $ajuste < 0 ? 'Descuento' : 'Recargo' ?>: <?= $pesos($ajuste) ?></p>
        <?php endif; ?>
        <hr>
    <?php endif; ?>

    <table class="totales">
        <?php if ($letra === 'A'): ?>
            <tr><td>Importe neto gravado</td><td class="derecha"><?= $pesos($iva['importe_neto']) ?></td></tr>
            <tr><td>IVA 21%</td><td class="derecha"><?= $pesos($iva['importe_iva']) ?></td></tr>
        <?php endif; ?>
        <tr class="negrita"><td>TOTAL</td><td class="derecha"><?= $pesos($importe) ?></td></tr>
    </table>
    <?php if ($letra === 'B'): ?>
        <p style="margin-top: 4px;">Régimen de Transparencia Fiscal al Consumidor (Ley 27.743)</p>
        <p>IVA Contenido: <?= $pesos($iva['importe_iva']) ?></p>
        <p>Otros Impuestos Nacionales Indirectos: $ 0,00</p>
    <?php endif; ?>
    <?php if ($venta && !$nota && !empty($venta['tipo_pago'])): ?>
        <p>Forma de pago: <?= esc($venta['tipo_pago']) ?></p>
    <?php endif; ?>
    <hr>

    <div class="centro">
        <img class="qr" src="<?= $qr ?>">
        <p class="negrita">CAE: <?= esc($comprobante['cae']) ?></p>
        <p>Vto. CAE: <?= $comprobante['cae_vencimiento'] ? date('d/m/Y', strtotime($comprobante['cae_vencimiento'])) : '-' ?></p>
        <p>Comprobante autorizado por ARCA</p>
    </div>
</body>
</html>
