<br>
<div>
  <div class="comprados nuevoTurno" style="width: 50%;">
    <div>
      <h2 style="color:black; font-weight:900;">Editar Cliente</h2>
    </div>
    <br>
 <?php $validation = \Config\Services::validation(); ?>
     <form method="post" action="<?php echo base_url('/edicionOk') ?>" enctype="multipart/form-data">
      <?=csrf_field();?>
      <?php if(!empty (session()->getFlashdata('fail'))):?>
      <div class="alert alert-danger"><?=session()->getFlashdata('fail');?></div>
 <?php endif?>
           <?php if(!empty (session()->getFlashdata('success'))):?>
      <div class="alert alert-danger"><?=session()->getFlashdata('success');?></div>
  <?php endif?>     
<div media="(max-width:768px)">
  <div>
   <label for="exampleFormControlInput1" style="color:black;">Nombre</label>
   <input name="nombre" type="text"  placeholder="nombre" required
   minlength="3" maxlength="20" 
   value="<?php echo $data['nombre']?>">
     <!-- Error -->
        <?php if($validation->getError('nombre')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('nombre'); ?>
            </div>
        <?php }?>
  </div>
  
  <div>
       <label for="exampleFormControlInput1" style="color:black;">Teléfono</label>
   <input name="telefono"  type="text"  placeholder="Telefono" required
   minlength="1" maxlength="10"
    
    oninput="this.value = this.value.replace(/[^0-9]/g, '')" value="<?php echo $data['telefono']?>" >
    <!-- Error -->
        <?php if($validation->getError('telefono')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('telefono'); ?>
            </div>
        <?php }?>
  </div>  

  <div>
       <label for="exampleFormControlInput1" style="color:black;">Dirección</label>
   <input name="direc"  type="text"  placeholder="Dirección"
   minlength="1" maxlength="50" value="<?php echo $data['direccion']?>" >
    <!-- Error -->
        <?php if($validation->getError('direc')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('direc'); ?>
            </div>
        <?php }?>
  </div>  

  <div>
       <label for="exampleFormControlInput1" style="color:black;">Cuil</label>
       <input name="cuil" type="text" placeholder="Cuil" required
        minlength="1" maxlength="11"
        pattern="0|[0-9]{11}"
        oninput="this.value = this.value.replace(/[^0-9]/g, '')" 
        value="<?php echo $data['cuil']; ?>">

    <!-- Error -->
    <?php if(session()->getFlashdata('msgEr')) { ?>
    <div class='alert alert-danger mt-2'>
        <?= session()->getFlashdata('msgEr'); ?>
    </div>
  <?php } ?>


  </div>  

    

  <input type="hidden" name="id" value="<?php echo $data['id_cliente']?>">
  <br>
  <br>
  <div class="button-container">
            <a type="reset" href="<?php echo base_url('/clientes');?>" class="btn">
            Volver</a>
           <button type="submit" value="Editar" class="btn">
           Modificar</button>
           
      <br><br>
  </div>
 </div>
</form>
</div>
</div>