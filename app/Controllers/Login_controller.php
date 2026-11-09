<?php 
namespace App\Controllers;
use CodeIgniter\Controller;
use App\Models\Usuarios_model;
use App\Models\Sesion_model;
use App\Models\Cabecera_model;
use App\Models\VentaDetalle_model;
use App\Models\Productos_model;

class Login_controller extends Controller
{
    public function index()
    {
        helper(['form','url']);

         $dato['titulo']='login'; 
        
        echo view('navbar/navbar');
        echo view('header/header',$dato);
        echo view('Login/login');
        echo view('footer/footer');
    } 
  
    public function auth()
    {
        $session = session();
        $model = new Usuarios_model();
        $email = $this->request->getVar('email');
        $password = $this->request->getVar('pass');
        $data = $model->where('email', $email)->first();
        if($data){
            $pass = $data['pass'];
            $verify_pass = password_verify($password, $pass);
            if($verify_pass){
                if($data['baja']=='SI'){
                    $session->setFlashdata('msg', 'Usted fue dado de Baja');
                    return redirect()->to('login');
                }else{

                //registrando inicio de sesion
                $registro_sesion = new Sesion_model();
                date_default_timezone_set('America/Argentina/Buenos_Aires');
                $registro_sesion ->save([
                    'id_usuario' => $data['id'],
                    'inicio_sesion' => date('d-m-Y H:i'), // Fecha y hora actual de Argentina
                    'estado' => 'activa'
                ]); 
                //print_r($data['id']);
                //exit;
                $id_regSesion = $registro_sesion->getInsertID(); 

                $ses_data = [
                    'id' => $data['id'],
                    'nombre' => $data['nombre'],
                    'apellido'=> $data['apellido'],
                    'email' =>  $data['email'],
                    'telefono' => $data['telefono'],
                    'direccion' => $data['direccion'],
                    'perfil_id'=> $data['perfil_id'],
                    'id_sesion'=> $id_regSesion,
                    'cd_efectivo' => 1,
                    'logged_in'     => TRUE
                ];
                

                $session->set($ses_data);
                if($ses_data['perfil_id'] == 2){
                return redirect()->to('catalogo');
                }else if($ses_data['perfil_id'] == 3){
                return redirect()->to('catalogo');
                }else{
                return redirect()->to('compras');   
                }
            }}else{
                $session->setFlashdata('msg', 'Password Incorrecta');
                return redirect()->to('login');
            }
        }else{
            $session->setFlashdata('msg', 'Email Incorrecto');
            return redirect()->to('login');
        }
    }


    
    public function logout()
    {
        $session = session();
        //CANCELAMOS EL COBRO O LA MODIFICACION ABIERTA ANTES DE SALIR: la venta vuelve al estado que tenía.
        \App\Libraries\BloqueoVenta::liberarDeSesion();
         $registro_sesion = new Sesion_model();
         $id_sesion = $session->get('id_sesion'); 
         //print_r();
         //exit;
                date_default_timezone_set('America/Argentina/Buenos_Aires');
                $data =[
                    'fin_sesion' => date('d-m-Y H:i'), // Fecha y hora actual de Argentina
                    'estado' => 'cerrada'
                ];

                //print_r($id_sesion);
                //print_r($data);
               // exit;
            //$registro_sesion->actualizar_sesion($id_sesion,$data);
            $session->destroy();
            return redirect()->to('/');
    }

    //muestra las sesiones de los usuarios
    public function mostrarSesiones()
    {
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }

        $usuarioModel = new Sesion_model();
        // Obtener parámetros de búsqueda y filtro
        $filter = $this->request->getVar('filter'); // Para filtrar por estado
       // $search = $this->request->getVar('search'); // Para filtrar por busqueda
        //print_r($search);
        //exit;
        // Pasar los parámetros al modelo
        if($filter){
            $data['sesiones'] = $usuarioModel->buscarYFiltrar($filter);
        }else{
            $data['sesiones'] = $usuarioModel->getSesionesConUsuarios();
        }
        //print_r($data['sesiones']); 
        //exit;
       // $sesiones = $usuarioModel->getSesionesConUsuarios();
       
        $dato['titulo']='Sesiones';
        echo view('navbar/navbar'); 
        echo view('header/header',$dato);
         echo view('Login/sesiones',$data);
       echo view('footer/footer');
       // return view('sesiones', ['sesiones' => $sesiones]);
    }
} 
