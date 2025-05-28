<?php 
namespace App\Controllers;  
use CodeIgniter\Controller;
use App\Models\Usuarios_model;
  
class Conect_controller extends Controller
{
    public function __construct(){
           helper(['form', 'url']);

    }
   
    public function index() {

         $dato['titulo']='login'; 
        echo view('header',$dato);
        echo view('nav_view');
        echo view('back/login/login2');
        echo view('footer');
     
    }

 
    public function loginAuth()
    {
        $session = session();
        $usuariosmodel = new Usuarios_model();
        $usuario = $this->request->getVar('usuario');
        $password = $this->request->getVar('pass');
        
        $data = $usuariosmodel->where('usuario', $usuario)->first();
        
        if($data){
            $pass = $data['pass'];
            $authenticatePassword = password_verify($password, $pass);
            if($authenticatePassword){
                $ses_data = [
                    'id' => $data['id'],
                    'nombre' => $data['nombre'],
                    'apellido'=> $data['apellido'],
                    'email' =>  $data['email'],
                    'usuario' => $data['usuario'],
                   // 'pass' => $data['pass'],
                    'perfil_id'=> $data['perfil_id'],
                    'isLoggedIn' => TRUE
                ];

                $session->set($ses_data);

                echo "bienvenido: ". $session->set($data['']);
                //return redirect()->route('perfil')->with('msg', [
                //    'type'=> 'success',
                //    'body' => 'Bienvenido nuevamente'.$data['nombre']
              //  ]);
              return redirect()->to('perfil');
            
            }else{
                $session->setFlashdata('msg', 'Password incorrecta.');
                return redirect()->to('perfil');
            }
        }else{
            $session->setFlashdata('msg', 'usuario no existe.');
            return redirect()->to('conectar');
        }
    }
}