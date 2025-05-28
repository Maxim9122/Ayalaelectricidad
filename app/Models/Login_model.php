<?php 
namespace App\Models;  
use CodeIgniter\Model;
  
class Login_model extends Model{
	
    protected $table = 'usuarios';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'nombre',
        'apellido',
        'usuario',
        'telefono',
        'direccion',
        'email',
        'pass',
        'perfil_id',
        'baja',
        'created_at'
    ];
}