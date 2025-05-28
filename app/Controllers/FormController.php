<?php 
namespace App\Controllers;
use App\Models\FormModel;
use CodeIgniter\Controller;
use App\Models\Usuarios_model;

class FormController extends Controller
{
    public function create() {

        $dato['titulo']='Contacto';
        echo view('header/header',$dato);
        echo view('navbar/navbar');
        echo view('contacto/contact_form');
        echo view('footer/footer');
    }
 
    public function formValidation() {
        helper(['form', 'url']);
        
        $input = $this->validate([
            'name' => 'required|min_length[3]',
            'email' => 'required|valid_email',
            'phone' => 'required|numeric|max_length[10]',
            'mensaje' =>'required'
        ]);
        $formModel = new FormModel();
 
        if (!$input) {
            $dato['titulo']='Contacto';
            echo view('header/header',$dato);
           echo view('navbar/navbar');
            echo view('contacto/contacto',['validation' => $this->validator]);
            echo view('footer/footer');
        } else {
            $formModel->save([
                'name' => $this->request->getVar('name'),
                'email'  => $this->request->getVar('email'),
                'phone'  => $this->request->getVar('phone'),
                'mensaje' => $this->request->getVar('mensaje'),
                'visitante' => $this->request->getVar('visitante'),
                'estado' => 'Pendiente',
            ]);          
            session()->setFlashdata('msg','Mensaje Enviado con éxito!');
            return $this->response->redirect(site_url(''));
        }
    }
}