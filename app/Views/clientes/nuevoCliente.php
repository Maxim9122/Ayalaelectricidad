<br>
<body>
<div class="nuevoTurno">
  <div style="" >
  
    <div>
    <h2>Nuevo Cliente<h2>
    </div>
  
 <?php $validation = \Config\Services::validation(); ?>
     <form method="post" action="<?php echo base_url('validar') ?>">
      <?=csrf_field();?>
      <?php if(!empty (session()->getFlashdata('fail'))):?>
      <div class="alert alert-danger"><?=session()->getFlashdata('fail');?></div>
 <?php endif?>
           <?php if(!empty (session()->getFlashdata('success'))):?>
      <div class="alert alert-danger"><?=session()->getFlashdata('success');?></div>
  <?php endif?>     
<div media="(max-width:768px)">

  <div>
   <label for="exampleFormControlInput1">Nombre</label>
   <input name="nombre" type="text" placeholder="Nombre del Cliente o Apodo" required
   minlength="3" maxlength="20" >
     <!-- Error -->
        <?php if($validation->getError('nombre')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('nombre'); ?>
            </div>
        <?php }?>
  </div>

  <div>
  <label for="exampleFormControlInput1" class="form-label">Telefono</label>
   <input  type="text" name="telefono" class="form-control" placeholder="Telefono" required
   minlength="1" maxlength="10"
    
    oninput="this.value = this.value.replace(/[^0-9]/g, '')">
   <!-- Error -->
        <?php if($validation->getError('telefono')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('telefono'); ?>
            </div>
        <?php }?>
  </div>

  <div>
  <label for="exampleFormControlInput1" class="form-label">Cuil</label>
  <input name="cuil" type="text" placeholder="Cuil" required
        minlength="1" maxlength="11"
        pattern="0|[0-9]{11}"
        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
   <!-- Error -->
   <?php if(session()->getFlashdata('msgEr')) { ?>
    <div class='alert alert-danger mt-2'>
        <?= session()->getFlashdata('msgEr'); ?>
    </div>
  <?php } ?>
  </div>

    <br>
  <div class="button-container">
  <a href="<?php echo base_url('clientes'); ?>" class="button2" type="reset">Cancelar</a>
  <button type="submit" class="button2">Registrar</button>
  </div>

      <br>
 </div>
</form>
</div>
</div>
</body>
<script>
  // Crear un objeto Date en UTC
  const today = new Date();

  // Ajustar la hora a la zona horaria de Argentina (UTC-3)
  const options = { timeZone: 'America/Argentina/Buenos_Aires', hour12: false };
  const formatter = new Intl.DateTimeFormat('es-AR', {
      ...options,
      year: 'numeric', month: '2-digit', day: '2-digit'
  });

  const formattedDate = formatter.format(today).split('/').reverse().join('-'); // Formato YYYY-MM-DD

  // Formatear la hora en formato HH:MM
  const formattedTime = new Intl.DateTimeFormat('es-AR', {
      ...options,
      hour: '2-digit',
      minute: '2-digit'
  }).format(today);

  // Establecer la fecha y hora actuales en los campos correspondientes
  document.getElementById('fecha').value = formattedDate;
  document.getElementById('hora').value = formattedTime;
</script>
<br>