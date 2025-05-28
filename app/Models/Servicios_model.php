<?php
namespace App\Models;
use CodeIgniter\Model;
class Servicios_model extends Model
{
	protected $table = 'servicios';
    protected $primaryKey = 'id_servi';
    protected $allowedFields = ['descripcion','precio'];

    public function getServicio(){

        return $this->findAll();

    }

    public function getServi($id){

    	return $this->where('id_servi',$id)->first($id);
    }

}