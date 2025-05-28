<br>
<div class="nuevoTurno">
  <div style="width: 100%;">
    <div>
      <h2>Nuevo Usuario / Vendedor</h2>
    </div>

    <?php $validation = \Config\Services::validation(); ?>
    <form method="post" action="<?php echo base_url('crearUs') ?>">
      <?= csrf_field(); ?>

      <?php if (!empty(session()->getFlashdata('fail'))) : ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('fail'); ?></div>
      <?php endif ?>

      <?php if (!empty(session()->getFlashdata('success'))) : ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success'); ?></div>
      <?php endif ?>

      <div class="form-container">
        <div class="input-group">
          <label>Nombre</label>
          <input name="nombre" type="text" class="form-control" placeholder="Nombre" required minlength="3" maxlength="20">
        </div>

        <div class="input-group">
          <label>Apellido</label>
          <input type="text" name="apellido" class="form-control" placeholder="Apellido" required minlength="3" maxlength="20">
        </div>

        <div class="input-group">
          <label>Email</label>
          <input name="email" type="email" class="form-control" placeholder="correo@algo.com" required maxlength="50">
        </div>

        <div class="input-group">
          <label>Teléfono</label>
          <input type="text" name="telefono" class="form-control" placeholder="Teléfono" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
        </div>

        <div class="input-group">
          <label>Dirección</label>
          <input type="text" name="direccion" class="form-control" placeholder="Dirección" maxlength="100">
        </div>

        <div class="input-group">
          <label>Contraseña</label>
          <input name="pass" type="password" class="form-control" placeholder="Password" required minlength="3" maxlength="20">
        </div>

        <div class="input-group full-width">
          <label>Perfil</label>
          <select name="perfil_id" class="form-control">
            <option>Seleccione Perfil</option>
            <option value="2">Vendedor</option>
            <option value="1">Admin</option>
            <option value="3">Cajero</option>
          </select>
        </div>
      </div>

      <div class="button-container">
        <a type="reset" href="<?php echo base_url('usuarios-list'); ?>" class="button2">Cancelar</a>
        <button type="submit" class="button2">Crear US</button>
      </div>

    </form>
  </div>
</div>

<style>
  /* Estilos generales */
  .form-container {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    justify-content: space-between;
  }

  .input-group {
    width: 48%; /* Dos columnas */
    display: flex;
    flex-direction: column;
  }

  .input-group.full-width {
    width: 100%; /* Perfil en una fila completa */
  }

  .form-control {
    padding: 8px;
    font-size: 14px;
    border: 1px solid #ccc;
    border-radius: 5px;
  }

  .button-container {
    margin-top: 20px;
    display: flex;
    gap: 10px;
  }

  .button2 {
    padding: 10px 20px;
    font-size: 14px;
    background-color: #007bff;
    color: white;
    border: none;
    cursor: pointer;
    text-decoration: none;
    text-align: center;
    border-radius: 5px;
  }

  .button2:hover {
    background-color: #0056b3;
  }

  /* Diseño responsive */
  @media (max-width: 768px) {
    .input-group {
      width: 100%; /* En móviles, una sola columna */
    }
  }
</style>
