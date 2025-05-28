<?php $session = session();
          $nombre= $session->get('nombre');
          $perfil=$session->get('perfil_id');
          $id=$session->get('id');?>
<section class="Fondo">

<!-- Mensajes temporales -->
<?php if (session()->getFlashdata('msg')): ?>
        <div id="flash-message" class="flash-message success">
            <?= session()->getFlashdata('msg') ?>
        </div>
    <?php endif; ?>
    <?php if (session("msgEr")): ?>
        <div id="flash-message" class="flash-message danger">
            <?php echo session("msgEr"); ?>
        </div>
    <?php endif; ?>
    <script>
        setTimeout(function() {
            document.getElementById('flash-message').style.display = 'none';
        }, 3000); // 3000 milisegundos = 3 segundos
    </script>
     <style>
         /* Hacer el campo de búsqueda más largo y ancho */
    .dataTables_filter input {
        width: 300px; /* Ajusta el tamaño según sea necesario */
        height: 55px; /* Ajusta la altura si lo deseas */
        font-size: 20px; /* Tamaño de la fuente */
        padding: 5px 10px; /* Añadir espacio dentro del campo */
        border-radius: 5px; /* Bordes redondeados */
        border: 1px solid #ccc; /* Borde gris claro */
    }

    /* Cambiar el color y hacer más nítida la letra del placeholder */
    .dataTables_filter input::placeholder {
        color: white; /* Cambiar a blanco */
        opacity: 1; /* Asegura que el color del placeholder no sea opaco */
        font-weight: bold; /* Hacer el texto más nítido */
    }
    </style>
<!-- Fin de los mensajes temporales -->

<div class="" style="width: 100%;">
<section class="contenedor-titulo">
  <strong class="titulo-vidrio">Lista de Clientes</strong>
  </section>
  <br>
  <div style="text-align: end;">

  <a href="<?= base_url('/nuevo-cliente')?>" class="button">Nuevo Cliente</a>

  <br><br>
  
  <?php $Recaudacion = 0; ?>
  <table class="table table-responsive table-hover" id="users-list">
       <thead>
          <tr class="colorTexto2">
             <th>Nro Cliente</th>
             <th>Nombre/Apodo</th>
             <th>Telefono</th>
             <th>Cuil</th>
             <th>Acciones</th>
          </tr>
       </thead>
       <tbody>
          <?php if($clientes): ?>
          <?php foreach($clientes as $cl): ?>
          <tr>
             <td><?php echo $cl['id_cliente']; ?></td>
             <td><?php echo $cl['nombre']; ?></td>
             <td><?php echo $cl['telefono']; ?></td>
             <td><?php echo $cl['cuil']; ?></td>
             
             <td class="row">
               <a class="btn btn-outline-primary" href="<?php echo base_url('editarCliente/'.$cl['id_cliente']);?>">
               ✏️ Editar</a>
             </td>
             
            </tr>
         <?php endforeach; ?>
         <?php endif; ?>
       
     </table>
     <br>
  </div>
</div>
</section>

<style>
  @media (max-width: 768px) { /* Aplica cambios en pantallas pequeñas */
    table td:last-child {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1px; /* Espaciado entre los botones */
        min-height: 50px; /* Ajusta la altura mínima según necesites */
    }
    
    table td:last-child a {
        width: 100%; /* Hace que los botones ocupen todo el ancho */
        text-align: center;
    }
}
</style>


          <script src="<?php echo base_url('./assets/js/jquery-3.5.1.slim.min.js');?>"></script>
          <link rel="stylesheet" type="text/css" href="<?php echo base_url('./assets/css/jquery.dataTables.min.css');?>">
          <script type="text/javascript" src="<?php echo base_url('./assets/js/jquery.dataTables.min.js');?>"></script>
          <script>
  $(document).ready(function () {
    $('#users-list').DataTable({
      "stateSave": true, // Habilitar el guardado del estado
      "language": {
        "lengthMenu": "Mostrar _MENU_ registros por página.",
        "zeroRecords": "Lo sentimos! No hay resultados.",
        "info": "Mostrando la página _PAGE_ de _PAGES_",
        "infoEmpty": "No hay registros disponibles.",
        "infoFiltered": "(filtrado de _MAX_ registros totales)",
        "search": "Buscar: ",
        "paginate": {
          "next": "Siguiente",
          "previous": "Anterior"
        }
      },
      initComplete: function () {
        // Agregar el placeholder personalizado al buscador
        $('#users-list_filter input').attr('placeholder', 'Nro Cliente,nombre,Tel,etc...');
      }
    });
  });
</script>

<br><br>