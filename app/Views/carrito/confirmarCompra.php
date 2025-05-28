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

//print_r($nombre_cli);

//exit;
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
            <u><i><h2 align="center">Resumen de la Compra</h2></i></u>

            <?php if($estado == 'Cobrando'){ ?>
                <!-- Botón para abrir el modal -->
                <button type="button" class="btn-ver-detalles" onclick="abrirModal()">
                    Ver Productos Adquiridos
                </button>

                <!-- Modal personalizado con animación de zoom -->
                <div id="miModal" class="modal-personalizado">
                    <div class="modal-contenido zoom-in">
                        <span class="cerrar-modal" onclick="cerrarModal()">&times;</span>
                        <h2 style="color:black;">Detalles de la Compra</h2>
                        <?php if (!empty($ventas)): ?>
                            <table class="tabla-detalles" style="color:black;">
                                <thead>
                                    <tr>
                                        <th style="color:black;">Producto</th>
                                        <th style="color:black;">Cantidad</th>
                                        <th style="color:black;">Precio Unitario</th>
                                        <th style="color:black;">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ventas as $venta): ?>
                                        <tr>
                                            <td><?= $venta['nombre'] ?></td>
                                            <td><?= $venta['cantidad'] ?></td>
                                            <td><?= number_format($venta['precio'], 2) ?></td>
                                            <td><?= number_format($venta['precio'] * $venta['cantidad'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p>No hay detalles de venta disponibles.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php } ?>

                <br>
        <?php if (!empty($id_pedido) && $total_venta == ''): ?>
            <h3 class="resaltado">
                Modificando Pedido Numero: <?php echo htmlspecialchars($id_pedido, ENT_QUOTES, 'UTF-8'); ?>
            </h3>
            <br>
        <?php endif; ?>
            <table style="font-weight: 900;" class="tableResponsive">
            <tr>
            <td style="color:rgb(192, 250, 214);"><strong>Total General:</strong></td>
            <td style="color: #ffff;">
            <strong id="totalCompra">
                $<?php echo number_format(($gran_total > 0 ? $gran_total : $total_venta), 2, '.', ','); ?>
            </strong>
            </td>
            </tr>
            <tr>
            <td style="color:rgb(192, 250, 214);"><strong>Vendedor:</strong></td>
            <td style="color: #ffff;">
                <?php echo (!empty($nombre_vendedor) ? $nombre_vendedor : $nombre); ?>

            </td>                      
            </tr>
            <?php if ($nombre_cli != ''): ?><!-- Filtro cajero-->
            <tr>
                <td style="color:rgb(192, 250, 214);"><strong>Nombre Cliente:</strong></td>
                <td style="color:#ffff;"><strong><?php echo $nombre_cli ?></strong></td>
            </tr>  
            <?php endif; ?>
            <?php if ($perfil == 3): ?><!-- Filtro cajero-->

                <tr>
                <td style="color:rgb(192, 250, 214);"><strong>Tipo Cliente:</strong></td>
                <td>
                    <?php if ($clientes): ?>
                        <select name="cliente_id" class="selector">
                            <option value="Anonimo">Consumidor Final</option>
                            <?php foreach ($clientes as $cl): ?>
                                <option value="<?php echo $cl['id_cliente']; ?>" <?php echo $cl['id_cliente'] == $id_cliente ? 'selected' : ''; ?>>
                                    <?php echo $cl['nombre']; ?>
                                    <?php echo "Cuil:" . $cl['cuil']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <span>No hay clientes disponibles</span>
                    <?php endif; ?>
                </td>
                 </tr>
                 <?php endif; ?><!-- Fin del if filtro cajero-->


                 <?php if ($perfil == 2 && $estado == ''): ?><!-- Filtro Vendedor-->
            </tr>
                <tr>
                <td style="color:rgb(192, 250, 214);"><strong>Nombre Identificador del Cliente:</strong></td>
                <td>
                    
                <input class="selector" type="text" name="nombre_prov" placeholder="Ingrese nombre cliente" maxlength="20" required>
                    
                </td>
                 </tr>
                 <?php endif; ?><!-- Fin del if filtro vendedor-->

                 <?php if ($perfil == 3 && $estado == 'Cobrando'): ?>
                          
                <tr>
                    <td style="color: rgb(192, 250, 214);"><strong>Monto en Tarjeta de Crédito</strong></td>
                    <td>
                        <input class="selector" type="text" id="pagoTarjetaCredito" name="pagoTarjetaCredito" placeholder="Monto en $" maxlength="15" oninput="this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1'); formatearMiles(); calcularMontoEfectivo();">
                    </td>
                </tr>
                <tr>
                    <td style="color: rgb(192, 250, 214);"><strong>Monto a Cobrar con Tarjeta de Crédito (+10%)</strong></td>
                    <td>
                    <span id="montoTarjetaCreditoAdvertencia" style="color: yellow; font-weight: bold;">$0.00</span>
                    </td>
                </tr>

                <tr>
                    <td style="color: rgb(192, 250, 214);"><strong>Monto en Transferencia:</strong></td>
                    <td>
                        <input class="selector" type="text" id="pagoTransferencia" name="pagoTransferencia" placeholder="Monto en $" maxlength="15" oninput="this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1'); formatearMiles(); calcularMontoEfectivo();">
                    </td>
                </tr>
                
                <tr>
                    <td style="color: rgb(192, 250, 214);"><strong>Monto en Efectivo (-5%):</strong></td>
                    <td>
                        <input class="selector" type="text" id="pagoEfectivo" name="pagoEfectivo" placeholder="Monto en $" maxlength="15" readonly>
                    </td>
                </tr>
                <?php endif; ?>

                <tr>
                <td style="color: rgb(192, 250, 214);"><strong>Tipo de Compra o Pedido:</strong></td>
                <td>
                <select name="tipo_compra" id="tipoCompra" class="selector">
                <?php if ($estado == 'Cobrando') {  ?>

                    
                    <option value="Compra_Normal" <?php echo $tipo_compra == 'Compra_Normal' ? 'selected' : ''; ?>>
                    <?php echo $estado; ?> -> <?php echo $tipo_compra; ?>
                    </option>

                    <?php } else if ($tipo_compra == 'Compra_Normal') {  ?>
                        <option value="Compra_Normal" <?php echo $tipo_compra == 'Compra_Normal' ? 'selected' : ''; ?>>Compra Normal</option>  
            
                    <?php } else if ($tipo_compra == 'Pedido') {  ?>
                        <option value="Pedido" <?php echo $tipo_compra == 'Pedido' ? 'selected' : ''; ?>>Reservar Pedido</option>
                    
                    <?php } else {  ?>                    
                        <option value="Compra_Normal" <?php echo $tipo_compra == 'Compra_Normal' ? 'selected' : ''; ?>>Compra Normal</option>
                        <option value="Pedido" <?php echo $tipo_compra == 'Pedido' ? 'selected' : ''; ?>>Reservar Pedido</option>
                    
                    <?php } ?>
                </select>
                <?php echo form_hidden('tipo_compra_input', $tipo_compra); ?>
                </td>

                </tr>
                <tr id="fechaPedidoFila" style="display: <?php echo !empty($fecha_pedido) ? 'table-row' : 'none'; ?>;">
                <td style="color: rgb(192, 250, 214);"><strong>Fecha de entrega del Pedido:</strong></td>
                <td>
                    <input class="selector" type="date" name="fecha_pedido" id="fechaPedido" 
                           value="<?php echo !empty($fecha_pedido) ? date('Y-m-d', strtotime($fecha_pedido)) : date('Y-m-d'); ?>" 
                           min="<?php echo date('Y-m-d'); ?>">
                    <?php echo form_hidden('fecha_pedido_input', ''); ?>
                </td>
                </tr>   
                
                <?php if ($estado == 'Cobrando') {  ?>
                <tr>
                <td style="color: rgb(192, 250, 214);"><strong>Con Envío:</strong></td>
                <td>
                    <select name="con_envio" id="conEnvio" class="selector">
                        <option value="No">No</option>
                        <option value="Si">Sí</option>
                    </select>
                </td>
                </tr>
                <tr id="costoEnvioFila" style="display: none;">
                <td style="color: rgb(192, 250, 214);"><strong>Costo de Envío:</strong></td>
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

            <?php if ($total_venta == '') { ?>               
            <a class="btn" href="<?php echo base_url('CarritoList') ?>">Volver</a>
            <?php } ?>

            <?php if ($total_venta != '') { ?>
                <a href="<?php echo base_url('cancelarCobro/'.$id_pedido);?>" class="btn danger" onclick="return confirmarAccionC_Cobro();">
                    Cancelar Cobro
                </a>
            <?php } else if ($id_cliente) { ?>
                <a href="<?php echo base_url('cancelar_edicion/'.$id_pedido);?>" class="btn danger" onclick="return confirmarAccionPedido();">
                    Cancelar Modificación Pedido
                </a>
            <?php } else { ?>
                <a href="<?php echo base_url('carrito_elimina/all');?>" class="btn danger" onclick="return confirmarAccionCompra();">
                    Cancelar Todo
                </a>
            <?php } ?>
            
            <?php echo form_hidden('id_pedido', $id_pedido); ?>
            <?php echo form_hidden('tipo_proceso', ''); ?>

            <?php if ($perfil == 2 || $estado == 'Modificando'): ?>
                <input type="submit" name="confirmarPerfil2" value="Confirmar" class="btn">
            <?php elseif ($perfil == 3 && $estado == 'Cobrando'): ?>
                <input type="submit" name="confirmarPerfil3" value="Confirmar" class="btn">
            <?php endif; ?>

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
    });

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
        const montoEfectivoConDescuento = faltaPagar / cd_efectivo; // Aplica el descuento

        // Mostrar el monto en efectivo con descuento
        document.getElementById('pagoEfectivo').value = montoEfectivoConDescuento.toLocaleString('de-DE', { 
            minimumFractionDigits: 2, 
            maximumFractionDigits: 2 
        });

        // Si no se ingresan montos en transferencia o tarjeta de crédito, el monto en efectivo es el total con descuento
        if (pagoTransferencia === 0 && pagoTarjetaCredito === 0) {
            const totalConDescuento = totalVenta / cd_efectivo;
            document.getElementById('pagoEfectivo').value = totalConDescuento.toLocaleString('de-DE', { 
                minimumFractionDigits: 2, 
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

<!-- Modal para Perfil 3 (Imprimir Presupuesto o Facturar) -->
<div id="confirmationModalPerfil3" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <p>¿Desea facturar (Factura tipo C) o solo imprimir ticket?</p>
        <button id="invoiceArca" class="btn">Facturar C (Arca)</button>
        <br><br>
        <button id="printTicket" class="btn">Imprimir Presupuesto</button>

    </div>
</div>

<!-- Segundo modal (Confirmación de facturación) -->
<div id="confirmationFacturaModal" class="modal">
    <div class="modal-content">
        <span class="closeFactura">&times;</span>
        <p>¿Estás seguro de que deseas FACTURAR.? (Factura tipo C)</p>
        <button id="confirmFactura" class="btn">Sí, Facturar</button>
        <button id="cancelFactura" class="btn danger">Cancelar</button>
    </div>
</div>



<!-- Modal para Perfil 2 (Registrar Compra) -->
<div id="confirmationModalPerfil2" class="modal">
    <div class="modal-content">
        <span class="close" id="closePerfil2">&times;</span>
        <p>¿Registrar Compra/Pedido.?</p>
        <button id="confirmarRegistro" class="btn">Sí, Registrar</button>
    </div>
</div>
        <!-- Script Modal perfil 2 -->
<script>
   document.addEventListener("DOMContentLoaded", function () {
    const modalConfirmacionPerfil2 = document.getElementById("confirmationModalPerfil2");
    const btnConfirmarPerfil2 = document.querySelector("input[name='confirmarPerfil2']");
    const spanClosePerfil2 = document.getElementById("closePerfil2"); // Cambiado a ID
    const btnConfirmarRegistro = document.getElementById("confirmarRegistro");

    function abrirModal(modal) {
        modal.style.display = "block";
        setTimeout(() => modal.classList.add("show"), 10);
    }

    function cerrarModal(modal) {
        modal.classList.remove("show");
        setTimeout(() => modal.style.display = "none", 300);
    }

    // Abrir modal al hacer clic en "Confirmar"
    btnConfirmarPerfil2.addEventListener("click", function (event) {
        event.preventDefault();
        abrirModal(modalConfirmacionPerfil2);
    });

    // Cerrar modal al hacer clic en "Sí, Registrar"
    btnConfirmarRegistro.addEventListener("click", function () {
        document.querySelector("form").submit();
    });

    // Cerrar modal al hacer clic en la "X"
    spanClosePerfil2.addEventListener("click", function () {
        cerrarModal(modalConfirmacionPerfil2);
    });

    // Cerrar modal al hacer clic fuera del contenido
    window.addEventListener("click", function (event) {
        if (event.target == modalConfirmacionPerfil2) {
            cerrarModal(modalConfirmacionPerfil2);
        }
    });

    // Cerrar modal al presionar la tecla Escape
    window.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            cerrarModal(modalConfirmacionPerfil2);
        }
    });
});
</script>

<style>

   /* Estilos para el modal */

.modal {
    display: none; /* Oculto por defecto */
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;

    background-color: rgba(0, 0, 0, 0.4);

    padding-top: 60px;
}

/* Agregamos animación de zoom */
.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 20px;
    border: 7px solid #888;
    width: 70%;
    max-width: 400px;
    text-align: center;
    transform: scale(0.5); /* Estado inicial pequeño */
    transition: transform 0.3s ease-in-out;
}

.modal-content p {
    font-weight: 750;
    background-color: #fefefe;
    margin: 5% auto;
    padding: 20px;
    border: 7px solid #888;
    width: 70%;
    max-width: 400px;
    text-align: center;
}

/* Cuando el modal se muestra, aplicamos el efecto de zoom */
.modal.show .modal-content {
    transform: scale(1); /* Escala normal al abrir */
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    font-weight: 700;
    color: red;
    text-decoration: none;
    cursor: pointer;
    box-shadow: 0px 0px 10px rgba(255, 255, 255, 0.3);
}
</style>


<!-- Script para el manejo del modal del Cajero (perfil 3)-->
<script>
    document.addEventListener("DOMContentLoaded", function () {
    const modalConfirmacionPerfil3 = document.getElementById("confirmationModalPerfil3");
    const modalFactura = document.getElementById("confirmationFacturaModal");
    const btnConfirmarPerfil3 = document.querySelector("input[name='confirmarPerfil3']");
    const btnInvoiceArca = document.getElementById("invoiceArca");
    const btnPrintTicket = document.getElementById("printTicket");
    const spanClosePerfil3 = document.getElementsByClassName("close")[0];

    const spanCloseFactura = document.getElementsByClassName("closeFactura")[0];
    const btnConfirmFactura = document.getElementById("confirmFactura");
    const btnCancelFactura = document.getElementById("cancelFactura");
    const tipoProcesoInput = document.querySelector("input[name='tipo_proceso']");


    function abrirModal(modal) {
        modal.style.display = "block";
        setTimeout(() => modal.classList.add("show"), 10);
    }

    function cerrarModal(modal) {
        modal.classList.remove("show");
        setTimeout(() => modal.style.display = "none", 300);
    }

    btnConfirmarPerfil3.addEventListener("click", function (event) {
        event.preventDefault();
        abrirModal(modalConfirmacionPerfil3);

    });

    btnInvoiceArca.addEventListener("click", function (event) {
        event.preventDefault();

        cerrarModal(modalConfirmacionPerfil3);

        setTimeout(() => abrirModal(modalFactura), 300);
    });

    btnPrintTicket.addEventListener("click", function () {
        tipoProcesoInput.value = "ticket";
        document.querySelector("form").submit();
    });

    btnConfirmFactura.addEventListener("click", function () {
        tipoProcesoInput.value = "factura";
        document.querySelector("form").submit();
    });

    btnCancelFactura.addEventListener("click", function () {
        cerrarModal(modalFactura);

        setTimeout(() => abrirModal(modalConfirmacionPerfil3), 300);
    });

    spanClosePerfil3.addEventListener("click", function () {
        cerrarModal(modalConfirmacionPerfil3);

    });

    spanCloseFactura.addEventListener("click", function () {
        cerrarModal(modalFactura);
    });

    window.addEventListener("click", function (event) {

        if (event.target == modalConfirmacionPerfil3) {
            cerrarModal(modalConfirmacionPerfil3);

        }
        if (event.target == modalFactura) {
            cerrarModal(modalFactura);
        }
    });

    window.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {

            cerrarModal(modalConfirmacionPerfil3);

            cerrarModal(modalFactura);
        }
    });
});

</script>


<style>
    .modal-personalizado {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
    justify-content: center;
    align-items: center;
    }

    .modal-contenido {
        background: white;
        padding: 20px;
        border-radius: 8px;
        width: 80%;
        max-width: 800px;
        transform: scale(0); /* Inicia invisible (zoom out) */
        transition: transform 0.3s ease; /* Duración de la animación */
        border-color: #8be9fd;
        box-shadow: 0 0 15px #8be9fd;
    }

    /* Zoom al abrir */
    .modal-contenido.zoom-in {
        transform: scale(1); /* Escala normal */
    }

    /* Zoom al cerrar */
    .modal-contenido.zoom-out {
        transform: scale(0); /* Vuelve a escala 0 */
    }

    .cerrar-modal {
        position: absolute;
        right: 15px;
        top: 10px;
        font-size: 30px;
        color: #888;
        cursor: pointer;
    }

    .cerrar-modal:hover {
        color: red;
    }

    .tabla-detalles {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;        
    }

    .tabla-detalles th, .tabla-detalles td {
        border: 1px solid #ddd;
        padding: 10px;
        text-align: left;
    }

    .tabla-detalles th {
        background-color: #f5f5f5;
    }

    .btn-ver-detalles {
        padding: 10px 20px;
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
    }

    .btn-ver-detalles:hover {
        background-color: #45a149;
    }
</style>

<script>
   function abrirModal() {
    const modal = document.getElementById('miModal');
    const modalContent = modal.querySelector('.modal-contenido');
    
    // Resetear estilos antes de abrir
    modal.style.display = 'flex';
    modalContent.classList.remove('zoom-out');
    
    // Forzar un "reflow" para que la animación funcione
    void modalContent.offsetWidth; // Truco para reiniciar la animación
    
    // Aplicar zoom-in
    modalContent.classList.add('zoom-in');
}

function cerrarModal() {
    const modal = document.getElementById('miModal');
    const modalContent = modal.querySelector('.modal-contenido');
    
    // Quitar zoom-in y aplicar zoom-out
    modalContent.classList.remove('zoom-in');
    modalContent.classList.add('zoom-out');
    
    // Esperar a que termine la animación antes de ocultar
    setTimeout(() => {
        modal.style.display = 'none';
    }, 300); // 300ms = duración de la animación (debe coincidir con CSS)
}

// Cerrar al hacer clic fuera del modal
window.onclick = function(event) {
    const modal = document.getElementById('miModal');
    if (event.target === modal) {
        cerrarModal();
    }
};
</script>