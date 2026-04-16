<?php 
$session = session();
$nombre = $session->get('nombre');
$perfil = $session->get('perfil_id');
$id = $session->get('id');
?>  

<?php if ($perfil == 1) { ?>

<div class="container">

    <?php if (session()->getFlashdata('msg') || session()->getFlashdata('msgEr')): ?>
        <div id="flash-message" 
             class="flash-message <?= session()->getFlashdata('msg') ? 'success' : 'danger' ?>">
            <?= session()->getFlashdata('msg') ?>
            <?= session()->getFlashdata('msgEr') ?>
        </div>

        <script>
            setTimeout(() => {
                document.getElementById('flash-message').style.display = 'none';
            }, 3000);
        </script>
    <?php endif; ?>

    <h2 class="mt-4 mb-4">Listado de Vendedores</h2>

    <!-- BOTÓN NUEVO VENDEDOR -->
    <div style="text-align: end; margin-bottom: 15px;">
        <a class="btn btn-primary" href="<?= base_url('nuevoVend') ?>">Nuevo Vendedor</a>
    </div>

    <!-- TABLA -->
    <table id="vendedores-list" class="display">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>
            <?php if ($vendedores): ?>
                <?php foreach ($vendedores as $v): ?>
                    <tr>
                        <td><?= $v['id_vendedor'] ?></td>
                        <td><?= $v['nombre'] ?></td>
                        <td><?= $v['apellido'] ?></td>

                        <td style="text-align: end;" >
                            <a class="btn btn-outline-danger" 
                               href="<?= base_url('editarVend/'.$v['id_vendedor']) ?>">
                               Editar
                            </a>

                            <a class="btn btn-outline-danger" 
                               href="<?= base_url('eliminarVend/'.$v['id_vendedor']) ?>">
                               Eliminar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

</div>

<!-- Estilo buscador grande -->
<style>
.dataTables_filter input {
    width: 280px;
    height: 45px;
    font-size: 18px;
    padding: 5px 10px;
    border-radius: 5px;
}
.dataTables_filter input::placeholder {
    color: #666;
    font-weight: bold;
}

.dataTables_filter {
    text-align: right !important;
}

.dataTables_length {
    text-align: left !important;
}
</style>

<!-- Mobile: botones uno debajo de otro -->
<style>
@media (max-width: 768px) {
    table td:last-child {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 3px;
        min-height: 70px;
    }
    table td:last-child a {
        width: 100%;
        text-align: center;
    }
}
</style>

<!-- JS DATATABLES -->
<script src="<?= base_url('./assets/js/jquery-3.5.1.slim.min.js') ?>"></script>
<link rel="stylesheet" href="<?= base_url('./assets/css/jquery.dataTables.min.css') ?>">
<script src="<?= base_url('./assets/js/jquery.dataTables.min.js') ?>"></script>

<script>
$(document).ready(function () {
    $('#vendedores-list').DataTable({
        "stateSave": true,
        "language": {
            "lengthMenu": "Mostrar _MENU_ registros por página",
            "zeroRecords": "Lo sentimos! No hay resultados.",
            "info": "Mostrando página _PAGE_ de _PAGES_",
            "infoEmpty": "No hay registros disponibles",
            "infoFiltered": "(filtrado de _MAX_ registros)",
            "search": "Buscar:",
            "paginate": { "next": "Siguiente", "previous": "Anterior" }
        },
        "initComplete": function () {
            $('.dataTables_filter input').attr('placeholder', 'Buscar vendedor...');
        }
    });
});
</script>

<?php } ?>
