<?php
namespace App\Models;
use CodeIgniter\Model;

class Defectuosos_model extends Model
{
    protected $table = 'defectuosos';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id', 'nombre_def', 'precio_def', 'cantidad_desc', 'nuevo_stock' , 'nombre_us', 'fecha_desc', 'motivo_desc'];

    /**
     * Guarda un nuevo registro en la tabla defectuosos.
     *
     * @param array $data Datos a guardar.
     * @return int|false ID del registro insertado o false en caso de error.
     */
    public function guardarDefectuoso($data)
    {
        // Validar que los datos no estén vacíos
        if (empty($data)) {
            return false;
        }

        // Insertar el registro en la base de datos
        return $this->insert($data);
    }

    //Devuelve el historial
    public function Traer_Historial()
    {
        return $this->findAll();
    }

}