<?php if(session("msg")):?>
   <div class="container alert alert-success text-center" style="width: 50%;">
      <?php echo session("msg"); ?>
      </div>
  <?php endif?> 
<div class="container mt-5 fondo3 rounded" style="width: 100%; text-align: center;">
  <br>
<h3 class="titulo-vidrio" style=" text-align: center;">Usuarios Eliminados</h3>
<div class="mt-3" style="width: 100%; text-align: end;">
  <br>
  <a class="btn btn-primary float-end" href="<?php echo base_url('usuarios-list');?>" tabindex="-1" aria-disabled="true">
  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-back" viewBox="0 0 16 16">
  <path d="M0 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v2h2a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-2H2a2 2 0 0 1-2-2V2zm2-1a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H2z"/>
</svg>Volver</a>
  <br><br>
  </div>
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
  <table class="table table-responsive table-hover" id="users-list">
       <thead>
          <tr class="bg-success">
             <th>Nombre</th>
             <th>Apellido</th>
             <th>E-mail</th>
             <th>Perfil</th>
             <th>Eliminado</th>
             <th>Acciones</th>
          </tr>
       </thead>
       <tbody>
          <?php if($usuarios): ?>
          <?php foreach($usuarios as $user): ?>
          <tr>
             <td><?php echo $user['nombre']; ?></td>
             <td><?php echo $user['apellido']; ?></td>
             <td><?php echo $user['email']; ?></td>
             <?php  switch ($user['perfil_id']) {
                case 1:
                    $perfil = 'Admin';
                    break;
                case 2:
                    $perfil = 'Vendedor';
                    break;
                    case 3:
                      $perfil = 'Cajero';
                      break;
            }?>
             <td><?php echo $perfil  ?></td>
             <td><?php echo $user['baja'];  ?></td>
             <td>
               <a class="btn btn-outline-primary" href="<?php echo base_url('habilitarUs/'.$user['id']);?>">
               <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bandaid-fill" viewBox="0 0 16 16">
                <path d="m2.68 7.676 6.49-6.504a4 4 0 0 1 5.66 5.653l-1.477 1.529-5.006 5.006-1.523 1.472a4 4 0 0 1-5.653-5.66l.001-.002 1.505-1.492.001-.002Zm5.71-2.858a.5.5 0 1 0-.708.707.5.5 0 0 0 .707-.707ZM6.974 6.939a.5.5 0 1 0-.707-.707.5.5 0 0 0 .707.707ZM5.56 8.354a.5.5 0 1 0-.707-.708.5.5 0 0 0 .707.708Zm2.828 2.828a.5.5 0 1 0-.707-.707.5.5 0 0 0 .707.707Zm1.414-2.121a.5.5 0 1 0-.707.707.5.5 0 0 0 .707-.707Zm1.414-.707a.5.5 0 1 0-.706-.708.5.5 0 0 0 .707.708Zm-4.242.707a.5.5 0 1 0-.707.707.5.5 0 0 0 .707-.707Zm1.414-.707a.5.5 0 1 0-.707-.708.5.5 0 0 0 .707.708Zm1.414-2.122a.5.5 0 1 0-.707.707.5.5 0 0 0 .707-.707ZM8.646 3.354l4 4 .708-.708-4-4-.708.708Zm-1.292 9.292-4-4-.708.708 4 4 .708-.708Z"/>
                </svg> Habilitar</a>
             </td>

            </tr>
         <?php endforeach; ?>
         <?php endif; ?>

       

     </table>
     <br>
  
</div>


<!-- Esto es para que la ultima celda de la tabla se haga mas ancha a lo alto
           cuando se vea desde un telefono-->
           <style>
  @media (max-width: 768px) { /* Aplica cambios en pantallas pequeñas */
    table td:last-child {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 3px; /* Espaciado entre los botones */
        min-height: 70px; /* Ajusta la altura mínima según necesites */
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
        $('#users-list_filter input').attr('placeholder', 'Nombre,Apellido,Perfil..');
      }
    });
  });
</script>

<br><br>