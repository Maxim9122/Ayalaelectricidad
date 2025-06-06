<?php $session = session();
          $nombre= $session->get('nombre');
          $perfil=$session->get('perfil_id');
          $id=$session->get('id');
          $estado =$session->get('estado');
          ?>
          <?php if (session()->getFlashdata('msg')): ?>
        <div id="flash-message" class="flash-message success">
            <?= session()->getFlashdata('msg') ?>
        </div>
    <?php endif; ?> 

    <?php if (session("msgEr")): ?>
    <div id="flash-message-Error" class="flash-message danger">
        <?php echo session("msgEr"); ?>
        <button class="close-btn" onclick="cerrarMensaje()">×</button>
    </div>
    <?php endif; ?>  
    <script>
        setTimeout(function() {
            document.getElementById('flash-message').style.display = 'none';
        }, 3000); // 3000 milisegundos = 3 segundos

        function cerrarMensaje() {
        document.getElementById("flash-message-Error").style.display = "none";
        }
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

    .espaciado {
    padding: 0 7px;
    }

    </style>
<section class="Fondo">
<div class="" style="width: 100%;" align="center">
<section class="contenedor-titulo">
  <strong class="titulo-vidrio">Ventas Normales y Pedidos (Facturada o No)</strong>
  </section>
<!-- Variable para la recaudacion -->
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
            <option value="Modificada_SF" <?= ($filtros['estado'] ?? '') == 'Modificada_SF' ? 'selected' : '' ?>>Modificada_SF</option>
            <option value="Error_factura" <?= ($filtros['estado'] ?? '') == 'Error_factura' ? 'selected' : '' ?>>Error_factura</option>
            <option value="Cancelado" <?= ($filtros['estado'] ?? '') == 'Cancelado' ? 'selected' : '' ?>>Cancelada</option>
        </select>

          <button type="submit" class="btn">Filtrar</button>
       </form>
        <a class="button" href="<?php echo base_url('compras');?>">
               <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-card-checklist" viewBox="0 0 16 16">
                <path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h13zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-13z"/>
                <path d="M7 5.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0zM7 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 0 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0z"/>
        </svg>TODAS</a>
        </div>





  <div style="text-align: end;">
<!-- Recaudacion de Ventas (Todas o por filtro)-->
  
  <br><br>
  <?php $Recaudacion = 0; ?>
  <table class="table table-responsive table-hover" id="users-list" style="text-shadow: -1px -1px 0 #000, 1px -1px 0 #000, 
                 -1px 1px 0 #000, 1px 1px 0 #000;">
       <thead>
          <tr class="colorTexto2">
             <th>Nro Venta</th>
             <th>Cliente</th>
             <th>Vendedor</th>
             <th>Tipo Compra</th>
             <th>ESTADO</th>             
             <th class="espaciado">Fecha Cobro/Original</th>           
            <th class="espaciado" style="color:orange;">Fecha Cobro/Modif</th>
            <th class="espaciado" style="color:orange;">Hora Cobro/Modif</th>
            <th>Total Venta</th>
             <th>Tipo Pago</th>
             <th>Acciones</th>
          </tr>
       </thead>
       <tbody>
          <?php if($ventas): ?>
          <?php foreach($ventas as $vta): ?>
          <tr>
          <td><?php echo $vta['id']; ?></td>
            <td><?php echo $vta['nombre_cliente']; ?></td>
            <td><?php echo $vta['nombre_vendedor']; ?></td>
            <td><?php echo $vta['tipo_compra']; ?></td>
            <td style="background-color: <?php
                if ($vta['estado'] == 'Cancelado') {
                    echo 'red';
                } elseif ($vta['estado'] == 'Sin_Facturar' || $vta['estado'] == 'Facturada') {
                    echo 'green';
                } elseif ($vta['estado'] == 'Modificada_SF') {
                    echo 'orange';
                } else {
                    echo 'transparent'; // Fondo transparente si no coincide con ninguna condición
                }
            ?>; color: #ffff;"><?php echo $vta['estado']; ?></td>
            <td><?php echo $vta['fecha_original']; ?></td>            
            <td style="color:orange;"><?php echo $vta['fecha_actual']; ?></td>
            <td style="color:orange;"><?php echo $vta['hora_actual']; ?></td>
            <td style="background-color: <?php
                if ($vta['estado'] == 'Cancelado') {
                    echo 'red';
                } elseif ($vta['estado'] == 'Sin_Facturar' || $vta['estado'] == 'Facturada') {
                    echo 'green';
                } elseif ($vta['estado'] == 'Modificada_SF') {
                    echo 'orange';
                } else {
                    echo 'transparent'; // Fondo transparente si no coincide con ninguna condición
                }
            ?>; color: #ffff;">$<?php echo number_format($vta['total_bonificado'], 0, '.', '.'); ?></td>
            <td><?php echo $vta['tipo_pago']; ?></td>
                        
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
                <?php if($vta['estado'] == 'Facturada'){?>
                    <a class="btnDesplegable" style="color:#ffff; background:#3c3d3c; border-radius:10px; padding:8px;" href="<?php echo base_url('generarTicketFacturaC/'.$vta['id']); ?>">
                        Imp.Factura
                    </a>
            </li>
            <li>      
                <?php  } if($vta['estado'] == 'Sin_Facturar' || $vta['estado'] == 'Modificada_SF'){  ?>
                    <a class="btnDesplegable" style="color:#ffff; background:#3c3d3c; border-radius:10px;  padding:8px;" href="<?php echo base_url('generarTicket/'.$vta['id']); ?>">
                        Imp.Boleta
                    </a>
                </li>
                <?php if($estado == '' && $perfil) {?>
                    <li>                
                    <a class="btnDesplegable" style="color:#ffff; background:#3c3d3c; border-radius:10px; padding:8px;" 
                    href="#" onclick="abrirModal('<?php echo base_url('modificarVenta_SF/'.$vta['id']); ?>'); return false;">
                        Modificar
                    </a>                
                    </li>

                    <li>                
                    <a class="btnDesplegable" style="color:#ffff; background:#3c3d3c; border-radius:10px; padding:8px;" 
                    href="#" onclick="abrirModalCancelar('<?php echo base_url('cancelarVenta/'.$vta['id']); ?>'); return false;">
                        CancelarVta
                    </a>                
                    </li>

                <?php  } ?>
            <li>      
                <?php } if($vta['estado'] == 'Error_factura') { ?>
                    <a class="btnDesplegable" style="color:#ffff; background:#3c3d3c; border-radius:10px; padding:8px;" href="<?php echo base_url('verificarTA/'.$vta['id']); ?>">
                        Re.Facturar
                    </a>
                <?php } ?> 
            </li>                                  
                    </ul>
                </div>

              </td>
              <?php 
                if ($vta['estado'] != 'Error_factura' && $vta['estado'] != 'Cancelado' && $vta['estado'] != 'Modificada_SF') { 
                    $TotalRecaudado += $vta['total_bonificado']; 
                } 

                if ($vta['estado'] == 'Modificada_SF') { 
                    if ($vta['fecha_original'] == $vta['fecha_actual']) { 
                        // Si las fechas coinciden, sumar todo el total bonificado
                        $TotalRecaudado += $vta['total_bonificado']; 
                    } else { 
                        // Si las fechas no coinciden, sumar solo la diferencia
                        $TotalRecaudado += ($vta['total_bonificado'] - $vta['total_anterior']); 
                    } 
                } 
                ?>

            </tr>
         <?php endforeach; ?>
         <?php endif; ?>
       
     </table>
     <!-- Recaudacion de Ventas (Todas o por filtro)-->      
     <h2 class="estiloTurno textColor">Total Recaudado: $ <?php echo $TotalRecaudado ?></h2>
     <section class="estiloTurno textColor">
     <h2>(No suman las Canceladas ni las que dieron Error_Factura)</h2>
     <h2>Importante.! Si el estado es Modificada_SF y la venta original fue una fecha pasada, solo se suma la Diferencia entre Total Original menos el Total Modificado (Ver Detalles)</h2>
     </section>
     <br>
  </div>
</div>
</section>

<!-- Modal para CancelarVta -->
<div id="modalCancelar" class="modal">
    <div class="modal-contenido">
        <span class="cerrar" onclick="cerrarModalCancelar()">&times;</span>
        <h2 style="color: white;">Ingrese Código de Autorización.</h2>
        <h2 style="color: orange;">Los productos seran devueltos al Stock y si hay DEFECTUOSOS descontarlos desde el Panel.</h2>
        <input type="password" id="codigoInputCancelar" placeholder="Ingrese el código">
        <button class="btn-confirmar" onclick="verificarCodigoCancelar()">Confirmar</button>
    </div>
</div>
<script>
    let urlRedireccion = ""; // Para almacenar la URL de redirección
let urlRedireccionCancelar = ""; // Para almacenar la URL de redirección de CancelarVta

// Funciones para el modal de Modificar
function abrirModal(url) {
    urlRedireccion = url; // Guarda la URL a la que se redirigirá después
    document.getElementById("modalCodigo").style.display = "block";
    document.getElementById("codigoInput").value = ""; // Limpia el input
    document.getElementById("codigoInput").focus(); // Enfoca el campo de entrada
}

function cerrarModal() {
    document.getElementById("modalCodigo").style.display = "none";
}

function verificarCodigo() {
    const codigoIngresado = document.getElementById("codigoInput").value.trim();

    fetch("<?= base_url('verificar-codigo') ?>", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `codigo=${codigoIngresado}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = urlRedireccion; // Redirige si el código es correcto
        } else {
            alert(data.message); // Mensaje desde el backend
        }
    })
    .catch(error => console.error("Error en la verificación:", error));
}

// Funciones para el modal de CancelarVta
function abrirModalCancelar(url) {
    urlRedireccionCancelar = url; // Guarda la URL a la que se redirigirá después
    document.getElementById("modalCancelar").style.display = "block";
    document.getElementById("codigoInputCancelar").value = ""; // Limpia el input
    document.getElementById("codigoInputCancelar").focus(); // Enfoca el campo de entrada
}

function cerrarModalCancelar() {
    document.getElementById("modalCancelar").style.display = "none";
}

function verificarCodigoCancelar() {
    const codigoIngresado = document.getElementById("codigoInputCancelar").value.trim();

    fetch("<?= base_url('verificar-codigo') ?>", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `codigo=${codigoIngresado}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = urlRedireccionCancelar; // Redirige si el código es correcto
        } else {
            alert(data.message); // Mensaje desde el backend
        }
    })
    .catch(error => console.error("Error en la verificación:", error));
}

// Permitir que presionar "Enter" envíe el código automáticamente
document.getElementById("codigoInput").addEventListener("keyup", function(event) {
    if (event.key === "Enter") { 
        verificarCodigo();
    }
});

document.getElementById("codigoInputCancelar").addEventListener("keyup", function(event) {
    if (event.key === "Enter") { 
        verificarCodigoCancelar();
    }
});

// Cerrar modales al hacer clic fuera del contenido
window.onclick = function(event) {
    const modal = document.getElementById("modalCodigo");
    const modalCancelar = document.getElementById("modalCancelar");
    if (event.target === modal) {
        cerrarModal();
    }
    if (event.target === modalCancelar) {
        cerrarModalCancelar();
    }
};
</script>
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
    margin-top: -17px;
    margin-right: -10px;
    float: right;
    font-size: 30px;
    cursor: pointer;
    color: red;
    font-weight: bold;
}

/* Estilo del Input */
#codigoInput, #codigoInputCancelar {
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
let urlRedireccion = ""; // Para almacenar la URL de redirección

function abrirModal(url) {
    urlRedireccion = url; // Guarda la URL a la que se redirigirá después
    document.getElementById("modalCodigo").style.display = "block";
    document.getElementById("codigoInput").value = ""; // Limpia el input
    document.getElementById("codigoInput").focus(); // Enfoca el campo de entrada
}

function cerrarModal() {
    document.getElementById("modalCodigo").style.display = "none";
}

function verificarCodigo() {
    const codigoIngresado = document.getElementById("codigoInput").value.trim();

    fetch("<?= base_url('verificar-codigo') ?>", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `codigo=${codigoIngresado}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = urlRedireccion; // Redirige si el código es correcto
        } else {
            alert(data.message); // Mensaje desde el backend
        }
    })
    .catch(error => console.error("Error en la verificación:", error));
}

// Permitir que presionar "Enter" envíe el código automáticamente
document.getElementById("codigoInput").addEventListener("keyup", function(event) {
    if (event.key === "Enter") { 
        verificarCodigo();
    }
});

// Cerrar modal al hacer clic fuera del contenido
window.onclick = function(event) {
    const modal = document.getElementById("modalCodigo");
    if (event.target === modal) {
        cerrarModal();
    }
};
</script>




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
        "order": [[0, "desc"]], // Ordenar por la primera columna de forma descendente
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


<br><br>