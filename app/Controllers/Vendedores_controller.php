<?php

namespace App\Controllers;

use App\Models\Vendedores_model;

class Vendedores_controller extends BaseController
{
    public function __construct()
    {
        helper(['form', 'url']);
    }

    // ---------------------------------------------------------
    // LISTA DE VENDEDORES
    // ---------------------------------------------------------
    public function listar_vendedores()
    {
        $session = session();

        if (!$session->has('id')) {
            return redirect()->to(base_url('login'));
        }

        if ($session->get('perfil_id') == 2) {
            return redirect()->to(base_url('catalogo'));
        }

        $vendModel = new Vendedores_model();
        $data['vendedores'] = $vendModel->findAll();

        $header['titulo'] = 'Lista de Vendedores';

        echo view('navbar/navbar');
        echo view('header/header', $header);
        echo view('admin/lista_vendedores_view', $data);
        echo view('footer/footer');
    }

    // ---------------------------------------------------------
    // FORMULARIO NUEVO
    // ---------------------------------------------------------
    public function vendedor_nuevo()
    {
        $session = session();
        if (!$session->has('id')) return redirect()->to(base_url('login'));
        if ($session->get('perfil_id') == 2) return redirect()->to(base_url('catalogo'));

        $header['titulo'] = 'Nuevo Vendedor';

        echo view('navbar/navbar');
        echo view('header/header', $header);
        echo view('admin/nuevo_vendedor', ['vendedor' => null]); // <-- vacio
        echo view('footer/footer');
    }

    // ---------------------------------------------------------
    // CREAR VENDEDOR
    // ---------------------------------------------------------
    public function crearVend()
    {
        $validation = $this->validate([
            'nombre'  => 'required|min_length[3]|max_length[20]',
            'apellido'=> 'required|min_length[3]|max_length[20]'
        ]);

        if (!$validation) {
            return redirect()->back()
                ->with('fail', 'Complete correctamente los campos.')
                ->withInput();
        }

        $model = new Vendedores_model();

        $model->save([
            'nombre'   => $this->request->getPost('nombre'),
            'apellido' => $this->request->getPost('apellido')
        ]);

        return redirect()
            ->to(base_url('vendedores'))
            ->with('success', 'Vendedor creado correctamente!');
    }

    // ---------------------------------------------------------
    // EDITAR – CARGAR DATOS EN EL FORM
    // ---------------------------------------------------------
    public function editarVend($id)
    {
        $session = session();
        if (!$session->has('id')) return redirect()->to(base_url('login'));
        if ($session->get('perfil_id') == 2) return redirect()->to(base_url('catalogo'));

        $model = new Vendedores_model();
        $vendedor = $model->find($id);

        if (!$vendedor) {
            return redirect()->to(base_url('vendedores'))
                ->with('fail', 'Vendedor no encontrado.');
        }

        $header['titulo'] = 'Editar Vendedor';

        echo view('navbar/navbar');
        echo view('header/header', $header);
        echo view('admin/nuevo_vendedor', ['vendedor' => $vendedor]);
        echo view('footer/footer');
    }

    // ---------------------------------------------------------
    // ACTUALIZAR VENDEDOR
    // ---------------------------------------------------------
    public function actualizarVend($id = null)
{
    // Chequeo de sesión (igual que en las demás funciones)
    $session = session();
    if (!$session->has('id')) {
        return redirect()->to(base_url('login'));
    }
    if ($session->get('perfil_id') == 2) {
        return redirect()->to(base_url('catalogo'));
    }

    // Validación
    $validation = $this->validate([
        'nombre'   => 'required|min_length[3]|max_length[20]',
        'apellido' => 'required|min_length[3]|max_length[20]'
    ]);

    if (!$validation) {
        return redirect()->back()
            ->with('fail', 'Complete correctamente los campos.')
            ->withInput();
    }

    // Model
    $model = new Vendedores_model();

    // Verificar que el vendedor exista
    $v = $model->find($id);
    if (!$v) {
        return redirect()->to(base_url('vendedores'))
            ->with('fail', 'Vendedor no encontrado.');
    }

    // Datos a actualizar
    $data = [
        'nombre'   => $this->request->getPost('nombre'),
        'apellido' => $this->request->getPost('apellido'),
    ];

    // Usamos update() pasando el id
    $model->update($id, $data);

    return redirect()->to(base_url('vendedores'))
                     ->with('success', 'Vendedor actualizado correctamente!');
}

    // ---------------------------------------------------------
    // ELIMINAR VENDEDOR
    // ---------------------------------------------------------
    public function eliminarVend($id)
    {
        $model = new Vendedores_model();

        $model->delete($id);

        return redirect()
            ->to(base_url('vendedores'))
            ->with('success', 'Vendedor eliminado correctamente!');
    }
}
