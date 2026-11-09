<?php
/**
 * Después de cobrar: el comprobante ya está generado y el cajero elige qué hacer.
 * @var string $titulo @var string|null $aviso @var string $urlImprimir @var string $urlDescargar @var string $urlListo
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Comprobante listo</title>
<style>
    body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
           background: #3a3f44; font-family: Arial, sans-serif; padding: 16px; box-sizing: border-box; }
    .cartel { background: #fff; border-radius: 12px; padding: 26px 24px; width: 100%; max-width: 360px;
              text-align: center; box-shadow: 0 8px 24px rgba(0,0,0,.35); }
    .ok { font-size: 42px; line-height: 1; color: #2e7d32; }
    h1 { font-size: 20px; margin: 10px 0 4px; color: #222; }
    .tipo { color: #555; margin: 0 0 18px; font-size: 15px; }
    .aviso { background: #fdecea; color: #b71c1c; border-radius: 8px; padding: 10px; font-size: 14px;
             margin: 0 0 16px; text-align: left; }
    .boton { display: block; width: 100%; box-sizing: border-box; padding: 13px; margin-top: 10px;
             border: none; border-radius: 8px; font-size: 16px; font-weight: bold; text-decoration: none;
             color: #fff; cursor: pointer; }
    .imprimir { background: #1e88e5; }
    .descargar { background: #43a047; }
    .listo { background: #757575; }
    .boton:hover { filter: brightness(.92); }
</style>
</head>
<body>
    <div class="cartel">
        <div class="ok">&#10004;</div>
        <h1>Venta registrada</h1>
        <p class="tipo"><?= esc($titulo) ?> lista</p>
        <?php if (!empty($aviso)): ?>
            <p class="aviso"><?= $aviso ?></p>
        <?php endif; ?>
        <a class="boton imprimir" href="<?= esc($urlImprimir, 'attr') ?>" target="_blank" rel="noopener">&#128424; Imprimir</a>
        <a class="boton descargar" href="<?= esc($urlDescargar, 'attr') ?>">&#11015; Descargar</a>
        <a class="boton listo" href="<?= esc($urlListo, 'attr') ?>" id="listoBtn">Listo</a>
    </div>
    <script>
        // Enter = Listo (para seguir cobrando rápido)
        document.addEventListener("keydown", function (e) {
            if (e.key === "Enter") { window.location.href = document.getElementById("listoBtn").href; }
        });
    </script>
</body>
</html>
