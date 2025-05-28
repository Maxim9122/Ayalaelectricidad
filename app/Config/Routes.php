<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(true);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'Login_controller');

//Rutas para el Cajero
$routes->get('caja', 'Caja_controller::Caja');
$routes->get('cargarVenta/(:num)', 'Caja_controller::CargarVenta/$1');
$routes->get('cancelarCobro/(:num)', 'Caja_controller::CancelarCobro/$1');
$routes->get('modificarVenta/(:num)', 'Caja_controller::cargar_Venta_en_Carrito/$1');
$routes->get('cancelar_edicion_Venta/(:num)', 'Caja_controller::cancelar_edicion_Venta/$1');
$routes->post('ventaModificada', 'Caja_controller::ModificarVenta');
$routes->get('cancelarVenta/(:num)', 'Caja_controller::Venta_cancelar/$1');
$routes->get('cobrarPedido/(:num)', 'Caja_controller::CargarVenta/$1');
//Venta Sin Facturar
$routes->get('modificarVenta_SF/(:num)', 'Caja_controller::cargar_Venta_Sin_Facturar/$1');
$routes->get('cancelar_edicion_Venta_SF/(:num)', 'Caja_controller::cancelar_edicion_Venta_SF/$1');
//Verificacion de codigo de acceso
$routes->post('verificar-codigo', 'Caja_controller::verificarCodigo');

//Productos Defectuosos
$routes->get('descontarDefectuosos', 'Prod_Defectuosos_controller::MostrarLista_Prod');
$routes->get('historial_Descuentos', 'Prod_Defectuosos_controller::MostrarHistorial_Descuentos');
$routes->post('descontarDelStock', 'Prod_Defectuosos_controller::DescontarStock');
$routes->get('descargar_comprobante_descuento', 'Prod_Defectuosos_controller::descargar_comprobante_descuento');
$routes->post('Verif_Codigo_Descuento', 'Prod_Defectuosos_controller::Verif_Codigo_Descuento');

//Todo sobre Pedidos
$routes->get('cancelar_edicion/(:num)', 'Pedidos_controller::cancelar_edicion/$1');
$routes->get('/pedidosCompletados', 'Pedidos_controller::PedidosCompletados');
$routes->post('/filtrarPedidos', 'Pedidos_controller::filtrarPedidos');
$routes->get('/pedidos', 'Pedidos_controller::ListarPedidos');
$routes->get('/pedidosTodos', 'Pedidos_controller::PedidosTodos');
$routes->get('/nuevoPedido', 'Pedidos_controller::nuevoPedido');
$routes->post('/RegistrarPedido', 'Pedidos_controller::RegistrarPedido');
$routes->post('pedido_actualizar/(:num)', 'Pedidos_controller::pedido_actualizar/$1');
$routes->post('clienteListo/(:num)', 'Pedidos_controller::Pedido_completado/$1');
$routes->get('cancelar/(:num)', 'Pedidos_controller::Pedido_cancelado/$1');
$routes->post('pedidoClienteRegistrado', 'Pedidos_controller::pedidoClienteRegistrado');

//Servicios
$routes->get('/Lista_servicios', 'Servicios_controller::Servicios');
$routes->get('/new_Servicio', 'Servicios_controller::new_Servicio');
$routes->post('/agregar_Servicio', 'Servicios_controller::agregar_Servicio');
$routes->get('/editarServi/(:num)', 'Servicios_controller::editarServi/$1');
$routes->post('/edicionServiOk', 'Servicios_controller::edicionServiOk');


//clientes
$routes->get('/clientes', 'Clientes_controller::ListarClientes');
$routes->get('/editarCliente/(:num)', 'Clientes_controller::editarCliente/$1');
$routes->post('/edicionOk', 'Clientes_controller::EdicionOk');
$routes->get('/nuevo-cliente', 'Clientes_controller::nuevo_cliente');
$routes->post('/validar', 'Clientes_controller::formValidation');
//usuarios
$routes->get('/registro', 'Usuario_controller::create');
$routes->post('/enviar-form', 'Usuario_controller::formValidation');

/*rutas del login*/
$routes->get('/login2', 'Home::login');
$routes->post('/enviar-login', 'Conect_controller::loginAuth');
$routes->get('/conectar', 'Conect_controller::index');
$routes->get('/perfil', 'Perfil_controller::index');

/*
/*rutas del usuario*/
$routes->get('/registro', 'Usuario_controller::create');
$routes->post('/enviar-form', 'Usuario_controller::formValidation');
$routes->post('/actualizarDatos', 'Usuario_controller::usuarioEdit');


//rutas del ADMIN (Usuarios)
$routes->post('/crearUs', 'Usuario_controller::formValidationAdmin');
$routes->get('/nuevoUs', 'Usuario_controller::nuevoUsuario');
$routes->get('/usuarios-list', 'Datatable_controller::index');
$routes->get('/editoMisDatos/(:num)','Datatable_controller::editoMisDatos/$1');
$routes->get('/habilitarUs/(:num)', 'Usuario_controller::habilitar/$1');
$routes->get('/editarUs/(:num)', 'Datatable_controller::editar/$1');
$routes->get('/delete/(:num)', 'Usuario_controller::delete/$1');
$routes->post('/enviarEdicion', 'Usuario_controller::formValidationEdit');
$routes->get('/eliminados', 'Usuario_controller::usuariosEliminados');



//Rutas del ADMIN (Productos)
$routes->post('/EdicionRapidaProd', 'Producto_controller::EdicionRapidaProd');
$routes->get('/nuevoProducto', 'Producto_controller::nuevoProducto');
$routes->post('/ProductoValidation', 'Producto_controller::ProductoValidation');
$routes->get('/Lista_Productos', 'Producto_controller::ListaProductos');
$routes->post('/enviarEdicionProd', 'Producto_controller::ProdValidationEdit');
$routes->get('/eliminadosProd', 'Producto_controller::ListaProductosElim');
$routes->get('/deleteProd/(:num)', 'Producto_controller::deleteProd/$1');
$routes->get('/habilitarProd/(:num)', 'Producto_controller::habilitarProd/$1');
$routes->get('/ProductoEdit/(:num)', 'Producto_controller::getProductoEdit/$1');
$routes->get('/nuevoCategoria', 'Producto_controller::nuevoCategoria');
$routes->post('/categoriaValidation', 'Producto_controller::categoriaValidation');
$routes->get('/ListaCategorias', 'Producto_controller::ListaCategorias');
$routes->get('/deleteCateg/(:num)', 'Producto_controller::deleteCateg/$1');
$routes->get('/CategoriaEdit/(:num)', 'Producto_controller::getCategoriaEdit/$1');
$routes->post('/enviarEdicionCateg', 'Producto_controller::CategValidationEdit');
$routes->get('/eliminadosCateg', 'Producto_controller::ListaCategElim');
$routes->get('/habilitarCateg/(:num)', 'Producto_controller::habilitarCateg/$1');
$routes->get('/StockBajo', 'Producto_controller::ProductosStockBajo');




//Rutas del Cliente(Ver Productos Disponibles)
$routes->get('/catalogo', 'Producto_controller::ProductosDisp');
$routes->get('/ProductoDetalle/(:num)', 'Producto_controller::ProductoDetalle/$1');
$routes->get('/Indumentaria', 'Producto_controller::Indumentaria');
$routes->get('/Calzado', 'Producto_controller::Calzado');
$routes->get('/Accesorios', 'Producto_controller::Accesorios');


//Rutas del Admin(Consultas)
$routes->get('contact-form', 'FormController::create');
$routes->post('submit-form', 'FormController::formValidation');
$routes->get('consultas', 'Contactocontroller::Datos_consultas');
$routes->get('ConsultaDetalle/(:num)', 'Contactocontroller::ConsultaDetalle/$1');
$routes->post('ConsultaResuelta/(:num)', 'Contactocontroller::deleteConsulta/$1');
$routes->get('consultasResueltas', 'Contactocontroller::Datos_consultasResueltas');

//Rutas del Login / Registro
$routes->get('/login', 'Login_controller');
$routes->post('/enviarlogin','Login_controller::auth');
$routes->get('/logout', 'Login_controller::logout');
$routes->get('/sesiones', 'Login_controller::mostrarSesiones');
$routes->post('/filtrarSesiones', 'Login_controller::mostrarSesiones');

//Carrito y Ventas
$routes->post('filtrarVentas', 'Carrito_controller::filtrarVentas');
$routes->get('filtrarPedidos', 'Carrito_controller::filtrarPedidos');
$routes->get('CarritoList', 'Carrito_controller::productosAgregados');
$routes->get('cargar_pedido/(:num)', 'Pedidos_controller::cargar_pedido_en_carrito/$1');
$routes->post('Carrito_agrega', 'Carrito_controller::add');
$routes->post('Agregamos', 'Carrito_controller::agregarDesdeListaProd');
$routes->post('Otros_gastos', 'Carrito_controller::agregar');
$routes->get('carrito_elimina/(:any)', 'Carrito_controller::remove/$1');
$routes->post('carrito_actualiza', 'Carrito_controller::actualiza_carrito');
$routes->post('comprar', 'Carrito_controller::actualiza_carrito_antesDeConfirmar');
$routes->post('carrito/procesarCarrito', 'Carrito_controller::procesarCarrito');
$routes->get('casiListo', 'Carrito_controller::muestra_compra');
$routes->post('confirma_compra', 'Carrito_controller::guarda_compra');
$routes->get('compras', 'Carrito_controller::ListVentasCabecera');
$routes->get('DetalleVta/(:num)', 'Carrito_controller::ListCompraDetalle/$1');
$routes->get('Gracias', 'Carrito_controller::GraciasPorSuCompra');

//Facturacion y Reportes
$routes->get('PDF/(:num)', 'Carrito_controller::FacturaAdmin/$1');
$routes->get('turnos/(:num)', 'Carrito_controller::ListaTurnosCabeceraCliente/$1');
$routes->get('factura/(:num)', 'Carrito_controller::FacturaCliente/$1');

//AFIP
$routes->get('verificarTA','Carrito_controller::verificarTA');
$routes->get('generarTicket', 'Carrito_controller::facturar');

$routes->get('descargar_ticket', 'Carrito_controller::descargar_ticket');

$routes->get('verificarTA/(:num)','Carrito_controller::verificarTA/$1');
$routes->get('generarTicket/(:num)', 'Carrito_controller::generarTicket/$1');
$routes->get('generarTicketFacturaC/(:num)', 'Carrito_controller::generarTicketFacturaC/$1');
$routes->get('generarPresupuesto/(:num)', 'Carrito_controller::generarPresupuesto/$1');



/*
	
*/
/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
