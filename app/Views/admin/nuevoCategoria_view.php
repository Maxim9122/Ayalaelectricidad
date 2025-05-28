<br>
<?php $session = session();
          $nombre= $session->get('nombre');
          $perfil=$session->get('perfil_id');
          $id=$session->get('id');?>  
 <?php if($perfil == 1){  ?>
<div>
  <div class="nuevoProd" >
    <div class= "">
      <h2>Registrar Nueva Categoria</h2>
    </div>
  <br>
 <?php $validation = \Config\Services::validation(); ?>
     <form method="post" enctype="multipart/form-data" action="<?php echo base_url('categoriaValidation') ?>">
      <?=csrf_field();?>
      <?php if(!empty (session()->getFlashdata('fail'))):?>
      <div class="alert alert-danger"><?=session()->getFlashdata('fail');?></div>
 <?php endif?>
           <?php if(!empty (session()->getFlashdata('success'))):?>
      <div class="alert alert-danger"><?=session()->getFlashdata('success');?></div>
  <?php endif?>     

  <br>
  <div class="mb-2">
  <label for="exampleFormControlTextarea1" class="form-label" style="color:white"><h3>Descripcion</h3></label>

   <input name="descripcion" type="text" required="required" maxlength="30" class="form-control" style="width: 100%; height: 50px; font-size: 16px;" autofocus>
    <!-- Error -->
        <?php if($validation->getError('descripcion')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('descripcion'); ?>
            </div>
        <?php }?>
</div>

  <br>
  <a href="<?php echo base_url('ListaCategorias');?>" class="btn">Cancelar</a>   
  <button type="submit" class="btn">Guardar</button>
   
  <br>
 </div>
</form>
<?php }else{ ?>
  <h2>Su perfil no tiene acceso a esta parte,
    Vuelva a alguna seccion de Empleado!
  </h2>
<?php }?>
</div>
</div>
<br>