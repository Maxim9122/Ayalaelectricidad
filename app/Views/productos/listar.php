<?php $session = session();
          $nombre= $session->get('nombre');
          $perfil=$session->get('perfil_id');
          $id=$session->get('id');
          $estado =$session->get('estado');
          $cd_efectivo =$session->get('cd_efectivo');                  
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
    
    table {
  width: 100%;
  border-collapse: collapse;
  font-family: 'Segoe UI', Arial, sans-serif;
  font-weight: bold;
  color: #333;
  padding:125px;
  text-align:center;
}

/* Encabezado */
thead {
  background-color: #f0f0f0;
}

tbody tr:hover {
  background-color:rgb(132, 160, 192) !important; /* Color de hover */
  cursor: pointer;
}

thead th {
  padding: 3px;
  border: 1px solid #ccc;
  text-align: left;
}

/* Filas y celdas */
tbody tr {
  background-color: #fff;
  border-bottom: 1px solid #ccc;
}

tbody tr:nth-child(even) {
  background-color: #f9f9f9;
}

tbody td {
  padding: 3px;
  border: 1px solid #ccc;
}

/* RESPONSIVE: formato tipo lista en pantallas pequeñas */
@media (max-width: 768px) {
  table.responsive-card,
  table.responsive-card thead,
  table.responsive-card tbody,
  table.responsive-card th,
  table.responsive-card td,
  table.responsive-card tr {
    display: block;
  }

  table.responsive-card thead {
    display: none;
  }

  table.responsive-card tbody tr {
    margin-bottom: 3px;
    border: 1px solid #ccc;
    border-radius: 8px;
    padding: 1px;
    background-color: #000;
  }

  table.responsive-card tbody td {
    border: none;
    padding: 1px 3px;
    position: relative;
    font-weight: 900;
  }

  table.responsive-card tbody td::before {
    content: attr(data-label);
    position: absolute;
    left: 10px;
    top: 8px;
    font-weight: 900;
    color: #666;
    font-size: 0.9em;
  }
}


.busqueda-form-derecha {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-bottom: 20px;
    margin-right: 5px;
    margin-top:10px;
    flex-wrap: wrap;
}

.busqueda-input {
    padding: 10px 15px;
    font-size: 16px;
    border: 1px solid #ccc;
    border-radius: 8px;
    flex: 1 1 250px;
    max-width: 400px;
    font-family: 'Segoe UI', sans-serif;
}

.busqueda-btn {
    padding: 10px 20px;
    background-color:rgb(88, 87, 87);
    color: white;
    font-weight: bold;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-family: 'Segoe UI', sans-serif;
    transition: background-color 0.3s ease;
}

.busqueda-btn:hover {
    background-color:rgb(100, 172, 100);
}

@media (max-width: 600px) {
    .busqueda-form-derecha {
        flex-direction: column;
        align-items: flex-end;
    }

    .busqueda-input {
        width: auto;
        min-width: 200px;
        max-width: 100%;
        flex: none;
    }

    .busqueda-btn {
        width: auto;
    }
}



</style>
<script>
    setTimeout(function() {
        let msg = document.getElementById('msg_stock');
        if (msg) {
            msg.style.display = 'none';
        }
    }, 1000); // Se oculta después de 1.5 segundos
</script>

<?php if (session()->getFlashdata('msg')): ?>
        <div id="flash-message" class="flash-message success">
            <?= session()->getFlashdata('msg') ?>
        </div>
    <?php endif; ?>   
    <script>
        setTimeout(function() {
            document.getElementById('flash-message').style.display = 'none';
        }, 1000); // 1000 milisegundos = 1 segundos
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
  <h2 class="textoColor" text-align: center !important;>Carrito de Productos</h2>
  <br>
  
  <style>
   .paginacion-productos .pagination {
    display: flex;
    justify-content: center;
    list-style: none;
    padding: 0;
}

.paginacion-productos .pagination li {
    margin: 10px 5px;
}

.paginacion-productos .pagination li a,
.paginacion-productos .pagination li span {
    display: inline-block;
    padding: 8px 12px;
    background-color: #000;
    color: #fff;
    text-decoration: none;
    border-radius: 4px;
    border: 1px solid #ff073a;
}

/* Página actual seleccionada */
.paginacion-productos .pagination li.active a,
.paginacion-productos .pagination li.active span {
    background-color: #ff073a;
    color: white;
    font-weight: bold;
    border-bottom: 4px solid white;
}

    
</style>


 <!-- <section class="buscador"> 
  
  <form id="product_form" action="<?php echo base_url('Carrito_agrega'); ?>" method="post">
  <label style="color: white; font-weight: bold;"><h1>Codigo de Barra</h1></label>

  <button type="submit" class="success" style="display: none;">Codigo de Barra</button>
  <br>
    <div style="position: relative; display: inline-block;">
    <input oninput="this.value = this.value.replace(/\D/g, '')"
       type="text"
       id="product_input"
       placeholder="Agregar producto por codigo de barra..."
       autocomplete="off"
       required />
        <input type="hidden" id="cantidad" name="cantidad">
        <select id="product_select" name="product_id" required size="3">
            <option class="separador">Seleccione un Producto!</option> 
            <?php if ($productos): ?>
                
                <?php foreach ($productos as $prod): ?>
                    <?php if ($prod['stock'] != 0) { ?>
                        <option class="product-option" 
                                value="<?php echo $prod['id']; ?>" 
                                data-nombre="<?php echo $prod['nombre']; ?>" 
                                data-precio="<?php echo $prod['precio_vta']; ?>" 
                                data-stock="<?php echo $prod['stock']; ?>">  
                            <?php echo $prod['codigo_barra']; ?>
                        </option>
                    <?php } ?>
                <?php endforeach; ?>

                
            <?php endif; ?>
        </select>
        <input type="hidden" name="nombre" id="nombre">
        <input type="hidden" name="precio_vta" id="precio_vta">
        <input type="hidden" name="id" id="product_id">
        <input type="hidden" name="stock" id="producto_stock">
    </div>
</form>
    </section>  -->

    <div style="position: relative; width: 100%;">

    <form method="get" action="<?= base_url('catalogo') ?>" class="busqueda-form-derecha">
    <?php $request = \Config\Services::request(); ?>
    <input type="text" name="search" value="<?= esc($request->getGet('search')) ?>" placeholder="Buscar productos..." class="busqueda-input" autofocus>
    <button type="submit" class="busqueda-btn">Buscar</button>
    </form>
    <script>
    window.addEventListener('DOMContentLoaded', function() {
        const input = document.querySelector('.busqueda-input');
        if (input) {
            input.focus();
            input.setSelectionRange(input.value.length, input.value.length); // Opcional: pone el cursor al final
        }
    });
    </script>


    <!-- Tu contenido actual aquí
     <?php if($perfil == 1 || $perfil == 3){?>
     <br><br><br>                   
     Botón Descontar Defectuosos
    <a class="btn" href="<?php echo base_url('descontarDefectuosos');?>" style="position: absolute; bottom: 0; right: 0; margin: 20px; color:red; font-weight: 900;">
        Descontar Defectuosos
    </a>
    <?php  } ?> -->
</div>

<?php if (isset($pager)): ?>

  <table class="" id="" style="color:black; font-weight:900;">
   <thead>
      <tr style="color:black;">
         <th style="text-align:center; font-weight:900; background:#92beea;">Nombre</th>
         <th style="text-align:center; font-weight:900; background:#92beea;">Precio Venta</th>        
         <th class="ocultar-en-movil" style="text-align:center; font-weight:900; background:#92beea;">Categoría</th>        
         <th style="text-align:center; font-weight:900; background:#92beea;">Stock</th>
         <th style="text-align:center; font-weight:900; background:#92beea;">Cantidad</th>
         <th style="text-align:center; font-weight:900; background:#92beea;">Acciones</th>
      </tr>
   </thead>
   <tbody>
      <?php if($productos): ?>
      <?php foreach($productos as $prod): ?>
      <tr>
         <td><?php echo $prod['nombre']; ?></td>
         <td>$ <?php echo number_format($prod['precio_vta'], 0, '.', '.'); ?></td>         
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
               
               <!-- Formulario para agregar al carrito -->
               <?php echo form_open('Carrito_agrega', ['class' => 'form-carrito']); ?>
               <?php echo form_hidden('id', $prod['id']); ?>
               <?php echo form_hidden('nombre', $prod['nombre']); ?>
               <?php echo form_hidden('precio_vta', $prod['precio_vta']); ?>
               <?php echo form_hidden('page', $page ?? 1); ?>  <!-- Página actual enviada aquí -->
               
               <input type="hidden" name="cantidad" id="inputCantidad_<?php echo $prod['id']; ?>" value="1">
               <?php if($perfil || $estado == 'Modificando' || $estado == 'Modificando_SF') {?>
               <button type="submit" class="btn btn-agregar" data-id="<?php echo $prod['id']; ?>">Agregar</button>
               <?php  } ?>
               <?php echo form_close(); ?>

            <?php } else { ?>
               <input class="margen" id="btnAdvertencia" type="button" onclick="alert('¡Debe registrarse o Logearse para Comprar!')" value="Desea Comprar?" />
            <?php } ?>
         </td>
         
      </tr>
      <?php endforeach; ?>
      <?php endif; ?>   

   </tbody>
   <div class="paginacion-productos" style="text-align: end; margin-top: 20px;">
    
        <?= $pager->links() ?>

    <?php endif; ?>

    </div>

</table>
     
  </div>
</div>


<!-- Modal de Confirmación -->
<div id="confirmModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); justify-content: center; align-items: center;">
    <div style="color:white; background: black; padding: 20px; border-radius: 10px; text-align: center;">
        <h2 id="modal_product_name"></h2>
        <br>
        <p>Stock Disponible: <span id="modal_product_stock"></span></p>
        <br>
        <label for="modal_quantity" style="color:white;">Cantidad:</label>
        <input type="number" id="modal_quantity" min="1" required maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
        <br><br>
        <button id="confirm_add" class="btn">Agregar</button>
        <button id="cancel_add" class="btn">Cancelar</button>
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


<script>
const input = document.getElementById('product_input');
const select = document.getElementById('product_select');
const form = document.getElementById('product_form');

// Elementos del modal
const modal = document.getElementById('confirmModal');
const modalProductName = document.getElementById('modal_product_name');
const modalProductStock = document.getElementById('modal_product_stock');
const modalQuantity = document.getElementById('modal_quantity');
const confirmAdd = document.getElementById('confirm_add');
const cancelAdd = document.getElementById('cancel_add');

let selectedProduct = {}; // Almacena temporalmente los datos del producto

// Detectar cuando se ingresa un código de barras completo (con Enter)
input.addEventListener('keypress', function(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
        buscarProductoPorCodigo(input.value.trim());
    }
});

// Función para buscar el producto por código de barras
function buscarProductoPorCodigo(codigo) {
    const options = select.options;
    let productoEncontrado = false;

    for (let i = 1; i < options.length; i++) { // Saltar la primera opción ("Seleccione un Producto!")
        if (options[i].text.trim() === codigo) {
            productoEncontrado = true;
            select.selectedIndex = i;

            selectedProduct = {
                id: options[i].value,
                nombre: options[i].getAttribute('data-nombre'),
                precio: options[i].getAttribute('data-precio'),
                stock: parseInt(options[i].getAttribute('data-stock'))
            };

            // Mostrar el modal con la información del producto
            modalProductName.textContent = selectedProduct.nombre;
            modalProductStock.textContent = selectedProduct.stock;
            modalQuantity.value = 1; // Iniciar cantidad en 1
            modal.style.display = 'flex';
            modalQuantity.focus(); // Enfocar el input de cantidad

            break; // Salimos del bucle ya que encontramos el producto
        }
    }

    if (!productoEncontrado) {
        alert('Producto no encontrado. Verifica el código de barras.');
        input.value = ''; // Limpiar input para un nuevo intento
    }
}

// Confirmar la adición del producto al carrito
confirmAdd.addEventListener('click', function() {
    const cantidad = parseInt(modalQuantity.value);

    if (cantidad > 0 && cantidad <= selectedProduct.stock) {
        // Asignar los valores al formulario
        document.getElementById('nombre').value = selectedProduct.nombre;
        document.getElementById('precio_vta').value = selectedProduct.precio;
        document.getElementById('product_id').value = selectedProduct.id;
        document.getElementById('cantidad').value = cantidad; // Guardar cantidad seleccionada en el campo correcto

        modal.style.display = 'none'; // Ocultar el modal
        form.submit(); // Enviar formulario
    } else {
        alert('Cantidad inválida o insuficiente.');
    }
});

// Cancelar la operación y cerrar el modal
cancelAdd.addEventListener('click', function() {
    modal.style.display = 'none'; // Ocultar modal
    input.value = ''; // Limpiar el input para volver a escanear
    input.focus();
});

// Cerrar modal con tecla ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        modal.style.display = 'none';
        input.value = '';
        input.focus();
    } else if (event.key === 'Enter' && modal.style.display === 'flex') {
        confirmAdd.click(); // Simular clic en "Agregar"
    }
});

// Enfocar el input al cargar la página
document.addEventListener("DOMContentLoaded", function() {
    input.focus();
});

</script>

<br><br>