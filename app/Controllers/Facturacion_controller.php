<?php

namespace App\Controllers;

use App\Libraries\Facturacion\CertificadoYaValidadoException;
use App\Libraries\Facturacion\ComprobantePdf;
use App\Libraries\Facturacion\CuitYaRegistradoException;
use App\Libraries\Facturacion\EmisionComprobanteService;
use App\Libraries\Facturacion\EmisionNotaCreditoService;
use App\Libraries\Facturacion\FacturacionApiClient;
use App\Libraries\Facturacion\FacturaVentaService;
use App\Libraries\Facturacion\OnboardingFacturacionService;
use App\Models\Cabecera_model;
use App\Models\ConfiguracionFacturacion_model;
use App\Models\CredencialFacturacion_model;
use App\Models\Factura_model;
use App\Models\NotaCredito_model;

/**
 * Facturación electrónica: configuración (admin) y acciones sobre ventas facturadas.
 */
class Facturacion_controller extends BaseController
{
    private const PERFIL_ADMIN = 1;
    private const PERFIL_CAJERO = 3;

    // ---------------------------------------------------------
    // CONFIGURACIÓN (solo admin)
    // ---------------------------------------------------------

    public function configuracion()
    {
        if ($redireccion = $this->exigirPerfil([self::PERFIL_ADMIN])) {
            return $redireccion;
        }

        $data = [
            'config'          => (new ConfiguracionFacturacion_model())->obtener(),
            'credencial'      => (new CredencialFacturacion_model())->obtener(),
            'apiConfigurada'  => (new FacturacionApiClient())->configurada(),
            // Para pedir el link de alta hacen falta la URL y la platform key
            'onboardingConfigurado' => (new FacturacionApiClient())->configurada() && trim((string) config('Facturacion')->platformKey) !== '',
            'webhookOnboarding'   => base_url('api/webhooks/facturacion/onboarding'),
            'webhookComprobantes' => base_url('api/webhooks/facturacion/comprobantes'),
        ];

        echo view('navbar/navbar');
        echo view('header/header', ['titulo' => 'Facturación Electrónica']);
        echo view('facturacion/configuracion_view', $data);
        echo view('footer/footer');
    }

    public function guardarConfiguracion()
    {
        if ($redireccion = $this->exigirPerfil([self::PERFIL_ADMIN])) {
            return $redireccion;
        }

        $modelo = new ConfiguracionFacturacion_model();
        $config = $modelo->obtener();

        $datos = [
            'razon_social'               => trim((string) $this->request->getPost('razon_social')),
            'cuit'                       => preg_replace('/\D/', '', (string) $this->request->getPost('cuit')),
            'email_contacto'             => trim((string) $this->request->getPost('email_contacto')),
            'domicilio'                  => trim((string) $this->request->getPost('domicilio')),
            'ingresos_brutos'            => trim((string) $this->request->getPost('ingresos_brutos')),
            'inicio_actividades'         => $this->request->getPost('inicio_actividades') ?: null,
            'condicion_fiscal'           => $this->request->getPost('condicion_fiscal') ?: null,
            'factura_habilitada'         => $this->request->getPost('factura_habilitada') ? 1 : 0,
            'comprobante_predeterminado' => (string) $this->request->getPost('comprobante_predeterminado'),
            'formato_comprobante'        => (string) $this->request->getPost('formato_comprobante'),
            // Acepta "10.000.000" o "10000000" (sin decimales)
            'monto_identificar_consumidor' => (float) preg_replace('/\D/', '', (string) $this->request->getPost('monto_identificar_consumidor')),
        ];

        $errores = $this->validarConfiguracion($datos, $config);

        // El código solo se reemplaza si se cargó uno nuevo; el actual nunca se muestra (se guarda como hash).
        $codigoNuevo = (string) $this->request->getPost('codigo_autorizacion_nuevo');
        if ($codigoNuevo !== '') {
            if (mb_strlen($codigoNuevo) < 4) {
                $errores[] = 'El código de autorización debe tener al menos 4 caracteres.';
            } elseif ($codigoNuevo !== (string) $this->request->getPost('codigo_autorizacion_confirmacion')) {
                $errores[] = 'La confirmación del código de autorización no coincide.';
            } else {
                $datos['codigo_autorizacion_hash'] = password_hash($codigoNuevo, PASSWORD_DEFAULT);
            }
        }
        if ($errores) {
            return redirect()->to(base_url('facturacion'))->withInput()->with('msgEr', implode('<br>', $errores));
        }

        // Si ya se inició el alta en la API, los datos fiscales se corrigen allá primero.
        $credencial = (new CredencialFacturacion_model())->obtener();
        $cambios = [];
        foreach (['razon_social', 'cuit', 'email_contacto'] as $campo) {
            if ((string) $datos[$campo] !== (string) $config[$campo]) {
                $cambios[$campo] = $datos[$campo];
            }
        }
        if (!empty($credencial['empresa_externa_id']) && $cambios) {
            try {
                (new OnboardingFacturacionService())->corregirDatos($credencial, $cambios);
            } catch (CertificadoYaValidadoException | CuitYaRegistradoException $e) {
                return redirect()->to(base_url('facturacion'))->withInput()->with('msgEr', $e->getMessage());
            } catch (\Throwable $e) {
                log_message('error', 'No se pudieron corregir los datos en la API de facturación: ' . $e->getMessage());
                return redirect()->to(base_url('facturacion'))->withInput()->with('msgEr', 'No se pudieron actualizar los datos en el servicio de facturación: ' . esc($e->getMessage()));
            }
        }

        $modelo->update(1, $datos);

        return redirect()->to(base_url('facturacion'))->with('msg', 'Configuración guardada.');
    }

    /** Pide el link de onboarding y redirige ahí (se abre en pestaña nueva). Sirve también para renovar. */
    public function iniciarOnboarding()
    {
        if ($redireccion = $this->exigirPerfil([self::PERFIL_ADMIN])) {
            return $redireccion;
        }

        $config = (new ConfiguracionFacturacion_model())->obtener();
        if (!ConfiguracionFacturacion_model::puedeFacturar($config)) {
            return redirect()->to(base_url('facturacion'))->with('msgEr', 'Primero configurá la condición fiscal.');
        }
        if (empty($config['razon_social']) || empty($config['cuit']) || empty($config['email_contacto'])) {
            return redirect()->to(base_url('facturacion'))->with('msgEr', 'Completá razón social, CUIT y email de contacto antes de configurar la facturación.');
        }

        $ambiente = $this->request->getPost('ambiente');
        if (!in_array($ambiente, ['homologacion', 'produccion'], true)) {
            return redirect()->to(base_url('facturacion'))->with('msgEr', 'Ambiente inválido.');
        }

        try {
            $url = (new OnboardingFacturacionService())->iniciar($config['razon_social'], $config['cuit'], $config['email_contacto'], $ambiente);
        } catch (CuitYaRegistradoException $e) {
            return redirect()->to(base_url('facturacion'))->with('msgEr', $e->getMessage());
        } catch (\Throwable $e) {
            log_message('error', 'No se pudo iniciar el onboarding de facturación: ' . $e->getMessage());
            return redirect()->to(base_url('facturacion'))->with('msgEr', 'No se pudo iniciar la configuración. Probá de nuevo en unos minutos.');
        }

        return redirect()->to($url);
    }

    /** Consulta /ping con la API Key guardada: confirma que la conexión y la key funcionan. */
    public function probarConexion()
    {
        if ($redireccion = $this->exigirPerfil([self::PERFIL_ADMIN])) {
            return $redireccion;
        }

        $credencial = (new CredencialFacturacion_model())->obtener();
        if (!CredencialFacturacion_model::estaActiva($credencial)) {
            return redirect()->to(base_url('facturacion'))->with('msgEr', 'Todavía no hay una API Key activa: primero completá la carga del certificado.');
        }

        try {
            $respuesta = (new FacturacionApiClient())->ping($credencial['api_key']);
        } catch (\Throwable $e) {
            log_message('error', 'Ping a la API de facturación falló: ' . $e->getMessage());
            return redirect()->to(base_url('facturacion'))->with('msgEr', 'No se pudo conectar con el servicio de facturación. ¿Está levantado?');
        }

        if ($respuesta->status !== 200) {
            return redirect()->to(base_url('facturacion'))->with('msgEr', "El servicio de facturación rechazó la API Key (HTTP {$respuesta->status}): " . esc($respuesta->mensaje('sin detalle')));
        }

        $j = $respuesta->json;

        return redirect()->to(base_url('facturacion'))->with('msg', 'Conexión OK — empresa: ' . esc($j['empresa'] ?? '?') . ', ambiente: ' . esc($j['ambiente'] ?? '?') . '.');
    }

    /**
     * Da de alta un punto de venta en la API (sin repetir el onboarding) y pasa a facturar con él.
     * Es un solo negocio con una sola caja: el punto de venta nuevo reemplaza al que se usaba.
     */
    public function agregarPuntoVenta()
    {
        if ($redireccion = $this->exigirPerfil([self::PERFIL_ADMIN])) {
            return $redireccion;
        }

        $credencial = (new CredencialFacturacion_model())->obtener();
        if (!CredencialFacturacion_model::estaActiva($credencial) || empty($credencial['empresa_externa_id'])) {
            return redirect()->to(base_url('facturacion'))->with('msgEr', 'Primero completá la carga del certificado.');
        }

        $numero = (int) $this->request->getPost('punto_venta');
        if ($numero < 1 || $numero > 99998) {
            return redirect()->to(base_url('facturacion'))->with('msgEr', 'Punto de venta inválido (debe ser un número entre 1 y 99998).');
        }

        try {
            (new FacturacionApiClient())->agregarPuntoVenta($credencial['empresa_externa_id'], $credencial['ambiente'], $numero);
        } catch (\Throwable $e) {
            log_message('error', 'No se pudo agregar el punto de venta: ' . $e->getMessage());
            return redirect()->to(base_url('facturacion'))->with('msgEr', 'No se pudo agregar el punto de venta: ' . esc($e->getMessage()));
        }

        (new CredencialFacturacion_model())->guardar(['punto_venta' => $numero]);

        return redirect()->to(base_url('facturacion'))->with('msg', "Punto de venta {$numero} habilitado. Las próximas facturas salen con ese punto de venta.");
    }

    // ---------------------------------------------------------
    // ACCIONES SOBRE UNA VENTA (admin y cajero)
    // ---------------------------------------------------------

    /** Reintenta una factura que quedó en error / rechazada / pendiente. */
    public function reintentarFactura($ventaId)
    {
        if ($redireccion = $this->exigirPerfil([self::PERFIL_ADMIN, self::PERFIL_CAJERO])) {
            return $redireccion;
        }

        $venta = (new Cabecera_model())->find($ventaId);
        $factura = $venta && $venta['factura_id'] ? (new Factura_model())->find($venta['factura_id']) : null;
        if (!$factura) {
            return $this->volver('msgEr', 'La venta no tiene factura.');
        }
        if ($venta['estado'] === 'Cancelado') {
            return $this->volver('msgEr', 'La venta está cancelada, no se puede facturar.');
        }

        (new EmisionComprobanteService())->emitir((int) $factura['id']);

        return $this->volver(...(new FacturaVentaService())->mensajeFactura((int) $factura['id']));
    }

    /** Anula la factura con una Nota de Crédito por el 100% (devuelve stock y cancela la venta). */
    public function anularFactura($ventaId)
    {
        if ($redireccion = $this->exigirPerfil([self::PERFIL_ADMIN, self::PERFIL_CAJERO])) {
            return $redireccion;
        }

        if (!(new ConfiguracionFacturacion_model())->verificarCodigoAutorizacion((string) $this->request->getPost('codigo'))) {
            return $this->volver('msgEr', 'Código de autorización incorrecto. La factura no se anuló.');
        }

        $motivo = trim((string) $this->request->getPost('motivo'));
        if ($motivo === '' || mb_strlen($motivo) > 1000) {
            return $this->volver('msgEr', 'El motivo de la anulación es obligatorio (máximo 1000 caracteres).');
        }

        try {
            $notaId = (new FacturaVentaService())->anularVenta((int) $ventaId, (int) session()->get('id'), $motivo);
        } catch (\RuntimeException $e) {
            return $this->volver('msgEr', $e->getMessage());
        }

        // Fuera de la transacción: la anulación local ya quedó firme.
        (new EmisionNotaCreditoService())->emitir($notaId);

        $nota = (new NotaCredito_model())->find($notaId);
        if (NotaCredito_model::estaAprobada($nota)) {
            return $this->volver('msg', "Venta anulada y stock restaurado. Nota de Crédito {$nota['numero_comprobante']} emitida.");
        }

        return $this->volver('msgEr', 'Venta anulada y stock restaurado, pero la Nota de Crédito no se emitió todavía: '
            . esc($nota['error_mensaje'] ?: 'pendiente de AFIP.') . ' Podés reintentarla desde Acciones.');
    }

    public function reintentarNotaCredito($ventaId)
    {
        if ($redireccion = $this->exigirPerfil([self::PERFIL_ADMIN, self::PERFIL_CAJERO])) {
            return $redireccion;
        }

        $nota = $this->notaDeVenta((int) $ventaId);
        if (!$nota) {
            return $this->volver('msgEr', 'La venta no tiene nota de crédito.');
        }

        (new EmisionNotaCreditoService())->emitir((int) $nota['id']);

        $nota = (new NotaCredito_model())->find($nota['id']);
        if (NotaCredito_model::estaAprobada($nota)) {
            return $this->volver('msg', "Nota de Crédito {$nota['numero_comprobante']} emitida.");
        }

        return $this->volver('msgEr', 'La Nota de Crédito todavía no se emitió: ' . esc($nota['error_mensaje'] ?: 'pendiente de AFIP.'));
    }

    // ---------------------------------------------------------
    // PDF
    // ---------------------------------------------------------

    public function pdfFactura($ventaId)
    {
        if ($redireccion = $this->exigirPerfil([self::PERFIL_ADMIN, self::PERFIL_CAJERO])) {
            return $redireccion;
        }

        $venta = (new Cabecera_model())->find($ventaId);
        $factura = $venta && $venta['factura_id'] ? (new Factura_model())->find($venta['factura_id']) : null;
        if (!$factura || !Factura_model::estaAprobada($factura)) {
            return $this->volver('msgEr', 'La venta no tiene una factura aprobada para imprimir.');
        }

        [$nombre, $pdf] = (new ComprobantePdf())->factura($factura);
        // Si viene de imprimirFactura(), el mensaje tiene que llegar a la pantalla siguiente.
        session()->keepFlashdata('msg');

        return $this->response->download($nombre, $pdf);
    }

    public function pdfNotaCredito($ventaId)
    {
        if ($redireccion = $this->exigirPerfil([self::PERFIL_ADMIN, self::PERFIL_CAJERO])) {
            return $redireccion;
        }

        $nota = $this->notaDeVenta((int) $ventaId);
        if (!$nota || !NotaCredito_model::estaAprobada($nota)) {
            return $this->volver('msgEr', 'La venta no tiene una nota de crédito aprobada para imprimir.');
        }

        [$nombre, $pdf] = (new ComprobantePdf())->notaCredito($nota);

        return $this->response->download($nombre, $pdf);
    }

    /** Después de cobrar: descarga la factura y vuelve a la pantalla anterior (igual que la boleta). */
    public function imprimirFactura($ventaId)
    {
        if ($redireccion = $this->exigirPerfil([self::PERFIL_ADMIN, self::PERFIL_CAJERO])) {
            return $redireccion;
        }

        session()->setFlashdata('msg', 'Venta facturada. Imprimiendo Factura.!');
        $volverA = session()->get('perfil_id') == self::PERFIL_CAJERO ? base_url('caja') : base_url('catalogo');

        return $this->response->setBody(
            "<script>window.location.href = " . json_encode(base_url('facturacion/factura/' . (int) $ventaId . '/pdf')) . ";"
            . "window.setTimeout(function () { window.location.href = " . json_encode($volverA) . "; }, 800);</script>"
        );
    }

    // ---------------------------------------------------------

    private function validarConfiguracion(array $datos, array $config): array
    {
        $errores = [];

        if (!in_array($datos['condicion_fiscal'], [null, ConfiguracionFacturacion_model::RESPONSABLE_INSCRIPTO, ConfiguracionFacturacion_model::MONOTRIBUTISTA], true)) {
            $errores[] = 'Condición fiscal inválida.';
        }
        // El CUIT se exige recién cuando hace falta: al configurar la condición fiscal.
        if ($datos['condicion_fiscal'] && strlen($datos['cuit']) !== 11) {
            $errores[] = 'Para configurar la condición fiscal primero necesitás cargar el CUIT de la empresa (11 dígitos).';
        }
        if ($datos['cuit'] !== '' && strlen($datos['cuit']) !== 11) {
            $errores[] = 'El CUIT debe tener 11 dígitos.';
        }
        if ($datos['email_contacto'] !== '' && !filter_var($datos['email_contacto'], FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El email de contacto no es válido.';
        }
        if (!in_array($datos['formato_comprobante'], ['ticket', 'a4'], true)) {
            $errores[] = 'Formato de impresión inválido.';
        }
        if ($datos['monto_identificar_consumidor'] <= 0) {
            $errores[] = 'Cargá el monto desde el cual ARCA exige identificar al consumidor final.';
        }

        $letras = $datos['condicion_fiscal'] === ConfiguracionFacturacion_model::RESPONSABLE_INSCRIPTO ? ['A', 'B']
            : ($datos['condicion_fiscal'] === ConfiguracionFacturacion_model::MONOTRIBUTISTA ? ['C'] : []);
        if ($datos['comprobante_predeterminado'] !== 'remito' && !in_array($datos['comprobante_predeterminado'], $letras, true)) {
            $errores[] = 'El comprobante preseleccionado no corresponde a la condición fiscal elegida.';
        }

        return $errores;
    }

    private function notaDeVenta(int $ventaId): ?array
    {
        $venta = (new Cabecera_model())->find($ventaId);
        if (!$venta || !$venta['factura_id']) {
            return null;
        }

        return (new NotaCredito_model())->where('factura_id', $venta['factura_id'])->first();
    }

    /** Devuelve una redirección si el usuario no tiene alguno de los perfiles, o null si puede seguir. */
    private function exigirPerfil(array $perfiles)
    {
        $session = session();
        if (!$session->has('id')) {
            return redirect()->to(base_url('login'));
        }
        if (!in_array((int) $session->get('perfil_id'), $perfiles, true)) {
            return redirect()->to(base_url('catalogo'));
        }

        return null;
    }

    private function volver(string $tipo, string $mensaje)
    {
        $anterior = $this->request->getHeaderLine('referer') ?: base_url('compras');

        return redirect()->to($anterior)->with($tipo, $mensaje);
    }
}
