<?php $session = session();
          $nombre= $session->get('nombre');
          $perfil=$session->get('perfil_id');
          $id=$session->get('id');?>  
 <?php if($perfil == 1){  ?>
   <div class="nuevoTurno">   
      <h2>Editar Categoria de Producto</h2>
 <?php $validation = \Config\Services::validation(); ?>
     <form method="post" enctype="multipart/form-data" action="<?php echo base_url('/enviarEdicionCateg') ?>">
      <?=csrf_field();?>
      <?php if(!empty (session()->getFlashdata('fail'))):?>
      <div class="alert alert-danger"><?=session()->getFlashdata('fail');?></div>
 <?php endif?>
           <?php if(!empty (session()->getFlashdata('success'))):?>
      <div class="alert alert-danger"><?=session()->getFlashdata('success');?></div>
  <?php endif?>     
<div class ="card-body" media="(max-width:768px)">
<div class="mb-3">
   <label for="exampleFormControlTextarea1" class="form-label">Id</label>
    <input readonly required type="text" name="categoria_id" class="form-control" value="<?php echo $data['categoria_id'] ?>">
    <!-- Error -->
        <?php if($validation->getError('categoria_id')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('categoria_id'); ?>
            </div>
        <?php }?>
    </div>
  <div class="mb-3">
   <label for="exampleFormControlTextarea1" class="form-label">Descripción</label>
    <input required type="text" name="descripcion" class="form-control" value="<?php echo $data['descripcion'] ?>">
    <!-- Error -->
        <?php if($validation->getError('descripcion')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('descripcion'); ?>
            </div>
        <?php }?>
    </div>
    
<br>
  <div class="mb-3">
   <label for="exampleFormControlInput1" class="form-label">Eliminado</label>
   <input required name="eliminado" type="text" readonly="true" class="form-control" value="<?php echo $data['eliminado']?>">
   <!-- Error -->
        <?php if($validation->getError('eliminado')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('eliminado'); ?>
            </div>
        <?php }?>
  </div>

  <input type="hidden" name="id" value="<?php echo $data['categoria_id']?>">

  <br>
  <div class="button-container">
           
            <a type="reset" href="<?php echo base_url('ListaCategorias');?>" class="btn">Cancelar</a>
            <input type="submit" value="Modificar" class="btn">
            <br><br>
        </div>
 </div>
</form>

<?php }else{ ?>
  <h2>Su perfil no tiene acceso a esta parte,
    Vuelva a alguna seccion de Empleado!
  </h2>
<?php }?>
        </div>
        <br>
        