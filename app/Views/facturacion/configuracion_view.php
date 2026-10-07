<?php
/**
 * @var array $config @var array|null $credencial @var bool $apiConfigurada @var bool $onboardingConfigurado
 * @var string $webhookOnboarding @var string $webhookComprobantes
 */
use App\Models\ConfiguracionFacturacion_model;
use App\Models\CredencialFacturacion_model;

$valor = function ($campo) use ($config) {
    return esc(old($campo, $config[$campo] ?? ''));
};
$condicion = old('condicion_fiscal', $config['condicion_fiscal']);
$predeterminado = old('comprobante_predeterminado', $config['comprobante_predeterminado']);
$habilitada = old('factura_habilitada', $config['factura_habilitada']);
$activa = CredencialFacturacion_model::estaActiva($credencial);
?>
<style>
    .fact-container { max-width: 900px; margin: 20px auto; padding: 0 16px; color: #fff; }
    .fact-card { background: rgba(0, 0, 0, 0.55); border-radius: 10px; padding: 18px 22px; margin-bottom: 18px; }
    .fact-card h3 { margin-top: 0; color: orange; }
    .fact-fila { display: flex; flex-wrap: wrap; gap: 6px 16px; align-items: center; margin-bottom: 10px; }
    .fact-fila label { min-width: 230px; font-weight: bold; }
    .fact-fila input[type=text], .fact-fila input[type=email], .fact-fila input[type=password], .fact-fila input[type=date], .fact-fila select { flex: 1; min-width: 220px; padding: 6px; border-radius: 5px; }
    .fact-ayuda { font-size: 13px; color: #ddd; margin: 2px 0 10px; }
    .fact-estado { padding: 8px 12px; border-radius: 6px; margin-bottom: 10px; }
    .fact-ok { background: #2e7d32; } .fact-pend { background: #b8860b; } .fact-off { background: #555; }
    .fact-codigo { font-family: monospace; background: #222; padding: 2px 6px; border-radius: 4px; word-break: break-all; }
</style>

<div class="fact-container">
    <?php if (session()->getFlashdata('msg') || session()->getFlashdata('msgEr')): ?>
        <div id="flash-message" class="flash-message <?= session()->getFlashdata('msg') ? 'success' : 'danger' ?>">
            <?= session()->getFlashdata('msg') ?>
            <?= session()->getFlashdata('msgEr') ?>
        </div>
        <script>setTimeout(() => { document.getElementById('flash-message').style.display = 'none'; }, 6000);</script>
    <?php endif; ?>

    <h2>Facturación Electrónica (ARCA / AFIP)</h2>

    <?php if (!$apiConfigurada): ?>
        <div class="fact-estado fact-off">
            El servicio de facturación todavía no está conectado (falta <span class="fact-codigo">FACTURACION_API_URL</span> en el .env).
            Mientras tanto todas las ventas se registran con Remito.
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= base_url('facturacion') ?>">
        <div class="fact-card">
            <h3>Datos del negocio</h3>
            <div class="fact-fila"><label>Razón social</label><input type="text" name="razon_social" maxlength="150" value="<?= $valor('razon_social') ?>"></div>
            <div class="fact-fila"><label>CUIT (11 dígitos)</label><input type="text" name="cuit" maxlength="13" value="<?= $valor('cuit') ?>"></div>
            <div class="fact-fila"><label>Email de contacto</label><input type="email" name="email_contacto" maxlength="150" value="<?= $valor('email_contacto') ?>"></div>
            <div class="fact-fila"><label>Domicilio comercial</label><input type="text" name="domicilio" maxlength="200" value="<?= $valor('domicilio') ?>"></div>
            <div class="fact-fila"><label>Ingresos Brutos</label><input type="text" name="ingresos_brutos" maxlength="50" value="<?= $valor('ingresos_brutos') ?>"></div>
            <div class="fact-fila"><label>Inicio de actividades</label><input type="date" name="inicio_actividades" value="<?= $valor('inicio_actividades') ?>"></div>
            <p class="fact-ayuda">Estos datos se imprimen en la factura. Razón social, CUIT y email se envían al servicio de facturación.</p>
        </div>

        <div class="fact-card">
            <h3>Facturación</h3>
            <div class="fact-fila">
                <label>
                    <input type="checkbox" name="factura_habilitada" value="1" <?= $habilitada ? 'checked' : '' ?>>
                    Habilitar facturación
                </label>
            </div>
            <p class="fact-ayuda">Si lo desmarcás, en Caja solo se puede elegir Remito. No borra la condición fiscal ni el certificado ya configurado: sirve para pausar temporalmente.</p>

            <div class="fact-fila">
                <label>Condición fiscal</label>
                <select name="condicion_fiscal" id="condicionFiscal" onchange="actualizarComprobantes()">
                    <option value="">Sin configurar</option>
                    <option value="responsable_inscripto" <?= $condicion === 'responsable_inscripto' ? 'selected' : '' ?>>Responsable Inscripto (Factura A / B)</option>
                    <option value="monotributista" <?= $condicion === 'monotributista' ? 'selected' : '' ?>>Monotributista (Factura C)</option>
                </select>
            </div>

            <div class="fact-fila">
                <label>Comprobante preseleccionado al cobrar</label>
                <select name="comprobante_predeterminado" id="comprobantePredeterminado">
                    <option value="remito" <?= $predeterminado === 'remito' ? 'selected' : '' ?>>Remito</option>
                    <option value="A" data-condicion="responsable_inscripto" <?= $predeterminado === 'A' ? 'selected' : '' ?>>Factura A</option>
                    <option value="B" data-condicion="responsable_inscripto" <?= $predeterminado === 'B' ? 'selected' : '' ?>>Factura B</option>
                    <option value="C" data-condicion="monotributista" <?= $predeterminado === 'C' ? 'selected' : '' ?>>Factura C</option>
                </select>
            </div>
            <p class="fact-ayuda">Es lo que viene marcado en el formulario de cobro; el cajero siempre lo puede cambiar.</p>

            <div class="fact-fila">
                <label>Formato de impresión</label>
                <select name="formato_comprobante">
                    <option value="ticket" <?= old('formato_comprobante', $config['formato_comprobante']) === 'ticket' ? 'selected' : '' ?>>Ticket (80mm)</option>
                    <option value="a4" <?= old('formato_comprobante', $config['formato_comprobante']) === 'a4' ? 'selected' : '' ?>>A4 (hoja entera)</option>
                </select>
            </div>
            <p class="fact-ayuda">Aplica a facturas y notas de crédito.</p>

            <div class="fact-fila">
                <label>Monto desde el cual se exige DNI al consumidor final ($)</label>
                <input type="text" name="monto_identificar_consumidor" maxlength="20"
                       value="<?= esc(old('monto_identificar_consumidor', number_format((float) ($config['monto_identificar_consumidor'] ?? 0), 0, ',', '.'))) ?>">
            </div>
            <p class="fact-ayuda">Factura B o C a consumidor final (sin CUIT) por este importe o más: ARCA exige el DNI del comprador. Valor vigente según RG 5866/2026: $ 10.000.000. Actualizalo si ARCA lo cambia.</p>
        </div>

        <div class="fact-card">
            <h3>Código de autorización</h3>
            <p class="fact-ayuda">Se pide para cancelar o modificar una venta cobrada y para anular una factura con Nota de Crédito. Por seguridad el código actual no se muestra: dejá los campos vacíos para mantenerlo.</p>
            <div class="fact-fila"><label>Código nuevo</label><input type="password" name="codigo_autorizacion_nuevo" maxlength="50" autocomplete="new-password"></div>
            <div class="fact-fila"><label>Repetir código nuevo</label><input type="password" name="codigo_autorizacion_confirmacion" maxlength="50" autocomplete="new-password"></div>
        </div>

        <div style="text-align: end; margin-bottom: 18px;">
            <button type="submit" class="btn">Guardar configuración</button>
        </div>
    </form>

    <?php if (ConfiguracionFacturacion_model::puedeFacturar($config)): ?>
        <div class="fact-card">
            <h3>Certificado AFIP</h3>
            <?php if ($activa): ?>
                <div class="fact-estado fact-ok">
                    Configurada — ambiente <strong><?= esc($credencial['ambiente']) ?></strong>, punto de venta <strong><?= esc($credencial['punto_venta']) ?></strong>.
                </div>
                <p>¿Cambió el dueño, venció el certificado o se comprometió la clave? Podés renovarlo acá abajo. La configuración actual sigue funcionando hasta que termines la renovación.</p>
            <?php elseif ($credencial && $credencial['estado'] === CredencialFacturacion_model::ESTADO_PENDIENTE_ONBOARDING): ?>
                <div class="fact-estado fact-pend">Se inició la configuración pero todavía no se completó. Si el link venció, volvé a iniciarla.</div>
            <?php else: ?>
                <p>Conectá tu certificado AFIP para emitir comprobantes con CAE automáticamente al cobrar.</p>
            <?php endif; ?>

            <?php if ($onboardingConfigurado): ?>
                <form method="POST" action="<?= base_url('facturacion/onboarding') ?>" target="_blank">
                    <div class="fact-fila">
                        <label>Ambiente</label>
                        <select name="ambiente">
                            <option value="homologacion">Homologación (pruebas)</option>
                            <option value="produccion">Producción</option>
                        </select>
                        <button type="submit" class="btn">
                            <?= $activa ? 'Renovar certificado' : ($credencial ? 'Reintentar configuración' : 'Configurar facturación electrónica') ?>
                        </button>
                    </div>
                </form>
                <p class="fact-ayuda">Se abre en una pestaña nueva. Cuando termines de cargar el certificado ahí, volvé y actualizá esta página.</p>
            <?php else: ?>
                <div class="fact-estado fact-off">
                    El botón para cargar el certificado aparece cuando este sistema está conectado al servicio de facturación.
                    Falta completar en el <span class="fact-codigo">.env</span>: <span class="fact-codigo">FACTURACION_API_URL</span>,
                    <span class="fact-codigo">FACTURACION_PLATFORM_KEY</span> y <span class="fact-codigo">FACTURACION_ONBOARDING_WEBHOOK_SECRET</span>.
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="fact-card">
        <h3>Datos para el administrador de la API de facturación</h3>
        <p class="fact-ayuda">URLs de los webhooks de este sistema (deben ser accesibles desde internet):</p>
        <p>Onboarding: <span class="fact-codigo"><?= esc($webhookOnboarding) ?></span></p>
        <p>Comprobantes: <span class="fact-codigo"><?= esc($webhookComprobantes) ?></span></p>
    </div>
</div>

<script>
// El comprobante preseleccionado solo ofrece las letras de la condición fiscal elegida.
function actualizarComprobantes() {
    const condicion = document.getElementById('condicionFiscal').value;
    const select = document.getElementById('comprobantePredeterminado');
    Array.from(select.options).forEach(function (opcion) {
        const permitida = !opcion.dataset.condicion || opcion.dataset.condicion === condicion;
        opcion.hidden = !permitida;
        opcion.disabled = !permitida;
    });
    if (select.selectedOptions[0].disabled) {
        select.value = 'remito';
    }
}
actualizarComprobantes();
</script>
