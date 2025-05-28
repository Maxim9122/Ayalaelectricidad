<?php $session = session();
          $nombre= $session->get('nombre');
          $perfil=$session->get('perfil_id');
          $id=$session->get('id');
          $estado =$session->get('estado');          
          ?>
          
<!-- Mensajes temporales -->
<?php if(session()->getFlashdata('mensaje_stock')): ?>
    <div id="msg_stock">
        <?= session()->getFlashdata('mensaje_stock'); ?>
    </div>
<?php endif; ?>
<style>
    #msg_stock {
        position: fixed;
        top: 100px;
        left: 50%;
        transform: translateX(-50%);
        background-color: black; /* Fondo oscuro para destacar el mensaje */
        color: white;
        font-weight: bold;
        padding: 10px 20px;
        border: 3px solid #ff073a; /* Rojo flúor */
        border-radius: 5px;
        text-align: center;
        z-index: 1000;
        box-shadow: 0px 0px 10px #ff073a; /* Efecto neón */
    }

    /*Estilos para los selectores de fecha, cliente y tipo compra*/
.selector {
    width: 85%;
    padding: 8px;
    max-width:250px;
    border: 2px solid #50fa7b;
    background-color: #282a36;
    color: #f8f8f2;
    border-radius: 5px;
    font-size: 16px;
    font-weight: bold;
}

.selector:focus {
    outline: none;
    border-color: #8be9fd;
    box-shadow: 0 0 5px #8be9fd;
}

</style>
<script>
    setTimeout(function() {
        let msg = document.getElementById('msg_stock');
        if (msg) {
            msg.style.display = 'none';
        }
    }, 1500); // Se oculta después de 1.5 segundos
</script>

<?php if (session()->getFlashdata('msg')): ?>
        <div id="flash-message" class="flash-message success">
            <?= session()->getFlashdata('msg') ?>
        </div>
    <?php endif; ?>   
    <script>
        setTimeout(function() {
            document.getElementById('flash-message').style.display = 'none';
        }, 3000); // 3000 milisegundos = 3 segundos
    </script>

<?php if (session("msgEr")): ?>
    <div id="flash-message-Error" class="flash-message danger">
        <?php echo session("msgEr"); ?>
        <button class="close-btn" onclick="cerrarMensaje()">×</button>
    </div>
<?php endif; ?>
<script>
function cerrarMensaje() {
    document.getElementById("flash-message-Error").style.display = "none";
}
</script>


<!-- Fin de los mensajes temporales -->
<br>
<div class="" style="width: 100%;">
  <div class="">
  <h2 class="textoColor" text-align: center !important; >Historial de Descuentos de productos Defectuosos</h2>
  <br>
  <style>
    /* Mover el buscador a la derecha */
    .dataTables_filter {
        display: flex;
        justify-content: flex-end;
        width: 100%;
    }

    /* Mover el selector de "registros por página" a la derecha */
    .dataTables_length {
        text-align: right;
        width: 100%;
    }

    .dataTables_length select {
        display: inline-block;
        margin: 0 auto;
    }

    /* Hacer el campo de búsqueda más largo y ancho */
    .dataTables_filter input {
        width: 300px; /* Ajusta el tamaño según sea necesario */
        height: 55px; /* Ajusta la altura si lo deseas */
        font-size: 24px; /* Tamaño de la fuente */
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

<div style="display: flex; justify-content: flex-end; gap: 10px; padding: 20px;">                      
    <a class="btn" href="<?php echo base_url('descontarDefectuosos');?>">Volver</a>
</div>

  <table class="" id="users-list">
   <thead>
      <tr class="colorTexto2">
         <th>ID</th>
         <th>Nombre-Prod</th>
         <th>Precio</th>
         <th>Cant. Descontada</th>
         <th>Stock Restante</th>
         <th>Nombre Personal</th>
         <th>Fecha</th>
         <th>Motivo</th>        
      </tr>
   </thead>
   <tbody>
      <?php if($productos): ?>
      <?php foreach($productos as $prod): ?>
      <tr>
         <td><?php echo $prod['id']; ?></td>
         <td><?php echo $prod['nombre_def']; ?></td>           
         <td><?php echo $prod['precio_def']; ?></td> 
         <td><?php echo $prod['cantidad_desc']; ?></td> 
         <td><?php echo $prod['nuevo_stock']; ?></td> 
         <td><?php echo $prod['nombre_us']; ?></td>
         <td><?php echo $prod['fecha_desc']; ?></td>
         <td><?php echo $prod['motivo_desc']; ?></td> 
      </tr>
      <?php endforeach; ?>
      <?php endif; ?>
   </tbody>
</table>
     <br>
  </div>
</div>


<script src="<?php echo base_url('./assets/js/jquery-3.5.1.slim.min.js');?>"></script>
<script src="<?php echo base_url('./assets/js/jquery-ui.js');?>"></script>
<link rel="stylesheet" type="text/css" href="<?php echo base_url('./assets/css/jquery.dataTables.min.css');?>">
<script type="text/javascript" src="<?php echo base_url('./assets/js/jquery.dataTables.min.js');?>"></script>

<script>
$(document).ready(function () {
    // Inicializar DataTables
    $('#users-list').DataTable({
        "order": [[0, "desc"]],

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
            // Cambiar el texto del placeholder en el input de búsqueda
            $('#users-list_filter input').attr('placeholder', 'Nombre, Categoría, etc...');

            // Enfocar el campo de búsqueda de la tabla después de inicializar DataTables
            $('#users-list_filter input').focus();
        }
    });

    // Deshabilitar autofocus en el campo de código de barras
    document.getElementById('product_input').removeAttribute('autofocus');
});
</script>



<br><br>