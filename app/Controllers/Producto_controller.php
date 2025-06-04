<?php
namespace App\Controllers;
Use App\Models\Productos_model;
Use App\Models\categoria_model;
use CodeIgniter\Controller; 

class Producto_controller extends Controller{

	public function __construct(){
           helper(['form', 'url']);

	}

    public function EdicionRapidaProd() {
        $model = new Productos_model();
        $id = $this->request->getPost('id_prod');
        
        // Obtener el producto actual primero
        $productoActual = $model->find($id);
        
        if (!$productoActual) {
            session()->setFlashdata('msgEr', 'Producto no encontrado');
            return redirect()->to(base_url('Lista_Productos'));
        }
        
        // Preparar datos para actualización
        $data = [];
        $hayCambios = false;
        
        // Validar y actualizar precio
        if ($this->request->getPost('precio') !== null && $this->request->getPost('precio') !== '') {
            $nuevoPrecio = $this->request->getPost('precio');
            $nuevoPrecio = str_replace('.', '', $nuevoPrecio); // Elimina el punto de miles
            if ($nuevoPrecio != $productoActual['precio']) {
                $data['precio'] = $nuevoPrecio;
                $hayCambios = true;
            }
        }
        
        // Validar y actualizar precio_vta
        if ($this->request->getPost('precio_vta') !== null && $this->request->getPost('precio_vta') !== '') {
            $nuevoPrecioVta = $this->request->getPost('precio_vta');            
            $nuevoPrecioVta = str_replace('.', '', $nuevoPrecioVta); // Elimina el punto de miles         
            if ($nuevoPrecioVta != $productoActual['precio_vta']) {
                $data['precio_vta'] = $nuevoPrecioVta;
                $hayCambios = true;
            }
        }
        
        // Validar y actualizar stock
        if ($this->request->getPost('stock') !== null && $this->request->getPost('stock') !== '') {
            $nuevoStock = (int)$this->request->getPost('stock');
            if ($nuevoStock != $productoActual['stock']) {
                $data['stock'] = $nuevoStock;
                $hayCambios = true;
            }
        }
    $page = $this->request->getPost('page') ?? 1;
    
        // Actualizar solo si hay cambios
        if ($hayCambios) {
            try {
                $model->updateDatosProd($id, $data);
                session()->setFlashdata('msg', 'Producto actualizado correctamente');
            } catch (\Exception $e) {
                session()->setFlashdata('msgEr', 'Error al actualizar: ' . $e->getMessage());
            }
        } else {
            session()->setFlashdata('msg', 'No se realizaron cambios');
        }
        
        return redirect()->to(base_url('Lista_Productos?page=' . $page));
    }

	public function nuevoProducto(){
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
        $Model = new categoria_model();
        $eliminado = 'NO';
        $data['categorias']= $Model->getProdBaja($eliminado);//trae la categoria del db
        
		$data['titulo']='Nuevo Producto';
                echo view('navbar/navbar');
                echo view('header/header',$data);
                echo view('admin/nuevoProducto_view',$data);
                echo view('footer/footer');
	}

    // funcion para agregar nueva categoria
    public function nuevoCategoria(){
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }    
		$data['titulo']='Nuevo Categoria';
                echo view('navbar/navbar');
                echo view('header/header',$data);
                echo view('admin/nuevoCategoria_view');
                echo view('footer/footer');
	}

    

	public function ProductoValidation() {
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
        $input = $this->validate([
            //'codigo_barra' => 'is_unique[productos.codigo_barra]',
            'nombre'   => 'required|min_length[3]',          
            //'categoria_id' => 'required|min_length[1]|max_length[20]',
            //'precio'    => 'required|min_length[2]|max_length[10]',
            'precio_vta'  => 'required|min_length[2]',
            'stock'     => 'required|min_length[1]|max_length[10]',
            //'stock_min'     => 'required|min_length[1]|max_length[10]',
            
        ]);
        $ProductoModel = new Productos_model();
        
        if (!$input) {
            $Model = new categoria_model();
            $eliminado = 'NO';
            $data['categorias']= $Model->getProdBaja($eliminado);//trae la categoria del db
            
            $data['titulo']='Nuevo Producto'; 
               echo view('navbar/navbar');
               echo view('header/header',$data);
                echo view('admin/nuevoProducto_view',['validation' => $this->validator]);
                echo view('footer/footer');
        } else {

        	//$img = $this->request->getFile('imagen');
        	//$nombre_aleatorio= $img->getRandomName();
        	//$img->move(ROOTPATH.'assets/uploads',$nombre_aleatorio);

            $ProductoModel->save([
                'nombre' => $this->request->getVar('nombre'),
                //'descripcion' => $this->request->getVar('descripcion'),
                //'imagen' => $img->getName(),
                'categoria_id' => 1, //$this->request->getVar('categoria_id')
                //'precio' => $this->request->getVar('precio'),
                'precio_vta'  => $this->request->getVar('precio_vta'),
                'stock' => $this->request->getVar('stock'),
                //'stock_min' => $this->request->getVar('stock_min'),
                //'codigo_barra' => $this->request->getVar('codigo_barra'),
                'eliminado' => 'NO',
                
            ]);  
            session()->setFlashdata('msg','Producto Creado con Éxito!');
             return redirect()->to(base_url('nuevoProducto'));
        }
    }
    // verifica los datos de la categoria nueva
    public function categoriaValidation() {
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
        $input = $this->validate([
            'descripcion'   => 'required'
        ]);
        $categoriaModel = new categoria_model();
        
        if (!$input) {
               $data['titulo']='Nuevo Categoria';
               echo view('navbar/navbar');
               echo view('header/header',$data);
                echo view('admin/nuevoCategoria_view',['validation' => $this->validator]);
                echo view('footer/footer');
        } else {

        	

            $categoriaModel->save([
                'descripcion' => $this->request->getVar('descripcion'),
                'eliminado' => "No" 
            ]);  
            session()->setFlashdata('msg','Producto Creado con Éxito!');
             return redirect()->to(base_url('Lista_Productos'));
        }
    }

    public function ListaProductos(){
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
        $Model = new categoria_model();
    	$dato['categorias']=$Model->getCategoria();//trae la categoria del db
        $ProductosModel = new Productos_model();
        $eliminado = 'NO';       

        // Capturamos la página actual de paginación (por defecto 1 si no existe)
        $page = $this->request->getGet('page') ?? 1;

        $busqueda = $this->request->getGet('search');
        // Pasamos la página actual para que paginate sepa cuál devolver
        $productos = $ProductosModel->getProductosPaginados($eliminado, $busqueda, $page);

        $pager = $ProductosModel->getPager();
    
        // Verificar si algún producto tiene stock bajo
        $productos_bajo_stock = array_filter($productos, function($producto) {
            return $producto['stock'] <= $producto['stock_min'];
        });
    
        // Si hay productos con stock bajo, guardamos un mensaje en sesión
        if (!empty($productos_bajo_stock)) {
            $session->setFlashdata('mensaje_stock', '¡Atención! Algunos productos tienen stock bajo o nulo.');
        }
        //print_r($dato);
        //exit;
        $dato1['titulo'] = 'Productos Disponibles';
        $data['productos'] = $productos;
        $data['pager'] = $pager;
        $data['page'] = $page;  // <-- enviar la página actual a la vista

        echo view('navbar/navbar');
        echo view('header/header',$dato1);
         echo view('admin/productos_view', $data + $dato);
          echo view('footer/footer');
       
    } 
    // muestra las categorias 
    public function ListaCategorias(){
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
        $Model = new categoria_model();
        $eliminado = 'NO';
        $productos = $Model->getProdBaja($eliminado);
        //print_r($dato);
        //exit;
        $dato1['titulo']='Lista de Categorias'; 
        $data['productos'] = $productos;
        echo view('navbar/navbar');
        echo view('header/header',$dato1);
         echo view('admin/categorias_view.php', $data);
          echo view('footer/footer');
       
    }

	public function ProductosDisp() {
    $session = session();
    $cart = \Config\Services::cart();
		$carrito['carrito']=$cart->contents();

    if (!$session->has('id')) {
        return redirect()->to(base_url('login'));
    }

    $Model = new categoria_model();
    $dato['categorias'] = $Model->getCategoria();

    $ProductosModel = new Productos_model();
    $eliminado = 'NO';

    // Capturamos la página actual de paginación (por defecto 1 si no existe)
    $page = $this->request->getGet('page') ?? 1;

    $busqueda = $this->request->getGet('search');
    // Pasamos la página actual para que paginate sepa cuál devolver
    $productos = $ProductosModel->getProductosPaginados($eliminado, $busqueda, $page);

    $pager = $ProductosModel->getPager();

    // Productos con stock bajo (igual que antes)
    $productos_bajo_stock = array_filter($productos, function($producto) {
        return $producto['stock'] <= $producto['stock_min'];
    });

    if (!empty($productos_bajo_stock)) {
        $session->setFlashdata('mensaje_stock', '¡Atención! Algunos productos tienen stock bajo o nulo.');
    }

    $dato1['titulo'] = 'Productos Disponibles';
    $data['productos'] = $productos;
    $data['pager'] = $pager;
    $data['page'] = $page;  // <-- enviar la página actual a la vista

    echo view('navbar/navbar');
    echo view('header/header', $dato1);        
    echo view('productos/listar', $data + $dato);
    echo view('carrito/ProductosEnCarrito',$carrito);
    echo view('footer/footer');
    }

    

    public function ProductosStockBajo(){
        $ProductosModel = new Productos_model();
        $data['productos'] = $ProductosModel->getPorStockBajo();
        $Model = new categoria_model();
    	$dato1['categorias']=$Model->getCategoria();//trae la categoria del db
        $dato['titulo']='Productos Disponibles';
        echo view('navbar/navbar');
        echo view('header/header',$dato);        
         echo view('admin/productos_view', $data + $dato1);
          echo view('footer/footer');
       
    }

    public function Calzado(){
        $ProductosModel = new Productos_model();
        $tipo='2';
        $data['productos'] = $ProductosModel->getTipo($tipo);
        $dato['titulo']='Productos Disponibles';
        echo view('navbar/navbar');
        echo view('header/header',$dato);        
         echo view('productos/listar', $data);
          echo view('footer/footer');
       
    }

    public function Accesorios(){
        $ProductosModel = new Productos_model();
        $tipo='3';
        $data['productos'] = $ProductosModel->getTipo($tipo);
        $dato['titulo']='Productos Disponibles';
        echo view('navbar/navbar');
        echo view('header/header',$dato);        
         echo view('productos/listar', $data);
          echo view('footer/footer');
       
    }

    public function getProductoEdit($id){
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
        $Model = new categoria_model();
        $eliminado = 'NO';
        $dato1['categorias']= $Model->getProdBaja($eliminado);//trae la categoria del db
    	$Model = new Productos_model();
    	$data=$Model->getProducto($id);
            $dato['titulo']='Editar Producto'; 
                echo view('navbar/navbar');
                echo view('header/header',$dato);
                echo view('admin/editarProducto_view',compact('data')+ $dato1);
                echo view('footer/footer');
    }
    //editar categoria
    public function getCategoriaEdit($categoria_id){
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
    	$Model = new categoria_model();
    	$data=$Model->getEdit($categoria_id);
            $dato['titulo']='Editar Producto'; 
                echo view('navbar/navbar');
                echo view('header/header',$dato);
                echo view('admin/editarCategoria_view',compact('data'));
                echo view('footer/footer');
    }


    public function ProductoDetalle($id){
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
    	$Model = new Productos_model();
    	$data=$Model->getProducto($id);
            $dato['titulo']='Editar Producto'; 
            echo view('navbar/navbar');
            echo view('header/header',$dato);   
                echo view('back/carrito/DetalleProducto_view',compact('data'));
                echo view('footer/footer');
    }

    public function ProdValidationEdit() {
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
        //print_r($_POST);exit;
        
        $input = $this->validate([
            //'codigo_barra' => "required|is_unique[productos.codigo_barra,id,{$_POST['id']}]", // Ignora el ID actual
            'nombre'   => 'required|min_length[3]',            
            //'categoria_id' => 'required|min_length[1]|max_length[2]',
            //'precio'    => 'required|min_length[2]|max_length[10]',
            'precio_vta'  => 'required|min_length[2]',
            'stock'     => 'required|min_length[1]|max_length[10]',
            //'stock_min'     => 'required|min_length[1]|max_length[10]',
            'eliminado' => 'required|min_length[2]|max_length[2]',
        ]);
        $Model = new Productos_model();
        $id=$_POST['id'];
        if (!$input) {
            $ModelCat = new categoria_model();
    	    $dato1['categorias']=$ModelCat->getCategoria();//trae la categoria del db
            $data=$Model->getProducto($id);
            $dato['titulo']='Editar Producto'; 
            echo view('navbar/navbar');
            echo view('header/header',$dato);   
                echo view('admin/editarProducto_view',compact('data') + $dato1);
                echo view('footer/footer');
        } else {
        /*	$validation= $this->validate([
        		'image' => ['uploaded[imagen]',
        		'mime_in[imagen,image/jpg,image/jpeg,image/png]',
        	]
        	]);
        	if($validation){
        	$img = $this->request->getFile('imagen');
        	$nombre_aleatorio= $img->getRandomName();
        	$img->move(ROOTPATH.'assets/uploads',$nombre_aleatorio);
            $datos=[
                'id' => $_POST['id'],
                'nombre' =>$_POST['nombre'],
                'descripcion' => $_POST['descripcion'],
                'imagen' => $img->getName(),
                'precio' => $_POST['precio'],
                'precio_vta'  => $_POST['precio_vta'],
                'categoria_id'  => $_POST['categoria_id'],
                'stock'  => $_POST['stock'],
                'stock_min'  => $_POST['stock_min'],
                'eliminado' => $_POST['eliminado'],
                'codigo_barra' => $_POST['codigo_barra'],
                
            ];  
         	}else { */
         	$datos=[
                'id' => $_POST['id'],
                'nombre' =>$_POST['nombre'],
                //'descripcion' => $_POST['descripcion'],
                //'precio' => $_POST['precio'],
                'precio_vta'  => $_POST['precio_vta'],
                //'categoria_id'  => $_POST['categoria_id'],
                'stock'  => $_POST['stock'],
                //'stock_min'  => $_POST['stock_min'],
                'eliminado' => $_POST['eliminado'],
                //'codigo_barra' => $_POST['codigo_barra'],
            ];
            
            }
         
         $Model -> updateDatosProd($id,$datos);

         session()->setFlashdata('msg','Producto Editado');

         return redirect()->to(base_url('Lista_Productos'));
        }
    //}
    
    //valida la edicion de categoria para cargar al db
    public function CategValidationEdit() {
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
        //print_r($_POST);exit;
        
        $input = $this->validate([
            'descripcion'   => 'required|max_length[200]'
        ]);
        $Model = new categoria_model();
        $categoria_id=$_POST['categoria_id'];
        if (!$input) {
            $data=$Model->getEdit($categoria_id);
            $dato['titulo']='Editar Categoria'; 
                echo view('header',$dato);
                echo view('nav_view');
                echo view('back/Admin/editarCategoria_view',compact('data'));
                echo view('footer');
        } else {
        	$validation= $this->validate([
        		'image' => ['uploaded[imagen]',
        		'mime_in[imagen,image/jpg,image/jpeg,image/png]',
        	]
        	]);
        	if($validation){
        	$img = $this->request->getFile('imagen');
        	$nombre_aleatorio= $img->getRandomName();
        	$img->move(ROOTPATH.'assets/uploads',$nombre_aleatorio);
            $datos=[
                'categiria_id' => $_POST['categoria_id'],
                'descripcion' => $_POST['descripcion'],
                'eliminado' => $_POST['eliminado'],
            ];  
         	}else{
         	$datos=[
                'categiria_id' => $_POST['categoria_id'],
                'descripcion' => $_POST['descripcion'],
                'eliminado' => $_POST['eliminado'],
                
            ];
            }
         
         $Model -> updateDatosCateg($categoria_id,$datos);

         session()->setFlashdata('msg','Categoria Editado');

         return redirect()->to(base_url('ListaCategorias'));
        }
    }

    public function deleteProd($id){
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
        $Model=new Productos_model();
        $data=$Model->getProducto($id);
        $datos=[
                'id' => 'id',
                'eliminado'  => 'SI',
                
            ];
        $Model->update($id,$datos);

        session()->setFlashdata('msg','Producto Eliminado');

        return redirect()->to(base_url('Lista_Productos'));
    }
    //elimina la categoria
    public function deleteCateg($categoria_id){
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
        $Model=new categoria_model();
        $data=$Model->getEliminar($categoria_id);
        $datos=[
                'categoria_id' => 'id',
                'eliminado'  => 'SI',
                
            ];
        $Model->update($categoria_id,$datos);

        session()->setFlashdata('msg','Categoria Eliminado');

        return redirect()->to(base_url('ListaCategorias'));
    }

    public function ListaProductosElim(){
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
        $Model = new categoria_model();
    	$dato['categorias']=$Model->getCategoria();//trae la categoria del db
        $userModel = new Productos_model();
        $eliminado='SI';
        $data['productos'] = $userModel->getProdBaja($eliminado);
        $dato1['titulo']='Productos Eliminados'; 
        echo view('navbar/navbar');
        echo view('header/header',$dato1);        
         echo view('admin/listProd_Eliminados_view',$data + $dato);
          echo view('footer/footer');
    }
    // lista de categorias eliminados
    public function ListaCategElim(){
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
        $Model = new categoria_model();
        $userModel = new Productos_model();
        $eliminado='SI';
        $data['productos'] = $Model->getProdBaja($eliminado);
        $dato1['titulo']='Productos Eliminados'; 
        echo view('navbar/navbar');
        echo view('header/header',$dato1);        
         echo view('admin/listCateg_Eliminados_view',$data);
          echo view('footer/footer');
    }


    public function habilitarProd($id){
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
        $Model=new Productos_model();
        $data=$Model->getProducto($id);
        $datos=[
                'id' => 'id',
                'eliminado'  => 'NO',
                
            ];
        $Model->update($id,$datos);

        session()->setFlashdata('msg','Producto Habilitado');

        return redirect()->to(base_url('eliminadosProd'));
    }
    //cambia el estado de categoria eliminado
    public function habilitarCateg($categoria_id){
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
        $Model=new categoria_model();
        $data=$Model->getCateg($categoria_id);
        $datos=[
                'categoria_id' => 'categoria_id',
                'eliminado'  => 'NO',
                
            ];
        $Model->update($categoria_id,$datos);

        session()->setFlashdata('msg','Categoria Habilitado');

        return redirect()->to(base_url('eliminadosCateg'));
    }
}