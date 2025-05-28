<?php
namespace App\Models;
use CodeIgniter\Model;
class VentaDetalle_model extends Model
{
	protected $table = 'ventas_detalle';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id','venta_id','producto_id', 'cantidad', 'precio', 'total'];

    public function getVtaDetalle($id){

    	return $this->where('id',$id)->first($id);
    }

}