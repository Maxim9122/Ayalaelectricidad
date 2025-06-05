<?php
namespace App\Models;
use CodeIgniter\Model;
class Clientes_model extends Model
{
	protected $table = 'cliente';
    protected $primaryKey = 'id_cliente';
    protected $allowedFields = ['nombre','telefono','direccion','cuil'];

    public function getClientes(){

        return $this->findAll();

    }

    public function getCliente($id){

    	return $this->where('id_cliente',$id)->first($id);
    }

}