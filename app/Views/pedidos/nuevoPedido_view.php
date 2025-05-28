<br>
<div class="nuevoTurno">
  <div style="width: 100%;" >
    <div>
      <h2>Nuevo Cliente/Pedido<h2>
    </div>
  
 <?php $validation = \Config\Services::validation(); ?>
     <form method="post" action="<?php echo base_url('RegistrarTurno') ?>">
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
   <input name="nombre_cliente" type="text" placeholder="Nombre del Cliente" required>
     <!-- Error -->
        <?php if($validation->getError('nombre')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('nombre'); ?>
            </div>
        <?php }?>
  </div>

  <div>
  <label for="exampleFormControlInput1" class="form-label">Telefono</label>
   <input  type="text" name="telefono" class="form-control" placeholder="Telefono" required>
   <!-- Error -->
        <?php if($validation->getError('telefono')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('telefono'); ?>
            </div>
        <?php }?>
  </div>

  <div>
  <label for="exampleFormControlTextarea1" class="form-label">Foto (Opcional)</label>
  <input name="foto" type="file">
  <?php if($validation->getError('imagen')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('imagen'); ?>
            </div>
        <?php }?>
  </div>

  <div>
  <label for="tipo_servicio">Tipo Servicio:</label>
  <select name="tipo_servicio">
    <option value="1">Seleccione un servicio</option> <!-- Sin servicio por defecto -->
    <?php foreach($servicios as $servicio): ?>
      <option value="<?= $servicio['id_servi']; ?>"><?= $servicio['descripcion']; ?> - $<?= $servicio['precio']; ?></option>
    <?php endforeach; ?>
  </select>
  <!-- Error -->
  <?php if($validation->getError('servicio')) {?>
    <div class='alert alert-danger mt-2'>
      <?= $validation->getError('servicio'); ?>
    </div>
  <?php } ?>
  
</div>

  <div class="nuevoTurno">
  <label for="fecha">Fecha:</label>
  <input type="date" class="form-control" id="fecha" name="fecha_turno">
  
  <label for="hora">Hora:</label>
  <input type="time" class="form-control" id="hora" name="hora_turno">

  <!-- Error -->
  <?php if($validation->getError('fecha') || $validation->getError('hora')) { ?>
      <div class='alert alert-danger mt-2'>
          <?= $validation->getError('fecha'); ?>
          <?= $validation->getError('hora'); ?>
      </div>
  <?php } ?>
</div>


    <br>
  <div class="button-container">
  <a href="<?php echo base_url('turnos'); ?>" class="button2" type="reset">Cancelar</a>
  <button type="submit" class="button2">Registrar</button>
  </div>

      <br>
 </div>
</form>
</div>
</div>
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