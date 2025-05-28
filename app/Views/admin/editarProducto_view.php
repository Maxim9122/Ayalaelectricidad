<?php $session = session();
$nombre = $session->get('nombre');
$perfil = $session->get('perfil_id');
$id = $session->get('id'); ?>  
<?php if ($perfil == 1) { ?>
  <br>
<div class="nuevoTurno">   
    <h2>Editar Producto</h2>
    <?php $validation = \Config\Services::validation(); 
    $validation = \Config\Services::validation();
    //echo '<pre>';
    //print_r($validation->getErrors());
    //echo '</pre>';
    //exit;
    ?>
    <form method="post" enctype="multipart/form-data" action="<?php echo base_url('/enviarEdicionProd') ?>">
        <?= csrf_field(); ?>
        <?php if (!empty(session()->getFlashdata('fail'))) : ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('fail'); ?></div>
        <?php endif ?>
        <?php if (!empty(session()->getFlashdata('success'))) : ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success'); ?></div>
        <?php endif ?>     
        <div class="card-body" media="(max-width:768px)">
            <div class="form-group-container">
                <div class="form-group">
                    <label>Codigo de Barra</label>
                    <input name="codigo_barra" type="text" pattern="[0-9]+" required value="<?php echo $data['codigo_barra'] ?>" maxlength="15" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    <?php if ($validation->getError('codigo_barra')) { ?>
                        <div class='alert alert-danger mt-2'><?= $validation->getError('codigo_barra'); ?></div>
                    <?php } ?>
                </div>
                <div class="form-group">
                    <label>Nombre</label>
                    <input name="nombre" type="text" class="form-control" placeholder="nombre" value="<?php echo $data['nombre'] ?>" required minlength="5" maxlength="50">
                    <?php if ($validation->getError('nombre')) { ?>
                        <div class='alert alert-danger mt-2'><?= $validation->getError('nombre'); ?></div>
                    <?php } ?>
                </div>
                <div class="form-group">
                    <label>Descripción</label>
                    <input type="text" name="descripcion" class="form-control" value="<?php echo $data['descripcion'] ?>">
                    <?php if ($validation->getError('descripcion')) { ?>
                        <div class='alert alert-danger mt-2'><?= $validation->getError('descripcion'); ?></div>
                    <?php } ?>
                </div>
                <div class="form-group">
                    <label>Precio Costo</label>
                    <input required type="text" name="precio" class="form-control"
                        value="<?php echo $data['precio'] ?>" step="0.01" min="0" maxlength="20"
                        oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">
                    <?php if ($validation->getError('precio')) { ?>
                        <div class='alert alert-danger mt-2'><?= $validation->getError('precio'); ?></div>
                    <?php } ?>
                </div>

                <div class="form-group">
                    <label>Precio Venta</label>
                    <input required name="precio_vta" type="text" class="form-control"
                        value="<?php echo $data['precio_vta'] ?>" step="0.01" min="0" maxlength="20"
                        oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">
                    <?php if ($validation->getError('precio_vta')) { ?>
                        <div class='alert alert-danger mt-2'><?= $validation->getError('precio_vta'); ?></div>
                    <?php } ?>
                </div>

                <div class="form-group">
                    <label>Stock</label>
                    <input required name="stock" type="text" class="form-control" value="<?php echo $data['stock'] ?>" maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    <?php if ($validation->getError('stock')) { ?>
                        <div class='alert alert-danger mt-2'><?= $validation->getError('stock'); ?></div>
                    <?php } ?>
                </div>
                <div class="form-group">
                    <label>Stock Minimo</label>
                    <input name="stock_min" type="text" class="form-control" value="<?php echo $data['stock_min'] ?>" maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    <?php if ($validation->getError('stock_min')) { ?>
                        <div class='alert alert-danger mt-2'><?= $validation->getError('stock_min'); ?></div>
                    <?php } ?>
                </div>
                <div class="form-group">
                    <label>Categoria</label>
                    <select required name="categoria_id" class="form-control">
                        <option value="">Seleccione Categoria</option>
                        <?php foreach ($categorias as $categoria) : ?>
                            <option value="<?= $categoria['categoria_id']; ?>" <?= ($categoria['categoria_id'] == $data['categoria_id']) ? 'selected' : ''; ?>>
                                <?= $categoria['descripcion']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>   
                    <?php if ($validation->getError('categoria_id')) { ?>
                        <div class='alert alert-danger mt-2'><?= $validation->getError('categoria_id'); ?></div>
                    <?php } ?>
                </div>
            </div>
            <div class="form-group-container">
      <!-- Otros campos ya existentes -->
    
          <div class="form-group">
              <label>Eliminado</label>
              <input name="eliminado" type="text" readonly="true" class="form-control" value="<?php echo $data['eliminado'] ?>">
              <?php if ($validation->getError('eliminado')) { ?>
                  <div class='alert alert-danger mt-2'><?= $validation->getError('eliminado'); ?></div>
              <?php } ?>
          </div>

          <div class="form-group">
              <label>Imagen Actual:</label>
              <img class="imagenForm" src="<?php echo base_url('assets/uploads/'.$data['imagen']); ?>">
              <input name="imagen" type="file" class="form-control">
              <?php if ($validation->getError('imagen')) { ?>
                  <div class='alert alert-danger mt-2'><?= $validation->getError('imagen'); ?></div>
              <?php } ?>
          </div>
         </div>

            <input type="hidden" name="id" value="<?php echo $data['id'] ?>">
            <div class="button-container">
                <a type="reset" href="<?php echo base_url('Lista_Productos'); ?>" class="btn">Cancelar</a>
                <input type="submit" value="Modificar" class="btn">
            </div>
        </div>
    </form>
<?php } else { ?>
    <h2>Su perfil no tiene acceso a esta parte, vuelva a alguna sección de Empleado.</h2>
<?php } ?>
</div>
<br>
<style>
    .form-group-container {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }
    .form-group {
        flex: 1 1 calc(50% - 15px);
    }
    @media (max-width: 768px) {
        .form-group {
            flex: 1 1 100%;
        }
    }
</style>