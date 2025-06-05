<?php
namespace App\Models;
use CodeIgniter\Model;
class Productos_model extends Model
{
	protected $table = 'productos';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nombre','descripcion', 'imagen' ,'categoria_id', 'precio', 'precio_vta', 'stock','stock_min','eliminado', 'codigo_barra'];

    // Agregamos método para paginar con condición
   public function getProductosPaginados($eliminado = 'NO', $busqueda = null, $page = 1)
    {
    // Si no hay búsqueda, no devolver nada
    if (empty($busqueda)) {
        return [];
    }

    $builder = $this->where('eliminado', $eliminado)
                    ->like('nombre', $busqueda);

    return $builder->paginate(5, 'default', $page);
    }


    public function getPager()
    {
        return $this->pager;
    }

   
    // Para búsqueda con paginación TODOS ordenados por nombre de A a Z
    public function getProductosPaginadosTodos($eliminado = 'NO', $busqueda = null, $page = 1)
    {
        $builder = $this->where('eliminado', $eliminado)
                        ->orderBy('nombre', 'ASC');

        if (!empty($busqueda)) {
            $builder->like('nombre', $busqueda);
        }

        return $builder->paginate(10, 'default', $page);
    }


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