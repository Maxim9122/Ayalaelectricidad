<?php
namespace App\Models;
use CodeIgniter\Model;
class Usuarios_model extends Model
{
	protected $table = 'usuarios';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nombre', 'apellido','telefono', 'direccion', 'email', 'pass','perfil_id','baja'];

    public function getUsuario($id){

    	return $this->where('id',$id)->first($id);
    }

    public function updateDatos($id,$datos){

    	return $this->update($id,$datos);
    }

    public function getUsBaja($baja){

    	return $this->where('baja',$baja)->findAll();
    }
    /**
     * Obtener sesiones con datos del usuario.
     */
    public function getSesionesConUsuarios()
    {
        return $this->db->table('sesiones')
            ->select('sesiones.id_sesion, sesiones.inicio_sesion, sesiones.fin_sesion, sesiones.estado, usuarios.nombre, usuarios.email')
            ->join('usuarios', 'usuarios.id = sesiones.id_usuario')
            ->get()
            ->getResult();
    }
    //funcion para guarda la sesion cuando inicia el usuario
        public function guardar_sesion($registro_sesion){
            $db = \Config\Database::connect();
            $builder = $db->table('sesiones');
            $builder->insert($registro_sesion);
    }

}
