<?php $session = session();
          $nombre= $session->get('nombre');
          $perfil=$session->get('perfil_id');
          $id=$session->get('id');?>
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
<h2 class="textoColor" align="center">Listado de todos los Pedidos Pendientes</h2>
        <br>
  <div style="text-align: end;">
  
  <br>   
    <a class="button" href="<?php echo base_url('pedidos');?>">
               <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-card-checklist" viewBox="0 0 16 16">
                <path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h13zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-13z"/>
                <path d="M7 5.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0zM7 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 0 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0z"/>
    </svg>Pedidos para Hoy</a>
    <a class="button" href="<?php echo base_url('pedidosCompletados');?>">
               <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-card-checklist" viewBox="0 0 16 16">
                <path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h13zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-13z"/>
                <path d="M7 5.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0zM7 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 0 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0z"/>
    </svg>Despachados</a>
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
        <td>$<?php echo $p['total_venta'];?></td>
        <td><?php echo $p['fecha'];?></td>
        <td><?php echo $p['hora'];?></td>
        <td><?php echo $p['fecha_pedido'];?></td>
        <td><?php echo $p['estado'];?></td>
        <!-- Formulario por cada turno -->
        <form id="pedidoForm" action="<?php echo base_url('pedido_actualizar/'.$p['id']); ?>" method="POST">
            
            
        <td>
        <div class="dropdown">
        <span class="dropdown-toggle btn">Acciones▼</span>
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
                <?php if(($perfil == 3 || $perfil == 2) && $estado == ''){?>
                <a href="<?php echo base_url('cargar_pedido/'.$p['id']); ?>">
                    ✏️ Modificar
                </a>
                <?php } ?>
            </li> 
            <li>
            <?php if($perfil == 3 && $estado == '') { ?>
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
<!-- Para la tabla de turnos-->
<script>
  $(document).ready(function () {
    $('#users-list').DataTable({

        "stateSave": true, // Habilitar el guardado del estado

      "language": {
        "lengthMenu": "Mostrar _MENU_ registros por página.",
        "zeroRecords": "Sin Resultados! No hay pedidos agendados.",
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



<!-- Buscador de clientes -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const buscarInput = document.getElementById("buscar_cliente");
    const clienteSelect = document.getElementById("id_cliente");

    // Mostrar opciones al escribir en el buscador
    buscarInput.addEventListener("input", function() {
        const filter = buscarInput.value.toLowerCase();
        const options = clienteSelect.querySelectorAll("option:not(.search-box)");

        let hasMatches = false; // Bandera para saber si hay coincidencias

        options.forEach(option => {
            const text = option.textContent.toLowerCase();
            if (text.includes(filter)) {
                option.style.display = "block"; // Mostrar coincidencias
                hasMatches = true;
            } else {
                option.style.display = "none"; // Ocultar otras
            }
        });

        // Mostrar solo si hay coincidencias y el filtro no está vacío
        if (filter.length > 0 && hasMatches) {
            clienteSelect.classList.add("show-options");
        } else {
            clienteSelect.classList.remove("show-options");
        }
    });

    // Ocultar opciones al borrar todo el buscador
    buscarInput.addEventListener("keydown", function(event) {
        if (event.key === "Escape" || buscarInput.value === "") {
            buscarInput.value = "";
            clienteSelect.classList.remove("show-options");
            const options = clienteSelect.querySelectorAll("option:not(.search-box)");
            options.forEach(option => {
                option.style.display = "none";
            });
        }
    });

    // Actualizar selección al hacer clic en una opción
    clienteSelect.addEventListener("change", function() {
        const selectedOption = clienteSelect.options[clienteSelect.selectedIndex];
        if (!selectedOption.classList.contains("search-box")) {
            buscarInput.value = selectedOption.textContent; // Mostrar el texto seleccionado en el buscador
            clienteSelect.classList.remove("show-options"); // Ocultar opciones
        }
    });

    // Enfocar el buscador automáticamente
    clienteSelect.addEventListener("focus", function() {
        buscarInput.focus();
    });
});
</script>

<!-- Cartel de la funcion que actualiza los campos de Barber Hora y Servicio 
 si se modificaron antes de guardar el turno Completado-->
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

<script>
//Todo sobre buscador de productos.
const input = document.getElementById('product_input');
const select = document.getElementById('product_select');
const form = document.getElementById('product_form'); // Obtener el formulario

// Filtrar opciones al escribir en el input
input.addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const options = select.options;

    let hasOptions = false; // Para mostrar/ocultar el select
    let firstMatchIndex = -1; // Para recordar la primera coincidencia

    for (let i = 1; i < options.length; i++) { // Comenzar desde 1 para omitir la opción por defecto
        const optionText = options[i].text.toLowerCase();
        options[i].style.display = optionText.includes(searchTerm) ? 'block' : 'none';
        if (options[i].style.display === 'block') {
            hasOptions = true; // Hay opciones que coinciden
            if (firstMatchIndex === -1) {
                firstMatchIndex = i; // Guarda la primera coincidencia
            }
        }
    }

    // Mostrar el select solo si hay opciones y se ha ingresado al menos una letra
    select.style.display = hasOptions && searchTerm.length > 0 ? 'block' : 'none';

    // Si hay opciones que coinciden, selecciona la primera
    if (firstMatchIndex !== -1) {
        select.selectedIndex = firstMatchIndex; // Selecciona la primera opción que coincide
    } else {
        select.selectedIndex = 0; // Reinicia la selección si no hay coincidencias
    }
});

// Manejar la selección al cambiar el select
select.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const nombre = selectedOption.getAttribute('data-nombre');
    const precio = selectedOption.getAttribute('data-precio');

    // Actualizar los campos ocultos
    document.getElementById('nombre').value = nombre;
    document.getElementById('precio_vta').value = precio;
    document.getElementById('product_id').value = selectedOption.value;

    // Reiniciar el campo de búsqueda
    input.value = nombre; // Para que el input muestre el nombre del producto
    select.style.display = 'none'; // Ocultar el select
    highlightedIndex = -1; // Reiniciar el índice
});

// Navegación con flechas y selección con Enter
let highlightedIndex = -1;
input.addEventListener('keydown', function(event) {
    const options = Array.from(select.options).filter(option => option.style.display === 'block');

    if (event.key === 'ArrowDown') {
        event.preventDefault();
        if (highlightedIndex < options.length - 1) {
            highlightedIndex++;
        }
        updateHighlight(options);
    } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        if (highlightedIndex > 0) {
            highlightedIndex--;
        }
        updateHighlight(options);
    } else if (event.key === 'Enter') {
        event.preventDefault();
        if (highlightedIndex >= 0 && highlightedIndex < options.length) {
            select.value = options[highlightedIndex].value; // Asignar el valor al select
            select.dispatchEvent(new Event('change')); // Despachar evento de cambio
            select.style.display = 'none'; // Ocultar el select
            form.submit(); // Enviar el formulario
        }
    }
});

// Función para actualizar el resaltado de las opciones
function updateHighlight(options) {
    for (let i = 0; i < options.length; i++) {
        options[i].style.backgroundColor = i === highlightedIndex ? '#5bb852' : ''; // Color de resaltado
    }
}

// Ocultar el select si se hace clic fuera de él
document.addEventListener('click', function(event) {
    if (!input.contains(event.target) && !select.contains(event.target)) {
        select.style.display = 'none';
    }
});

// Enviar el formulario cuando se presiona Enter después de seleccionar un producto
form.addEventListener('submit', function(event) {
    const productId = document.getElementById('product_id').value;
    if (!productId) {
        event.preventDefault(); // Prevenir el envío si no hay un ID de producto
        alert('Por favor, selecciona un producto antes de agregar al carrito.'); // Mensaje de error
    } else {
        // Reiniciar el estado después del envío
        input.value = '';
        select.value = ''; // Reiniciar el select
        highlightedIndex = -1; // Reiniciar el índice destacado
        Array.from(select.options).forEach(option => option.style.display = 'block'); // Mostrar todas las opciones
        select.style.display = 'none'; // Asegurarse de que el select esté oculto
    }
});

// Al cargar el documento
document.addEventListener("DOMContentLoaded", function() {
    const productInput = document.getElementById('product_input');
    productInput.focus();  // Enfoca el input
    productInput.select(); // Selecciona el texto en el input
});




</script>
<br><br>