<?php
namespace App\Controllers;

use CodeIgniter\Controller;
Use App\Models\Productos_model;
Use App\Models\Cabecera_model;
Use App\Models\VentaDetalle_model;
Use App\Models\Clientes_model;
use App\Models\Usuarios_model;
use App\Models\Cae_model;
use App\Models\Vendedores_model;
use App\Models\Factura_model;
use App\Models\ConfiguracionFacturacion_model;

class Caja_controller extends Controller{

	public function __construct(){
           helper(['form', 'url']);
	}
//verificacion de codigo de acceso
    // Solo da feedback rápido al navegador: cada acción protegida vuelve a validar el código en el servidor.
    public function verificarCodigo()
    {
        if (!session()->has('id')) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Sesión vencida. Volvé a ingresar.']);
        }

        $codigoIngresado = (string) $this->request->getPost('codigo');

        if ((new ConfiguracionFacturacion_model())->verificarCodigoAutorizacion($codigoIngresado)) {
            return $this->response->setJSON(['success' => true]);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Código incorrecto. Intente de nuevo.']);
        }
    }

    //Vista para el cajero
    public function Caja(){
        $session = session();
        $perfil=$session->get('perfil_id');
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
        if($perfil == 2){
            return redirect()->to(base_url('catalogo'));
       }
    //print_r($session->get());
    //exit;
    // Instanciar el modelo
    $USmodel = new Usuarios_model();
    $cabeceraModel = new Cabecera_model();
    // Obtener y limpiar filtros
    $filtros = [
        'tipo_compra' => 'Compra_Normal',
        'estado' => 'Pendiente'    
    ];
    // Llamar al método del modelo para obtener las ventas con clientes
    $datos['ventas'] = $cabeceraModel->getVentasRegistradas($filtros);
    $datos2['usuarios'] = $USmodel->getUsBaja('NO');
    // Pasar el título y los datos a las vistas
    $data['titulo'] = 'Listado de Compras';
    echo view('navbar/navbar');
    echo view('header/header', $data);
    echo view('comprasXcliente/ListaVentas_Caja', $datos + $datos2);
    echo view('footer/footer');
    }

    //Cargo la venta a Cobrar
    public function CargarVenta($id_vta)
{
    $cart = \Config\Services::cart();
    $cart->destroy();
    $session = session();
        $perfil=$session->get('perfil_id');
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
        if($perfil == 2){
            return redirect()->to(base_url('catalogo'));
        }
   
    $detalle_model = new VentaDetalle_model();
    $producto_model = new Productos_model();
    $cabecera_model = new Cabecera_model();
    $vendedoresmodel = new Vendedores_model();
    $model_clientes = new Clientes_model();
    // Obtener los datos de la cabecera de la venta para obtener el id_cliente
    // Bloquea la venta como "Cobrando" (libera la que esta sesión tuviera abierta)
    $error_bloqueo = \App\Libraries\BloqueoVenta::tomar((int) $id_vta, ['Pendiente', 'Cta_Cte'], 'Cobrando');
    $cabecera = $cabecera_model->find($id_vta);
    if($error_bloqueo === null){

    $id_vendedor = $cabecera ? $cabecera['id_usuario'] : null;
    $vendedor = $vendedoresmodel->find($id_vendedor);
    $nombre_vendedor = $vendedor ? $vendedor['nombre'] : 'No encontrado';

    $id_cliente = $cabecera ? $cabecera['id_cliente'] : null;
    if($id_cliente > 1){ 
    $cliente = $model_clientes->find($id_cliente);
    $nombre_cli_regis = $cliente ? $cliente['nombre'] : 'No encontrado';
    }else{
    $nombre_cli_regis = null;
    }
    $nombre_cli = $cabecera ? $cabecera['nombre_prov_client'] : null;   
    $id_pedido = $cabecera ? $cabecera['id'] : null;
    $fecha_pedido = $cabecera ? $cabecera['fecha_pedido'] : null;
    $tipo_compra = $cabecera ? $cabecera['tipo_compra'] : null;
    $tipo_pago = $cabecera ? $cabecera['tipo_pago'] : null;
    $total_venta = $cabecera ? $cabecera['total_venta'] : null;    
    //print_r($nombre_cli_regis);
    //exit;
    $session->set([
        'id_vendedor' => $id_vendedor,
        'nombre_vendedor' => $nombre_vendedor,
        'id_cliente' => $id_cliente,
        'nombre_cli_regis' => $nombre_cli_regis,
        'nombre_cli' => $nombre_cli,
        'id_pedido' => $id_pedido,
        'fecha_pedido' => $fecha_pedido,
        'tipo_compra' => $tipo_compra,
        'tipo_pago' => $tipo_pago,
        'total_venta' => $total_venta,
        'estado' => 'Cobrando'
    ]);
    // Obtener los productos del pedido
    $detalles = $detalle_model->where('venta_id', $id_vta)->findAll();
    foreach ($detalles as $detalle) {
        $producto = $producto_model->find($detalle['producto_id']);
        if ($producto) {
            $cart->insert([
                'id'    => $producto['id'],
                'qty'   => $detalle['cantidad'],
                'price' => $detalle['precio'],
                'name'  => $producto['nombre'],
                'options' => array(
                    'stock' => $producto['stock'],                   
                )
            ]);
        }
    }
    //print_r($fecha_pedido);
    //exit;    
    // Redirigir a la vista de edición del pedido
    return redirect()->to('casiListo');
    }
    
    session()->setFlashdata('msgEr', $error_bloqueo);
    return redirect()->to('comprasCta_Cte');
    }

    //Cancelar Cobro de la venta
    public function CancelarCobro($id_pedido){
        $session = session();
        $tipo_compra = $session->get('tipo_compra');
        // Vuelve a su estado anterior (Pendiente o Cta_Cte) y limpia sesión y carrito
        \App\Libraries\BloqueoVenta::cancelar((int) $id_pedido);
        if($tipo_compra == 'Compra_Normal'){ 
            session()->setFlashdata('msg', 'Se Cancelo el cobro de la Venta!');
        return redirect()->to('comprasCta_Cte');
        } else {
            session()->setFlashdata('msg', 'Se Cancelo el cobro del Pedido!');
        return redirect()->to('pedidos');
        }
    }


    //Funcion para modificar la venta
    public function cargar_Venta_en_Carrito($id_pedido)
{
    $session = session();
    $cart = \Config\Services::cart();
    $US_model = new Usuarios_model();
    $detalle_model = new VentaDetalle_model();
    $cabecera_model = new Cabecera_model(); // Asegúrate de tener este modelo
    $producto_model = new Productos_model();
    $model_clientes = new Clientes_model();

    // Bloquea la venta como "Modificando" (libera la que esta sesión tuviera abierta)
    $error_bloqueo = \App\Libraries\BloqueoVenta::tomar((int) $id_pedido, ['Pendiente'], 'Modificando');
    // Obtener los datos de la cabecera de la venta para obtener el id_cliente
    $cabecera = $cabecera_model->find($id_pedido);
    if($error_bloqueo === null){
    $id_vendedor = $cabecera ? $cabecera['id_usuario'] : null;
    $vendedor = $US_model->find($id_vendedor);
    $nombre_vendedor = $vendedor ? $vendedor['nombre'] : 'No encontrado';

    $id_cliente = $cabecera ? $cabecera['id_cliente'] : null;
    if($id_cliente > 1){ 
    $cliente = $model_clientes->find($id_cliente);
    $nombre_cli_regis = $cliente ? $cliente['nombre'] : 'No encontrado';
    }else{
    $nombre_cli_regis = null;
    }

    $id_pedido = $cabecera ? $cabecera['id'] : null;
    $fecha_pedido = $cabecera ? $cabecera['fecha_pedido'] : null;
    $tipo_compra = $cabecera ? $cabecera['tipo_compra'] : null;
    $tipo_pago = $cabecera ? $cabecera['tipo_pago'] : null;
    $total_venta = $cabecera ? $cabecera['total_venta'] : null;
    $session->set([
        'id_vendedor' => $id_vendedor,
        'nombre_vendedor' => $nombre_vendedor,
        'id_cliente' => $id_cliente,
        'nombre_cli_regis' => $nombre_cli_regis,
        'id_pedido' => $id_pedido,
        'fecha_pedido' => $fecha_pedido,
        'tipo_compra' => $tipo_compra,
        'tipo_pago' => $tipo_pago,
        'total_venta' => $total_venta,
        'estado' => 'Modificando'
    ]);
    // Obtener los productos del pedido
    $detalles = $detalle_model->where('venta_id', $id_pedido)->findAll();

    // Limpiar el carrito antes de cargar los productos
    $cart->destroy();


    if (!$detalles) {
        \App\Libraries\BloqueoVenta::liberarDeSesion();
        session()->setFlashdata('error', 'No se encontraron productos en el pedido.');
        return redirect()->to($this->request->getHeaderLine('referer') ?: base_url('caja'));
    }

    foreach ($detalles as $detalle) {
        $producto = $producto_model->find($detalle['producto_id']);
        if ($producto) {
            $cart->insert([
                'id'    => $producto['id'],
                'qty'   => $detalle['cantidad'],
                'price' => $detalle['precio'],
                'name'  => $producto['nombre'],
                'options' => array(
                    'stock' => $producto['stock'],
                )
            ]);
        }
    }
    // Redirigir a la vista de edición del pedido
    return redirect()->to('CarritoList');
    }

    session()->setFlashdata('msgEr', $error_bloqueo);
    return redirect()->to('caja');
    }

//Cancelar la modificacion de la Venta
public function cancelar_edicion_Venta($id_pedido){
    // Vuelve a Pendiente y limpia sesión y carrito
    \App\Libraries\BloqueoVenta::cancelar((int) $id_pedido);
    return redirect()->to(base_url('caja'));
}

//Cancela la Venta
public function Venta_cancelar($id_pedido)
{
    $session = session();
    $perfil = $session->get('perfil_id');

    // Verifica si el usuario está logueado
    if (!$session->has('id')) {
        return redirect()->to(base_url('login'));
    }

    if ($perfil == 2) {
        return redirect()->to(base_url('catalogo'));
    }

    $cabecera_model = new Cabecera_model();
    $detalle_model = new VentaDetalle_model();
    $producto_model = new Productos_model();

    $venta = $cabecera_model->find($id_pedido);

    // Cancelar una venta ya cobrada exige el código de autorización (un Pedido Pendiente no).
    if ($venta && $venta['estado'] != 'Pendiente'
        && !(new ConfiguracionFacturacion_model())->verificarCodigoAutorizacion((string) $this->request->getPost('codigo'))) {
        session()->setFlashdata('msgEr', 'Código de autorización incorrecto. La venta no se canceló.');
        return redirect()->to($this->request->getHeaderLine('referer') ?: base_url('compras'));
    }

    // Con factura aprobada o pendiente de AFIP, la única forma de cancelar es la Nota de Crédito.
    $factura = $venta && $venta['factura_id'] ? (new Factura_model())->find($venta['factura_id']) : null;
    if ($factura && !Factura_model::fallo($factura)) {
        session()->setFlashdata('msgEr', 'Esta venta tiene factura electrónica. Para cancelarla usá "Anular (Nota de Crédito)".');
        return redirect()->to($this->request->getHeaderLine('referer') ?: base_url('compras'));
    }

    // Obtener los detalles de la Venta
    $detalles = $detalle_model->where('venta_id', $id_pedido)->findAll();

    if (!$detalles) {
        // Eliminar físicamente la cabecera si no hay detalles
        $cabecera_model->delete($id_pedido);

        session()->setFlashdata('msg', 'Se Elimino Error de Venta Duplicada.');
        return redirect()->to($this->request->getHeader('referer')->getValue());
    }

    // Restaurar el stock de cada producto
    foreach ($detalles as $detalle) {
        $producto = $producto_model->find($detalle['producto_id']);
        if ($producto) {
            $nuevo_stock = $producto['stock'] + $detalle['cantidad'];
            $producto_model->update($detalle['producto_id'], ['stock' => $nuevo_stock]);
        }
    }

    // Actualizar el estado del pedido a "Cancelado"
    $cabecera_model->update($id_pedido, ['estado' => 'Cancelado']);

    session()->setFlashdata('msg', 'Venta cancelada y stock restaurado.');

    return redirect()->to($this->request->getHeader('referer')->getValue());
}



//Modificar Venta Sin_Facturar
public function cargar_Venta_Sin_Facturar($id_pedido)
{
    $session = session();
    $cart = \Config\Services::cart();
    $US_model = new Usuarios_model();
    $detalle_model = new VentaDetalle_model();
    $cabecera_model = new Cabecera_model(); // Asegúrate de tener este modelo
    $producto_model = new Productos_model();

    if (!$session->has('id')) {
        return redirect()->to(base_url('login'));
    }

    // Modificar una venta ya cobrada exige el código de autorización.
    if (!(new ConfiguracionFacturacion_model())->verificarCodigoAutorizacion((string) $this->request->getPost('codigo'))) {
        session()->setFlashdata('msgEr', 'Código de autorización incorrecto.');
        return redirect()->to($this->request->getHeaderLine('referer') ?: base_url('compras'));
    }

    // Obtener los datos de la cabecera de la venta para obtener el id_cliente
    $cabecera = $cabecera_model->find($id_pedido);

    // Una venta con factura electrónica no se modifica: el importe ya se informó a AFIP.
    if ($cabecera && $cabecera['factura_id']) {
        session()->setFlashdata('msgEr', 'Esta venta tiene factura electrónica y no se puede modificar. Si hace falta, anulala con Nota de Crédito.');
        return redirect()->to($this->request->getHeaderLine('referer') ?: base_url('compras'));
    }

    // Bloquea la venta como "Modificando_SF" (libera la que esta sesión tuviera abierta)
    $error_bloqueo = \App\Libraries\BloqueoVenta::tomar((int) $id_pedido, ['Sin_Facturar', 'Modificada_SF'], 'Modificando_SF');
    if($error_bloqueo === null){
    $id_vendedor = $cabecera ? $cabecera['id_usuario'] : null;
    $vendedor = $US_model->find($id_vendedor);
    $nombre_vendedor = $vendedor ? $vendedor['nombre'] : 'No encontrado';
    $id_cliente = $cabecera ? $cabecera['id_cliente'] : null;
    $id_pedido = $cabecera ? $cabecera['id'] : null;
    $fecha_pedido = $cabecera ? $cabecera['fecha_pedido'] : null;
    $tipo_compra = $cabecera ? $cabecera['tipo_compra'] : null;
    $tipo_pago = $cabecera ? $cabecera['tipo_pago'] : null;
    $total_venta = $cabecera ? $cabecera['total_venta'] : null;
    $total_bonificado = $cabecera ? $cabecera['total_bonificado'] : null;
    $total_anterior = $cabecera ? $cabecera['total_anterior'] : null;

    $pago_efec = $cabecera ? $cabecera['monto_efectivo'] : null;
    $pago_transfer = $cabecera ? $cabecera['monto_transferencia'] : null;
    $pago_tarjeta = $cabecera ? $cabecera['monto_tarjetaC'] : null;

    $session->set([
        'id_vendedor' => $id_vendedor,
        'nombre_vendedor' => $nombre_vendedor,
        'id_cliente' => $id_cliente,
        'id_pedido' => $id_pedido,
        'fecha_pedido' => $fecha_pedido,
        'tipo_compra' => $tipo_compra,
        'tipo_pago' => $tipo_pago,
        'total_venta' => $total_venta,
        'total_bonificado' => $total_bonificado,
        'total_anterior' => $total_anterior,
        'pago_efec' => $pago_efec,
        'pago_transfer' => $pago_transfer,
        'pago_tarjeta' => $pago_tarjeta,
        'estado' => 'Modificando_SF'
    ]);
    // Obtener los productos del pedido
    $detalles = $detalle_model->where('venta_id', $id_pedido)->findAll();

    // Limpiar el carrito antes de cargar los productos
    $cart->destroy();


    if (!$detalles) {
        \App\Libraries\BloqueoVenta::liberarDeSesion();
        session()->setFlashdata('error', 'No se encontraron productos en el pedido.');
        return redirect()->to($this->request->getHeaderLine('referer') ?: base_url('compras'));
    }

    foreach ($detalles as $detalle) {
        $producto = $producto_model->find($detalle['producto_id']);
        if ($producto) {
            $cart->insert([
                'id'    => $producto['id'],
                'qty'   => $detalle['cantidad'],
                'price' => $detalle['precio'],
                'name'  => $producto['nombre'],
                'options' => array(
                    'stock' => $producto['stock'],                   
                )
            ]);
        }
    }
    // Redirigir a la vista de edición del pedido
    return redirect()->to('CarritoList');
    }
    
    session()->setFlashdata('msgEr', $error_bloqueo);
    return redirect()->to('compras');
    }

    //Cancelar la modificacion de la Venta Sin Facturar
public function cancelar_edicion_Venta_SF($id_pedido){
    // Vuelve a Sin_Facturar o Modificada_SF (el estado que tenía) y limpia sesión y carrito
    \App\Libraries\BloqueoVenta::cancelar((int) $id_pedido);
    return redirect()->to(base_url('compras'));
}

}