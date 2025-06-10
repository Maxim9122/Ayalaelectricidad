<?php $session = session();
          $nombre= $session->get('nombre');
          $perfil=$session->get('perfil_id');
          $id=$session->get('id');
          //print_r($session->get());
          //exit;
          ?>
<section>
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
<!-- Fin de los mensajes temporales -->

        <?php
        $session = session();
        $id_cliente_seleccionado = $session->get('id_cliente') ?? '';
        $id_pedido = $session->get('id_pedido') ?? '';
        $tipo_compra = $session->get('tipo_compra') ?? '';
        $estado = $session->get('estado') ?? '';
        ?>
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
<div style="width: 100%;">
    <br>
<h2 class="textoColor" align="center">Listado de Pedidos para Hoy</h2>
        <br>
  <div style="text-align: end;">
  
  <br>
  <a class="button" href="<?php echo base_url('pedidosTodos');?>">
               <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-card-checklist" viewBox="0 0 16 16">
                <path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h13zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-13z"/>
                <path d="M7 5.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0zM7 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 0 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0z"/>
    </svg> Pedidos Para Otros Días</a>
  <!-- <a class="button" href="<?php echo base_url('pedidosCompletados');?>">
               <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-card-checklist" viewBox="0 0 16 16">
                <path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h13zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-13z"/>
                <path d="M7 5.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0zM7 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 0 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0z"/>
    </svg>Despachados</a> -->
  <br><br>
  <?php $Recaudacion = 0; ?>
  <table class="table table-responsive table-hover" id="users-list" style="color:white; ">
       <thead>
          <tr class="" style="color:white font-weight:900;">
             <th style="">Nro Pedido</th>
             <th style="color:orange">Cliente</th>
             <th>Teléfono</th>
             <th>Vendedor</th>
             <th style="color:orange">Total</th>
             <th>Fecha Registro</th>
             <th>Hora Reg.</th>
             <th>Fecha Entrega</th>
             <th>Estado</th>                          
             <th>Acciones</th>
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
        <td>$ <?php echo $p['total_venta'];?></td>
        <td><?php echo $p['fecha'];?></td>
        <td><?php echo $p['hora'];?></td>
        <td><?php echo $p['fecha_pedido'];?></td>
        <td><?php echo $p['estado'];?></td>

        <!-- Formulario de acciones por cada pedido -->

        <form id="pedidoForm" action="<?php echo base_url('pedido_actualizar/'.$p['id']); ?>" method="POST">
            
            
        <td>
        <div class="dropdown">
        <span class="dropdown-toggle btn" style="color:white">Acciones▼</span>
        <ul class="dropdown-menu">
            <li>
                <a href="<?php echo base_url('DetalleVta/'.$p['id']); ?>">
                    📄 Ver Detalle
                </a>
            </li>
            <li>
                <a href="<?php echo base_url('cancelar/'.$p['id']); ?>" class="text-danger"
                   onclick="mostrarConfirmacionCancelar(event, '¿Estás seguro de cancelar este pedido?', this.href);">
                    ❌ Cancelar
                </a>
            </li>
             <li>
                <?php if(($perfil == 3 || $perfil == 1) && $estado == ''){?>
                <a href="<?php echo base_url('cargar_pedido/'.$p['id']); ?>">
                    ✏️ Modificar
                </a>
            <li>
                <a href="<?php echo base_url('generarPresupuesto/'.$p['id']); ?>">
                    🖨️ Presupuesto
                </a>
            </li>
            <li>
                <a href="<?php echo base_url('DescargarPresupuesto/'.$p['id']); ?>">
                    ⬇️ Descargar
                </a>
            </li>
                <?php } ?>
            </li> 
            <li>
                <?php if($perfil && $estado == '') { ?>
                <a class="text-success btn" href="<?php echo base_url('cobrarPedido/'.$p['id']);?>">
                    ✅ Cobrar
                </a>
                <?php } ?>
            </li>
                </ul>
            </div>
        </td>

            
         </form>
         
         </tr>
         <?php endforeach; ?>

         <?php endif; ?>
       
     </table>

<!-- Cuadro de confirmación de Cancelar Pedido -->
<div id="confirm-dialog-Cancelar" class="confirm-dialog" style="display: none;">
    <div class="confirm-content btn2">
        <p id="confirm-message-cancelar">¿Estás seguro de Cancelar el pedido??</p>
        <div class="confirm-buttons">
            <button id="confirm-yes" class="btn btn-yes" autofocus>Sí</button>
            <button id="confirm-no" class="btn btn-no">No</button>
        </div>
    </div>
</div>
<!-- Esta parte es del cartel de confirmacion de Cancelar pedido o pedido Listo-->
<script>
function mostrarConfirmacionCancelar(event, mensaje, url) {
    event.preventDefault(); // Previene la acción por defecto del enlace
    const confirmDialog = document.getElementById('confirm-dialog-Cancelar');
    const confirmMessage = document.getElementById('confirm-message-cancelar');
    const confirmYes = document.getElementById('confirm-yes');
    const confirmNo = document.getElementById('confirm-no');

    // Muestra el cuadro de confirmación con el mensaje proporcionado
    confirmMessage.textContent = mensaje;
    confirmDialog.style.display = 'flex';

    // Si el usuario confirma, redirige a la URL
    confirmYes.onclick = function () {
        window.location.href = url;
    };

    // Si el usuario cancela, oculta el cuadro de confirmación
    confirmNo.onclick = function () {
        confirmDialog.style.display = 'none';
    };

    
}

// Detectar clics fuera del cuadro de diálogo
window.onclick = function (e) {
        if (e.target === dialog) {
            cerrarConfirmacion();
        }
    };

    // Detectar las teclas Enter y Escape
    window.onkeydown = function (e) {
        if (e.key === "Escape") {
            cerrarConfirmacion();
        } else if (e.key === "Enter") {
            enviarFormulario(url);
        }
    };


function enviarFormulario(url) {
    // Enviar el formulario al hacer clic en "Sí"
    const formulario = document.getElementById('pedidoForm');
    formulario.action = url; // Cambiar la acción del formulario
    formulario.submit(); // Enviar el formulario
    cerrarConfirmacion(); // Cerrar el cuadro de confirmación
}

function cerrarConfirmacion() {
    const dialog = document.getElementById('confirm-dialog-Cancelar');
    dialog.style.display = 'none';

    // Eliminar los eventos para evitar interferencias en el futuro
    window.onclick = null;
    window.onkeydown = null;
}
</script>
     
  </div>
</div>

</section>
          <script src="<?php echo base_url('./assets/js/jquery-3.5.1.slim.min.js');?>"></script>
          <link rel="stylesheet" type="text/css" href="<?php echo base_url('./assets/css/jquery.dataTables.min.css');?>">
          <script type="text/javascript" src="<?php echo base_url('./assets/js/jquery.dataTables.min.js');?>"></script>
<!-- Para la tabla de pedido-->
<script>
  $(document).ready(function () {
    $('#users-list').DataTable({

        "stateSave": true, // Habilitar el guardado del estado

      "language": {
        "lengthMenu": "Mostrar _MENU_ registros por página.",
        "zeroRecords": "Sin Resultados! No hay pedidos agendados para Hoy.",
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
        $('#users-list_filter input').attr('placeholder', 'Nro Pedido,cliente,estado,vendedor');
      }
    });
  });

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
  // Establecer la fecha mínima y el valor predeterminado
  const fechaInput = document.getElementById('fecha');
  fechaInput.setAttribute('min', formattedDate);
  fechaInput.setAttribute('value', formattedDate);

  // Rescatar la hora del input por medio del id y asignar la hora actual
  document.getElementById('hora').value = formattedTime;
</script>



<!-- Cartel de la funcion que actualiza los campos de Barber Hora y Servicio 
 si se modificaron antes de guardar el pedido Completado-->
<script>

function confirmarYEnviar(url) {
    // Detener la acción predeterminada del enlace (si es necesario, en un evento de tipo 'click')
    event.preventDefault();

    // Mostrar el cuadro de diálogo
    const dialog = document.getElementById('confirm-dialog');
    const messageElement = document.getElementById('confirm-message');
    const yesButton = document.getElementById('confirm-yes');
    const noButton = document.getElementById('confirm-no');

    messageElement.textContent = 'Marcar Pedido como completado?';
    dialog.style.display = 'flex';

    // Acción para confirmar
    yesButton.onclick = function () {
        enviarFormulario(url);
    };

    // Acción para cancelar
    noButton.onclick = cerrarConfirmacion;

    // Detectar clics fuera del cuadro de diálogo
    window.onclick = function (e) {
        if (e.target === dialog) {
            cerrarConfirmacion();
        }
    };

    // Detectar las teclas Enter y Escape
    window.onkeydown = function (e) {
        if (e.key === "Escape") {
            cerrarConfirmacion();
        } else if (e.key === "Enter") {
            enviarFormulario(url);
        }
    };
}

function enviarFormulario(url) {
    // Enviar el formulario al hacer clic en "Sí"
    const formulario = document.getElementById('pedidoForm');
    formulario.action = url; // Cambiar la acción del formulario
    formulario.submit(); // Enviar el formulario
    cerrarConfirmacion(); // Cerrar el cuadro de confirmación
}

function cerrarConfirmacion() {
    const dialog = document.getElementById('confirm-dialog');
    dialog.style.display = 'none';

    // Eliminar los eventos para evitar interferencias en el futuro
    window.onclick = null;
    window.onkeydown = null;
}

</script>

<br><br>