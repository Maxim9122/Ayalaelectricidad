<?php
namespace App\Controllers;

require_once APPPATH . 'Libraries/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

use CodeIgniter\Controller;
Use App\Models\Productos_model;
Use App\Models\Cabecera_model;
Use App\Models\VentaDetalle_model;
Use App\Models\Clientes_model;
use App\Models\Usuarios_model;
use App\Models\Cae_model;


class Carrito_controller extends Controller{

	public function __construct(){
           helper(['form', 'url']);
	}

	public function ListVentasCabecera()
{
    $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
    $perfil = $session->get('perfil_id');
        if($perfil == 2){
            return redirect()->to(base_url('catalogo'));
        }
    // Instanciar el modelo
    $USmodel = new Usuarios_model();
    $cabeceraModel = new Cabecera_model();
    
    // Llamar al método del modelo para obtener las ventas con clientes
    $datos['ventas'] = $cabeceraModel->getVentasConClientes();
    $datos2['usuarios'] = $USmodel->getUsBaja('NO');
    // Pasar el título y los datos a las vistas
    $data['titulo'] = 'Listado de Compras';
    echo view('navbar/navbar');
    echo view('header/header', $data);
    echo view('comprasXcliente/ListaVentas_view', $datos + $datos2);
    echo view('footer/footer');
}

//Filtrado de ventas por fechas y vendedor.
public function filtrarVentas()
{
    $session = session();

    // Verifica si el usuario está logueado
    if (!$session->get('id')) { 
        return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
    }

    // Cargar modelos
    $cabeceraModel = new Cabecera_model();
    $usuariosModel = new Usuarios_model();

    // Obtener y limpiar filtros
    $filtros = [
        'fecha_hoy' => '',
        'fecha_desde' => trim($this->request->getVar('fecha_desde') ?? ''),
        'fecha_hasta' => trim($this->request->getVar('fecha_hasta') ?? ''),
        'estado' => trim($this->request->getVar('estado') ?? ''),
    ];

    // Obtener datos
    $datos['ventas'] = $cabeceraModel->getVentasConClientes($filtros);
    $datos['usuarios'] = $usuariosModel->getUsBaja('NO');

    // Pasar filtros a la vista para mantener los valores seleccionados
    $datos['filtros'] = $filtros;

    // Definir título
    $data['titulo'] = 'Listado de Pedidos Filtrados';

    // Cargar vistas
    return view('navbar/navbar')
        . view('header/header', $data)
        . view('comprasXcliente/ListaVentas_view', $datos)
        . view('footer/footer');
}



public function ListaComprasCabeceraCliente($id)
{
    // Obtener la fecha de hoy
    $fechaHoy = date('d-m-Y');

    // Instanciar el modelo
    $cabeceraModel = new Cabecera_model();

    // Obtener las ventas del cliente para la fecha de hoy
    $datos['ventas'] = $cabeceraModel->getVentasPorClienteYFecha($id, $fechaHoy);

    // Preparar el título y cargar las vistas
    $data['titulo'] = 'Listado de Compras';
    echo view('navbar/navbar');
    echo view('header/header', $data);
    echo view('comprasXcliente/ListaTurnos_view', $datos);
    echo view('footer/footer');
}

public function ListCompraDetalle($id)
{
    $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
    // Instanciar el modelo
    $cabeceraModel = new Cabecera_model();

    // Obtener los detalles de la venta
    $datos['ventas'] = $cabeceraModel->getDetallesVenta($id);

    // Preparar el título y cargar las vistas
    $data['titulo'] = 'Detalle de Compras';
    echo view('navbar/navbar');
    echo view('header/header', $data);
    echo view('comprasXcliente/CompraDetalle_view', $datos);
    echo view('footer/footer');
}

    public function productosAgregados(){
        $cart = \Config\Services::cart();
		$carrito['carrito']=$cart->contents();
        $data['titulo']='Productos en el Carrito'; 
		echo view('navbar/navbar');
        echo view('header/header',$data);        
        echo view('carrito/ProductosEnCarrito',$carrito);
        echo view('footer/footer');
    }

    //Agrega elemento al carrito
	function add()
    {
    $session = session();
    $estado = $session->get('estado'); // Verificamos el estado de la sesión
    $id_pedido = $session->get('id_pedido');

    $producto_id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio_vta'];
    $cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;

    $prodModel = new Productos_model();
    $producto = $prodModel->getProducto($producto_id);
    $stock_actual = $producto['stock'];

    $VentaDetalle_model = new VentaDetalle_model();
    
    // Si estamos en modo modificación, obtener la cantidad ya reservada
    $cantidad_reservada = 0;
    if ($estado == 'Modificando' || $estado == 'Modificando_SF') {
        $cantidad_reservada = $VentaDetalle_model
            ->where('venta_id', $id_pedido)
            ->where('producto_id', $producto_id)
            ->select('cantidad')
            ->get()
            ->getRowArray()['cantidad'] ?? 0;
    }

    // Calcular el stock disponible real
    $stock_disponible = $stock_actual + $cantidad_reservada;

    // Verificar si hay suficiente stock
    if ($stock_disponible <= 0) {
        session()->setFlashdata('msgEr', 'No hay Stock Disponible para este Producto.');
        return redirect()->to(base_url('catalogo'));
    }

    $cart = \Config\Services::cart();
    $cart_items = $cart->contents();
    $producto_encontrado = false;

    foreach ($cart_items as $item) {
        if ($item['id'] == $producto_id) {
            // Si el producto ya está en el carrito, incrementar la cantidad seleccionada
            $nueva_cantidad = $item['qty'] + $cantidad;

            // Verificar si supera el stock disponible
            if ($nueva_cantidad > $stock_disponible) {
                session()->setFlashdata('msgEr', 'No puedes agregar más productos de los disponibles en stock.');
                return redirect()->to(base_url('catalogo'));
            }

            // Actualizar cantidad en el carrito
            $cart->update([
                'rowid' => $item['rowid'],
                'qty'   => $nueva_cantidad
            ]);

            $producto_encontrado = true;
            break;
        }
    }

    // Si el producto no está en el carrito, agregarlo con la cantidad seleccionada
    if (!$producto_encontrado) {
        $cart->insert([
            'id'      => $producto_id,
            'qty'     => $cantidad,
            'price'   => $precio,
            'name'    => $nombre,
            'options' => ['stock' => $stock_disponible] // Guardamos el stock disponible como referencia
        ]);
    }

    session()->setFlashdata('msg', 'Producto Agregado!');
    return redirect()->to(base_url('catalogo'));
    }



	//Agrega elemento al carrito desde confirmar
	function agregar()
	{
        $cart = \Config\Services::cart();
        // Genera array para insertar en el carrito
        
		$id_producto = uniqid('prod_') . random_int(100000, 999900);
		$cart->insert(array(
            'id'      => $id_producto,
            'qty'     => 1,
            'price'   => $_POST['precio_vta'],
            'name'    => $_POST['nombre'],
            'options' => array('stock' => $_POST['stock'])
            
         ));
		 session()->setFlashdata('msg','Producto Agregado!');
        // Redirige a la misma página que se encuentra
		return redirect()->to(base_url('CarritoList'));
	}

	//Agrega elemento al carrito desde confirmar
	function agregarDesdeListaProd()
	{
        $cart = \Config\Services::cart();
        // Genera array para insertar en el carrito
		$id_producto = uniqid('prod_') . random_int(100000, 999900);
		$cart->insert(array(
            'id'      => $id_producto,
            'qty'     => 1,
            'price'   => $_POST['precio_vta'],
            'name'    => $_POST['nombre'],
            'options' => array('stock' => $_POST['stock'])
            
         ));
		 session()->setFlashdata('msg','Producto Agregado!');
        // Redirige a la misma página que se encuentra
		return redirect()->to($this->request->getHeader('referer')->getValue());
	}

    //Elimina elemento del carrito o el carrito entero
	function remove($rowid){
        $cart = \Config\Services::cart();
        //Si $rowid es "all" destruye el carrito
		if ($rowid==="all")
		{
			$cart->destroy();
		}
		else //Sino destruye sola fila seleccionada
		{               
			session()->setFlashdata('msg','Producto Eliminado');
            // Actualiza los datos
			$cart->remove($rowid);
		}
		
        // Redirige a la misma página que se encuentra
		return redirect()->to(base_url('CarritoList'));
	}

    public function procesarCarrito()
    {
        $accion = $this->request->getPost('accion');
     //Actualizamos los importes del carrito y cantidades   
        if ($accion == 'actualizar') {
            
            $session = session();
            $id_pedido = $session->get('id_pedido');
            
            $cart = \Config\Services::cart();
            $cart_info = $this->request->getPost('cart');
            
            $VentaDetalle_model = new VentaDetalle_model();
            $Producto_model = new Productos_model();
        
                //Array para guardar todos los productos que tengan stock no disponible
            $errores_stock = [];
            foreach ($cart_info as $id => $carrito) {   
            $id_producto = $carrito['id'];

            // Obtener el stock actual desde la base de datos
            $producto = $Producto_model->find($id_producto);
            $stock_actual = $producto['stock'];
            $nombre_producto = $producto['nombre']; // Obtener el nombre del producto
            $precioLimpio = str_replace('.', '', $carrito['price']); // Elimina el punto de miles
            //print_r($precioLimpio);exit;
            // Obtener la cantidad que ya estaba reservada en la venta anterior
            $cantidad_reservada = $VentaDetalle_model
                ->where('venta_id', $id_pedido)
                ->where('producto_id', $id_producto)
                ->select('cantidad')
                ->get()
                ->getRowArray()['cantidad'] ?? 0;

            // Calcular el stock disponible para esta modificación
            $stock_disponible = $stock_actual + $cantidad_reservada;

            $rowid = $carrito['rowid'];
            $price = $precioLimpio;
            $amount = $price * $carrito['qty'];
            $qty = $carrito['qty'];

            // Validar contra el stock disponible, considerando lo ya reservado
            if ($qty <= $stock_disponible && $qty >= 1) { 
                $cart->update([
                    'rowid'   => $rowid,
                    'price'   => $price,
                    'amount'  => $amount,
                    'qty'     => $qty
                ]);	    	
            } else {
                // Agregar el producto a la lista de errores
                $errores_stock[] = "Producto: <strong>$nombre_producto</strong> - Cantidad solicitada: <strong>$qty</strong> - Stock disponible: $cantidad_reservada(reservados) mas $stock_actual <strong>($stock_disponible)</strong>";
            }                
            }

            // Si hay errores de stock, mostrar mensaje y redirigir
            if (!empty($errores_stock)) {
                $mensaje_error = "Los siguientes productos no tienen suficiente Stock:<br>" . implode("<br>", $errores_stock);
                session()->setFlashdata('msgEr', $mensaje_error);
                return redirect()->to('CarritoList');
            }
        
            
        session()->setFlashdata('msg', 'Carrito Actualizado!');
            // Redirige a la misma página que se encuentra
        return redirect()->to(base_url('CarritoList'));
            


//Esta parte es para avanzar a CasiListo primero vuelve a validar el $cart
        } elseif ($accion == 'confirmar') {
            
            $cart = \Config\Services::cart();
            // Recibe los datos del carrito, calcula y actualiza
            $cart_info = $this->request->getPost('cart');
            $errores_stock = false; // Variable para controlar si hay errores de stock
            $session = session();
            $id_pedido = $session->get('id_pedido');
            $VentaDetalle_model = new VentaDetalle_model();
            $Producto_model = new Productos_model();

            //Array para guardar todos los productos que tengan stock no disponible
            $errores_stock = [];
            foreach ($cart_info as $id => $carrito) {   
            $id_producto = $carrito['id'];

            // Obtener el stock actual desde la base de datos
            $producto = $Producto_model->find($id_producto);
            $stock_actual = $producto['stock'];
            $nombre_producto = $producto['nombre']; // Obtener el nombre del producto
            $precioLimpio = str_replace('.', '', $carrito['price']); // Elimina el punto de miles
            // Obtener la cantidad que ya estaba reservada en la venta anterior
            $cantidad_reservada = $VentaDetalle_model
                ->where('venta_id', $id_pedido)
                ->where('producto_id', $id_producto)
                ->select('cantidad')
                ->get()
                ->getRowArray()['cantidad'] ?? 0;

            // Calcular el stock disponible para esta modificación
            $stock_disponible = $stock_actual + $cantidad_reservada;

            $rowid = $carrito['rowid'];
            $price = $precioLimpio;
            $amount = $price * $carrito['qty'];
            $qty = $carrito['qty'];

            // Validar contra el stock disponible, considerando lo ya reservado
            if ($qty <= $stock_disponible && $qty >= 1) { 
                $cart->update([
                    'rowid'   => $rowid,
                    'price'   => $price,
                    'amount'  => $amount,
                    'qty'     => $qty
                ]);	    	
            } else {
                // Agregar el producto a la lista de errores
                $errores_stock[] = "Producto: <strong>$nombre_producto</strong> - Cantidad solicitada: <strong>$qty</strong> - Stock disponible: $cantidad_reservada(reservados) mas $stock_actual <strong>($stock_disponible)</strong>";
            }                
            }

            // Si hay errores de stock, mostrar mensaje y redirigir
            if (!empty($errores_stock)) {
                $mensaje_error = "Los siguientes productos no tienen suficiente Stock:<br>" . implode("<br>", $errores_stock);
                session()->setFlashdata('msgEr', $mensaje_error);
                return redirect()->to('CarritoList');
            }
        
            // Redirige a la página de confirmacion de compra si los calculos de stock estan bien.
            return redirect()->to(base_url('casiListo'));

//Si el proceso es de una Venta que se esta modificando entra aqui.
        } elseif ($accion == 'modificar') {
        $Producto_model = new Productos_model();
        $VentaDetalle_model = new VentaDetalle_model();
        $session = session();
        $cart = \Config\Services::cart();
    
    // Recibe los datos del carrito, calcula y actualiza
        $cart_info = $this->request->getPost('cart');
        $id_pedido = $session->get('id_pedido');
        
    //Array para guardar todos los productos que tengan stock no disponible
    $errores_stock = [];
    foreach ($cart_info as $id => $carrito) {   
       $id_producto = $carrito['id'];

       // Obtener el stock actual desde la base de datos
       $producto = $Producto_model->find($id_producto);
       $stock_actual = $producto['stock'];
       $nombre_producto = $producto['nombre']; // Obtener el nombre del producto
        $precioLimpio = str_replace('.', '', $carrito['price']); // Elimina el punto de miles
       // Obtener la cantidad que ya estaba reservada en la venta anterior
       $cantidad_reservada = $VentaDetalle_model
           ->where('venta_id', $id_pedido)
           ->where('producto_id', $id_producto)
           ->select('cantidad')
           ->get()
           ->getRowArray()['cantidad'] ?? 0;

       // Calcular el stock disponible para esta modificación
       $stock_disponible = $stock_actual + $cantidad_reservada;

       $rowid = $carrito['rowid'];
       $price = $precioLimpio;
       $amount = $price * $carrito['qty'];
       $qty = $carrito['qty'];

       // Validar contra el stock disponible, considerando lo ya reservado
       if ($qty <= $stock_disponible && $qty >= 1) { 
           $cart->update([
               'rowid'   => $rowid,
               'price'   => $price,
               'amount'  => $amount,
               'qty'     => $qty
           ]);	    	
       } else {
           // Agregar el producto a la lista de errores
           $errores_stock[] = "Producto: <strong>$nombre_producto</strong> - Cantidad solicitada: <strong>$qty</strong> - Stock disponible: $cantidad_reservada(reservados) mas $stock_actual <strong>($stock_disponible)</strong>";
       }                
       }

       // Si hay errores de stock, mostrar mensaje y redirigir
       if (!empty($errores_stock)) {
           $mensaje_error = "Los siguientes productos no tienen suficiente Stock:<br>" . implode("<br>", $errores_stock);
           session()->setFlashdata('msgEr', $mensaje_error);
           return redirect()->to('CarritoList');
       }
        
       // Inicializar la variable para la suma total de la venta
       $total_venta = 0;

       // Recorrer el carrito y calcular el total
       foreach ($cart->contents() as $item) {
           $total_venta += $item['subtotal']; // Sumar cada subtotal (precio * cantidad)
       }

        $id_vendedor = $session->get('id_vendedor');
        $id_cliente = $session->get('id_cliente');        
        $fecha_pedido = $session->get('fecha_pedido');
        $tipo_compra = $session->get('tipo_compra');

        // Establecer zona horaria y obtener fecha/hora en formato correcto
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $hora = date('H:i:s'); // Formato TIME
        $fecha = date('d-m-Y'); // Formato DATE

        // Actualizar Venta existente con los nuevos datos
        if ($id_pedido > 0 && $tipo_compra == 'Compra_Normal') {
            // Cargar los modelos necesarios para trabajar con los detalles y la cabecera
            $VentaDetalle_model = new VentaDetalle_model();
            $Producto_model = new Productos_model();
            $Cabecera_model = new Cabecera_model();

            // Actualizar la cabecera de la venta con los nuevos datos
            $cabecera_model = new Cabecera_model();
            $cabecera_model->update($id_pedido, [
        'fecha' => $fecha, // Actualizamos la fecha
        'hora' => $hora, // Actualizamos la hora
        'id_cliente' => $id_cliente, // Actualizamos el id del cliente
        'id_usuario' => $id_vendedor, // Actualizamos el id del usuario (vendedor)
        'total_venta' => $total_venta, // Actualizamos el total de la venta
        'total_bonificado' => $total_venta, // Actualizamos el total con descuento (si aplica)
        'tipo_compra' => 'Compra_Normal', // Actualizamos el tipo de compra (Pedido o Compra_Normal)
        'estado' => 'Pendiente', // Mantenemos el estado como "Pendiente" (puede cambiar según el flujo)
        ]);

        // Obtener los productos del pedido anterior
        $productos_anteriores = $VentaDetalle_model->where('venta_id', $id_pedido)->findAll();
    
        // 1️⃣ **Devolver stock del pedido anterior**
        foreach ($productos_anteriores as $detalle) {
            $producto = $Producto_model->find($detalle['producto_id']);
            if ($producto) {
                $nuevo_stock = $producto['stock'] + $detalle['cantidad'];
                $Producto_model->update($detalle['producto_id'], ['stock' => $nuevo_stock]);
            }
        }
        
    // Eliminar los detalles de la venta anterior para luego agregar los nuevos detalles del carrito
    $VentaDetalle_model->where('venta_id', $id_pedido)->delete();

    // Insertar los nuevos detalles del carrito en la base de datos
    if ($cart) {
        foreach ($cart->contents() as $item) {
            // Guardar cada producto del carrito como un nuevo detalle de la venta
            $VentaDetalle_model->save([
                'venta_id' => $id_pedido,  // Usamos el id del pedido existente para vincular el detalle
                'producto_id' => $item['id'], // Producto id desde el carrito
                'cantidad' => $item['qty'], // Cantidad del producto en el carrito
                'precio' => $item['price'], // Precio del producto
                'total' => $item['subtotal'], // Total por ese producto (precio * cantidad)
            ]);

            // Actualizar el stock de cada producto después de la venta
            $producto = $Producto_model->find($item['id']); // Obtener el producto desde la base de datos
            if ($producto && isset($producto['stock'])) {
                // Restar la cantidad vendida del stock del producto
                $stock_edit = $producto['stock'] - $item['qty'];
                $Producto_model->update($item['id'], ['stock' => $stock_edit]); // Actualizamos el stock en la base de datos
            }
            }
        }

       $session->remove(['estado','id_vendedor', 'nombre_vendedor', 'id_cliente', 'id_pedido', 'fecha_pedido','tipo_compra','tipo_pago','total_venta']);
        // Limpiar el carrito después de guardar los datos
        $cart->destroy();

        // Redirigir al usuario con un mensaje de éxito según el tipo de compra
        session()->setFlashdata('msg', 'Venta Actualizada con Éxito!');
        return redirect()->to('caja');
        }

//Modifica y guarda los cambios de la venta realizada Sin Facturar
        } elseif ($accion == 'GuardarCambios'){
            $Producto_model = new Productos_model();
            $VentaDetalle_model = new VentaDetalle_model();
            $cart = \Config\Services::cart();           
            $session = session();
           
            // Recibe los datos del carrito, calcula y actualiza
            $cart_info = $this->request->getPost('cart');
            $motivo = $this->request->getPost('motivo_modif');            
            $tipo_pago_dif = $this->request->getPost('tipo_pago_dif'); // Puede ser 'Efectivo' o 'Transferencia o Tarjeta'
            $tipo_pago_anterior = $session->get('tipo_pago'); // Puede ser 'Mixto', 'Transferencia' o 'Efectivo'
            $id_pedido = $session->get('id_pedido');
            $tipo_pago_Modif = '';            

            //Array para guardar todos los productos que tengan stock no disponible
            $errores_stock = [];
         foreach ($cart_info as $id => $carrito) {   
            $id_producto = $carrito['id'];

            // Obtener el stock actual desde la base de datos
            $producto = $Producto_model->find($id_producto);
            $stock_actual = $producto['stock'];
            $nombre_producto = $producto['nombre']; // Obtener el nombre del producto
            $precioLimpio = str_replace('.', '', $carrito['price']); // Elimina el punto de miles
            // Obtener la cantidad que ya estaba reservada en la venta anterior
            $cantidad_reservada = $VentaDetalle_model
                ->where('venta_id', $id_pedido)
                ->where('producto_id', $id_producto)
                ->select('cantidad')
                ->get()
                ->getRowArray()['cantidad'] ?? 0;

            // Calcular el stock disponible para esta modificación
            $stock_disponible = $stock_actual + $cantidad_reservada;

            $rowid = $carrito['rowid'];
            $price = $precioLimpio;
            $amount = $price * $carrito['qty'];
            $qty = $carrito['qty'];

            // Validar contra el stock disponible, considerando lo ya reservado
            if ($qty <= $stock_disponible && $qty >= 1) { 
                $cart->update([
                    'rowid'   => $rowid,
                    'price'   => $price,
                    'amount'  => $amount,
                    'qty'     => $qty
                ]);	    	
            } else {
                // Agregar el producto a la lista de errores
                $errores_stock[] = "Producto: <strong>$nombre_producto</strong> - Cantidad solicitada: <strong>$qty</strong> - Stock disponible: $cantidad_reservada(reservados) mas $stock_actual <strong>($stock_disponible)</strong>";
            }                
            }

            // Si hay errores de stock, mostrar mensaje y redirigir
            if (!empty($errores_stock)) {
                $mensaje_error = "Los siguientes productos no tienen suficiente Stock:<br>" . implode("<br>", $errores_stock);
                session()->setFlashdata('msgEr', $mensaje_error);
                return redirect()->to('CarritoList');
            }

            //Si el campo del motivo viene vacio lo devuelve a la vista.
                if(!$motivo){
                    session()->setFlashdata('msgEr', 'El Motivo es Obligatorio.!!');
                    return redirect()->to('CarritoList');
                }

            // Comparar ambas variables y asignar el valor a $tipo_pago_Modif
                if ($tipo_pago_dif === $tipo_pago_anterior) {
                    $tipo_pago_Modif = $tipo_pago_dif; // Coinciden
                } elseif (in_array($tipo_pago_dif, ['Efectivo', 'Transferencia', 'Tarjeta'])) {
                    $tipo_pago_Modif = 'Mixto'; // No coinciden
                } else {
                    $tipo_pago_Modif = $tipo_pago_anterior; // Si el valor no es válido, mantener el anterior
                }           
           
            // Inicializar la variable para la suma total de la venta
            $total_venta = 0;

            // Recorrer el carrito y calcular el total
            foreach ($cart->contents() as $item) {
                $precioLimpio = str_replace('.', '', $item['subtotal']); // Elimina el punto de miles
                $total_venta += $precioLimpio; // Sumar cada subtotal (precio * cantidad)
            }                
        
    
            $id_vendedor = $session->get('id_vendedor');
            $id_cliente = $session->get('id_cliente');            
            $fecha_pedido = $session->get('fecha_pedido');
            $tipo_compra = $session->get('tipo_compra');            
            $total_anterior = $session->get('total_venta');
            $total_anterior_bonif = $session->get('total_bonificado');
            $estado = $session->get('estado');
            $cd_efectivo =$session->get('cd_efectivo'); 

            $pago_efec = $session->get('pago_efec');
            $nuevoPago_Efec = $pago_efec;
            $pago_transfer = $session->get('pago_transfer');
            $nuevoPago_Transfer = $pago_transfer;
            $pago_tarjeta = $session->get('pago_tarjeta'); 
            $nuevoPago_Tarjeta = $pago_tarjeta; 
            $dif_pago_efec = 0;
                  
            //El resto entre el total actual de la venta menos el total anterior que usamos el total bonificado
            $resul_descuento = 0;
            $resul_adicional = 0;           
            $total_bonificado_OK = 0;
            //$total_venta = 200;
            $resto_ActualMenosAnterior = $total_venta - $total_anterior;
             
            //Si el resultado de la resta de los totales actual y anterior da mayor a 0, significa que tiene 
            //que pagar una diferencia, en efectivo o transferencia.
            if($resto_ActualMenosAnterior > 0){ 

            if($tipo_pago_dif == 'Efectivo'){
                //Calculo cuanto tengo que restar al total general de la venta nueva modificada (Bonificacion)
                $resul_descuento = $resto_ActualMenosAnterior / $cd_efectivo;

                $total_bonificado_OK = $total_anterior_bonif + $resul_descuento;
                //Sumo el pago de la nueva diferencia pagada en efectivo al monto anterior de efectivo.
                $nuevoPago_Efec = $nuevoPago_Efec + $resul_descuento;
                
             //Si el pago es con transferencia el total con bonificacion es igual al total general.   
            }elseif ($tipo_pago_dif == 'Transferencia'){
                $total_bonificado_OK = $total_anterior_bonif + $resto_ActualMenosAnterior;
                //Sumamos el resto de la venta nueva menos la anterior al monto transferencia
                $nuevoPago_Transfer = $nuevoPago_Transfer + $resto_ActualMenosAnterior; 
                
            //Si el pago es con tarjeta el total con bonificacion es el total de la venta nueva
            //mas la diferencia con adicional.  
            }elseif ($tipo_pago_dif == 'Tarjeta'){
                $resul_adicional = $resto_ActualMenosAnterior * 1.1;
                $total_bonificado_OK = $total_anterior_bonif + $resul_adicional;

                $nuevoPago_Tarjeta = $nuevoPago_Tarjeta + $resul_adicional;
                
            }
            //Si el resto del total actual menos el total anterior(Bonif) es negativo o igual a 0
            //significa que tiene que devolver parte de la plata del gasto en la venta anterior
            //por eso se le asigna el mismo valor de la venta actual al total bonificado
            }elseif ($resto_ActualMenosAnterior == 0){

                $total_bonificado_OK = $total_anterior_bonif;
                

            }elseif ($resto_ActualMenosAnterior < 0){  
                  
                //Si el nuevo total es menor al disponible en efectivo que ya tenia, el nuevo monto en efectivo
                //es el valor de la nueva venta en precio descuento, se devolvio parte del efectivo
                //mas todo de la tarjeta y todo de la transferencia
                
                if($total_venta <= ($pago_efec * $cd_efectivo)){ 
                    $nuevoPago_Efec = $total_venta / $cd_efectivo;                    
                    //Los otros pagos fueron devueltos al cubrir la nueva venta solo con el efectivo
                    //Entonces quedarian en 0 la tarjeta y la transfer
                    $nuevoPago_Transfer = 0;
                    $nuevoPago_Tarjeta = 0;
                    
                    // El nuevo total bonificado seria el total de la venta pagada solo con saldo del efectivo
                    $total_bonificado_OK = $nuevoPago_Efec;
                    
                 }else if($total_venta > ($pago_efec * $cd_efectivo)){
                    //Al usar todo el efectivo para pagar la nueva venta guardo todo ese efectivo en
                    //nuevo_Pago_EfecT Y QUEDA UN RESTO PARA SEGUIR DESCONTANDO A LOS DEMAS MONTOS
                    $nuevoPago_Efec = $pago_efec;
                    //Calculo cuanto queda de restar el monto en transfer.
                    $nuevoPago_Transfer = $pago_transfer - ($total_venta - ($pago_efec * $cd_efectivo));
                    
                        if($nuevoPago_Transfer <= 0){
                            //Si el resto de restar lo que quedo a pagar despues de usar todo el efectivo
                            //es negativo o 0 significa que tambien se uso todo lo de transferencia.
                            //Solo se asigna lo que se ocupo del salgo de tarjeta para pagar lo que faltó, el resto ya se devolvio                                    
                            $nuevoPago_Tarjeta = abs($nuevoPago_Transfer);
                            //Al resto de lo que quedo del saldo de tarjeta lo multiplicamos por 1.1 para guardar el adicional
                            //que tambien cuenta para guardar.
                            $nuevoPago_Tarjeta = ($nuevoPago_Tarjeta * 1.1);
                            //Al ocuparse todo el saldo de transferencia esta se re asigna el monto total original
                            $nuevoPago_Transfer = $pago_transfer;
                            //Sumo los nuevos pagos, en este caso se uso todo de efectivo y transfer 
                            //y se suma lo que se ocupo del saldo en tarjeta porque lo que sobra se develve
                            $total_bonificado_OK = $nuevoPago_Efec + $nuevoPago_Transfer + $nuevoPago_Tarjeta;
                            
                        //Si queda saldo de transferencia luego de restar significa que alcanzo y es mayor a 0
                        } else if($nuevoPago_Transfer > 0){
                        //Guardamos en nuevo pago tarjeta 0 porque no se uso ese saldo y se devolvio
                            $nuevoPago_Tarjeta = 0;
                        //A la variable $nuevoPago_transfer le quedó saldo y hay que restar el saldo original
                        //menos el resto para guardar el resultado como lo que se uso para pagar.
                            $nuevoPago_Transfer = ($total_venta - ($pago_efec * $cd_efectivo));
                        //Asignamos la suma del saldo efectivo mas lo que se ocupdo del saldo de transferencia
                            $total_bonificado_OK = $nuevoPago_Efec + $nuevoPago_Transfer;
                            
                        }
                 }

            }

        
            //Establecer zona horaria y obtener fecha/hora en formato correcto
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $hora = date('H:i:s'); // Formato TIME
            $fecha = date('d-m-Y'); // Formato DATE
    
            // Actualizar el pedido o Venta existente con los nuevos datos
            if ($estado == 'Modificando_SF') {

            
                $Producto_model = new Productos_model();
                $VentaDetalle_model = new VentaDetalle_model();
                $cart = \Config\Services::cart();
                $session = session();
                
                // Recibe los datos del carrito, calcula y actualiza
                $cart_info = $this->request->getPost('cart');               
                
                $id_pedido = $session->get('id_pedido');                
                
                // Obtener los detalles originales de la venta
                $detalles_originales = $VentaDetalle_model->where('venta_id', $id_pedido)->findAll();
                
                // Crear un array para facilitar la búsqueda de detalles originales
                $detalles_originales_map = [];
                foreach ($detalles_originales as $detalle) {
                    $detalles_originales_map[$detalle['producto_id']] = $detalle;
                }
                
                // Recorrer el carrito y manejar el stock según los casos
                foreach ($cart_info as $id => $carrito) {
                    $id_producto = $carrito['id'];
                    $cantidad_original = isset($detalles_originales_map[$id_producto]) ? $detalles_originales_map[$id_producto]['cantidad'] : 0;
                    $cantidad_nueva = $carrito['qty'];
                    
                    // Obtener el stock actual desde la base de datos
                    $producto = $Producto_model->find($id_producto);
                    $stock_actual = $producto['stock'];
                
                    if ($cantidad_original > 0) {
                        // Caso 1: Reducción de la cantidad
                        if ($cantidad_nueva < $cantidad_original) {
                            // Devolver la cantidad original al stock
                            $stock_actual += $cantidad_original;
                
                            // Restar la cantidad actual del carrito más la diferencia
                            $diferencia = $cantidad_original - $cantidad_nueva;
                            $stock_actual -= ($cantidad_nueva + $diferencia);
                        }
                        // Caso 2: Eliminación del producto (no se hace nada, ya que no se devuelve al stock)
                        // Caso 3: Aumento de la cantidad
                        elseif ($cantidad_nueva > $cantidad_original) {
                            // Devolver la cantidad original al stock
                            $stock_actual += $cantidad_original;
                
                            // Restar la nueva cantidad del stock
                            $stock_actual -= $cantidad_nueva;
                        }
                        // Caso 4: No se modifica la cantidad (cantidad_nueva == cantidad_original)
                        elseif ($cantidad_nueva == $cantidad_original) {
                            // Devolver la cantidad original al stock
                            $stock_actual += $cantidad_original;
                
                            // Restar la misma cantidad del stock (la cantidad actual del carrito)
                            $stock_actual -= $cantidad_nueva;
                        }
                    } else {
                        // Caso 5: Nuevo producto
                        $stock_actual -= $cantidad_nueva; // Restar la cantidad del stock
                    }
                
                    // Actualizar el stock en la base de datos
                    $Producto_model->update($id_producto, ['stock' => $stock_actual]);
                }
                
                // Eliminar los detalles originales de la venta
                $VentaDetalle_model->where('venta_id', $id_pedido)->delete();
                
                // Insertar los nuevos detalles del carrito en la base de datos
                foreach ($cart->contents() as $item) {
                    $VentaDetalle_model->save([
                        'venta_id' => $id_pedido,
                        'producto_id' => $item['id'],
                        'cantidad' => $item['qty'],
                        'precio' => $item['price'],
                        'total' => $item['subtotal'],
                    ]);
                }
                //print_r($nuevoPago_Tarjeta);
                //exit;
                // Actualizar la cabecera de la venta con los nuevos datos
                $cabecera_model = new Cabecera_model();
                $cabecera_model->update($id_pedido, [                    
                    'fecha_pedido' => $fecha,                    
                    'hora_entrega' => $hora,
                    'id_cliente' => $session->get('id_cliente'),
                    'id_usuario' => $session->get('id_vendedor'),
                    'tipo_pago' => $tipo_pago_Modif,
                    'total_venta' => $total_venta,
                    'total_bonificado' => $total_bonificado_OK,
                    'motivo' => $motivo,
                    'total_anterior' => $total_anterior_bonif,
                    'monto_efectivo' => $nuevoPago_Efec,
                    'monto_transferencia' => $nuevoPago_Transfer,
                    'monto_tarjetaC' => $nuevoPago_Tarjeta,
                    'estado' => 'Modificada_SF',
                ]);
                
                // Limpiar la sesión y el carrito
                $session->remove(['pago_efec','pago_transfer','pago_tarjeta','estado', 'id_vendedor', 'nombre_vendedor', 'id_cliente', 'id_pedido', 'fecha_pedido', 'tipo_compra', 'tipo_pago', 'total_venta', 'total_bonificado', 'total_anterior']);
                $cart->destroy();
                
                // Redirigir al usuario con un mensaje de éxito
                return redirect()->to('Carrito_controller/generarTicket/' . $id_pedido);
            }
        }
    }


    //Muestra los detalles de la venta y confirma(función guarda_compra())
	function muestra_compra()
{
    $session = session();
    // Verifica si el usuario está logueado
    if (!$session->has('id')) { 
        return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
    }
    
    $id_pedido = $session->get('id_pedido');
    $cabeceraModel = new Cabecera_model();

    // Obtener los detalles de la venta
    $data['ventas'] = $cabeceraModel->getDetallesVenta($id_pedido);

    $ClientesModel = new Clientes_model();
    $data['clientes'] = $ClientesModel->getClientes();
    $data['titulo'] = 'Confirmar compra';
    
    echo view('navbar/navbar');
    echo view('header/header', $data);        
    echo view('carrito/confirmarCompra', $data);
    echo view('footer/footer');
}


//GUARDA LA COMPRA
    public function guarda_compra()
{    
    $cart = \Config\Services::cart();
    $session = session();
    $perfil = $session->get('perfil_id');
    $estado = $session->get('estado');    
    $id_pedido = $this->request->getPost('id_pedido');
    //print_r($estado);
    //exit;
    
    if(!$cart){
    return redirect()->to(base_url('catalogo'));
    }
    //id del vendedor
    $id_usuario = $session->get('id');

    if(!$id_pedido){    
    //Nombre provisorio del cliente para identificar venta
    $bombre_provisorios_cliente = $this->request->getPost('nombre_prov');    
    if (!$bombre_provisorios_cliente) {
        session()->setFlashdata('msgEr', 'El Campo nombre cliente es Obligatorio!');
        return redirect()->to('casiListo');
    }
    }

    
    //id del cliente seleccionado o se selecciona Consumidor final por defecto.
    $id_cliente = $this->request->getPost('cliente_id');
    if ($id_cliente == 'Anonimo') {
        $id_cliente = 1; // Valor por defecto si no se envía cliente_id
    }
    
    function convertirAFloat($numero) {
    if (empty($numero)) {
        return 0.0;
    }

    // Si contiene coma, asume formato europeo (ej: 40.000,00)
    if (strpos($numero, ',') !== false) {
        $numero = str_replace('.', '', $numero); // quita separador de miles
        $numero = str_replace(',', '.', $numero); // reemplaza decimal
    }

    return floatval($numero);
    }

    
    $monto_transferencia = (int) str_replace('.', '', $this->request->getPost('pagoTransferencia'));
    $monto_en_Efectivo   = (int) str_replace('.', '', $this->request->getPost('pagoEfectivo'));
    $monto_tarjetaC      = (int) str_replace('.', '', $this->request->getPost('pagoTarjetaCredito'));

    if($monto_tarjetaC){
        $monto_tarjetaC = $monto_tarjetaC * 1.1;
    }
    

    //Verificamos si se envio el costo de envio
    $costo_envio =  convertirAFloat($this->request->getPost('costoEnvio'));    
    if(!$costo_envio){
        $costo_envio = 0;
    }
    
    // Contar cuántos tipos de pago tienen un monto mayor a 0
    $metodos_pago = 0;
    if ($monto_en_Efectivo > 0) $metodos_pago++;
    if ($monto_transferencia > 0) $metodos_pago++;
    if ($monto_tarjetaC > 0) $metodos_pago++;

    switch ($metodos_pago) {
        case 1:
            if ($monto_en_Efectivo > 0) {
                $tipo_pago_cobro = 'Efectivo';
            } elseif ($monto_transferencia > 0) {
                $tipo_pago_cobro = 'Transferencia';
            } elseif ($monto_tarjetaC > 0) {
                $tipo_pago_cobro = 'Tarjeta';
            }
            break;
        default:
            $tipo_pago_cobro = 'Mixto';
            break;
    }
       
    //Total de la venta
    $total = $this->request->getPost('total_venta');
    //Total menos el descuento si se pago en efectivo.
    $total_conDescuento = $monto_transferencia + $monto_en_Efectivo + $monto_tarjetaC;
    
    //Si no trajo el descuento y esa variable quedo vacia se asigna el mismo valor de la venta total.
    if (!$total_conDescuento) {
        $total_conDescuento = $total;
    }
    
    //print_r($total_conDescuento);exit;
    // Establecer zona horaria y obtener fecha/hora en formato correcto
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    $hora = date('H:i:s'); // Formato TIME
    $fecha = date('d-m-Y'); // Formato DATE
    //Rescato el tipo de compra (Pedido o Compra_Normal)
    $tipo_compra = $this->request->getVar('tipo_compra');
    //$tipo_compra = $this->request->getPost('tipo_compra_input');
    
    //Si no se selecciono una fecha se asigna la fecha de hoy por defecto para el pedido.
    $fecha_pedido = $this->request->getPost('fecha_pedido');
    //print_r($fecha_pedido);
    //exit;
    if (!$fecha_pedido){
        $fecha_pedido = date('d-m-Y');
    }
    //print_r($tipo_compra);
    //exit;
    //Formateamos la fecha del pedido al formato dia-mes-año
    $fecha_pedido_formateada = date('d-m-Y', strtotime($fecha_pedido));   
    
    $id_pedido = $this->request->getPost('id_pedido');
    
    $Producto_model = new Productos_model();    
    $VentaDetalle_model = new VentaDetalle_model();
    //Array para guardar todos los productos que tengan stock no disponible
    $cart = \Config\Services::cart();
    $cart_info = $cart->contents(); // Obtiene los productos del carrito almacenados en la sesión

    $errores_stock = [];
    foreach ($cart_info as $id => $carrito) {   
       $id_producto = $carrito['id'];

       // Obtener el stock actual desde la base de datos
       $producto = $Producto_model->find($id_producto);
       $stock_actual = $producto['stock'];
       $nombre_producto = $producto['nombre']; // Obtener el nombre del producto

       // Obtener la cantidad que ya estaba reservada en la venta anterior
       $cantidad_reservada = $VentaDetalle_model
           ->where('venta_id', $id_pedido)
           ->where('producto_id', $id_producto)
           ->select('cantidad')
           ->get()
           ->getRowArray()['cantidad'] ?? 0;

       // Calcular el stock disponible para esta modificación
       $stock_disponible = $stock_actual + $cantidad_reservada;

       $rowid = $carrito['rowid'];
       $price = $carrito['price'];
       $amount = $price * $carrito['qty'];
       $qty = $carrito['qty'];

       // Validar contra el stock disponible, considerando lo ya reservado
       if ($qty <= $stock_disponible && $qty >= 1) { 
           $cart->update([
               'rowid'   => $rowid,
               'price'   => $price,
               'amount'  => $amount,
               'qty'     => $qty
           ]);	    	
       } else {
           // Agregar el producto a la lista de errores
           $errores_stock[] = "Producto: <strong>$nombre_producto</strong> - Cantidad solicitada: <strong>$qty</strong> - Stock disponible: $cantidad_reservada(reservados) mas $stock_actual <strong>($stock_disponible)</strong>";
       }                
       }

       // Si hay errores de stock, mostrar mensaje y redirigir
       if (!empty($errores_stock)) {
           $mensaje_error = "Los siguientes productos no tienen suficiente Stock:<br>" . implode("<br>", $errores_stock);
           session()->setFlashdata('msgEr', $mensaje_error);
           return redirect()->to('CarritoList');
       }
    
    
    // Si se encontró un id de pedido y estado modificando, actualizar el pedido existente con los nuevos datos
    if ($estado == 'Modificando' && $tipo_compra == 'Pedido') {
        // Cargar modelos
        $VentaDetalle_model = new VentaDetalle_model();
        $Producto_model = new Productos_model();
        $Cabecera_model = new Cabecera_model();
    
        // Obtener los productos del pedido anterior
        $productos_anteriores = $VentaDetalle_model->where('venta_id', $id_pedido)->findAll();
    
        // 1️⃣ **Devolver stock del pedido anterior**
        foreach ($productos_anteriores as $detalle) {
            $producto = $Producto_model->find($detalle['producto_id']);
            if ($producto) {
                $nuevo_stock = $producto['stock'] + $detalle['cantidad'];
                $Producto_model->update($detalle['producto_id'], ['stock' => $nuevo_stock]);
            }
        }
    
        // 2️⃣ **Eliminar los detalles anteriores**
        $VentaDetalle_model->where('venta_id', $id_pedido)->delete();
    
        // 3️⃣ **Actualizar la cabecera del pedido**
        $Cabecera_model->update($id_pedido, [
            'fecha' => $fecha,
            'hora' => $hora,
            'id_cliente' => $id_cliente,
            'total_venta' => $total,
            'total_bonificado' => $total_conDescuento,
            'tipo_compra' => 'Pedido',
            'estado' => 'Pendiente',
            'fecha_pedido' => $fecha_pedido_formateada
        ]);
    
        // 4️⃣ **Reservar las nuevas cantidades y actualizar stock**
        if ($cart) {
            foreach ($cart->contents() as $item) {
                // Guardar los nuevos detalles de la venta
                $VentaDetalle_model->save([
                    'venta_id' => $id_pedido,
                    'producto_id' => $item['id'],
                    'cantidad' => $item['qty'],
                    'precio' => $item['price'],
                    'total' => $item['subtotal'],
                ]);
    
                // Restar la nueva cantidad del stock
                $producto = $Producto_model->find($item['id']);
                if ($producto) {
                    $nuevo_stock = $producto['stock'] - $item['qty'];
                    $Producto_model->update($item['id'], ['stock' => $nuevo_stock]);
                }
            }
        }
    
        // Limpiar sesión y carrito
        $session->remove(['estado', 'id_vendedor', 'nombre_vendedor', 'id_cliente_pedido', 'id_pedido', 'fecha_pedido', 'tipo_compra', 'tipo_pago']);
        $cart->destroy();
    
        session()->setFlashdata('msg', 'Pedido actualizado con éxito!');
        return redirect()->to('pedidos');
    }
    

    

    //Identifico si es una compra para facturar si este campo viene con el dato "Factura"
    $facturacion = $this->request->getPost('tipo_proceso');
    
    //Si el tipo de proceso es para facturar y el estado es Cobrando se manda a facturar.
    if($estado == 'Cobrando' && $facturacion == "factura"){
                
            $Cabecera_model = new Cabecera_model();
            $Cabecera_model->update($id_pedido, [
                'estado'            => 'Facturada',
                'total_venta'       => $total,
                'tipo_pago'         => $tipo_pago_cobro,
                'total_bonificado'  => $total_conDescuento,               
                'fecha'        => $fecha,
                'hora'         => $hora,
                'fecha_pedido'      => $fecha_pedido_formateada,
                'hora_entrega' => $hora,
                'id_cliente'   => $id_cliente, 
                'costo_envio' => $costo_envio,
                'monto_efectivo'    => $monto_en_Efectivo,
                'monto_transferencia' => $monto_transferencia,
                'monto_tarjetaC' => $monto_tarjetaC              
            ]);           
            $session->remove(['estado','id_vendedor', 'nombre_vendedor', 'id_cliente', 'id_pedido', 'fecha_pedido','tipo_compra','tipo_pago','total_venta']);
        
        $cart->destroy(); 
        //Una vez guardada la compra manda a verificar la factura en ARCA.
        return redirect()->to('Carrito_controller/verificarTA/' . $id_pedido);
    }


    // Guardar la nueva cabecera del Pedido (Nuevo) utiliza el mismo carrito.
    if ($tipo_compra == 'Pedido' && $estado == '') { 
        // Guardar cabecera de la venta tipo pedido
        $cabecera_model = new Cabecera_model();
        $ventas_id = $cabecera_model->save([
            'fecha'        => $fecha,
            'hora'         => $hora,
            'id_cliente'   => $id_cliente,
            'nombre_prov_client' => $bombre_provisorios_cliente,
            'id_usuario'   => $id_usuario,
            'total_venta'  => $total,            
            'total_bonificado' => $total_conDescuento,
            'tipo_compra' => $tipo_compra,
            'fecha_pedido' => $fecha_pedido_formateada,
            'estado' => 'Pendiente'
        ]);
        
    } else {
        //Si el perfil es vendedor guarda la compra con el estado Pendiente
        
        if($perfil && $estado == ''){ 
        // Guardar cabecera de la venta tipo compra normal
        $cabecera_model = new Cabecera_model();
        $ventas_id = $cabecera_model->save([
            'estado'            => 'Sin_Facturar',
            'total_venta'       => $total,
            'tipo_pago'         => $tipo_pago_cobro,
            'total_bonificado'  => $total_conDescuento,                  
            'fecha_pedido'      => $fecha_pedido_formateada,
            'fecha'        => $fecha,
            'hora'         => $hora,
            'hora_entrega'      => $hora,
            'id_cliente'   => $id_cliente,
            'nombre_prov_client' => $bombre_provisorios_cliente,
            'id_usuario'   => $id_usuario,            
            'tipo_compra' => $tipo_compra,
            'costo_envio'       => $costo_envio,
            'monto_efectivo'    => $monto_en_Efectivo,
            'monto_transferencia' => $monto_transferencia,
            'monto_tarjetaC' => $monto_tarjetaC
            
        ]);
        }
        
        if($perfil && $estado == 'Cobrando'){ 
            // Se está cobrando una venta
            if($estado == 'Cobrando'){
                $Cabecera_model = new Cabecera_model();                
        
                // Actualizar la cabecera de la venta
                $Cabecera_model->update($id_pedido, [
                    'estado'            => 'Sin_Facturar',
                    'total_venta'       => $total,
                    'tipo_pago'         => $tipo_pago_cobro,
                    'total_bonificado'  => $total_conDescuento,                  
                    'fecha_pedido'      => $fecha_pedido_formateada,
                    'fecha'             => $fecha,                                      
                    'hora'              => $hora,
                    'hora_entrega'      => $hora,
                    'id_cliente'        => $id_cliente,
                    'costo_envio'       => $costo_envio,
                    'monto_efectivo'    => $monto_en_Efectivo,
                    'monto_transferencia' => $monto_transferencia,
                    'monto_tarjetaC' => $monto_tarjetaC
                ]);           
                
                $session->remove(['estado','id_vendedor', 'nombre_vendedor', 'id_cliente', 'id_pedido', 'fecha_pedido','tipo_compra','tipo_pago','total_venta']);
            }
            
            $cart->destroy();            
            return redirect()->to('Carrito_controller/generarTicket/' . $id_pedido);
        }
        

    }

    // Guardar detalles de la venta si el carrito no está vacío
    if ($cart):
        // Obtener ID de la nueva cabecera guardada
        $id_cabecera = $cabecera_model->getInsertID();
        foreach ($cart->contents() as $item):
            $VentaDetalle_model = new VentaDetalle_model();
            $VentaDetalle_model->save([
                'venta_id'    => $id_cabecera,
                'producto_id' => $item['id'],
                'cantidad'    => $item['qty'],
                'precio'      => $item['price'],
                'total'       => $item['subtotal'],
            ]);

            // Actualizar stock del producto
            $Producto_model = new Productos_model();
            $producto = $Producto_model->find($item['id']); // Asegúrate de usar el método correcto para obtener datos

            if ($producto && isset($producto['stock'])) {
                $stock_edit = $producto['stock'] - $item['qty'];
                $Producto_model->update($item['id'], ['stock' => $stock_edit]);
            }
        endforeach;
    endif;
    
    // Limpiar el carrito y redirigir con mensaje
    $cart->destroy();
    if ($tipo_compra == 'Pedido') {
        session()->setFlashdata('msg', 'Pedido Guardado con Éxito!');
        return redirect()->to('catalogo');
    }
    if($perfil == 0){
        session()->setFlashdata('msg', 'Compra Registrada con Exito!');
        return redirect()->to('catalogo');
    }

    session()->setFlashdata('msg', 'Compra Guardada con Éxito!');
    // Redirige a la vista de la factura
    return redirect()->to('Carrito_controller/generarTicket/' . $id_cabecera);
}


//Genera ticket venta normal
public function generarPresupuesto($id_cabecera)
{
    // Cargar los modelos necesarios
    $Us_Model = new \App\Models\Usuarios_model();
    $ventaModel = new \App\Models\Cabecera_model();
    $detalleModel = new \App\Models\VentaDetalle_model();
    $productoModel = new \App\Models\Productos_model();
    $clienteModel = new \App\Models\Clientes_model();
    
    $session = session();
    $cajero_nombre = $session->get('nombre');
    $cd_efectivo =$session->get('cd_efectivo');
    // Obtener los detalles de la venta
    $cabecera = $ventaModel->find($id_cabecera);
    
    $CostoEnvio = $cabecera['costo_envio'];
   
    // Actualizar el campo costo_envio a 0 porque se muestra una sola vez.
    $ventaModel->update($id_cabecera, ['costo_envio' => 0]);
    
    $detalles = $detalleModel->where('venta_id', $id_cabecera)->findAll();
    //print_r($detalles);
    //exit;
    // Obtener los productos relacionados
    $productos = [];
    foreach ($detalles as $detalle) {
        $productos[$detalle['producto_id']] = $productoModel->find($detalle['producto_id']);
    }

    // Obtener la información del cliente
    $cliente = $clienteModel->find($cabecera['id_cliente']);

    // Obtener el nombre del vendedor    
    $vendedor = $Us_Model->find($cabecera['id_usuario']);
    $nombreVendedor = $vendedor ? $vendedor['nombre'] : 'No encontrado';
    // Crear el HTML para la vista previa
    ob_start();
    ?>
    <html>
    <head>
        <style>
            /* Estilos CSS para el ticket */
            body {
                font-family: Arial, sans-serif; /* Cambiar a una fuente más legible */
                margin: 0;
                padding: 0;
                width: 100%; /* Ancho del ticket */
            }
            .ticket {
                width: 100%;
                font-size: 18px; /* Ajustar tamaño de fuente */
                font-weight:bold;
            }
            h1 {
                font-size: 18px;
                text-align: center;
                margin: 3px 0;
                font-weight: bold;
            }
            h3 {
                text-align: center;
                margin: 3px 0;
                font-weight: bold;
            }
            h4 {
                text-align: center;
                margin: 3px 0;
                font-weight: bold;
                font-size: 14px;
            }
            h5 {
                text-align: center;
                margin: 3px 0;
                font-weight: bold;
                font-size: 12px;
            }
            .ticket p {
                margin: 2px 0;
                font-size: 15px;
                font-weight: bold;
                text-align: justify; /* Justificar el texto */
            }
            .ticket hr {
                border: 0.5px solid #000;
                margin: 5px 0;
            }
            .ticket .header,
            .ticket .footer {
                text-align: center;
                font-size: 10px;
            }
            .ticket .details {
                margin-top: 3px;
                font-size: 10px;
            }
            .ticket .details td {
                padding: 0px;
                font-size: 14px;
            }
            .ticket .details th {
                text-align: left;
                padding-right: 5px;
            }
        </style>
    </head>
    <body>
        <div class="ticket">
             <!-- Derecha: Fecha y hora -->
                        <div style="text-align: right; font-size: 12px;">
                            Fecha: <?= ($cabecera['tipo_compra'] == 'Pedido') 
                                        ? date('d-m-Y H:i') 
                                        : $cabecera['fecha'] . ' ' . $cabecera['hora']; ?>
                        </div>
            <h4>Presupuesto Nro:<?= number_format($cabecera['id']) ?></h4>
            <h5>no valido como factura</5>
            <!-- Cabecera del ticket -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <!-- Izquierda: datos del cliente y vendedor -->
                        <div style="font-size: 10px;">
                            <div style="font-size: 18px; font-weight: bold;">Ferreteria Ayala</div>
                        </div>
                    </div>
                    <div style="text-align: left">Cliente: <?= $cliente['cuil'] > 0 ? $cliente['nombre'] . ' Cuil: ' . $cliente['cuil'] : $cliente['nombre'] ?></div>
                            <div style="text-align: left">Atendido por: <?= $nombreVendedor ?></div>
                            <div style="text-align: left">Cajero: <?= $cajero_nombre ?></div>
                    <hr>

            <!-- Detalle de la compra -->
            <div class="details" style="width: 100%; font-size: 10px;">
                <h3>Productos Adquiridos</h3>
                

                <table style="width:100%; border-collapse: collapse; font-size: 10px;">
            <thead>
                <tr>
                    <th style="border-bottom: 1px solid #000; text-align: left;">Cant.</th>
                    <th style="border-bottom: 1px solid #000; text-align: left;">Descripción</th>
                    <th style="border-bottom: 1px solid #000; text-align: right;">Precio</th>
                    <th style="border-bottom: 1px solid #000; text-align: right;">SubTotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalles as $detalle): ?>
                    <?php 
                        $precio_unitario = $detalle['precio'];
                        $subtotal = $detalle['cantidad'] * $precio_unitario;
                    ?>
                    <tr>
                        <td style="border-bottom: 1px solid #ccc;"><?= $detalle['cantidad'] ?></td>
                        <td style="border-bottom: 1px solid #ccc;"><?= $productos[$detalle['producto_id']]['nombre'] ?></td>
                        <td style="border-bottom: 1px solid #ccc; text-align: right;">$ <?= number_format($detalle['precio'], 0, '.', '.') ?></td>
                        <td style="border-bottom: 1px solid #ccc; text-align: right;">$ <?= number_format($subtotal, 0, '.', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
                
                <!-- Dos saltos de línea (espaciado visual antes del total) -->
                <tr><td colspan="4" style="height: 10px;"></td></tr>
                <tr><td colspan="4" style="height: 10px;"></td></tr>
            </tbody>

            <tfoot>                        
                <tr>
                    <td></td>
                    <td></td>                            
                    <td style="text-align: right; font-weight: bold;">TOTAL:</td>
                    <td style="text-align: right; font-weight: bold;">
                        $ <?= number_format($cabecera['total_venta'], 0, '.', '.') ?>
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

            </div>


            <!-- Totales -->

            <?php if (!empty($cabecera['motivo'])): ?>
            <hr>
            <p>---------------------Recortar Aqui-------------------------</p>
            <p><strong>Motivo de los Cambios:</strong> <?= nl2br(htmlspecialchars($cabecera['motivo'])) ?></p>
            <p><strong>Cajero:</strong> <?= nl2br(htmlspecialchars($cajero_nombre)) ?></p>
            <p><strong>Vendedor:</strong> <?= nl2br(htmlspecialchars($nombreVendedor)) ?></p>
            <p><strong>Fecha y Hora:</strong> <?= date('d-m-Y H:i', strtotime($cabecera['fecha'] . ' ' . $cabecera['hora'])) ?></p>
            <p><strong>Total Anterior: $ </strong> <?= number_format($cabecera['total_anterior'], 0, '.', '.') ?></p>
            <p><strong>Total Actual: $ </strong> <?= number_format($cabecera['total_bonificado'], 0, '.', '.') ?></p>
            <p><strong>Diferencia: $ </strong> <?= number_format($cabecera['total_bonificado'] - $cabecera['total_anterior'], 0, '.', '.') ?></p>
            <p>Si la Diferencia es negativa, eso es saldo a favor para el Cliente.</p>
            <?php endif; ?>


            
        </div>
    </body>
    </html>
    <?php
       
    // Generar el PDF
    $html = ob_get_clean();
    $dompdf = new \Dompdf\Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait'); // O 'landscape' si prefieres horizontal
    $dompdf->render();
    
    // Guardar el archivo PDF en un archivo temporal
    $output = $dompdf->output();
    $tempFolder = 'path/to/temp/folder';  // Ruta de la carpeta temporal
    $tempFile = $tempFolder . '/ticket.pdf';  // Ruta completa del archivo PDF
    
    // Crear la carpeta si no existe
    if (!is_dir($tempFolder)) {
        mkdir($tempFolder, 0777, true);  // Crea la carpeta con permisos 0777 (lectura, escritura y ejecución)
    }
    
    // Guardar el archivo PDF en la carpeta temporal
    file_put_contents($tempFile, $output);
    session()->setFlashdata('msg', 'Imprimiendo Ticket.!');

     // Obtener el perfil del usuario desde la sesión
    $perfil = session()->get('perfil_id');
    
    // Redirigir a una página de confirmación con JavaScript
        echo "<script type='text/javascript'>
        // Descargar el archivo PDF
        window.location.href = '" . base_url('descargar_ticket') . "';

        // Pasar el valor de perfil desde PHP a JavaScript
        var perfil = " . $perfil . "; // Asignar el perfil de PHP a la variable JS

        // Redirigir a la página de referencia después de la descarga o a otra según perfil
        window.setTimeout(function() {
            if (perfil == 3) {
                window.location.href = document.referrer; // Volver a la página anterior
            } else if (document.referrer) {
                window.location.href = document.referrer; // Volver a la página anterior
            }
        }, 500);  // 0.5 segundos de espera para asegurar que la descarga termine
        </script>";
        exit;

}


//Genera ticket venta normal
public function generarTicket($id_cabecera)
{
    // Cargar los modelos necesarios
    $Us_Model = new \App\Models\Usuarios_model();
    $ventaModel = new \App\Models\Cabecera_model();
    $detalleModel = new \App\Models\VentaDetalle_model();
    $productoModel = new \App\Models\Productos_model();
    $clienteModel = new \App\Models\Clientes_model();
    
    $session = session();
    $cajero_nombre = $session->get('nombre');
    $cd_efectivo =$session->get('cd_efectivo');
    // Obtener los detalles de la venta
    $cabecera = $ventaModel->find($id_cabecera);
    
    $CostoEnvio = $cabecera['costo_envio'];

    $ClienteNom = $cabecera['nombre_prov_client'];
   
    // Actualizar el campo costo_envio a 0 porque se muestra una sola vez.
    $ventaModel->update($id_cabecera, ['costo_envio' => 0]);
    
    $detalles = $detalleModel->where('venta_id', $id_cabecera)->findAll();
    //print_r($detalles);
    //exit;
    // Obtener los productos relacionados
    $productos = [];
    foreach ($detalles as $detalle) {
        $productos[$detalle['producto_id']] = $productoModel->find($detalle['producto_id']);
    }

    // Obtener la información del cliente
    $cliente = $clienteModel->find($cabecera['id_cliente']);

    // Obtener el nombre del vendedor    
    $vendedor = $Us_Model->find($cabecera['id_usuario']);
    $nombreVendedor = $vendedor ? $vendedor['nombre'] : 'No encontrado';
    
    //Cambia el estado del Pedido
    if($cabecera['tipo_compra'] == 'Pedido' && $cabecera['total_anterior'] == 0){

        $ventaModel->cambiarEstado($id_cabecera, 'Sin_Facturar');
    }
    // Crear el HTML para la vista previa
    ob_start();
    ?>
    <html>
    <head>
        <style>
            /* Estilos CSS para el ticket */
            body {
                font-family: Arial, sans-serif; /* Cambiar a una fuente más legible */
                margin: 0;
                padding: 0;
                width: 100%; /* Ancho del ticket */
            }
            .ticket {
                width: 100%;
                font-size: 18px; /* Ajustar tamaño de fuente */
                font-weight:bold;
            }
            h1 {
                font-size: 18px;
                text-align: center;
                margin: 3px 0;
                font-weight: bold;
            }
            h3 {
                text-align: center;
                margin: 3px 0;
                font-weight: bold;
            }
            h4 {
                text-align: center;
                margin: 3px 0;
                font-weight: bold;
                font-size: 14px;
            }
            h5 {
                text-align: center;
                margin: 3px 0;
                font-weight: bold;
                font-size: 12px;
            }
            .ticket p {
                margin: 2px 0;
                font-size: 15px;
                font-weight: bold;
                text-align: justify; /* Justificar el texto */
            }
            .ticket hr {
                border: 0.5px solid #000;
                margin: 5px 0;
            }
            .ticket .header,
            .ticket .footer {
                text-align: center;
                font-size: 10px;
            }
            .ticket .details {
                margin-top: 3px;
                font-size: 10px;
            }
            .ticket .details td {
                padding: 0px;
                font-size: 14px;
            }
            .ticket .details th {
                text-align: left;
                padding-right: 5px;
            }
        </style>
    </head>
    <body>
        <div class="ticket">
             <!-- Derecha: Fecha y hora -->
                        <div style="text-align: right; font-size: 12px;">
                            Fecha: <?= ($cabecera['tipo_compra'] == 'Pedido') 
                                        ? date('d-m-Y H:i') 
                                        : $cabecera['fecha'] . ' ' . $cabecera['hora']; ?>
                        </div>
            <h4>Remito Nro:<?= number_format($cabecera['id']) ?></h4>
            <h5>no valido como factura</5>
            <!-- Cabecera del ticket -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <!-- Izquierda: datos del cliente y vendedor -->
                        <div style="font-size: 10px;">
                            <div style="font-size: 18px; font-weight: bold;">Ferreteria Ayala</div>
                        </div>
                    </div>
                    <div style="text-align: left">
                        Cliente: 
                        <?= !empty($cliente) ? 
                            ($cliente['cuil'] > 0 ? $cliente['nombre'] . ' Cuil: ' . $cliente['cuil'] : $cliente['nombre']) 
                            : $ClienteNom ?>
                    </div>
                            <div style="text-align: left">Atendido por: <?= $nombreVendedor ?></div>
                            <!-- <div style="text-align: left">Cajero: <?= $cajero_nombre ?></div> -->
                    <hr>

            <!-- Detalle de la compra -->
            <div class="details" style="width: 100%; font-size: 10px;">
                <h3>Productos Adquiridos</h3>
                

                <table style="width:100%; border-collapse: collapse; font-size: 10px;">
                    <thead>
                        <tr>
                            <th style="border-bottom: 1px solid #000; text-align: left;">Cant.</th>
                            <th style="border-bottom: 1px solid #000; text-align: left;">Descripción</th>
                            <th style="border-bottom: 1px solid #000; text-align: right;">Precio</th>
                            <th style="border-bottom: 1px solid #000; text-align: right;">SubTotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detalles as $detalle): ?>
                            <?php 
                                $precio_unitario = $detalle['precio'];
                                $subtotal = $detalle['cantidad'] * $precio_unitario;
                            ?>
                        <tr>
                            <td style="border-bottom: 1px solid #ccc;"><?= $detalle['cantidad'] ?></td>
                            <td style="border-bottom: 1px solid #ccc;"><?= $productos[$detalle['producto_id']]['nombre'] ?></td>
                            <td style="border-bottom: 1px solid #ccc; text-align: right;">$<?= number_format($detalle['precio'], 0, '.', '.') ?></td>
                            <td style="border-bottom: 1px solid #ccc; text-align: right;">$<?= number_format($subtotal, 0, '.', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <!-- Dos saltos de línea (espaciado visual antes del total) -->
                            <tr><td colspan="4" style="height: 10px;"></td></tr>
                            <tr><td colspan="4" style="height: 10px;"></td></tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td></td>
                            <td></td>
                            <td style="text-align: right; font-weight: bold;">TOTAL</td>
                            <td style="text-align: right; font-weight: bold;">
                                $<?= number_format($cabecera['total_venta'], 0, '.', '.') ?>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>


            <!-- Totales -->

            <?php if (!empty($cabecera['motivo'])): ?>
            <hr>
            <p>---------------------Recortar Aqui-------------------------</p>
            <p><strong>Motivo de los Cambios:</strong> <?= nl2br(htmlspecialchars($cabecera['motivo'])) ?></p>
            <p><strong>Cajero:</strong> <?= nl2br(htmlspecialchars($cajero_nombre)) ?></p>
            <p><strong>Vendedor:</strong> <?= nl2br(htmlspecialchars($nombreVendedor)) ?></p>
            <p><strong>Fecha y Hora:</strong> <?= date('d-m-Y H:i', strtotime($cabecera['fecha'] . ' ' . $cabecera['hora'])) ?></p>
            <p><strong>Total Anterior: $ </strong> <?= number_format($cabecera['total_anterior'], 0, '.', '.') ?></p>
            <p><strong>Total Actual: $ </strong> <?= number_format($cabecera['total_bonificado'], 0, '.', '.') ?></p>
            <p><strong>Diferencia: $ </strong> <?= number_format($cabecera['total_bonificado'] - $cabecera['total_anterior'], 0, '.', '.') ?></p>
            <p>Si la Diferencia es negativa, eso es saldo a favor para el Cliente.</p>
            <?php endif; ?>


            
        </div>
    </body>
    </html>
    <?php
       
    // Generar el PDF
    $html = ob_get_clean();
    $dompdf = new \Dompdf\Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait'); // O 'landscape' si prefieres horizontal
    $dompdf->render();
    
    // Guardar el archivo PDF en un archivo temporal
    $output = $dompdf->output();
    $tempFolder = 'path/to/temp/folder';  // Ruta de la carpeta temporal
    $tempFile = $tempFolder . '/ticket.pdf';  // Ruta completa del archivo PDF
    
    // Crear la carpeta si no existe
    if (!is_dir($tempFolder)) {
        mkdir($tempFolder, 0777, true);  // Crea la carpeta con permisos 0777 (lectura, escritura y ejecución)
    }
    
    // Guardar el archivo PDF en la carpeta temporal
    file_put_contents($tempFile, $output);
    session()->setFlashdata('msg', 'Imprimiendo Ticket.!');

     // Obtener el perfil del usuario desde la sesión
    $perfil = session()->get('perfil_id');
    
    // Redirigir a una página de confirmación con JavaScript
        echo "<script type='text/javascript'>
        // Descargar el archivo PDF
        window.location.href = '" . base_url('descargar_ticket') . "';

        // Pasar el valor de perfil desde PHP a JavaScript
        var perfil = " . $perfil . "; // Asignar el perfil de PHP a la variable JS

        // Redirigir a la página de referencia después de la descarga o a otra según perfil
        window.setTimeout(function() {
            if (perfil == 3) {
                window.location.href = document.referrer; // Volver a la página anterior
            } else if (document.referrer) {
                window.location.href = document.referrer; // Volver a la página anterior
            }
        }, 500);  // 0.5 segundos de espera para asegurar que la descarga termine
        </script>";
        exit;

}

// En tu ruta 'descargar_ticket', puedes usar:
public function descargar_ticket()
{
    $filePath = 'path/to/temp/folder/ticket.pdf';
    if (file_exists($filePath)) {
        return $this->response->download($filePath, null)->setFileName('ticket.pdf');
    }
    // Si no existe el archivo, muestra un error o redirige a otra página.
}


//Verifica que todo este bien para Facturar
public function verificarTA($id_cabecera = null) {
    
    //phpinfo();
    //exit;
    $ventaModel = new \App\Models\Cabecera_model();
    // Obtener los detalles de la venta
    $cabecera = $ventaModel->find($id_cabecera);
    //print_r($cabecera);
    //exit;
    if ($cabecera['estado'] == 'Facturado' || $cabecera['id_cae'] > 0) {
        session()->setFlashdata('msgEr', 'No se puede facturar una misma venta dos veces, solo puede volver a imprimir la factura.');
        return redirect()->to(base_url('catalogo'));
    }
    //$id_cabecera = 252;
    $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
    //Si es un vendedor no le permite
    $perfil=$session->get('perfil_id');
    if($perfil == 2){
            return redirect()->to(base_url('catalogo'));
        }
    
    if ($id_cabecera === null) {
        //session()->setFlashdata('msgEr', 'No se puede facturar sin enviar una Venta.');
        return redirect()->to(base_url('caja'));
    }
    //$id_cabecera = 24;
    // Ruta del archivo TA.xml
    $taPath = ROOTPATH . 'writable/facturacionARCA/TA.xml';

    // Zona horaria de Argentina
    $zonaHorariaArgentina = new \DateTimeZone('America/Argentina/Buenos_Aires');

   // Verificar si el archivo TA.xml existe
   if (!file_exists($taPath)) {

    $ventaModel->update($id_cabecera,['estado' => 'Error_factura']);
    session()->setFlashdata('msgEr', 'Problemas con el servidor ARCA, se guardo la compra sin Facturar, intente mas tarde');
    return redirect()->to(base_url('catalogo'));
    } 
    // Cargar el XML    
    $xml = simplexml_load_file($taPath);
    if (!$xml) {
        $ventaModel->update($id_cabecera,['estado' => 'Error_factura']);
        session()->setFlashdata('msgER', 'Problemas con el servidor ARCA, se guardo la compra sin Facturar, intente mas tarde');
        return redirect()->to($this->request->getHeader('referer')->getValue());
    }
    

    // Obtener la fecha de expiración del XML
    $expirationTime = (string)$xml->header->expirationTime;
    $expirationDateTime = new \DateTime($expirationTime, new \DateTimeZone('UTC')); // AFIP usa UTC
    $expirationDateTime->setTimezone($zonaHorariaArgentina); // Convertir a Argentina

    // Obtener la fecha y hora actuales en la misma zona horaria
    $currentDateTime = new \DateTime('now', $zonaHorariaArgentina);

    // Comparar fechas
    if ($expirationDateTime > $currentDateTime) {
        // El ticket sigue siendo válido, continuar con la facturación
        $TA = [
            'token' => (string)$xml->credentials->token,
            'sign'  => (string)$xml->credentials->sign            
        ];
        //print_r($TA);
        //exit;
        //Manda a facturar con el TA y el id de cabecera, y redireccion con msg si es venta o pedido facturado con exito.
        $this->facturar($TA,$id_cabecera);
        session()->setFlashdata('msg', 'La Factura se realizo con Exito.!');
        return redirect()->to(base_url('catalogo'));
    } else {
        // El ticket ha expirado, eliminar el archivo y generar uno nuevo
        //unlink($taPath);
        rename($taPath, $taPath . ".bak");
        //echo "El ticket ha expirado y se eliminó TA.xml. Generando uno nuevo...<br>";
        return redirect()->to('Carrito_controller/generarTA/'. $id_cabecera);
        //$this->generarTA($id_cabecera);

        // Verificar si se generó correctamente antes de continuar
        if (!file_exists($taPath)) {

            session()->setFlashdata('msgER', 'Problemas con el Servidor ARCA, intente mas tarde.!');
            return redirect()->to(base_url('casiListo'));
        }
    }
}

//Genera un nuevo TA.xml si es necesario.
public function generarTA($id_cabecera = null) {
    $session = session();

    // Verifica si el usuario está logueado
    if (!$session->has('id')) { 
        return redirect()->to(base_url('login')); 
    } 

    if ($id_cabecera === null) {
        return redirect()->to(base_url('catalogo'));
    }

    // Ruta al script wsaa-client.php
    $path = APPPATH . 'Libraries/afip/wsaa-client.php';

    // Configuración de descriptores para la ejecución
    $descriptorspec = [
        0 => ["pipe", "r"],  // Entrada estándar (no usada)
        1 => ["pipe", "w"],  // Salida estándar
        2 => ["pipe", "w"]   // Salida de error
    ];

    // Ejecutar el script PHP con proc_open
    $process = proc_open("php " . escapeshellarg($path) . " wsfe", $descriptorspec, $pipes);

    if (is_resource($process)) {
        $output = stream_get_contents($pipes[1]); // Captura la salida
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process); // Cierra el proceso

        // Mostrar la salida para depuración (puedes comentar esto en producción)
        //echo "<pre>$output</pre>";
        //exit;
    } else {
        echo "Error al ejecutar el proceso.";
        exit;
    }

    return redirect()->to('Carrito_controller/verificarTA/' . $id_cabecera);
}


//Aqui va el xml de factura para enviar a ARCA
//re copiar abajo $TA,$id_cabecera
public function facturar($TA = null,$id_cabecera = null) {
    $ventaModel = new \App\Models\Cabecera_model();
    // Obtener los detalles de la venta
    $cabecera = $ventaModel->find($id_cabecera);
    //print_r($cabecera);
    //exit;
    if ($cabecera['estado'] == 'Facturado' || $cabecera['id_cae'] > 0 ) {
        session()->setFlashdata('msgEr', 'No se puede facturar una misma Venta dos Veces, Solo puede volver a imprimir la factura.');
        return redirect()->to(base_url('catalogo'));
    }
    $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        } 
    if ($id_cabecera === null) {
        //session()->setFlashdata('msgEr', 'No se puede facturar sin enviar una Venta.');
        return redirect()->to(base_url('catalogo'));
    }

    // Cargar los modelos necesarios 
    $clienteModel = new \App\Models\Clientes_model();
    //Obtengo el ultimo id del cae
    $caeModel = new \App\Models\Cae_model();    
    $ultimoRegistro = $caeModel->orderBy('id_cae', 'DESC')->first(); // Trae el último registro de la tabla cae
    $ultimo_id_cae = $ultimoRegistro ? $ultimoRegistro['id_cae'] : 0;
    //echo "Último ID registrado: " . $ultimo_id_cae;
    //exit;
    //sumamos uno al ultimo id_cae para que ARCA lo acepte porque tiene que ser de 1 en 1.
    $id_cae_siguiente = $ultimo_id_cae + 1;
    //print_r($id_cae_siguiente);
    //exit;
    // Obtener los detalles de la venta
    
    //print_r($cabecera);
    //exit;
    //Obtengo el total de la venta, con descuento o sin
    $total_venta = $cabecera['total_bonificado'];
    //Obtengo la fecha
    $fecha_venta = $cabecera['fecha'];
    $fecha_formateadaF = date('Ymd', strtotime($fecha_venta)); // Ajusta y suma 2 dias porque es el rango permitido por AFIP.
    //print_r($fecha_formateadaF);    
    //exit;
    // Obtener la información del cliente
    $cliente = $clienteModel->find($cabecera['id_cliente']);
    //Obtener el cuil del cliente
    $cuil_cliente = $cliente['cuil'];
    //print_r($cuil_cliente);
    //Obtener el tipo de Documento.
    $tipoDoc = 80; //Si tiene un cuil real
    if($cuil_cliente == 0){
        $tipoDoc = 99; //Si no tiene Cuil
    }
    //print_r($tipoDoc);
    //exit;

    $new_cae = null;
    //echo "Token para crear la factura xml para ARCA.\n";
    //print_r($TA['token']);
    $token = $TA['token'];
    //print_r($token);
    //echo "\nSign para crear la factura xml para ARCA.\n";
    //print_r($TA['sign']);
    $sign = $TA['sign'];
    //print_r($sign);

    $curl = curl_init();
    
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://servicios1.afip.gov.ar/wsfev1/service.asmx',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS =>'<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" 
                      xmlns:ar="http://ar.gov.afip.dif.FEV1/">
        <soapenv:Header/>
        <soapenv:Body>
            <ar:FECAESolicitar>
                <ar:Auth>
                    <ar:Token>' . $token . '</ar:Token>
                    <ar:Sign>' . $sign . '</ar:Sign>
                    <ar:Cuit>20369557263</ar:Cuit>
                </ar:Auth>
                <ar:FeCAEReq>
        <ar:FeCabReq>
            <ar:CantReg>1</ar:CantReg>
            <ar:PtoVta>2</ar:PtoVta> <!-- El punto de venta tiene que ser uno habilitado para Factura Electronica -->
            <ar:CbteTipo>11</ar:CbteTipo> <!-- 11 para FACTURA C -->
        </ar:FeCabReq>
        <ar:FeDetReq>
            <ar:FECAEDetRequest>
                <ar:Concepto>1</ar:Concepto> <!-- Productos -->
                <ar:DocTipo>' . $tipoDoc . '</ar:DocTipo> <!-- 80 CUIT, 99 Consumidor_Final-->
                <ar:DocNro>' . $cuil_cliente . '</ar:DocNro> <!-- 0 para C_final-->
                <ar:CbteDesde>' . $id_cae_siguiente . '</ar:CbteDesde> <!-- Nuevo comprobante: debe ser mayor al anterior -->
                <ar:CbteHasta>' . $id_cae_siguiente . '</ar:CbteHasta> <!-- Debe ser igual al número de <CbteDesde> -->
                <ar:CbteFch>' . $fecha_formateadaF . '</ar:CbteFch> <!-- Fecha dentro del rango N-5 a N+5, 5 dias antes o despues del dia vigente-->
                <ar:ImpTotal>' . $total_venta . '</ar:ImpTotal> <!-- Suma de ImpNeto + ImpTrib -->
                <ar:ImpTotConc>0</ar:ImpTotConc>
                <ar:ImpNeto>' . $total_venta . '</ar:ImpNeto>
                <ar:MonId>PES</ar:MonId>
                <ar:MonCotiz>1</ar:MonCotiz>
                <ar:CondicionIVAReceptorId>5</ar:CondicionIVAReceptorId> 
                
            </ar:FECAEDetRequest>
        </ar:FeDetReq>
    </ar:FeCAEReq>
    </ar:FECAESolicitar>
    </soapenv:Body>
    </soapenv:Envelope>
    ',
      CURLOPT_HTTPHEADER => array(
        'SOAPAction: http://ar.gov.afip.dif.FEV1/FECAESolicitar',
        'Content-Type: text/xml; charset=utf-8',        
      ),
    ));
    
    $response = curl_exec($curl);
    
    curl_close($curl);
    
    
    // **Extraer los datos del XML**
    
        // Cargar el XML y registrar el namespace
        $xml = new \SimpleXMLElement($response);
        $xml->registerXPathNamespace('ns', 'http://ar.gov.afip.dif.FEV1/');

        // Buscar los valores dentro del XML
        $resultado_nodes = $xml->xpath('//ns:FECAEDetResponse/ns:Resultado');
        $cae_nodes = $xml->xpath('//ns:FECAEDetResponse/ns:CAE');
        $cae_vencimiento_nodes = $xml->xpath('//ns:FECAEDetResponse/ns:CAEFchVto');
        $observaciones_nodes = $xml->xpath('//ns:FECAEDetResponse/ns:Observaciones/ns:Obs/ns:Msg');

        // Verificar si los nodos existen antes de acceder a ellos
        $resultado = isset($resultado_nodes[0]) ? (string) $resultado_nodes[0] : 'No encontrado';
        $cae = isset($cae_nodes[0]) ? (string) $cae_nodes[0] : 'No encontrado';
        $cae_vencimiento = isset($cae_vencimiento_nodes[0]) ? (string) $cae_vencimiento_nodes[0] : 'No encontrado';
        // Capturar mensaje de error si la factura fue rechazada
        $mensaje_error = isset($observaciones_nodes[0]) ? (string) $observaciones_nodes[0] : '';
        //Pregunta si fue aprobada la factura guarda si no re direcciona a otra vista.
    if($resultado == 'A'){ 
        $caeModel->save([
            'cae'       => $cae,
            'vto_cae'   => $cae_vencimiento
        ]); // Muestra los errores si la inserción falla
        //Rescato el id del ultimo cae generado y guardado en la DB.
        $new_cae = $caeModel->getInsertID();
        //asignamos el id_cae a la venta y cambiamos el estado a Facturado.
        $ventaModel->facturado($id_cabecera,$new_cae);

    }else{ 
        //print_r($response);
        //exit;
        $ventaModel->update($id_cabecera,['estado' => 'Error_factura']);
        //Si tiene una R en resultado redirecciona por rechazado
        session()->setFlashdata('msgEr', 'No se pudo facturar, Motivo: ' . $mensaje_error . ' La venta se guardó para facturar despues de corregir el error.');
        return redirect()->to(base_url('catalogo'));
    }
        // Mostrar los datos obtenidos
        //echo "Resultado: $resultado\n";
        //echo "CAE: $cae\n";
        //echo "Vencimiento CAE: $cae_vencimiento\n";
        $this->generarTicketFacturaC($id_cabecera);
}


//Genera el ticket factura tipo C
public function generarTicketFacturaC($id_cabecera)
{
    // Cargar los modelos necesarios
    $Us_Model = new Usuarios_model;
    $ventaModel = new \App\Models\Cabecera_model();
    $detalleModel = new \App\Models\VentaDetalle_model();
    $productoModel = new \App\Models\Productos_model();
    $clienteModel = new \App\Models\Clientes_model();
    $caeModel = new \App\Models\Cae_model();

    // Obtener los detalles de la venta y el CAE
    $cabecera = $ventaModel->find($id_cabecera);
    $detalle_CAE = $caeModel->find($cabecera['id_cae']);
    $detalles = $detalleModel->where('venta_id', $id_cabecera)->findAll();

    $session = session();
    $cd_efectivo =$session->get('cd_efectivo');
    $cajero_nombre = $session->get('nombre');

    $CostoEnvio = $cabecera['costo_envio'];
   
    // Actualizar el campo costo_envio a 0 porque se muestra una sola vez.
    $ventaModel->update($id_cabecera, ['costo_envio' => 0]);

    // Obtener los productos relacionados
    $productos = [];
    foreach ($detalles as $detalle) {
        $productos[$detalle['producto_id']] = $productoModel->find($detalle['producto_id']);
    }

    // Obtener la información del cliente
    $cliente = $clienteModel->find($cabecera['id_cliente']);

    // Obtener el nombre del vendedor    
    $vendedor = $Us_Model->find($cabecera['id_usuario']);
    $nombreVendedor = $vendedor ? $vendedor['nombre'] : 'No encontrado';

    // Crear el HTML para la vista previa
    ob_start();
    ?>
    <html>
    <head>
        <style>
            /* Estilos CSS para la factura */
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 0;
                width: 220px;
            }
            .ticket {
                width: 100%;
                font-size: 12px;
            }
            h1 {
                font-size: 18px;
                text-align: center;
                margin: 3px 0;
                font-weight: bold;
            }
            h3 {
                text-align: center;
                margin: 3px 0;
                font-weight: bold;
            }
            h4 {
                text-align: center;
                margin: 3px 0;
                font-weight: bold;
            }
            .ticket p {
                margin: 2px 0;
                font-size: 10px;
                font-weight: bold;
                text-align: justify;
            }
            .ticket hr {
                border: 0.5px solid #000;
                margin: 5px 0;
            }
            .ticket .header,
            .ticket .footer {
                text-align: center;
                font-size: 10px;
            }
            .ticket .details {
                margin-top: 3px;
                font-size: 10px;
            }
            .ticket .details td {
                padding: 0px;
            }
            .ticket .details th {
                text-align: left;
                padding-right: 5px;
            }
        </style>
    </head>
    <body>
        <div class="ticket">
            <!-- Cabecera del ticket -->
            <h1>MULTIRRUBRO BLASS</h1>
            <p>GONZALEZ EMMANUEL ALEJANDRO</p>
            <p>CUIT Nro: 20-36955726-3</p>
            <p>Domicilio: Belgrano 2077, Corrientes (3400)</p>
            <p>Cel: 3794-095020</p>
            <p>Inicio de actividades: 01/02/2023</p>
            <p>Ingresos Brutos: 20-36955726-3</p>
            <p>Resp. Monotributo</p>
            <hr>

            <!-- Información de la venta -->
            <p>Fecha y Hora: <?= ($cabecera['tipo_compra'] == 'Pedido') ? date('d-m-Y H:i:s') : $cabecera['fecha'] . ' ' . $cabecera['hora']; ?></p>
            <p>Factura C (Cod.011) a Consumidor Final</p>
            <p>P.Venta: 002    NroFactura: <?= $detalle_CAE['id_cae'] ?></p>
            
            <p>Cliente: <?= $cliente['cuil'] > 0 ? $cliente['nombre'] . ' Cuil: ' . $cliente['cuil'] : 'Consumidor Final Cuil: 0' ?></p>
            <p>Atendido por: <?= $nombreVendedor ?></p>
            <p>Cajero: <?= $cajero_nombre ?></p>
            <hr>

            <!-- Detalle de la compra -->
            <div class="details" style="width: 100%; font-size: 10px;">
                <h3>Detalle de la Compra</h3>
                <h4>COD: <?= $cabecera['id'] ?></h4>
                <?php foreach ($detalles as $detalle): ?>
                    <div>
                        <p><?= $productos[$detalle['producto_id']]['nombre'] ?> Cant:<?= $detalle['cantidad'] ?> x $<?= number_format($detalle['precio'], 0, '.', '.') ?></p>
                    </div>
                <?php endforeach; ?>            
            </div>

            <!-- Totales -->
            <p>Subtotal sin descuentos: $<?= number_format($cabecera['total_venta'], 0, '.', '.') ?></p>
            <p>Descuento: 
            <?= ($cabecera['tipo_pago'] == 'Efectivo' || $cabecera['tipo_pago'] == 'Mixto') 
                ? '$' . number_format(($cabecera['monto_efectivo'] * $cd_efectivo) - $cabecera['monto_efectivo'], 0, '.', '.') 
                : '$0.00' ?>
            </p>
            <p>Adicional por Tarjeta: 
            <?= ($cabecera['tipo_pago'] == 'Tarjeta' || $cabecera['tipo_pago'] == 'Mixto') 
                ? '$' . number_format($cabecera['monto_tarjetaC'] - ($cabecera['monto_tarjetaC'] / 1.1), 0, '.', '.') 
                : '$0.00' ?>
            </p>
            <p>Total: $<?= number_format($cabecera['total_bonificado'], 0, '.', '.') ?></p>
            <?php if ($CostoEnvio > 0): ?>
            <p>Costo de Envio: $ <?= $CostoEnvio ?></p>
            <?php endif; ?>            
            <hr>
            
            <p>Reg. Transparencia fiscal al consumidor</p>
            <p>IVA CONTENIDO: $ <?= number_format($cabecera['total_bonificado'] * 0.21, 0, '.', '.') ?></p>
            <p>Otros Imp. Nac. Indirectos: $0.00</p>
            <p>Tipo de pago: <?=$cabecera['tipo_pago'];?></p>
            <p>Referencia electronica del Comprobante:</p>
            <p>CAE: <?= $detalle_CAE['cae'] ?>   Vto: <?= date('d-m-Y', strtotime($detalle_CAE['vto_cae'])) ?></p>
            
            <hr>
            
            <!-- Footer -->
            <div class="footer">
                <p>Importante:</p>
                <p>La mercaderia viaja por cuenta y riesgo del comprador.</p>
                <p>Es responsabilidad del cliente controlar su compra antes de salir del local.</p>
                <p>Su compra tiene 48hs para cambio ante fallas previas del producto.</p>
                <p>Instagram: @Blass.Multirrubro</p>
                <p>Facebook: Blass Multirrubro</p>
                <h3>Muchas Gracias por su Compra.!</h3>
            </div>
        </div>
    </body>
    </html>
    <?php
    // Generar el PDF
    $html = ob_get_clean();
    $dompdf = new \Dompdf\Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->render();
    
    // Guardar el archivo PDF en un archivo temporal
    $output = $dompdf->output();
    $tempFolder = 'path/to/temp/folder';  // Ruta de la carpeta temporal
    $tempFile = $tempFolder . '/ticket.pdf';  // Ruta completa del archivo PDF
    
    // Crear la carpeta si no existe
    if (!is_dir($tempFolder)) {
        mkdir($tempFolder, 0777, true);  // Crea la carpeta con permisos 0777 (lectura, escritura y ejecución)
    }
    
    // Guardar el archivo PDF en la carpeta temporal
    file_put_contents($tempFile, $output);
    session()->setFlashdata('msg', 'Imprimiendo Ticket.!');

     // Obtener el perfil del usuario desde la sesión
    $perfil = session()->get('perfil_id');
    
    // Redirigir a una página de confirmación con JavaScript
        echo "<script type='text/javascript'>
        // Descargar el archivo PDF
        window.location.href = '" . base_url('descargar_ticket') . "';

        // Pasar el valor de perfil desde PHP a JavaScript
        var perfil = " . $perfil . "; // Asignar el perfil de PHP a la variable JS

        // Redirigir a la página de referencia después de la descarga o a otra según perfil
        window.setTimeout(function() {
            if (perfil == 3) {
                 window.location.href = '" . base_url('caja') . "'; // Redirigir al perfil 3
            } else if (document.referrer) {
                window.location.href = document.referrer; // Volver a la página anterior
            }
        }, 500);  // 0.5 segundos de espera para asegurar que la descarga termine
        </script>";
        exit;

}


}