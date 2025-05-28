<?php
namespace App\Models;
use CodeIgniter\Model;
class Pedidos_model extends Model
{
	protected $table = 'pedidos';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id_cliente','id_usuario', 'id_servi' ,'fecha_registro','fecha_turno','hora_turno','estado'];

    public function getUsuario($id){

    	return $this->where('id',$id)->first($id);
    }

    public function actualizar_pedido($id_pedido, $data) {
        return $this->db->table('pedidos') // Indicar la tabla
                        ->where('id', $id_pedido) // Condición
                        ->update($data); // Actualización
    }

     // Obtiene turnos con las relaciones necesarias
     public function obtenerPedidos($filtros = [])
     {
         $builder = $this->db->table($this->table . ' t');
         $builder->select('
             t.id, 
             t.id_usuario, 
             t.hora_turno, 
             t.estado, 
             t.fecha_turno, 
             t.fecha_registro, 
             t.id_servi,
             c.nombre AS cliente_nombre, 
             c.telefono AS cliente_telefono,
             u.nombre AS usuario_nombre,
             s.descripcion,
             s.precio
         ');
         $builder->join('cliente c', 'c.id_cliente = t.id_cliente');
         $builder->join('usuarios u', 'u.id = t.id_usuario');
         $builder->join('servicios s', 's.id_servi = t.id_servi');
 
         // Aplicar filtros si existen
         if (!empty($filtros['estado'])) {
             $builder->where('t.estado', $filtros['estado']);
         }
         if (!empty($filtros['fecha_turno'])) {
             $builder->where('t.fecha_turno', $filtros['fecha_turno']);
         }
         if (!empty($filtros['fecha_desde'])) {
             $builder->where('STR_TO_DATE(t.fecha_turno, "%d-%m-%Y") >=', date('Y-m-d', strtotime($filtros['fecha_desde'])));
         }
         if (!empty($filtros['fecha_hasta'])) {
             $builder->where('STR_TO_DATE(t.fecha_turno, "%d-%m-%Y") <=', date('Y-m-d', strtotime($filtros['fecha_hasta'])));
         }
         if (!empty($filtros['id_usuario'])) {
             $builder->where('t.id_usuario', $filtros['id_usuario']);
         }
 
         return $builder->get()->getResultArray();
     }

      // Actualiza el turno
    public function actualizarPedido($id_turno, $data)
    {
        return $this->update($id_turno, $data);
    }

    // Cambia el estado del turno
    public function cambiarEstado($id_turno, $estado)
    {
        return $this->update($id_turno, ['estado' => $estado]);
    }

    public function obtenerPedidosCompletados()
    {
        return $this->select('
                pedidos.id, 
                pedidos.id_usuario, 
                pedidos.hora_turno, 
                pedidos.estado, 
                pedidos.fecha_registro,
                pedidos.fecha_turno, 
                pedidos.id_servi,
                cliente.nombre AS cliente_nombre, 
                cliente.telefono AS cliente_telefono,
                usuarios.nombre AS usuario_nombre,
                servicios.descripcion,
                servicios.precio
            ')
            ->join('cliente', 'cliente.id_cliente = pedidos.id_cliente')
            ->join('usuarios', 'usuarios.id = pedidos.id_usuario')
            ->join('servicios', 'servicios.id_servi = pedidos.id_servi')
            ->where('pedidos.estado', 'Listo')
            ->orderBy('pedidos.fecha_turno', 'DESC')
            ->orderBy('pedidos.hora_turno', 'DESC ')
            ->findAll();
    }

    //Elimina de forma fisica el turno porque el Cliente del Soft asi lo quiere.
    public function eliminarPedido($id_pedido)
    {
    return $this->db->table('pedidos')->delete(['id' => $id_pedido]);
    }

}