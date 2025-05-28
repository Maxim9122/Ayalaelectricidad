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
  <div style="text-align:center;">
  <h2 class="textoColor" text-align: center !important; >Lista de Porductos (Para restar Defectuosos)</h2>
  <h3 style="color:orange; margin-top:7px;">Esta Sección es para cuando se hacen cambios de productos defectuosos por otro del mismo pero funcional.</h3>
  
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
    <a class="btn" href="<?php echo base_url('historial_Descuentos');?>">Historial de Descuentos</a>                   
    <?php if($perfil == 3) {?>
    <a class="btn" href="<?php echo base_url('catalogo');?>">Volver</a>
    <?php } else { ?>
    <a class="btn" href="<?php echo base_url('Lista_Productos');?>">Volver</a>
    <?php } ?>
</div>

  <table class="" id="users-list">
   <thead>
      <tr class="colorTexto2">
         <th>Nombre</th>
         <th>Precio Venta</th>
         <th class="ocultar-en-movil">Categoría</th>
         <th>Imagen</th>
         <th>Stock</th>
         <th>Cantidad</th>
         <th>Acciones</th>
      </tr>
   </thead>
   <tbody>
      <?php if($productos): ?>
      <?php foreach($productos as $prod): ?>
      <tr>
         <td><?php echo $prod['nombre']; ?></td>
         <td>$<?php echo $prod['precio_vta']; ?></td>
         <?php 
         $categoria_nombre = 'Desconocida';
         foreach ($categorias as $categoria) {
             if ($categoria['categoria_id'] == $prod['categoria_id']) {
                 $categoria_nombre = $categoria['descripcion'];
                 break;
             }
         }
         ?>
         <td class="ocultar-en-movil"><?php echo $categoria_nombre; ?></td>
         <td><img class="frmImg" src="<?php echo base_url('assets/uploads/'.$prod['imagen']);?>"></td>
         
         <?php if($prod['stock'] <= $prod['stock_min']){ ?>
            <td class="text-center">
                <span class="low-stock-ring"><?php echo $prod['stock']; ?></span>
            </td>
         <?php } else { ?>
                <td class="text-center"><?php echo $prod['stock']; ?></td>
         <?php } ?>

         <td>
            <!-- Campo para ingresar la cantidad -->
            <input oninput="this.value = this.value.replace(/\D/g, ''); if (this.value < 1) this.value = 1;" type="number" id="cantidad_<?php echo $prod['id']; ?>" min="1" max="<?php echo $prod['stock']; ?>" value="1" class="input-cantidad">
         </td>

         <td>
            <?php if($prod['stock'] <= 0){ ?>
               <button class="btn danger" disabled>Sin Stock</button>
            <?php } else if ($session && ($perfil == 2 || $perfil == 1 || $perfil == 3)) { ?>
               
               <!-- Formulario para descontar stock -->
               <?php echo form_open('descontarDelStock', ['class' => 'form-carrito']); ?>
                <?php echo form_hidden('id', $prod['id']); ?> <!-- ID del producto -->
                <?php echo form_hidden('nombre', $prod['nombre']); ?> <!-- Nombre del producto -->
                <?php echo form_hidden('precio_vta', $prod['precio_vta']); ?> <!-- Precio de venta -->
                <input type="hidden" name="cantidad" id="inputCantidad_<?php echo $prod['id']; ?>" value="1"> <!-- Cantidad a descontar -->

                <!-- Campo para el motivo del descuento -->
                <div style="margin-top: 10px;">                    
                    <input class="selector" type="text" name="motivo_desc" id="motivo_desc_<?php echo $prod['id']; ?>" placeholder="Ingrese el motivo descuento" required>
                </div>

                <!-- Botón para descontar -->
                <?php if($perfil == 3 || $perfil == 2 || $perfil == 1) { ?>
                <button type="button" class="btn" style="margin-top:7px; cursor:pointer;" onclick="abrirModal(<?php echo $prod['id']; ?>)">
                    Descontar Defectuoso
                </button>
                <?php } ?>

            <?php echo form_close(); ?>

            <?php } else { ?>
               <input class="margen" id="btnAdvertencia" type="button" onclick="alert('¡Debe registrarse o Logearse para Comprar!')" value="Desea Comprar?" />
            <?php } ?>
         </td>
         
      </tr>
      <?php endforeach; ?>
      <?php endif; ?>
   </tbody>
</table>
     <br>
  </div>
</div>



<script>
//Script para manejo de stock en la tabla con boton Agregar
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".form-carrito").forEach(form => {
        form.addEventListener("submit", function(event) {
            let productId = form.querySelector(".btn-agregar").getAttribute("data-id");
            let cantidadInput = document.getElementById("cantidad_" + productId);
            let cantidadHidden = document.getElementById("inputCantidad_" + productId);
            let stockMax = parseInt(cantidadInput.getAttribute("max"));

            // Verifica que la cantidad no sea mayor al stock
            if (parseInt(cantidadInput.value) > stockMax) {
                alert("No puedes agregar más de " + stockMax + " unidades.");
                cantidadInput.value = stockMax; // Ajusta la cantidad al máximo permitido
                cantidadHidden.value = stockMax;
                event.preventDefault(); // Evita que se envíe el formulario
                return;
            }

            // Actualiza el input hidden antes de enviar
            cantidadHidden.value = cantidadInput.value;
        });
    });
});

</script>

<!-- Modal -->
<div id="modalCodigo" class="modal">
    <div class="modal-contenido">
        <span class="cerrar" onclick="cerrarModal()">&times;</span>
        <h2 style="color: white;">Ingrese Código de Autorización</h2>
        <input type="password" id="codigoInput" placeholder="Ingrese el código">
        <button class="btn-confirmar" onclick="verificarCodigo()">Confirmar</button>
    </div>
</div>

<style>
/* Estilos del Modal */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(53, 51, 51, 0.5);
}

/* Contenedor del Modal */
.modal-contenido {
    background-color: rgba(63, 117, 86, 0.9);
    padding: 20px;
    border-radius: 10px;
    width: 300px;
    text-align: center;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    border: 3px solid white; /* Borde blanco */
}

/* Botón de Cierre */
.cerrar {
    margin-top:-17px;
    margin-right:-10px;
    float: right;
    font-size: 30px;
    cursor: pointer;
    color: red;
    font-weight: bold;
}

/* Estilo del Input */
#codigoInput {
    width: 90%;
    padding: 8px;
    margin: 10px 0;
    border: 2px solid white;
    border-radius: 5px;
    background-color: rgba(255, 255, 255, 0.8);
    font-size: 16px;
    text-align: center;
}

/* Botón Confirmar con efecto 3D */
.btn-confirmar {
    background: linear-gradient(to bottom, #808080 0%, #505050 100%);
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    font-size: 15px;
    cursor: pointer;
    box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.5); /* Efecto 3D */
    transition: all 0.2s ease-in-out;
    font-weight: bold;
}

.btn-confirmar:hover {
    background: linear-gradient(to bottom, #909090 0%, #606060 100%);
    transform: translateY(3px); /* Efecto de presión */
}
</style>


<script>
let productoId = null; // Variable global para el ID del producto

function abrirModal(id) {
    productoId = id; // Guarda el ID del producto
    document.getElementById("modalCodigo").style.display = "block";

    // Limpiar y enfocar el input
    let codigoInput = document.getElementById("codigoInput");
    codigoInput.value = "";
    codigoInput.focus();
}

// Cerrar modal
function cerrarModal() {
    document.getElementById("modalCodigo").style.display = "none";
}

// Detectar "Enter" para verificar el código
document.getElementById("codigoInput").addEventListener("keyup", function(event) {
    if (event.key === "Enter") {
        verificarCodigo();
    }
});

function verificarCodigo() {
    let codigo = document.getElementById("codigoInput").value.trim();

    if (codigo === "") {
        alert("Ingrese un código de autorización.");
        return;
    }

    fetch("<?= site_url('Verif_Codigo_Descuento') ?>", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ codigo: codigo })
    })
    .then(response => response.json())
    .then(data => {
        if (data.valido) {
            document.getElementById("modalCodigo").style.display = "none"; // Cierra el modal

            // Buscar el formulario correcto basado en el productoId
            let form = document.querySelector(`#inputCantidad_${productoId}`).form;

            if (form) {
                // Asegurar que el input cantidad tenga el valor correcto
                let cantidadInput = document.getElementById(`cantidad_${productoId}`);
                let cantidadHidden = document.getElementById(`inputCantidad_${productoId}`);

                if (cantidadInput) {
                    cantidadHidden.value = cantidadInput.value; // Asigna el valor al input hidden
                }

                form.submit(); // Enviar el formulario
            } else {
                alert("Error: No se encontró el formulario para el producto.");
            }
        } else {
            alert("Código incorrecto, no autorizado.");
        }
    })
    .catch(error => {
        console.error("Error en la validación:", error);
    });
}
</script>




<script src="<?php echo base_url('./assets/js/jquery-3.5.1.slim.min.js');?>"></script>
<script src="<?php echo base_url('./assets/js/jquery-ui.js');?>"></script>
<link rel="stylesheet" type="text/css" href="<?php echo base_url('./assets/css/jquery.dataTables.min.css');?>">
<script type="text/javascript" src="<?php echo base_url('./assets/js/jquery.dataTables.min.js');?>"></script>

<script>
$(document).ready(function () {
    // Inicializar DataTables
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