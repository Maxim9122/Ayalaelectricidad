<?php
namespace App\Controllers;
use CodeIgniter\Controller;
Use App\Models\Productos_model;
Use App\Models\Cabecera_model;
Use App\Models\VentaDetalle_model;
use App\Models\Pedidos_model;
use App\Models\Usuarios_model;
use App\Models\Clientes_model;
use App\Models\Servicios_model;
//use Dompdf\Dompdf;

class Cta_Cte_controller extends Controller{

	public function __construct(){
           helper(['form', 'url']);
	}


public function cargar_cta_cte_en_carrito($id_pedido)
    {
    $session = session();
    $cart = \Config\Services::cart();
    $US_model = new Usuarios_model();
    $detalle_model = new VentaDetalle_model();
    $cabecera_model = new Cabecera_model(); // Asegúrate de tener este modelo
    $producto_model = new Productos_model();
    $model_clientes = new Clientes_model();
    // Obtener los datos de la cabecera de la venta para obtener el id_cliente
    // Bloquea la cuenta corriente como "Modificando" (libera la venta que esta sesión tuviera abierta)
    $error_bloqueo = \App\Libraries\BloqueoVenta::tomar((int) $id_pedido, ['Cta_Cte'], 'Modificando');
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

    $nombre_cli = $cabecera ? $cabecera['nombre_prov_client'] : null; 
    $id_pedido = $cabecera ? $cabecera['id'] : null;
    $fecha_pedido = $cabecera ? $cabecera['fecha_pedido'] : null;
    $tipo_compra = $cabecera ? $cabecera['tipo_compra'] : null;
    $tipo_pago = $cabecera ? $cabecera['tipo_pago'] : null;
    //print_r($fecha_pedido);
    //exit;
    // Guardar los datos en la sesión para no perderlos si el carrito queda vacío
    $session->set([
        'id_pedido' => $id_pedido,
        'id_cliente_pedido' => $id_cliente,
        'nombre_cli_regis' => $nombre_cli_regis,
        'id_vendedor' => $id_vendedor,
        'nombre_vendedor' => $nombre_vendedor,        
        'fecha_pedido' => $fecha_pedido,
        'tipo_compra' => $tipo_compra,
        'tipo_pago' => $tipo_pago,
        'estado' => 'Modificando_Cta_Cte'
    ]);
    // Obtener los productos del pedido
    $detalles = $detalle_model->where('venta_id', $id_pedido)->findAll();

    // Limpiar el carrito antes de cargar los productos
    $cart->destroy();


    if (!$detalles) {
        \App\Libraries\BloqueoVenta::liberarDeSesion();
        session()->setFlashdata('error', 'No se encontraron productos en el pedido.');
        return redirect()->to($this->request->getHeaderLine('referer') ?: base_url('comprasCta_Cte'));
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
    return redirect()->to('catalogo');
    }
    
    session()->setFlashdata('msgEr', $error_bloqueo);
    return redirect()->to('comprasCta_Cte');
}


//Cancelamos la edicion del Pedido.
public function cancelar_edicion($id_pedido){
        //print_r($id_pedido);
        //exit;
        // Vuelve a Cta_Cte y limpia sesión y carrito
        \App\Libraries\BloqueoVenta::cancelar((int) $id_pedido);
        return redirect()->to(base_url('comprasCta_Cte'));
    }

}