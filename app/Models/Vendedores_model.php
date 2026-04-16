<?php
namespace App\Models;

use CodeIgniter\Model;

class Vendedores_model extends Model
{
    protected $table = 'vendedores';       // Tabla
    protected $primaryKey = 'id_vendedor'; // Clave primaria

    protected $allowedFields = [
        'id_vendedor',
        'nombre',
        'apellido'
    ];
}
