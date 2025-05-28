<?php
namespace App\Models;
use CodeIgniter\Model;
class Productos_model extends Model
{
	protected $table = 'productos';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nombre','descripcion', 'imagen' ,'categoria_id', 'precio', 'precio_vta', 'stock','stock_min','eliminado', 'codigo_barra'];

    public function getProdBaja($eliminado){

    	return $this->where('eliminado',$eliminado)->findAll();
    }

    public function getTipo($tipo){

    	return $this->where('categoria_id',$tipo)->findAll();
    }

    public function updateDatosProd($id,$datos){

    	return $this->update($id,$datos);
    }

    public function getProducto($id){

    	return $this->where('id',$id)->first($id);
    }

    public function getPorStockBajo(){

    	return $this->where('stock <= stock_min')->findAll();
    }

}