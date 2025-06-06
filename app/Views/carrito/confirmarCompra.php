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
$cart = \Config\Services::cart(); 
$session = session();
$nombre = $session->get('nombre');
$perfil = $session->get('perfil_id');
$cd_efectivo =$session->get('cd_efectivo');

//print_r($session->get());
//exit;
// Inicializar las variables con una cadena vacía
$id_vendedor = '';
$nombre_vendedor = '';
$id_cliente = '';
$nombre_cli = '';
$fecha_pedido = '';
$tipo_compra = '';
$tipo_pago = '';
$id_pedido = '';
$total_venta = 0;
$estado = '';

$id_cliente_cobro = '';
// Asignar valores desde la sesión solo si existen
if ($session->has('id_cliente_pedido')) {
    $id_cliente = $session->get('id_cliente_pedido');
}
if ($session->has('estado')) {
    $estado = $session->get('estado');
}
if ($session->has('id_cliente')) {
    $id_cliente = $session->get('id_cliente');    
}

if ($session->has('nombre_cli')) {
    $nombre_cli = $session->get('nombre_cli');    
}

if ($session->has('nombre_cli_regis')) {
    $nombre_cli = $session->get('nombre_cli_regis');    
}
//$nombre_cli = $session->get('nombre_cli_regis');
if ($session->has('fecha_pedido')) {
    $fecha_pedido = $session->get('fecha_pedido');
}
if ($session->has('tipo_compra')) {
    $tipo_compra = $session->get('tipo_compra');
}
if ($session->has('tipo_pago')) {
    $tipo_pago = $session->get('tipo_pago');
}
if ($session->has('id_pedido')) {
    $id_pedido = $session->get('id_pedido');
}
if ($session->has('id_vendedor')) {
    $id_vendedor = $session->get('id_vendedor');
}
if ($session->has('nombre_vendedor')) {
    $nombre_vendedor = $session->get('nombre_vendedor');
}

//print_r($estado);exit;

if ($session->has('total_venta')) {
    $total_venta = $session->get('total_venta');
}
//print_r($id_pedido);
//exit;
?>
<style>
    .resaltado {
    color: orange;
    border: 2px solid orange;
    padding: 10px;
    display: inline-block;
    border-radius: 5px;
    text-align: center;
}

.contenedor {
    text-align: center;
}



.modal-personalizado {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.6);
}

.modal-contenido {
    background-color: #111;
    margin: 5% auto;
    padding: 20px;
    border: 1px solid #39ff14; /* Borde verde fluor */
    width: 90%;
    max-width: 600px;
    color: #fff;
    border-radius: 10px;
    box-shadow: 0 0 5px #39ff14;
}

.cerrar-modal {
    color:rgb(230, 30, 30);
    float: right;
    font-size: 35px;
    font-weight: bold;
    cursor: pointer;
}

.cerrar-modal:hover {
    color: red;
}

.zoom-in {
    animation: zoomIn 0.5s ease-in-out;
}

@keyframes zoomIn {
    from { transform: scale(0.7); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

.tabla-detalles {
    width: 100%;
    border-collapse: collapse;
    color: #fff;
}

.tabla-detalles th, .tabla-detalles td {
    border: 1px solid #39ff14;
    padding: 8px;
    text-align: center;
}

</style>
<?php
$gran_total = 0;

// Calcula gran total si el carrito tiene elementos
if ($cart):
    foreach ($cart->contents() as $item):
        $gran_total = $gran_total + $item['subtotal'];
    endforeach;
endif;
?>

<div style="width:100%;"align="center">
    <div id="">
        <?php 
        // Crea formulario para guardar los datos de la venta
        echo form_open("confirma_compra", ['class' => 'form-signin', 'role' => 'form']);
        ?>
        <br>
        <div align="center">
            <u><i><h2 align="center" style="color:black; text-shadow: -1px -1px 0 #ffff, 1px -1px 0 #ffff, 
                 -1px 1px 0 #fff, 1px 1px 0 #fff;">Resumen de la Compra</h2></i></u>
            
                <!-- Botón para abrir el modal -->
                <button type="button" class="btn" onclick="abrirModal()">
                    Ver Productos Adquiridos
                </button>          

                <!-- Modal personalizado -->
                <div id="miModal" class="modal-personalizado">
                    <div class="modal-contenido zoom-in">
                        <span class="cerrar-modal" onclick="cerrarModal()">&times;</span>
                        <h2>Detalles del Carrito</h2>

                        <?php if ($cart): ?>
                            <table class="tabla-detalles">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Precio Unitario</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart->contents() as $item): ?>
                                        <tr>
                                            <td><?= esc($item['name']) ?></td>
                                            <td><?= esc($item['qty']) ?></td>
                                            <td>$<?= number_format($item['price'], 2) ?></td>
                                            <td>$<?= number_format($item['price'] * $item['qty'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p>No hay productos en el carrito.</p>
                        <?php endif; ?>
                    </div>
                </div>
           
            <script>
                function abrirModal() {
                    document.getElementById("miModal").style.display = "block";
                }

                function cerrarModal() {
                    document.getElementById("miModal").style.display = "none";
                }
            </script>



                <br>
        <?php if (!empty($id_pedido) && $total_venta == ''): ?>
            <h3 class="resaltado">
                Modificando Pedido Numero: <?php echo htmlspecialchars($id_pedido, ENT_QUOTES, 'UTF-8'); ?>
            </h3>
            <br>
        <?php endif; ?>
            <table style="font-weight: 900;" class="tableResponsive">
            <tr>
                <td style="color:black; text-shadow: -1px -1px 0 #ffff, 1px -1px 0 #ffff, 
                 -1px 1px 0 #fff, 1px 1px 0 #fff;"><strong>Tipo de Compra o Pedido:</strong></td>
                <td>
                <select name="tipo_compra" id="tipoCompra" class="selector" onchange="toggleInputs()">
                <?php if ($estado == 'Cobrando') {  ?>
                    <option value="Compra_Normal" <?php echo $tipo_compra == 'Compra_Normal' ? 'selected' : ''; ?>>
                    <?php echo $estado; ?> -> <?php echo $tipo_compra; ?>
                    </option>
                    <?php } else if ($tipo_compra == 'Compra_Normal') {  ?>
                        <option value="Compra_Normal" <?php echo $tipo_compra == 'Compra_Normal' ? 'selected' : ''; ?>>Compra Normal</option>  
                    <?php } else if ($tipo_compra == 'Pedido') {  ?>
                        <option value="Pedido" <?php echo $tipo_compra == 'Pedido' ? 'selected' : ''; ?>>Reservar Pedido</option>
                    <?php } else {  ?>                    
                        <option value="Compra_Normal" selected>Compra Normal</option>
                        <option value="Pedido">Reservar Pedido</option>
                    <?php } ?>
                </select>
                <?php echo form_hidden('tipo_compra_input', $tipo_compra); ?>
                </td>
            </tr>
            
            <tr>
            <td style="color:black; text-shadow: -1px -1px 0 #ffff, 1px -1px 0 #ffff, 
                 -1px 1px 0 #fff, 1px 1px 0 #fff;"><strong>Total General:</strong></td>
            <td style="color:black; text-shadow: -1px -1px 0 #ffff, 1px -1px 0 #ffff, 
                 -1px 1px 0 #fff, 1px 1px 0 #fff;">
            <strong id="totalCompra">
                $<?php echo number_format(($gran_total > 0 ? $gran_total : $total_venta), 0, '.', '.'); ?>
            </strong>
            </td>
            </tr>
            <tr>
            <td style="color:black; text-shadow: -1px -1px 0 #ffff, 1px -1px 0 #ffff, 
                 -1px 1px 0 #fff, 1px 1px 0 #fff;"><strong>Vendedor:</strong></td>
            <td style="color:black; text-shadow: -1px -1px 0 #ffff, 1px -1px 0 #ffff, 
                 -1px 1px 0 #fff, 1px 1px 0 #fff;">
                <?php echo (!empty($nombre_vendedor) ? $nombre_vendedor : $nombre); ?>

            </td>                      
            </tr>
            <?php if ($nombre_cli != ''): ?><!-- Filtro cajero-->
            <tr>
                <td style="color:black; text-shadow: -1px -1px 0 #ffff, 1px -1px 0 #ffff, 
                 -1px 1px 0 #fff, 1px 1px 0 #fff;"><strong>Nombre Cliente:</strong></td>
                <td style="color:black; text-shadow: -1px -1px 0 #ffff, 1px -1px 0 #ffff, 
                 -1px 1px 0 #fff, 1px 1px 0 #fff;"><strong><?php echo $nombre_cli ?></strong></td>
            </tr>  
            <?php endif; ?>
            <?php if ($perfil): ?><!-- Filtro cajero-->
                <?php if (!$estado): ?>
                <!-- Selector de tipo de cliente -->
                <tr>
                    <td style="color:#000000;">SELECCIONAR TIPO DE CLIENTE:</td>
                    <td>
                        <select id="tipo_cliente" class="selector" onchange="cambiarTipoCliente()">
                            <option value="no_registrado" selected>Cliente NO Registrado</option>
                            <option value="registrado">Cliente Registrado</option>
                        </select>
                    </td>
                </tr>
            <?php endif; ?>
                <!-- Campo para clientes registrados -->
                <tr id="fila_cliente_registrado" style="display: none;">
                    <td style="color:black; text-shadow: -1px -1px 0 #ffff, 1px -1px 0 #ffff, 
                            -1px 1px 0 #fff, 1px 1px 0 #fff;"><strong>Tipo Cliente:</strong></td>
                    <td>
                        <?php if ($clientes): ?>
                            <select name="cliente_id" class="selector">
                                <option value="Anonimo">Consumidor Final</option>
                                <?php foreach ($clientes as $cl): ?>
                                    <option value="<?php echo $cl['id_cliente']; ?>" <?php echo $cl['id_cliente'] == $id_cliente ? 'selected' : ''; ?>>
                                        <?php echo $cl['nombre']; ?> <?php echo "-- DIR:" . $cl['direccion']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <span>No hay clientes disponibles</span>
                        <?php endif; ?>
                    </td>
                </tr>

                <!-- Campo para nombre manual -->
                <?php if ($perfil && $estado == ''): ?>
                <tr id="fila_nombre_manual">
                    <td style="color:black; text-shadow: -1px -1px 0 #ffff, 1px -1px 0 #ffff, 
                            -1px 1px 0 #fff, 1px 1px 0 #fff;"><strong>Nombre Identificador del Cliente:</strong></td>
                    <td>
                        <input class="selector" type="text" name="nombre_prov" placeholder="Ingrese nombre cliente" maxlength="20">
                    </td>
                </tr>
                <?php endif; ?>

                 <?php endif; ?><!-- Fin del if filtro vendedor-->

                 <?php if ($perfil && ($estado == ''  ||  $estado == 'Cobrando')): ?>
                          
                <tr style="display: none;">
                    <td style="color:black;"><strong>Monto en Tarjeta de Crédito</strong></td>
                    <td>
                        <input class="selector" type="text" id="pagoTarjetaCredito" name="pagoTarjetaCredito" placeholder="Monto en $" maxlength="15" oninput="this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1'); formatearMiles(); calcularMontoEfectivo();">
                    </td>
                </tr>
                <tr style="display: none;">
                    <td style="color:black;"><strong>Monto a Cobrar con Tarjeta de Crédito (+10%)</strong></td>
                    <td>
                        <span id="montoTarjetaCreditoAdvertencia" style="color: yellow; font-weight: bold;">$0.00</span>
                    </td>
                </tr>

                <tr id="transferenciaRow">
                    <td style="color:black; text-shadow: -1px -1px 0 #ffff, 1px -1px 0 #ffff, 
                 -1px 1px 0 #fff, 1px 1px 0 #fff;"><strong>Monto en Transferencia:</strong></td>
                    <td>
                        <input class="selector" type="text" id="pagoTransferencia" name="pagoTransferencia" placeholder="Monto en $" maxlength="15" oninput="this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1'); formatearMiles(); calcularMontoEfectivo();">
                    </td>
                </tr>
                
                <tr id="efectivoRow">
                    <td style="color:black; text-shadow: -1px -1px 0 #ffff, 1px -1px 0 #ffff, 
                 -1px 1px 0 #fff, 1px 1px 0 #fff;"><strong>Monto en Efectivo:</strong></td>
                    <td>
                        <input class="selector" type="text" id="pagoEfectivo" name="pagoEfectivo" placeholder="Monto en $" maxlength="15" readonly>
                    </td>
                </tr>
                <?php endif; ?>

                <tr id="fechaPedidoFila" style="display: <?php echo !empty($fecha_pedido) ? 'table-row' : 'none'; ?>;">
                <td style="color:black; text-shadow: -1px -1px 0 #ffff, 1px -1px 0 #ffff, 
                 -1px 1px 0 #fff, 1px 1px 0 #fff;"><strong>Fecha de entrega del Pedido:</strong></td>
                <td>
                    <input class="selector" type="date" name="fecha_pedido" id="fechaPedido" 
                           value="<?php echo !empty($fecha_pedido) ? date('Y-m-d', strtotime($fecha_pedido)) : date('Y-m-d'); ?>" 
                           min="<?php echo date('Y-m-d'); ?>">
                    <?php echo form_hidden('fecha_pedido_input', ''); ?>
                </td>
                </tr>   
                
                <?php if ($estado == '' || $estado == 'Cobrando') {  ?>
                <tr>
                <td style="color:black; text-shadow: -1px -1px 0 #ffff, 1px -1px 0 #ffff, 
                 -1px 1px 0 #fff, 1px 1px 0 #fff;"><strong>Con Envío:</strong></td>
                <td>
                    <select name="con_envio" id="conEnvio" class="selector">
                        <option value="No">No</option>
                        <option value="Si">Sí</option>
                    </select>
                </td>
                </tr>
                <tr id="costoEnvioFila" style="display: none;">
                <td style="color:black;"><strong>Costo de Envío:</strong></td>
                <td>
                    <input class="selector" type="text" id="costoEnvio" name="costoEnvio" placeholder="Costo de envío en $" maxlength="15" oninput="formatearCostoEnvio(this);">
                </td>
                </tr>
                <?php  } ?>

                <?php echo form_hidden('total_venta', ($gran_total > 0 ? $gran_total : $total_venta)); ?>
                <?php echo form_hidden('total_con_descuento', ''); // Campo para el descuento ?>
                
                <br>
            </table>
            <section class="botones-container" style="width:65%;">

            <?php if ($gran_total > 0 || $total_venta > 0) { ?>               
                <a class="btn" href="<?php echo base_url('CarritoList') ?>">Volver</a>
            <?php } else {?>
                <a class="btn" href="<?php echo base_url('catalogo') ?>">Volver a Productos</a>
            <?php } ?>    
            
            <?php if ($total_venta > 0) { ?>
                <a href="<?php echo base_url('cancelarCobro/'.$id_pedido);?>" class="btn danger" onclick="return confirmarAccionC_Cobro();">
                    Cancelar Cobro
                </a>
            <?php } else if ($id_cliente) { ?>
                <a href="<?php echo base_url('cancelar_edicion/'.$id_pedido);?>" class="btn danger" onclick="return confirmarAccionPedido();">
                    Cancelar Modificación Pedido
                </a>
            <?php } else if ($gran_total > 0){ ?>
                <a href="<?php echo base_url('carrito_elimina/all');?>" class="btn danger" onclick="return confirmarAccionCompra();">
                    Cancelar Todo
                </a>
            <?php } ?>
            
            <?php echo form_hidden('id_pedido', $id_pedido); ?>
            <?php echo form_hidden('tipo_proceso', ''); ?>
                
            <?php if ($gran_total > 0 || $total_venta > 0) { ?>
    
                <?php if ($estado == 'Modificando' || $estado == '') { ?>
                    <input type="submit" name="confirmarPerfil2" value="Registrar Pedido" class="btn" id="registrarPedidoBtn">
                <?php } ?>

                <?php if ($estado == 'Cobrando' || $estado == '') { ?>
                    <input type="submit" name="confirmarPerfil3" value="Registrar Venta" class="btn" id="registrarCompraBtn">
                <?php } ?>

            <?php } ?>


            </section>

        </div>
    </div>
    <?php echo form_close(); ?>
</div>

            <!-- Estilos para el input de costo de envio-->
    <!-- Script único con toda la lógica -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Mostrar u ocultar el campo de costo de envío
        const conEnvioSelect = document.getElementById("conEnvio");
        const costoEnvioFila = document.getElementById("costoEnvioFila");

        conEnvioSelect.addEventListener("change", function () {
            if (conEnvioSelect.value === "Si") {
                costoEnvioFila.style.display = "table-row"; // Mostrar el campo
            } else {
                costoEnvioFila.style.display = "none"; // Ocultar el campo
                document.getElementById("costoEnvio").value = ""; // Limpiar el valor
            }
        });
        
        // Ejecutar toggleInputs al cargar la página
        toggleInputs();
    });

    // Función para mostrar/ocultar elementos según el tipo de compra
    function toggleInputs() {
    const tipoCompra = document.getElementById("tipoCompra").value;
    const transferenciaRow = document.getElementById("transferenciaRow");
    const efectivoRow = document.getElementById("efectivoRow");
    const fechaPedidoFila = document.getElementById("fechaPedidoFila");
    const registrarPedidoBtn = document.getElementById("registrarPedidoBtn");
    const registrarCompraBtn = document.getElementById("registrarCompraBtn");
    const estadoModificando = <?php echo json_encode($estado == 'Modificando'); ?>;
    
    if (tipoCompra === "Pedido" || estadoModificando) {
        if (transferenciaRow) transferenciaRow.style.display = "none";
        if (efectivoRow) efectivoRow.style.display = "none";
        fechaPedidoFila.style.display = "table-row";
        registrarPedidoBtn.style.display = "inline-block";
        registrarCompraBtn.style.display = "none";
    } else {
        if (transferenciaRow) transferenciaRow.style.display = "table-row";
        if (efectivoRow) efectivoRow.style.display = "table-row";
        fechaPedidoFila.style.display = "none";
        registrarPedidoBtn.style.display = "none";
        registrarCompraBtn.style.display = "inline-block";
    }
    }

    // Función para formatear el costo de envío
    function formatearCostoEnvio(input) {
        // Eliminar todos los caracteres que no sean números o puntos
        let value = input.value.replace(/[^0-9.]/g, '');

        // Separar la parte entera de los decimales
        let parts = value.split('.');
        let entera = parts[0];
        let decimal = parts.length > 1 ? ',' + parts[1].substring(0, 2) : '';

        // Formatear la parte entera con separadores de miles
        entera = entera.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

        // Unir la parte entera y los decimales
        input.value = entera + decimal;
    }
</script>

            <!-- Esto es para cancelar todo, edicion de pedido o compra normal-->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>

function confirmarAccionC_Cobro() {
        Swal.fire({
            title: "¿Cancelar Cobro?",
            text: "Se cancelara el Cobro de la Venta y esta volvera a la lista de Pendientes.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, Cancelar Cobro",
            cancelButtonText: "No, Seguir Cobrando"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "<?php echo base_url('cancelarCobro/'.$id_pedido); ?>";
            }
        });
        return false; // Evita que el enlace siga su curso normal
    }

    function confirmarAccionCompra() {
        Swal.fire({
            title: "¿Estás seguro?",
            text: "Esto eliminará todos los productos del carrito.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, Eliminar Todo",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "<?php echo base_url('carrito_elimina/all'); ?>";
            }
        });
        return false; // Evita que el enlace siga su curso normal
    }

    
    function confirmarAccionPedido() {
        Swal.fire({
            title: "¿Estás seguro?",
            text: "Se cancelara la modificacion del pedido y quedara como estaba.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, Cancelar",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "<?php echo base_url('cancelar_edicion/'.$id_pedido); ?>";
            }
        });
        return false; // Evita que el enlace siga su curso normal
    }

</script>

<style>
.tableResponsive{
    width: 50%;
    text-align: center;
}
@media screen and (max-width: 768px) {
.tableResponsive{
    width: 100%;
}
}

/*Estilos para los selectores de fecha, cliente y tipo compra*/
.selector {
    width: 85%;
    padding: 8px;
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

/*Estilos para los botones de confirmar, cancelar modif o volver*/
.botones-container {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: center; /* Centra los botones horizontalmente */
    padding: 15px;
}

.btn {
    padding: 10px 20px;
    background-color: #50fa7b;
    color: #282a36;
    text-decoration: none;
    font-size: 16px;
    border-radius: 5px;
    transition: background 0.3s;
    text-align: center;
    display: inline-block;
}

.btn:hover {
    background-color: #8be9fd;
}

.danger {
    background-color: #ff5555;
    color: white;
}

.danger:hover {
    background-color: #ff4444;
}

/* Responsive */
@media (max-width: 600px) {
    .botones-container {
        flex-direction: column;
        align-items: center;
    }

    .btn {
        width: 100%;
        text-align: center;
    }
}

</style>

<?php
// Determina el valor correcto del total de la venta
$totalVenta = ($gran_total > 0) ? $gran_total : $total_venta;
?>

<script>
    // Pasa el valor de PHP a JavaScript
    const granTotal = <?php echo json_encode($totalVenta); ?>;
    const cd_efectivo = <?php echo json_encode($cd_efectivo); ?>;

    document.addEventListener("DOMContentLoaded", function () {
        // Calcula el monto en efectivo con descuento al cargar la página
        calcularMontoEfectivo();

        // Agrega eventos para validar los montos en tiempo real
        const pagoTransferenciaInput = document.getElementById('pagoTransferencia');
        const pagoTarjetaCreditoInput = document.getElementById('pagoTarjetaCredito');

        pagoTransferenciaInput.addEventListener('input', function () {
            validarMontos();
        });

        pagoTarjetaCreditoInput.addEventListener('input', function () {
            validarMontos();
        });
    });

    // Función para validar los montos ingresados
    function validarMontos() {
        const pagoTransferencia = parseFloat(document.getElementById('pagoTransferencia').value.replace(/\./g, '').replace('.', ',')) || 0;
        const pagoTarjetaCredito = parseFloat(document.getElementById('pagoTarjetaCredito').value.replace(/\./g, '').replace('.', ',')) || 0;
        const totalVenta = granTotal;

        // Validar que el monto en transferencia no sea mayor que el total general
        if (pagoTransferencia > totalVenta) {
            alert('El monto en transferencia no puede ser mayor al total general de la venta.');
            document.getElementById('pagoTransferencia').value = ''; // Limpia el campo
            validarMontos(); // Revalida los montos
            return;
        }

        // Validar que el monto en tarjeta de crédito no sea mayor que el total general
        if (pagoTarjetaCredito > totalVenta) {
            alert('El monto en tarjeta de crédito no puede ser mayor al total general de la venta.');
            document.getElementById('pagoTarjetaCredito').value = ''; // Limpia el campo
            validarMontos(); // Revalida los montos
            return;
        }

        // Validar que la suma de transferencia y tarjeta de crédito no sea mayor que el total general
        if (pagoTransferencia + pagoTarjetaCredito > totalVenta) {
            alert('La suma de los montos en transferencia y tarjeta de crédito no puede ser mayor al total general de la venta.');
            document.getElementById('pagoTransferencia').value = ''; // Limpia el campo de transferencia
            document.getElementById('pagoTarjetaCredito').value = ''; // Limpia el campo de tarjeta de crédito
            validarMontos(); // Revalida los montos
            return;
        }

        // Si todo está correcto, calcular el monto en efectivo
        calcularMontoEfectivo();
    }

    // Función para calcular el monto en efectivo con descuento
    function calcularMontoEfectivo() {
        const pagoTransferencia = parseFloat(document.getElementById('pagoTransferencia').value.replace(/\./g, '').replace('.', ',')) || 0;
        const pagoTarjetaCredito = parseFloat(document.getElementById('pagoTarjetaCredito').value.replace(/\./g, '').replace('.', ',')) || 0;
        const totalVenta = granTotal;

        // Calcular el monto a cobrar con tarjeta de crédito (monto ingresado + 10%)
        const montoTarjetaCreditoAdvertencia = pagoTarjetaCredito * 1.1;
        document.getElementById('montoTarjetaCreditoAdvertencia').textContent = `$${montoTarjetaCreditoAdvertencia.toLocaleString('de-DE', { 
            minimumFractionDigits: 2, 
            maximumFractionDigits: 2 
        })}`;

        // Calcular cuánto falta pagar después del pago en transferencia y tarjeta de crédito
        const faltaPagar = totalVenta - pagoTransferencia - pagoTarjetaCredito;

        // Aplicar el descuento del 10% al monto en efectivo
        const montoEfectivoConDescuento = faltaPagar; // Aplica el descuento (/ cd_efectivo)

        // Mostrar el monto en efectivo con descuento
        document.getElementById('pagoEfectivo').value = montoEfectivoConDescuento.toLocaleString('de-DE', { 
            minimumFractionDigits: 0, 
            maximumFractionDigits: 2 
        });

        // Si no se ingresan montos en transferencia o tarjeta de crédito, el monto en efectivo es el total con descuento
        if (pagoTransferencia === 0 && pagoTarjetaCredito === 0) {
            const totalConDescuento = totalVenta; // (/ cd_efectivo)
            document.getElementById('pagoEfectivo').value = totalConDescuento.toLocaleString('de-DE', { 
                minimumFractionDigits: 0, 
                maximumFractionDigits: 2 
            });
        }
    }
</script>


<script>
    document.addEventListener("DOMContentLoaded", function () {
        const tipoCompra = document.getElementById("tipoCompra");
        const fechaPedidoFila = document.getElementById("fechaPedidoFila");

        // Función para mostrar/ocultar el campo de fecha
        function actualizarFechaPedido() {
            if (tipoCompra.value === "Pedido") {
                fechaPedidoFila.style.display = "table-row"; // Muestra el campo de fecha
            } else {
                fechaPedidoFila.style.display = "none"; // Oculta el campo de fecha
            }
        }

        // Ejecuta la función al cargar la página para verificar el valor inicial
        actualizarFechaPedido();

        // Agrega el evento change al select
        tipoCompra.addEventListener("change", function () {
            actualizarFechaPedido();
        });
    });
</script>

<!-- Fondo oscuro -->
<div id="modalFondo" class="modal-fondo"></div>

<!-- Modal de confirmación -->
<div id="modalConfirmacion" class="modal-contenedor">
    <p class="modal-texto">¿QUÉ DESEA HACER.?</p>
    <div class="modal-botones">
        <button type="button" class="btn-modal" onclick="seleccionarProceso('boleta')">BOLETA REMITO</button>
        <button type="button" class="btn-modal" onclick="seleccionarProceso('guardar')">GUARDAR COMPRA</button>
        <button type="button" class="btn-cancelar" onclick="cerrarModalP()">VOLVER</button>
    </div>
</div>
<style>
    .modal-fondo {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 9998;
}

.modal-contenedor {
    display: none;
    position: fixed;
    top: 30%;
    left: 50%;
    transform: translate(-50%, -30%);
    background-color: #ffffff;
    padding: 25px;
    border-radius: 10px;
    border: 2px solid #444;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    z-index: 9999;
    max-width: 300px;
    text-align: center;
}

.modal-texto {
    font-size: 18px;
    margin-bottom: 15px;
    color: #333;
    font-weight:900;
}

.modal-botones {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.btn-modal {
    background-color: #1e90ff;
    color: white;
    font-weight:900;
    padding: 10px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.3s;
    margin-top:10px;
}

.btn-modal:hover {
    background-color: #0d74d1;
}

.btn-cancelar {
    background-color: #aaa;
    color: white;
    font-weight:900;
    margin-top:10px;
    padding: 10px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.3s;
}

.btn-cancelar:hover {
    background-color: #888;
}

</style>


<script>
document.getElementById('registrarCompraBtn').addEventListener('click', function(event) {
    event.preventDefault();
    document.getElementById('modalConfirmacion').style.display = 'block';
    document.getElementById('modalFondo').style.display = 'block';
});

function seleccionarProceso(valor) {
    document.querySelector('input[name="tipo_proceso"]').value = valor;
    cerrarModal();
    document.querySelector('form').submit();
}

function cerrarModalP() {
    document.getElementById('modalConfirmacion').style.display = 'none';
    document.getElementById('modalFondo').style.display = 'none';
}
</script>

<script>
function cambiarTipoCliente() {
    const tipo = document.getElementById("tipo_cliente").value;
    const filaNombre = document.getElementById("fila_nombre_manual");
    const filaRegistrado = document.getElementById("fila_cliente_registrado");

    if (tipo === "registrado") {
        filaNombre.style.display = "none";
        filaRegistrado.style.display = "";
    } else {
        filaNombre.style.display = "";
        filaRegistrado.style.display = "none";
    }
}

// Ejecutar al cargar por si el valor queda en memoria (por ej., al volver atrás)
window.onload = cambiarTipoCliente;
</script>
