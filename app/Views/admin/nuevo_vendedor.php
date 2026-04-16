<div class="nuevoTurno">
  <div style="width: 100%;">

    <h2><?= isset($vendedor) ? 'Editar Vendedor' : 'Nuevo Vendedor' ?></h2>

    <?php $validation = \Config\Services::validation(); ?>
    <form method="post" action="<?= isset($vendedor) ? base_url('actualizarVend/'.$vendedor['id_vendedor']) : base_url('crearVend') ?>">
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
          <input name="nombre" type="text" class="form-control"
            placeholder="Nombre"
            value="<?= isset($vendedor) ? $vendedor['nombre'] : old('nombre') ?>"
            required minlength="3" maxlength="20">
        </div>

        <div class="input-group">
          <label>Apellido</label>
          <input name="apellido" type="text" class="form-control"
            placeholder="Apellido"
            value="<?= isset($vendedor) ? $vendedor['apellido'] : old('apellido') ?>"
            required minlength="3" maxlength="20">
        </div>

      </div>

      <div class="button-container">
        <a href="<?= base_url('vendedores'); ?>" class="button2">Cancelar</a>

        <button type="submit" class="button2">
          <?= isset($vendedor) ? 'Actualizar' : 'Crear' ?>
        </button>
      </div>
    </form>
  </div>
</div>

<style>
  .form-container {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    justify-content: space-between;
  }

  .input-group {
    width: 48%;
    display: flex;
    flex-direction: column;
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

  @media (max-width: 768px) {
    .input-group {
      width: 100%;
    }
  }
</style>
