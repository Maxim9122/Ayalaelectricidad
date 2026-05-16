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
  <strong class="titulo-vidrio">Ventas Cuenta Corriente Sin Cobrar</strong>
  </section>
<!-- Variable para la recaudacion -->
<?php $TotalRecaudado = 0;?>

<div class="estiloTurno" style="width: 70%;">
    <form method="GET" action="<?= base_url('Carrito_controller/filtrarVentasCtaCte') ?>">

        <label for="cliente_id" style="color:#ffff;">Cliente:</label>
        <select name="cliente_id" id="cliente_id" class="selector">
            <option value="">-- Todos los clientes --</option>
            <?php foreach ($clientes as $cl): ?>
                <option value="<?= $cl['id_cliente'] ?>" 
                    <?= ($filtros['cliente_id'] ?? '') == $cl['id_cliente'] ? 'selected' : '' ?>>
                    <?= esc($cl['nombre']) ?> -- DIR: <?= esc($cl['direccion']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="fecha_desde" style="color:#ffff;">Desde:</label>
        <input type="date" name="fecha_desde" id="fecha_desde" 
               value="<?= esc($filtros['fecha_desde'] ?? '') ?>">

        <label for="fecha_hasta" style="color:#ffff;">Hasta:</label>
        <input type="date" name="fecha_hasta" id="fecha_hasta" 
               value="<?= esc($filtros['fecha_hasta'] ?? '') ?>">

        <button type="submit" class="btn">Filtrar</button>
        <a href="<?= base_url('Carrito_controller/ListVentasCta_Cte') ?>" class="btn">Ver Todas</a>
    </form> 
</div>

<!-- Recaudacion de Ventas (Todas o por filtro)-->
  
  <br><br>
  <?php $Recaudacion = 0; ?>
  <?php $RecaudadoEfectivo = 0; ?>
  <?php $RecaudadoTransfer = 0; ?>
  <table class="table table-responsive table-hover" id="users-list" style="text-shadow: -1px -1px 0 #000, 1px -1px 0 #000, 
                 -1px 1px 0 #000, 1px 1px 0 #000;">
       <thead>
          <tr class="colorTexto2">
             <th>Nro Venta</th>
             <th>Cliente</th>
             <th>Vendedor</th>
             <th>Tipo Compra</th>
             <th>ESTADO</th>             
             <th class="espaciado">Fecha</th>           
             <th>Total Venta</th>   
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
                        
             <td class="row">               

             <div class="dropdown">
              <span class="dropdown-toggle btn">Acciones▼</span>
               <ul class="dropdown-menu">
            <li>
                <a  style="color: #ffff;" href="<?php echo base_url('DetalleVta/'.$vta['id']); ?>">
                    📄 Ver Detalle
                </a>
            </li>
            <?php if($perfil == 1 && $estado == ''){?>
            <li>
                <a style="color: #ffff;" 
                href="<?php echo base_url('cancelar/'.$vta['id']); ?>" 
                class="text-danger"
                onclick="mostrarConfirmacionCancelar(event, this.href, '<?php echo $vta['id']; ?>');">
                    ❌ Cancelar
                </a>
            </li>
             <li>                
                <a  style="color: #ffff;" href="<?php echo base_url('cargar_cta_cte/'.$vta['id']); ?>">
                    ✏️ Modificar
                </a>
            </li>
                <?php } ?>          
            <li>
                <a  style="color: #ffff;" href="<?php echo base_url('impCta_Cte/'.$vta['id']); ?>">
                    ⬇️ Descargar
                </a>
            </li>
            </li> 
            <li>
                <?php if($perfil && $estado == '') { ?>
                <a  style="color: #ffff;" class="text-success btn" href="<?php echo base_url('cobrarPedido/'.$vta['id']);?>">
                    ✅ Cobrar
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
                
                <?php 
                    //Canculo de monto EFECTIVO
                    if ($vta['estado'] != 'Error_factura' && $vta['estado'] != 'Cancelado') { 
                        $RecaudadoEfectivo += $vta['monto_efectivo']; 
                    }          
                ?>

                <?php 
                    //Canculo de monto ETRANSFERENCIA
                    if ($vta['estado'] != 'Error_factura' && $vta['estado'] != 'Cancelado') { 
                        $RecaudadoTransfer += $vta['monto_transferencia']; 
                    }
                ?>

            </tr>
         <?php endforeach; ?>
         <?php endif; ?>
       
     </table>
     <!-- Recaudacion de Ventas (Todas o por filtro)-->    
     <?php if ($perfil) { ?>
     <h2 class="estiloTurno textColor">Suma Total: $ <?php echo $TotalRecaudado ?></h2>
     <section class="estiloTurno textColor">
     <h2>(No suman las Canceladas ni las que dieron Error_Factura)</h2>
     <h2>Importante.! Si el estado es Modificada_SF y la venta original fue una fecha pasada, solo se suma la Diferencia entre Total Original menos el Total Modificado (Ver Detalles)</h2>
     </section>
     <?php } ?>
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

<script>
function mostrarConfirmacionCancelar(event, url, numeroVenta) {
    event.preventDefault();

    Swal.fire({
        title: '¿Cancelar Cuenta Corriente #' + numeroVenta + '?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'No',
        background: '#1e1e2f',
        color: '#fff'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
}
</script>
<br><br>