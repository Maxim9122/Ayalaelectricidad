<?php
namespace App\Models;
use CodeIgniter\Model;
class Sesion_model extends Model
{
	protected $table = 'sesiones'; // Nombre de la tabla
    protected $primaryKey = 'id_sesion'; // Clave primaria
    protected $allowedFields = ['id_sesion', 'id_usuario','inicio_sesion', 'fin_sesion', 'estado']; // Campos que se pueden insertar
    /**
     * Obtener sesiones con datos del usuario.
     */
    public function getSesionesConUsuarios()
{
    $query = $this->db->table('sesiones')
        ->select('sesiones.id_sesion, 
                  sesiones.inicio_sesion,
                  sesiones.fin_sesion, 
                  sesiones.estado, 
                  usuarios.nombre, 
                  usuarios.apellido')
        ->join('usuarios', 'usuarios.id = sesiones.id_usuario')
        ->orderBy('sesiones.id_sesion', 'DESC') // Ordena por id_sesion de mayor a menor
        ->get();
    
    //echo $this->db->getLastQuery(); exit; // Esto imprimirá la consulta SQL
    return $query->getResult();
}



    public function getSesion($id){

    	return $this->where('id_sesion',$id)->first($id);
    }

    public function actualizar_sesion($id_sesion, $data) {
        return $this->db->table('sesiones') // Indicar la tabla
        ->where('id_sesion', $id_sesion) // Condición si son iguales actualiza
        ->update($data); // Actualización
    }

    //buscador y filtro para sesion
    public function buscarYFiltrar($filter)
    {
        // Usar el Query Builder del modelo
    return $this->db->table('sesiones')
    ->select('sesiones.id_sesion, 
                sesiones.inicio_sesion, 
                sesiones.fin_sesion, 
                sesiones.estado, 
                usuarios.nombre, 
                usuarios.apellido')
    ->join('usuarios', 'usuarios.id = sesiones.id_usuario', 'left') // Unir con la tabla usuarios
    ->where('sesiones.estado', $filter) // Filtrar por estado
    ->orderBy('sesiones.id_sesion', 'DESC') // Ordena por id_sesion de mayor a menor
    ->get()
    ->getResult();
}
    }
