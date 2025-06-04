<?php $session = session();
          $nombre= $session->get('nombre');
          $perfil=$session->get('perfil_id');
          $id=$session->get('id');?>  
 <?php if($perfil == 1){  ?>

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


    .botones-acciones {
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
    justify-content: center;
}

.botones-acciones .btn {
    flex-shrink: 0;
}

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
    background-color:rgb(78, 117, 83);
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
    }, 1500); // Se oculta después de 1.5 segundos
</script>
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
 
<section class="contenedor-titulo">
  <strong class="titulo-vidrio">ABM de Productos</strong>
  </section>
  
<div style="width: 100%; text-align: end;">
<div style="position: relative; width: 100%;">
    <br> 
    <!-- Tu contenido actual aquí 
     <?php if($perfil == 1 || $perfil == 3){?>
     <br><br><br><br>                   
     Botón Descontar Defectuosos 
    <a class="btn" href="<?php echo base_url('descontarDefectuosos');?>" style="position: absolute; bottom: 0; right: 0; margin: 20px; color:red; font-weight: 900;">
        Descontar Defectuosos
    </a>
    <?php  } ?> -->
</div>

  <div class="dropdown2" style="margin-right: 45px;">
        <span class="dropdown-toggle2 btn">Mas Opciones▼</span>
        <ul class="dropdown-menu2">
            <li>
            <a class="btn" href="<?php echo base_url('StockBajo');?>">
                    📄 Productos Stock Bajo
                </a>
            </li>
            <li>
                <a class="btn" href="<?php echo base_url('nuevoProducto');?>">
                    📄 Crear Producto
                </a>
            </li>
            <li>
                <a class="btn" href="<?php echo base_url('eliminadosProd');?>">
                    ❌ Eliminados
                </a>
            </li>
                </ul>
    </div>
<br><br>
    <form method="get" action="<?= base_url('Lista_Productos') ?>" class="busqueda-form-derecha">
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




  <div class="mt-3 text">
      <!-- Variables para calcular cuanto hay en $ en mercaderia total -->
  <?php $TotalArticulos= 0; 
        $totalCU = 0;
  ?>
  <!-- Si no se busco nada aun no muestra nada -->
        <?php if (isset($pager)): ?>
  <br>
  <table class="table table-responsive table-hover" id="">
       <thead>
          <tr class="colorTexto">
             <th>Nombre</th>
             <th style="display:none;">Precio Costo</th>
             <th>Precio Venta</th>
             <th>Categoría</th>       
             <th>Stock</th>
             <th>Acciones</th>
          </tr>
       </thead>
       <tbody>
          <?php if($productos): ?>
          <?php foreach($productos as $prod): ?>
            <tr>
             <td style="font-weight:900;"><?php echo $prod['nombre']; ?></td>
             <td style="display:none;">
                    <form method="post" action="<?php echo base_url('/EdicionRapidaProd') ?>">
                    <?php echo form_hidden('page', $page ?? 1); ?>  <!-- Página actual enviada aquí -->
                    <input type="number" step="0.01" name="precio" value="<?php echo number_format($prod['precio'], 0, '.', '.'); ?>" 
                    class="form-control form-control-sm d-inline" style="width: 110px; text-align:center;">
             </td>
             <td>
                <input type="text" 
                    name="precio_vta" 
                    value="<?php echo number_format($prod['precio_vta'], 0, '.', '.'); ?>" 
                    class="form-control form-control-sm d-inline" 
                    style="width: 110px; text-align:center;"
                    oninput="formatearMiles(this)">
                <input type="hidden" name="id_prod" value="<?php echo $prod['id']; ?>">
            </td>

             <?php 
             $categoria_nombre = 'Desconocida';
             foreach ($categorias as $categoria) {
                 if ($categoria['categoria_id'] == $prod['categoria_id']) {
                     $categoria_nombre = $categoria['descripcion'];
                     break;
                 }
             }
             ?>
             <td style="font-weight:900;"><?php echo $categoria_nombre; ?></td>  
            
             
             <td class="text-center">
                <?php if($prod['stock'] <= $prod['stock_min']){ ?>
                    <span class="low-stock-ring">
                        <input type="number" name="stock" value="<?php echo $prod['stock']; ?>" 
                            class="form-control form-control-sm d-inline" style="width: 70px;">
                    </span>
                <?php } else { ?>
                    <input type="number" name="stock" value="<?php echo $prod['stock']; ?>" 
                        class="form-control form-control-sm d-inline" style="width: 70px;">
                <?php } ?>
                
            </td>
             
            <td>
            <div class="botones-acciones">
                <form action="" method="post" style="display:inline;">
                    <button type="submit" class="btn btn-primary">
                        💾 Edit Rápido
                    </button>
                </form>

                <a class="btn btn-outline-warning" href="<?php echo base_url('ProductoEdit/'.$prod['id']); ?>">
                    ✏️ Editar
                </a>

                <a class="btn btn-outline-danger" href="<?php echo base_url('deleteProd/'.$prod['id']); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16">
                        <path d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1H2.5zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5zM8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5zm3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0z"/>
                    </svg> Eliminar
                </a>
            </div>
        </td>

             <?php $totalCU = $prod['precio_vta'] * $prod['stock']; ?>
             <?php $TotalArticulos = $TotalArticulos + $totalCU; ?>
            </tr>
         <?php endforeach; ?>
         <?php endif; ?>

        <div class="paginacion-productos" style="text-align: end; margin-top: 20px;">
            
         <?= $pager->links() ?>

        <?php endif; ?>

        </div>

     </table>
     <h2 class="estiloTurno textColor day">Total en articulos: $ <?php echo $TotalArticulos ?></h2>
     <br>
  </div>
</div>

<script src="<?php echo base_url('./assets/js/jquery-3.5.1.slim.min.js');?>"></script>
<link rel="stylesheet" type="text/css" href="<?php echo base_url('./assets/css/jquery.dataTables.min.css');?>">
<script type="text/javascript" src="<?php echo base_url('./assets/js/jquery.dataTables.min.js');?>"></script>

<script>


  function formatearMiles() {
    const input = document.getElementById('pago');
    let valor = input.value.replace(/\./g, ''); // Quita los puntos
    if (valor === '') {
      input.value = '';
      return;
    }
    valor = parseFloat(valor).toLocaleString('de-DE'); // Agrega el formato de miles con puntos
    input.value = valor;
  }
</script>

<?php }else{ ?>
  <h2>Su perfil no tiene acceso a esta parte,
    Vuelva a alguna seccion de Empleado!
  </h2>
<?php }?>
<br><br>