<?php $session = session();
          $nombre= $session->get('nombre');
          $perfil=$session->get('perfil_id');
          $id=$session->get('id');?>
<section class="Fondo">

<?php if (session()->getFlashdata('msg')): ?>
    <div id="flash-message" class="success" style="width: 30%;">
        <?= session()->getFlashdata('msg') ?>
    </div>
    <script>
        setTimeout(function() {
            document.getElementById('flash-message').style.display = 'none';
        }, 2000); // 2000 milisegundos = 2 segundos
    </script>
<?php endif; ?>
  <?php if(session("msgEr")):?>
   <div id="flash-message2" class="danger" style="width: 30%;">
      <?php echo session("msgEr"); ?>
      </div>
      <script>
        setTimeout(function() {
            document.getElementById('flash-message2').style.display = 'none';
        }, 2000); // 2000 milisegundos = 2 segundos
    </script>
  <?php endif?>
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
<div class="" style="width: 100%;" align="center">
    <br>
<h2 class="textoColor" align="center">Listado de Pedidos Entregados o Cancelados</h2>
        <section class="">

        <div class="estiloTurno" style="width: 72%;">
            <form action="<?php echo base_url('filtrarPedidos'); ?>" method="POST">
            <label for="start-date" class="label-inline">Fecha desde:</label>
            <input type="date" id="fecha_desde" name="fecha_desde" value="<?= isset($_POST['fecha_desde']) ? $_POST['fecha_desde'] : '' ?>" required>
            
            <label for="end-date" class="label-inline">Fecha hasta:</label>
            <input type="date" id="fecha_hasta" name="fecha_hasta" value="<?= isset($_POST['fecha_hasta']) ? $_POST['fecha_hasta'] : '' ?>" required>
            
            <label for="barber-id" class="label-inline">Vendedor:</label>
            <select id="barber-id" name="id_usuario">
                <option value="">Todos</option>
                <?php foreach ($usuarios as $us): ?>
                    <option value="<?= $us['id']; ?>" <?= (isset($_POST['id_usuario']) && $_POST['id_usuario'] == $us['id']) ? 'selected' : '' ?>>
                        <?= $us['nombre']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" class="btn">Filtrar</button>
            </form>

        <a class="button" href="<?php echo base_url('pedidosCompletados');?>">
               <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-card-checklist" viewBox="0 0 16 16">
                <path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h13zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-13z"/>
                <path d="M7 5.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0zM7 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 0 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0z"/>
        </svg>Todos</a>
        </div>
        
  <div style="text-align: end;">
  
  <br>

   <a class="button" href="<?php echo base_url('pedidos');?>">
               <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-card-checklist" viewBox="0 0 16 16">
                <path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h13zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-13z"/>
                <path d="M7 5.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0zM7 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 0 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0z"/>
    </svg> Volver a Pedidos</a>

  <br><br>
  <?php $Recaudacion = 0; ?>
  <table class="table table-responsive table-hover" id="users-list">
       <thead>
          <tr class="colorTexto2">
             <th>Nro Pedido</th>
             <th>Cliente</th>
             <th>Teléfono</th>
             <th>Vendedor</th>
             <th>Total</th>
             <th>Fecha Registro</th>
             <th>Hora Reg.</th>
             <th>Fecha Entrega.</th>  
             <th>Hora Entrega.</th>  
             <th>Estado</th>
             <th>Detalle</th>
          </tr>
       </thead>
       <tbody>
          <?php if($pedidos): ?>
            <?php foreach($pedidos as $p): ?>
    <tr>
        <td><?php echo $p['id']; ?></td>
        <td><?php echo $p['nombre_cliente']; ?></td>
        <td><?php echo $p['telefono']; ?></td>
        <td><?php echo $p['nombre_usuario'];?></td>
        <td>$<?php echo $p['total_bonificado'];?></td>
        <td><?php echo $p['fecha'];?></td>
        <td><?php echo $p['hora'];?></td>
        <td><?php echo $p['fecha_pedido'];?></td>
        <td><?php echo $p['hora_entrega'];?></td>
        <td><?php echo $p['estado'];?></td>
        <td>
                <a class="btn btn-outline-primary" href="<?php echo base_url('DetalleVta/'.$p['id']);?>">
                Detalle</a>
        </td>
         </tr>
         <?php endforeach; ?>

         <?php endif; ?>
       
     </table>
     
     
  </div>
</div>

</section>





          <script src="<?php echo base_url('./assets/js/jquery-3.5.1.slim.min.js');?>"></script>
          <link rel="stylesheet" type="text/css" href="<?php echo base_url('./assets/css/jquery.dataTables.min.css');?>">
          <script type="text/javascript" src="<?php echo base_url('./assets/js/jquery.dataTables.min.js');?>"></script>
          <script>
  $(document).ready(function () {
    $('#users-list').DataTable({
      "order": [[0, "desc"]], // Ordena de manera descendente (Primero el último registrado)

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
        $('#users-list_filter input').attr('placeholder', 'Nro Pedido,cliente,estado,vendedor..');
      }
    });
  });
</script>



<br><br>