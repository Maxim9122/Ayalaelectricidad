<?php
namespace App\Controllers;

require_once APPPATH . 'Libraries/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

use CodeIgniter\Controller;
Use App\Models\Productos_model;
Use App\Models\categoria_model;
Use App\Models\Defectuosos_model;
use App\Models\Usuarios_model;


class Prod_Defectuosos_controller extends Controller{

	public function __construct(){
           helper(['form', 'url']);
	}

//Historial de descuentos de stock.
    public function MostrarHistorial_Descuentos(){
        $session = session();
        
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login'));
        }
        $perfil = $session->has('perfil_id');
        if ($perfil == 2) { 
            return redirect()->to(base_url('catalogo'));
        }          
        
        $historial_Def = new Defectuosos_model();   
        $dato['productos'] = $historial_Def->Traer_Historial();
        $dato1['titulo'] = 'Historial de Descuentos de Stock'; 
        
    
        echo view('navbar/navbar');
        echo view('header/header', $dato1);        
        echo view('productos/Historial_Descuentos',$dato);
        echo view('footer/footer');
    }

    //Lista de productos para seleccionar y descontar.
    public function MostrarLista_Prod(){
        $session = session();
        
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login'));
        }
        $perfil = $session->get('perfil_id');        
        if ($perfil == 2) { 
            return redirect()->to(base_url('catalogo'));
        }  
        $Model = new categoria_model();
    	$dato['categorias']=$Model->getCategoria();//trae la categoria del db
        $ProductosModel = new Productos_model();
        $eliminado = 'NO';
        $productos = $ProductosModel->getProdBaja($eliminado);
    
        // Verificar si algún producto tiene stock bajo
        $productos_bajo_stock = array_filter($productos, function($producto) {
            return $producto['stock'] <= $producto['stock_min'];
        });
    
        // Si hay productos con stock bajo, guardamos un mensaje en sesión
        if (!empty($productos_bajo_stock)) {
            $session->setFlashdata('mensaje_stock', '¡Atención! Algunos productos tienen stock bajo o nulo.');
        }
    
        $dato1['titulo'] = 'Lista Productos'; 
        $data['productos'] = $productos;
    
        echo view('navbar/navbar');
        echo view('header/header', $dato1);        
        echo view('productos/listarProd_paraDescontar', $data + $dato);
        echo view('footer/footer');
    }

//funcion para verificar y descontar el stock del producto defectuoso
public function DescontarStock() {
    $session = session();
    $perfil = $session->get('perfil_id');        
        if ($perfil == 2) { 
            return redirect()->to(base_url('catalogo'));
        }   
    $session = session();
    $Producto_model = new Productos_model();
    $Defectuosos_model = new Defectuosos_model();

    // Obtener los datos del formulario
    $producto_id = $this->request->getPost('id');
    $cantidad = $this->request->getPost('cantidad');
    $nombre_producto = $this->request->getPost('nombre');
    $precio_vta = $this->request->getPost('precio_vta');
    $motivo_desc = $this->request->getPost('motivo_desc');
    $nombre_us = $session->get('nombre');

    if (!$motivo_desc) {
        session()->setFlashdata('msgEr', 'El campo Motivo es Obligatorio.!');
        return redirect()->to('descontarDefectuosos');
    }

    // Obtener el stock actual desde la base de datos
    $producto = $Producto_model->find($producto_id);
    if (!$producto) {
        session()->setFlashdata('msgEr', 'El producto no existe.');
        return redirect()->to('descontarDefectuosos');
    }

    date_default_timezone_set('America/Argentina/Buenos_Aires');

    $stock_actual = $producto['stock'];

    // Validar que la cantidad a descontar no sea mayor que el stock disponible
    if ($cantidad <= $stock_actual && $cantidad >= 1) {
        // Actualizar el stock en la base de datos
        $nuevo_stock = $stock_actual - $cantidad;
        $Producto_model->update($producto_id, ['stock' => $nuevo_stock]);

        // Guardar el registro en la tabla defectuosos
        $data_defectuoso = [
            'nombre_def'    => $nombre_producto,
            'precio_def'   => $precio_vta,
            'cantidad_desc' => $cantidad,
            'nuevo_stock' => $nuevo_stock,
            'nombre_us'    => $nombre_us,
            'fecha_desc'   => date('d-m-Y H:i'),
            'motivo_desc'  => $motivo_desc
        ];
        $id_defectuoso = $Defectuosos_model->guardarDefectuoso($data_defectuoso); // Insertar en la tabla defectuosos

        // Generar y descargar el comprobante de descuento
        return $this->generarComprobanteDescuento($id_defectuoso);
    } else {
        // Agregar el producto a la lista de errores
        $errores_stock[] = "Producto: <strong>$nombre_producto</strong> - Cantidad solicitada: <strong>$cantidad</strong> - Stock disponible: <strong>$stock_actual</strong>";

        // Si hay errores de stock, mostrar mensaje y redirigir
        $mensaje_error = "No se puede descontar la cantidad solicitada:<br>" . implode("<br>", $errores_stock);
        session()->setFlashdata('msgEr', $mensaje_error);
        return redirect()->to('descontarDefectuosos');
    }
    }

//Descargar Comprbante de descuento de stock
    public function generarComprobanteDescuento($id_defectuoso)
    {
        // Cargar los modelos necesarios
        $Defectuosos_model = new \App\Models\Defectuosos_model();
        $Us_Model = new \App\Models\Usuarios_model(); 
        
    
        // Obtener los detalles del descuento
        $defectuoso = $Defectuosos_model->find($id_defectuoso);
    
        if (!$defectuoso) {
            session()->setFlashdata('msgEr', 'El descuento no existe.');
            return redirect()->to('descontarDefectuosos');
        }
    
        // Crear el HTML para la vista previa
        ob_start();
        ?>
        <html>
        <head>
            <style>
                /* Estilos CSS para el comprobante */
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 0;
                    width: 220px; /* Ancho del comprobante */
                }
                .comprobante {
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
                .comprobante p {
                    margin: 2px 0;
                    font-size: 10px;
                    font-weight: bold;
                    text-align: justify;
                }
                .comprobante hr {
                    border: 0.5px solid #000;
                    margin: 5px 0;
                }
                .comprobante .header,
                .comprobante .footer {
                    text-align: center;
                    font-size: 10px;
                }
                .comprobante .details {
                    margin-top: 3px;
                    font-size: 10px;
                }
                .comprobante .details td {
                    padding: 0px;
                }
                .comprobante .details th {
                    text-align: left;
                    padding-right: 5px;
                }
            </style>
        </head>
        <body>
            <div class="comprobante">
                <h3>Comprobante de Descuento</h3>      
                <h3>Nro: <?= $defectuoso['id']; ?></h3>
                <!-- Información del descuento -->
                <p>Fecha y Hora: <?= date('d-m-Y H:i', strtotime($defectuoso['fecha_desc'])); ?></p>
                <p>Producto: <?= $defectuoso['nombre_def']; ?></p>
                <p>Cantidad Descontada: <?= $defectuoso['cantidad_desc']; ?></p>
                <p>Nuevo Stock: <?= $defectuoso['nuevo_stock']; ?></p>
                <p>Precio Unitario: $<?= number_format($defectuoso['precio_def'], 2); ?></p>              
                <p>Stock Descontado por: <?= $defectuoso['nombre_us']; ?></p>
                <p>Motivo: <?= $defectuoso['motivo_desc']; ?></p>
                <hr>    
            
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
        $tempFolder = WRITEPATH . 'temp';  // Ruta de la carpeta temporal
        $tempFile = $tempFolder . '/comprobante_descuento.pdf';  // Ruta completa del archivo PDF
    
        // Crear la carpeta si no existe
        if (!is_dir($tempFolder)) {
            mkdir($tempFolder, 0777, true);  // Crea la carpeta con permisos 0777
        }
    
        // Guardar el archivo PDF en la carpeta temporal
        file_put_contents($tempFile, $output);
    
        // Redirigir a la descarga del comprobante
        echo "<script type='text/javascript'>
                // Descargar el archivo PDF
                window.location.href = '" . base_url('descargar_comprobante_descuento') . "';
                
                // Redirigir a la página deseada después de la descarga
                window.setTimeout(function() {
                    window.location.href = '" . base_url('descontarDefectuosos') . "';
                }, 500);  // 0.5 segundo de espera para asegurar que la descarga termine
              </script>";
        exit;
    }
    
    // Función para descargar el comprobante
    public function descargar_comprobante_descuento()
    {
        $filePath = WRITEPATH . 'temp/comprobante_descuento.pdf';
        if (file_exists($filePath)) {
            return $this->response->download($filePath, null)->setFileName('comprobante_descuento.pdf');
        } else {
            session()->setFlashdata('msgEr', 'El comprobante no existe.');
            return redirect()->to('descontarDefectuosos');
        }
    }

//Verificar Codigo de autorizacion de descuento de stock
    public function Verif_Codigo_Descuento()
{
    $json = $this->request->getJSON();
    $codigoIngresado = $json->codigo;

    // Código autorizado (esto puede venir de la base de datos)
    $codigoAutorizado = "7791293043746"; 

    if ($codigoIngresado === $codigoAutorizado) {
        return $this->response->setJSON(['valido' => true]);
    } else {
        return $this->response->setJSON(['valido' => false]);
    }
}


}