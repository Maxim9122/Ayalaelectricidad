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

class Pedidos_controller extends Controller{

	public function __construct(){
           helper(['form', 'url']);
	}

    public function ListarPedidos()
    {
        
        $session = session();
        //print_r($session->get());
        //exit;
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
        $filtros = [
            'estado' => 'Pendiente',
            'fecha_hoy' => date('d-m-Y'), 
        ];
        
        // Instanciar el modelo
        $cabeceraModel = new Cabecera_model();
    
        // Llamar al método del modelo para obtener las ventas con clientes
        $datos['pedidos'] = $cabeceraModel->obtenerPedidos($filtros);
        //print_r($datos);
        //exit;

        $data['titulo'] = 'Listado de Pedidos';
        echo view('navbar/navbar');
        echo view('header/header', $data);
        echo view('pedidos/ListaPedidos_view', $datos);
        echo view('footer/footer');
    }

    public function PedidosTodos()
    {
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
        $filtros = [
            'estado' => 'Pendiente',
            'fecha_hoy' => '',            
        ];
        // Instanciar el modelo
        $cabeceraModel = new Cabecera_model();
    
        // Llamar al método del modelo para obtener las ventas con clientes
        $datos['pedidos'] = $cabeceraModel->obtenerPedidos($filtros);
        //print_r($datos);
        //exit;

        $data['titulo'] = 'Listado de Pedidos';
        echo view('navbar/navbar');
        echo view('header/header', $data);
        echo view('pedidos/pedidosDeDiaDistinto_alActual', $datos);
        echo view('footer/footer');
    }


    public function nuevoPedido()
    {
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
            // Cargar el modelo de servicios
            $serviciosModel = new Servicios_model();
    
            // Obtener todos los servicios desde la base de datos
            $servicios = $serviciosModel->getServicio();
    
            // Preparar los datos para la vista
            $data = [
                'titulo' => 'Crear Nuevo Turno',
                'servicios' => $servicios // Pasamos los servicios a la vista
            ];
    
            // Cargar las vistas
            echo view('navbar/navbar');
            echo view('header/header', $data);
            echo view('pedidos/nuevoPedido_view', $data); // Pasamos los datos a la vista
            echo view('footer/footer');
        }


    //Verifica y guarda los pedidos de clientes ya registrados
    public function pedidoClienteRegistrado() {
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $fecha = date('d-m-Y');

         // Rescato el ID del cliente.
         $id_cliente = $this->request->getVar('id_cliente');

         // Convertir la fecha al formato dd-mm-yyyy
         $fecha_turno = $this->request->getVar('fecha_turno');
         $fecha_turno_formateada = date('d-m-Y', strtotime($fecha_turno));
        $session = session();
        

        // Guardar datos del formulario en la sesión
        $session->set('id_cliente', $this->request->getPost('id_cliente'));
        $session->set('fecha_turno', $fecha_turno_formateada);
        $session->set('hora_turno', $this->request->getPost('hora_turno'));

        $pedidosModel = new Pedidos_model();
        $clienteModel = new Clientes_model();
        $cliente = $clienteModel->getCliente($this->request->getPost('id_cliente'));
        $nombre_cliente = $cliente['nombre'];  // Suponiendo que 'nombre' es el campo que contiene el nombre del cliente
        $session->set('nombre_cliente', $nombre_cliente);
 
         // Guardar el pedido en la base de datos
         $pedidosModel->save([
             'id_cliente' => $id_cliente,
             'id_usuario' => 1,
             'fecha_registro' => $fecha,
             'fecha_turno' => $fecha_turno_formateada,
             'hora_turno' => $this->request->getVar('hora_turno'),
             'id_servi' => $this->request->getVar('tipo_servicio'),
             'estado' => 'Pendiente',
         ]);
 
         session()->setFlashdata('msg', 'Pedido Registrado!');
         return redirect()->to($this->request->getHeader('referer')->getValue());
    }


    public function cargar_pedido_en_carrito($id_pedido)
{
    $session = session();
    $cart = \Config\Services::cart();
    $US_model = new Usuarios_model();
    $detalle_model = new VentaDetalle_model();
    $cabecera_model = new Cabecera_model(); // Asegúrate de tener este modelo
    $producto_model = new Productos_model();
    $model_clientes = new Clientes_model();
    // Obtener los datos de la cabecera de la venta para obtener el id_cliente
    $cabecera = $cabecera_model->find($id_pedido);
    if($cabecera['estado'] == 'Pendiente'){
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
        'estado' => 'Modificando'
    ]);
    // Obtener los productos del pedido
    $detalles = $detalle_model->where('venta_id', $id_pedido)->findAll();

    // Limpiar el carrito antes de cargar los productos
    $cart->destroy();


    if (!$detalles) {
        session()->setFlashdata('error', 'No se encontraron productos en el pedido.');
        return redirect()->to($this->request->getHeader('referer')->getValue());
    }

    // Actualizar el estado del pedido a "Modificando"
    $cabecera_model->update($id_pedido, ['estado' => 'Modificando']);

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
    
    session()->setFlashdata('msg', 'Este pedido ya esta siendo Modificado por otro usuario!');
    return redirect()->to('pedidos');
    }


//Cancelamos la edicion del Pedido.
public function cancelar_edicion($id_pedido){
        //print_r($id_pedido);
        //exit;
        $cart = \Config\Services::cart();
        $Cabecera_model = new Cabecera_model();
            
        // Después de guardar el pedido (cuando ya no se necesiten los datos de la sesión)
        $session = session();
        $session->remove(['nombre_cli_regis','nombre_cli','estado','id_vendedor', 'nombre_vendedor', 'id_cliente_pedido' , 'id_pedido', 'fecha_pedido','tipo_compra','tipo_pago','total_venta']);
        // Actualizar el estado del pedido a "Pendiente"
        $Cabecera_model->update($id_pedido, ['estado' => 'Pendiente']);
        $cart->destroy();
        return redirect()->to(base_url('pedidos'));
    }


    //Elimina el pedido Cancelado
    public function Pedido_cancelado($id_pedido)
    {
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
        $cabecera_model = new Cabecera_model();
        $detalle_model = new VentaDetalle_model();
        $producto_model = new Productos_model();
    
        // Obtener los detalles del pedido
        $detalles = $detalle_model->where('venta_id', $id_pedido)->findAll();
    
        if (!$detalles) {
            session()->setFlashdata('error', 'No se encontraron productos en el pedido.');
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
    
        session()->setFlashdata('msg', 'Pedido cancelado y stock restaurado con éxito.');
    
        return redirect()->to($this->request->getHeader('referer')->getValue());
    }
    
    //Muestra todos los pedidos realizados    
    public function pedidosCompletados()
    {
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
        $filtros = [            
            'estado' => '',           
            'fecha_hoy' => '',            
        ];
        // Instanciar el modelo
        $cabeceraModel = new Cabecera_model();
    
        // Llamar al método del modelo para obtener las ventas con clientes
        $datos['pedidos'] = $cabeceraModel->obtenerPedidos($filtros);
        //print_r($datos);
        //exit;
        $UsuariosModel = new Usuarios_model();     

        // Obtener barberos, servicios y clientes
        $datos['usuarios'] = $UsuariosModel->getUsBaja('NO');       

        // Preparar datos para la vista
        $data['titulo'] = 'Listado de Pedidos Completados';

        // Cargar las vistas
        echo view('navbar/navbar');
        echo view('header/header', $data);
        echo view('pedidos/pedidosCompletados', $datos);
        echo view('footer/footer');
    }

//Filtrado de pedidos por fecha y vendedor
public function filtrarPedidos()
{
    $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
    $cabeceraModel = new Cabecera_model();
    $filtros = [
        'estado' => '',
        'fecha_hoy' => '',       
        'fecha_desde' => $this->request->getVar('fecha_desde'),
        'fecha_hasta' => $this->request->getVar('fecha_hasta'),
        'id_usuario' => $this->request->getVar('id_usuario'),
    ];

    $datos['pedidos'] = $cabeceraModel->obtenerPedidos($filtros);
    //Creo un objeto del tipo modelo y en la misma linea ejecuto una funcion de ese modelo.
    $datos2['usuarios'] = (new Usuarios_model())->getUsBaja('NO');

    $data['titulo'] = 'Listado de Pedidos Filtrados';
    echo view('navbar/navbar');
    echo view('header/header', $data);
    echo view('pedidos/pedidosCompletados', $datos + $datos2);
    echo view('footer/footer');
}


}