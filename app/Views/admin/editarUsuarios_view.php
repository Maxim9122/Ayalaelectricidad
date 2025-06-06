<?php $session = session();
$nombre= $session->get('nombre');
$perfil=$session->get('perfil_id');
$id=$session->get('id');?>

<?php if($perfil == 1){ ?>
<div class="container mt-1 mb-0 nuevoTurno" style="max-width: 800px; margin: 20px auto; padding: 20px; border: 1px solid #ccc; border-radius: 5px;">
    <div class="card" style="border: none;">
        <div class="card-header text-center" style="background-color:rgb(45, 39, 39); padding: 10px; border-bottom: 1px solid #ccc;">
            <h2>Editar Usuarios</h2>
        </div>

        <?php $validation = \Config\Services::validation(); ?>
        <form method="post" action="<?php echo base_url('/enviarEdicion') ?>">
            <?=csrf_field();?>

            <?php if(!empty(session()->getFlashdata('fail'))): ?>
                <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border: 1px solid #f5c6cb; border-radius: 5px;"><?= session()->getFlashdata('fail'); ?></div>
            <?php endif ?>

            <?php if(!empty(session()->getFlashdata('success'))): ?>
                <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 10px; margin: 10px 0; border: 1px solid #c3e6cb; border-radius: 5px;"><?= session()->getFlashdata('success'); ?></div>
            <?php endif ?>

            <div class="card-body" style="padding: 20px;">
                <div style="display: flex; flex-wrap: wrap;">
                    <div style="width: 50%; padding: 5px;">
                        <div style="margin-bottom: 10px;">
                            <label style="display: block;">Nombre</label>
                            <input name="nombre" type="text" style="width: 95%; padding: 5px; border: 1px solid #ccc;" placeholder="Nombre"
                                   value="<?php echo $data['nombre']?>" required minlength="3" maxlength="20">
                        </div>
                    </div>

                    <div style="width: 50%; padding: 5px;">
                        <div style="margin-bottom: 10px;">
                            <label style="display: block;">Apellido</label>
                            <input type="text" name="apellido" style="width: 95%; padding: 5px; border: 1px solid #ccc;" placeholder="Apellido"
                                   value="<?php echo $data['apellido'] ?>" required minlength="3" maxlength="20">
                        </div>
                    </div>

                    <div style="width: 50%; padding: 5px;">
                        <div style="margin-bottom: 10px;">
                            <label style="display: block;">Email</label>
                            <input name="email" type="email" style="width: 95%; padding: 5px; border: 1px solid #ccc;" placeholder="correo@algo.com"
                                   value="<?php echo $data['email']?>" required>
                        </div>
                    </div>

                    <div style="width: 50%; padding: 5px;">
                        <div style="margin-bottom: 10px;">
                            <label style="display: block;">Teléfono</label>
                            <input name="telefono" type="text" style="width: 95%; padding: 5px; border: 1px solid #ccc;" placeholder="Teléfono"

                            value="<?php echo $data['telefono']?>" maxlength="10"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')">

                        </div>
                    </div>

                    <div style="width: 50%; padding: 5px;">
                        <div style="margin-bottom: 10px;">
                            <label style="display: block;">Dirección</label>
                            <input name="direccion" type="text" style="width: 95%; padding: 5px; border: 1px solid #ccc;" placeholder="Dirección"
                                   value="<?php echo $data['direccion']?>">
                        </div>
                    </div>

                    <div style="width: 50%; padding: 5px;">
                        <div style="margin-bottom: 10px;">
                            <label style="display: block;">Contraseña</label>
                            <input name="pass" type="text" style="width: 95%; padding: 5px; border: 1px solid #ccc;" placeholder="Password"
                                   value="<?php echo $data['pass']?>" minlength="3" maxlength="20" required>
                        </div>
                    </div>
                <?php if($data['id'] > 1){  ?>
                    <div style="width: 50%; padding: 5px;">
                        <div style="margin-bottom: 10px;">
                            <?php
                            $perfil = '';
                            switch ($data['perfil_id']) {
                                case 1:
                                    $perfil = 'Admin';
                                    break;
                                case 2:
                                    $perfil = 'Vendedor';
                                    break;
                                case 3:
                                    $perfil = 'Cajero';
                                    break;
                            }
                            ?>
                            <label style="display: block;">Tipo de Perfil</label>
                            <select name="perfil_id" style="width: 95%; padding: 5px; border: 1px solid #ccc;">
                                <option value="<?= $data['perfil_id'] ?>" selected><?= $perfil ?></option>
                                <?php if ($data['perfil_id'] != 3) { echo '<option value="3">Cajero</option>'; } ?>                                
                                <?php if ($data['perfil_id'] != 1) { echo '<option value="1">Admin</option>'; } ?>
                            </select>
                        </div>
                    </div>
                <?php } ?>
                    <div style="width: 50%; padding: 5px;">
                        <div style="margin-bottom: 10px;">
                            <label style="display: block;">Eliminado</label>
                            <input name="baja" type="text" readonly style="width: 95%; padding: 5px; border: 1px solid #ccc;"
                                   value="<?php echo $data['baja']?>">
                        </div>
                    </div>

                    <input type="hidden" name="id" value="<?php echo $data['id']?>">

                </div> 
                <div style="text-align: right; margin-top: 20px;">
                    <a href="<?php echo base_url('usuarios-list'); ?>" style="padding: 10px 20px; background-color: #ccc; color: #333; text-decoration: none; border-radius: 5px; margin-right: 10px;">Cancelar</a>
                    <input type="submit" value="Editar" style="padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px;">
                </div>

            </div>
        </form>

    </div>
</div>
<?php } else { ?>
    <h2>Su perfil no tiene acceso a esta parte. Vuelva a alguna sección de Empleado!</h2>
<?php } ?>