<?php
/**
 * Abre el PDF en la pestaña y lanza el diálogo de impresión del navegador.
 * @var string $titulo @var string $urlPdf
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Imprimir <?= esc($titulo) ?></title>
<style>
    html, body { margin: 0; height: 100%; background: #525659; font-family: Arial, sans-serif; }
    .barra { height: 46px; display: flex; align-items: center; gap: 8px; padding: 0 12px; background: #2b2f33; color: #fff; }
    .barra strong { flex: 1; font-size: 15px; }
    .barra button, .barra a { background: #1e88e5; color: #fff; border: none; border-radius: 6px; padding: 8px 12px;
                              font-size: 14px; font-weight: bold; cursor: pointer; text-decoration: none; }
    .barra .cerrar { background: #757575; }
    iframe { display: block; width: 100%; height: calc(100% - 46px); border: 0; }
</style>
</head>
<body>
    <div class="barra">
        <strong><?= esc($titulo) ?></strong>
        <button type="button" onclick="imprimir()">&#128424; Imprimir</button>
        <a href="<?= esc($urlPdf, 'attr') ?>" target="_blank" rel="noopener">Abrir PDF</a>
        <button type="button" class="cerrar" onclick="window.close()">Cerrar</button>
    </div>
    <iframe id="pdf" src="<?= esc($urlPdf, 'attr') ?>"></iframe>
    <script>
        function imprimir() {
            const marco = document.getElementById("pdf");
            try {
                marco.contentWindow.focus();
                marco.contentWindow.print();
            } catch (e) {
                // Si el navegador no deja imprimir el marco, se abre el PDF para imprimirlo desde ahí.
                window.open(marco.src, "_blank");
            }
        }
        // Cuando el PDF terminó de cargar, se abre el diálogo de impresión solo.
        document.getElementById("pdf").addEventListener("load", function () {
            setTimeout(imprimir, 600);
        });
    </script>
</body>
</html>
