<?php 
namespace App\Models;
use CodeIgniter\Model;
class FormModel extends Model
{
	
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'email', 'phone','mensaje','estado','visitante'];

    public function getConsulta($id){

    	return $this->where('id',$id)->first($id);
    }

    public function updateConsulta($id,$datos){

    	return $this->update($id,$datos);
    }

    public function getConsultas($estado){

    	return $this->where('estado',$estado)->findAll();
    }

}