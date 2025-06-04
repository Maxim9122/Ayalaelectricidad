<?php $session = session();
$nombre = $session->get('nombre');
$perfil = $session->get('perfil_id');
$id = $session->get('id');
?>
<br>
<style>
  .form-row {
  display: flex;
  flex-wrap: wrap;
  gap: 20px; /* Espacio entre los campos */
}

.form-row .mb-2 {
  flex: 1; /* Para que ambos campos ocupen la misma proporción */
  min-width: 250px; /* Evita que los campos sean demasiado pequeños */
}

/* En pantallas pequeñas, los campos se apilan en una columna */
@media (max-width: 768px) {
  .form-row {
    flex-direction: column;
  }
}

</style>
<?php if ($perfil) { ?>
    <?php if (session()->getFlashdata('msg')): ?>
        <div id="flash-message" class="flash-message success">
            <?= session()->getFlashdata('msg') ?>
        </div>
    <?php endif; ?>
    <?php if (session("msgEr")): ?>
        <div id="flash-message" class="flash-message danger">
            <?= session("msgEr"); ?>
        </div>
    <?php endif; ?>

    <script>
        setTimeout(function () {
            document.getElementById('flash-message').style.display = 'none';
        }, 3000);
    </script>

    <div class="nuevoTurno">
        <h2>Registrar Nuevo Producto</h2>
        <br>

        <?php $validation = \Config\Services::validation(); ?>
        <form method="post" enctype="multipart/form-data" action="<?= base_url('ProductoValidation') ?>">
            <?= csrf_field(); ?>

            <?php if (!empty(session()->getFlashdata('fail'))): ?>
                <div class="alert alert-danger"><?= session()->getFlashdata('fail'); ?></div>
            <?php endif ?>
            <?php if (!empty(session()->getFlashdata('success'))): ?>
                <div class="alert alert-success"><?= session()->getFlashdata('success'); ?></div>
            <?php endif ?>

            <!-- Primera fila -->
            <div class="form-row">
                <div class="mb-2">
                    <label>Código de Barra</label>
                    <input name="codigo_barra" type="text" maxlength="15" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    <?= $validation->getError('codigo_barra') ? "<div class='alert alert-danger mt-2'>{$validation->getError('codigo_barra')}</div>" : "" ?>
                </div>

                <div class="mb-2">
                    <label>Nombre</label>
                    <input name="nombre" type="text" minlength="5" maxlength="20" required>
                    <?= $validation->getError('nombre') ? "<div class='alert alert-danger mt-2'>{$validation->getError('nombre')}</div>" : "" ?>
                </div>
            </div>

            <!-- Segunda fila -->
            <div class="form-row">
                <div class="mb-2">
                    <label>Descripción</label>
                    <input name="descripcion" type="text" maxlength="20">
                    <?= $validation->getError('descripcion') ? "<div class='alert alert-danger mt-2'>{$validation->getError('descripcion')}</div>" : "" ?>
                </div>
            <!--
                <div class="mb-2">
                    <label>Imagen</label>
                    <input name="imagen" type="file" required>
                    <?= $validation->getError('imagen') ? "<div class='alert alert-danger mt-2'>{$validation->getError('imagen')}</div>" : "" ?>
                </div>
            -->
            </div>

            <!-- Tercera fila -->
            <div class="form-row">
                <div class="mb-2">
                    <label>Categoría</label>
                    <select name="categoria_id" class="form-control">
                        <option value="1">Seleccione Categoría</option>
                        <?php foreach ($categorias as $categoria) : ?>
                            <option value="<?= $categoria['categoria_id']; ?>"><?= $categoria['descripcion']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?= $validation->getError('categoria_id') ? "<div class='alert alert-danger mt-2'>{$validation->getError('categoria_id')}</div>" : "" ?>
                </div>
            <!--
                <div class="mb-2">
                    <label>Precio de Costo</label>
                    <input name="precio" type="text" required maxlength="20" 
                        oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">
                    <?= $validation->getError('precio') ? "<div class='alert alert-danger mt-2'>{$validation->getError('precio')}</div>" : "" ?>
                </div> -->

                <!-- Cuarta fila -->
                <div class="form-row">
                    <div class="mb-2">
                        <label>Precio de Venta</label>
                        <input name="precio_vta" type="text" required maxlength="20" 
                            oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">
                        <?= $validation->getError('precio_vta') ? "<div class='alert alert-danger mt-2'>{$validation->getError('precio_vta')}</div>" : "" ?>
                    </div>
                </div>


                <div class="mb-2">
                    <label>Stock</label>
                    <input name="stock" type="text" required maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    <?= $validation->getError('stock') ? "<div class='alert alert-danger mt-2'>{$validation->getError('stock')}</div>" : "" ?>
                </div>
            </div>

            <!-- Quinta fila 
            <div class="form-row">
                <div class="mb-2">
                    <label>Stock Mínimo</label>
                    <input name="stock_min" type="text" required maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    <?= $validation->getError('stock_min') ? "<div class='alert alert-danger mt-2'>{$validation->getError('stock_min')}</div>" : "" ?>
                </div>
            </div> -->

            <br>
            <div class="button-container">
                <a href="<?= base_url('Lista_Productos'); ?>" class="btn">Cancelar</a>
                <button type="submit" class="btn">Guardar</button>
            </div>
            <br>
        </form>
    </div>
<?php } else { ?>
    <h2>Su perfil no tiene acceso a esta parte, vuelva a alguna sección de Empleado.</h2>
<?php } ?>
