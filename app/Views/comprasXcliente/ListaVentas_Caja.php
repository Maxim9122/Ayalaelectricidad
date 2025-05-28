<?php $session = session();
          $nombre= $session->get('nombre');
          $perfil=$session->get('perfil_id');
          $id=$session->get('id');
          $estado=$session->get('estado');
          $cobro = $session->get('total_venta');
          $tipo_compra = $session->get('tipo_venta');
          //print_r($estado);
          //exit;
          ?>
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
<section class="Fondo">
<div class="" style="width: 100%;" align="center">
<section class="contenedor-titulo">
  <strong class="titulo-vidrio">Ventas Pendientes por Cobrar</strong>
  </section>
<!-- Variable para la recaudacion 
<?php $TotalRecaudado = 0;?>

  <div class="estiloTurno" style="width: 70%;">
      <form method="GET" action="<?= base_url('Carrito_controller/filtrarVentas') ?>">
        <label for="fecha_desde" style="color:#ffff;">Desde:</label>
        <input type="date" name="fecha_desde" id="fecha_desde" value="<?= esc($filtros['fecha_desde'] ?? '') ?>">

        <label for="fecha_hasta" style="color:#ffff;">Hasta:</label>
        <input type="date" name="fecha_hasta" id="fecha_hasta" value="<?= esc($filtros['fecha_hasta'] ?? '') ?>">

        <label for="estado" style="color:#ffff;">Estado:</label>
        <select name="estado" id="estado">
            <option value="">Todos</option>
            <option value="Facturada" <?= ($filtros['estado'] ?? '') == 'Facturada' ? 'selected' : '' ?>>Facturada</option>
            <option value="Sin_Facturar" <?= ($filtros['estado'] ?? '') == 'Sin_Facturar' ? 'selected' : '' ?>>Sin_Facturar</option>
            <option value="Error_factura" <?= ($filtros['estado'] ?? '') == 'Error_factura' ? 'selected' : '' ?>>Error_factura</option>
        </select>

          <button type="submit" class="btn">Filtrar</button>
       </form>
        <a class="button" href="<?php echo base_url('compras');?>">
               <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-card-checklist" viewBox="0 0 16 16">
                <path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h13zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-13z"/>
                <path d="M7 5.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0zM7 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 0 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0z"/>
        </svg>TODAS</a>
        </div>

-->



  <div style="text-align: end;">
    
<!-- Recaudacion de Ventas (Todas o por filtro)-->
    <br>
    <?php if($cobro > 0 && $estado == 'Cobrando'){?>
        <a style="color:#ffff; padding:7px;background-color: #375f31; border: 1px solid #ffff; border-radius: 10px; box-shadow: 0 0 3px #375f31, 0 0 7px rgba(85, 218, 136, 0.6);"  href="<?php echo base_url('casiListo'); ?>">
            Cobrar la Venta ya Seleccionada
        </a>
    <?php } ?>
  <br><br>
  <?php $Recaudacion = 0; ?>
  <table class="table table-responsive table-hover" id="users-list">
       <thead>
          <tr class="colorTexto2">
             <th>Nro Venta</th>
             <th style="color:orange">Cliente</th>
             <th>Vendedor</th>
             <th>Tipo Compra</th>
             <th>ESTADO</th>
             <th style="color:orange">Total Venta</th>
             <th>Fecha</th>
             <th>Hora</th>             
             <th>Acciones</th>
          </tr>
       </thead>
       <tbody>
          <?php if($ventas): ?>
          <?php foreach($ventas as $vta): ?>
          <tr>
             <td><?php echo $vta['id']; ?></td>
             <td style="color:orange"><?php echo $vta['nombre_cliente']; ?></td>
             <td><?php echo $vta['nombre_vendedor']; ?></td>
             <td><?php echo $vta['tipo_compra']; ?></td>
             <td><?php echo $vta['estado']; ?></td>
             <td style="color:orange">$<?php echo $vta['total_venta']; ?></td>
             <td><?php echo $vta['fecha'];?></td>
             <td><?php echo $vta['hora']; ?></td>             
             
             <td class="row">               

             <div class="dropdown">
              <span class="dropdown-toggle btn">Acciones▼</span>
               <ul class="dropdown-menu">
               <li>
                <a class="btnDesplegable" style="color:#ffff; background:#3c3d3c; border-radius:10px;" href="<?php echo base_url('DetalleVta/'.$vta['id']);?>">
                    Ver Detalle
                </a>
            </li>
            <li>
                <?php if(($vta['estado'] == 'Pendiente' || $vta['estado'] == 'Modificada_SF') && ($tipo_compra == 'Compra_Normal' || empty($tipo_compra)) ){?>
                    <a href="#" 
                    style="color:#ffff; background-color:#d52c0b;" 
                    class="danger" 
                    onclick="return confirmarAccionCancelar('<?php echo $vta['id']; ?>');">
                        Cancelar
                    </a>                
                    
            </li>
            <li>                          
                <?php  } if ($vta['estado'] == 'Pendiente' && $estado == '') {  ?>
                    <a class="btnDesplegable" style="color:#ffff; background-color: transparent; border: 2px solid #f1775f; border-radius: 10px; box-shadow: 0 0 3px #f1775f, 0 0 15px rgba(241, 119, 95, 0.6);" href="<?php echo base_url('modificarVenta/'.$vta['id']);?>">
                    Modificar
                    </a>

            </li>
            <li>        
                <?php } if (($vta['estado'] == 'Pendiente' || $vta['estado'] == 'Modificada_SF') && $estado == '') { ?>
                    <a class="btnDesplegable" style="color:#ffff; background-color:#467c62; border-radius:10px; padding:8px; text-align: center;" href="<?php echo base_url('cargarVenta/'.$vta['id']); ?>">
                    ✅COBRAR
                    </a>
                <?php } ?> 
                 </li>                                  
                    </ul>
                </div>

              </td>
              <?php if($vta['estado'] != 'Error_factura'){?>
              <?php $TotalRecaudado = $TotalRecaudado + $vta['total_bonificado']; ?>
              <?php } ?> 
            </tr>
         <?php endforeach; ?>
         <?php endif; ?>
       
     </table>
     <!-- Recaudacion de Ventas (Todas o por filtro)
     <h2 class="estiloTurno textColor">Total Recaudado: $ <?php echo $TotalRecaudado ?> (Excluyendo las que dieron Error al Facturar)</h2>
     -->
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
        $('#users-list_filter input').attr('placeholder', 'Nro Venta,cliente,estado,vendedor..');
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
</script>



<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmarAccionCancelar(idVenta) {
        Swal.fire({
            title: "¿Estás seguro?",
            text: "Esto eliminará la Venta y devolverá los productos.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, Cancelar Venta",
            cancelButtonText: "No, Volver"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "<?php echo base_url('cancelarVenta'); ?>/" + idVenta;
            }
        });

        return false; // Evita que el enlace siga su curso normal
    }
</script>




<br><br>