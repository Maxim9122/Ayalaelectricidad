<?php $cart = \Config\Services::cart(); ?>

<!-- Mensajes temporales -->
<?php if (session()->getFlashdata('msg')): ?>
    <div id="flash-message-success" class="flash-message success">
        <?= session()->getFlashdata('msg') ?>
    </div>
<?php endif; ?>

<?php if (session("msgEr")): ?>
    <div id="flash-message-Error" class="flash-message danger">
        <?php echo nl2br(session("msgEr")); ?>
        <button class="close-btn" onclick="cerrarMensaje()">×</button>
    </div>
<?php endif; ?> 

<script>
    function cerrarMensaje() {
        document.getElementById("flash-message-Error").style.display = "none";
    }
    // Ocultar mensaje de éxito después de 3 segundos
    setTimeout(function() {
        const successMessage = document.getElementById('flash-message-success');
        if (successMessage) {
            successMessage.style.display = 'none';
        }
    }, 3000);

    // Ocultar mensaje de error después de 3 segundos
    setTimeout(function() {
        const errorMessage = document.getElementById('flash-message-danger');
        if (errorMessage) {
            errorMessage.style.display = 'none';
        }
    }, 3000);
</script>

<!-- Fin de los mensajes temporales -->
<br>

<style>
@media (max-width: 768px) { /* Para dispositivos con ancho menor o igual a 768px (tablets y teléfonos) */
    .ocultar-en-movil {
        display: none;
    }
}

    /* Estilos generales de la tabla del carrito */
.tabla-carrito {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.tabla-carrito th,
.tabla-carrito td {
    border: 1px solid #ccc;
    padding: 0px;
    text-align: center;
}

.tabla-carrito thead {
    background-color: #f4f4f4;
    font-weight: bold;
}

/* Ajustar tabla en pantallas pequeñas */
@media screen and (max-width: 600px) {
    .tabla-carrito {
        font-size: 14px; /* Reducir tamaño de fuente */
    }
    
    .ocultar-en-movil {
        display: none; /* Ocultar columnas innecesarias */
    }

    .tabla-carrito th, 
    .tabla-carrito td {
        padding: 1px; /* Reducir espacio interno */
    }
}

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

/*Estilos para el input de motivo*/
.motivo {
    width: 100%;
    max-width: 750px;
    padding: 8px;
    border: 2px solid #50fa7b;
    background-color: #282a36;
    color: #f8f8f2;
    border-radius: 5px;
    font-size: 16px;
    font-weight: 800px;
    color:#ffff;
}

.motivo:focus {
    outline: none;
    border-color: #8be9fd;
    box-shadow: 0 0 5px #8be9fd;
}
.total_ant {
    width: 100%;
    max-width: 440px;
    padding: 8px;
    border: 2px solid #50fa7b;
    background-color: #282a36;
    color: #f8f8f2;
    border-radius: 5px;
    font-size: 16px;
    font-weight: 800px;
    color:#ffff;
}

.total_ant:focus {
    outline: none;
    border-color: #8be9fd;
    box-shadow: 0 0 5px #8be9fd;
}

.total_ant_pagos {
    width: 100%;
    max-width: 520px;
    padding: 8px;
    border: 2px solid #50fa7b;
    background-color: #282a36;
    color: #f8f8f2;
    border-radius: 5px;
    font-size: 16px;
    font-weight: 800px;
    color:#ffff;
}

.total_ant_pagos:focus {
    outline: none;
    border-color: #8be9fd;
    box-shadow: 0 0 5px #8be9fd;
}

.diferencia_result {
    width: 100%;
    max-width: 450px;
    padding: 8px;
    border: 2px solid #50fa7b;
    background-color: #282a36;
    color: red;
    border-radius: 5px;
    font-size: 16px;
    font-weight: 800px;
    color:#ffff;
}

.diferencia_result:focus {
    outline: none;
    border-color: #8be9fd;
    box-shadow: 0 0 5px #8be9fd;
}


.tabla-fina td,
.tabla-fina th {
    padding: 1px !important;
    color: #000; /* letra negra */
    text-shadow: -1px -1px 0 #fff, 1px -1px 0 #fff, 
                 -1px 1px 0 #fff, 1px 1px 0 #fff; /* borde blanco */
}

.tabla-fina td,
.tabla-fina th {
    padding: 0px !important;
    color: #000; /* letra negra */
    text-shadow: -1px -1px 0 #fff, 1px -1px 0 #fff, 
                 -1px 1px 0 #fff, 1px 1px 0 #fff; /* borde blanco */
    font-weight:900px;
}
</style>




<?php

$id_pedido = '';
// Añadido para el tipo de compra
$session = session();
$perfil = $session->get('perfil_id');
if (!empty($session)) {
    $id_pedido = $session->get('id_pedido');
    $tipo_compra = $session->get('tipo_compra');
    $estado = $session->get('estado');

    $total_anterior_bonif = $session->get('total_bonificado');
    $total_anterior_gen = $session->get('total_venta');

    $pago_efec = $session->get('pago_efec');
    $pago_transfer = $session->get('pago_transfer');
    $pago_tarjeta = $session->get('pago_tarjeta');

    $cd_efectivo =$session->get('cd_efectivo');

}

?>

<div class="compados" style="width:100%;">

<div class="" >
<div class="contenedor">
        <u><i><h2 style="color:black">Productos En Carrito</h2></i></u>
        <br>
        <?php if ($estado == 'Modificando'): ?>
            <h3 class="resaltado">
                Modificando Venta/Pedido Numero: <?php echo htmlspecialchars($id_pedido, ENT_QUOTES, 'UTF-8'); ?>
            </h3>
        <?php endif; ?>
        <?php if ($estado == 'Modificando_SF'): ?>
            <h4 class="resaltado">
                "Importante!" Si se cambia un producto defectuosos por otro del mismo, ir al "Panel de descuento de Stock."
            </h4>
        <?php endif; ?>
        </div>
        <br>
        <div class="sinProductos" style="color:#ffff; " align="center" >
            <h2>
            <?php  
                if (empty($carrito)) {
                    echo 'No hay productos agregados todavía.!<br><br>';
                    
                    if ($id_pedido > 0 && $tipo_compra == 'Pedido' && $estado == 'Modificando') { ?>
                        <a href="<?php echo base_url('cancelar_edicion/' . $id_pedido); ?>" class="danger" onclick="return confirmarAccionPedido();">
                            Cancelar Modificación Pedido
                        </a>
                        <br><br>
                    <?php 
                    } elseif ($perfil == 3 && $tipo_compra == 'Compra_Normal' && $estado == 'Modificando') { ?>
                        <a href="<?php echo base_url('cancelar_edicion_Venta/' . $id_pedido); ?>" class="danger" onclick="return confirmarAccionVenta();">
                            Cancelar Modificación Venta
                        </a>
                        <br><br>
                    <?php  
                    } elseif ($perfil == 3 && $estado == 'Modificando_SF') { ?>
                        <a href="<?php echo base_url('cancelar_edicion_Venta_SF/' . $id_pedido); ?>" class="danger" onclick="return confirmarAccionVenta_SF();">
                            Cancelar Cambios en Venta
                        </a>
                        <br><br>
                    <?php 
                    } 
                } 
                ?>
            </h2>
        </div>
   

<?php
// Asegúrate de definir $gran_total antes de este script
$gran_total = isset($gran_total) ? $gran_total : 0; // Si $gran_total no está definido, usa 0 como valor
$dif_pago_efec = 0;
$dif_devolver = 0;
$resto_desc_pago_efec = 0;
?>

        <table class="texto-negrita tabla-fina">

            <?php // Todos los items de carrito en "$cart".
            if ($carrito):
            ?>
                <tr class=" colorTexto2"  >
                    <td class="ocultar-en-movil">ID</td>
                    <td>Nombre</td>
                    <td>Precio</td>                    
                    <td>Cantidad</td>
                    <td>Subtotal</td>                    
                    <td>Eliminar?</td>
                </tr>
                
            <?php // Crea un formulario php y manda los valores a carrito_controller/actualiza carrito
            echo form_open('carrito/procesarCarrito', ['id' => 'carrito_form']); // Deja vacío para enviar al mismo controlador
                $gastos = 0;
                $i = 1;

                foreach ($carrito as $item):
                    echo form_hidden('cart[' . $item['id'] . '][id]', $item['id']);
                    echo form_hidden('cart[' . $item['id'] . '][rowid]', $item['rowid']);
                    echo form_hidden('cart[' . $item['id'] . '][name]', $item['name']);
                    echo form_hidden('cart[' . $item['id'] . '][price]', $item['price']);
                    echo form_hidden('cart[' . $item['id'] . '][qty]', $item['qty']);
            ?>
                    <tr style="color: black;">
                        
                        <td  class="separador ocultar-en-movil">
                            <?php echo $i++; ?>
                        </td>
                        <td class="separador">
                            <?php echo $item['name']; ?>
                        </td>

                        <td class="separador">$
                            <?php 
                                
                                    echo form_input([
                                        'name' => 'cart[' . $item['id'] . '][price]',
                                        'value' => number_format($item['price'], 0, '.', '.'),
                                        'type' => 'text',
                                        'style' => 'text-align: right; width: 90px;',
                                        'oninput' => "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\\..*?)\\..*/g, '\$1')"
                                    ]);
                                
                            ?>
                        </td>


                        <td class="separador">
                        <?php 
                            if ($item['id'] < 10000) {
                                echo form_input([
                                    'name' => 'cart[' . $item['id'] . '][qty]',
                                    'value' => $item['qty'],
                                    'type' => 'number',
                                    'min' => '1',
                                    'maxlength' => '5',
                                    'size' => '1',
                                    'style' => 'text-align: right; width: 70px;',
                                    'oninput' => "this.value = this.value.replace(/[^0-9]/g, '')"
                                ]);?>
                            <?php } else {
                                echo number_format($item['qty']);
                            }
                            ?>
                        </td>
                        
                            <?php $gran_total = $gran_total + $item['subtotal']; ?>

                        <td class="separador">
                        $ <?php echo number_format($item['subtotal'], 0, '.', '.'); ?>
                        </td>

                        <td class="imagenCarrito separador" align="center">
                            <?php // Imagen para Eliminar Item
                                $path = '<img src= '. base_url('assets/img/icons/basura3.png') . ' width="10px" height="10px">';
                                echo anchor('carrito_elimina/'. $item['rowid'], $path);
                            ?>
                            
                        </td>
                        
                    </tr>
                    
                <?php
                endforeach;
                ?>

                    <?php if ($estado == 'Modificando_SF'): ?>
                        <tr>
                            <td></td>
                            <td></td>
                            <td colspan="6" align="right">
                                <label style="color:orange;" for="motivo_cambio">Motivo de los cambios de la Venta:</label>
                                <input class="motivo" type="text" id="motivo_cambio" name="motivo_modif" placeholder="Ingrese el motivo de los cambios">
                        
                        <h4 style="color:orange;">Montos a Favor de la Venta Anterior:</h4>
                                <h4 class="total_ant_pagos"> Pago Efectivo: $
                                    <?php
                                    echo number_format($pago_efec, 0, '.', '.');
                                    ?>
                                   <!-- (Equivale a $ <?php //Gran Total
                                    echo number_format(($pago_efec * $cd_efectivo), 0, '.', '.');
                                    ?>) -->                   
                                </h4>
                                <h4 class="total_ant_pagos"> Pago Transferencia: $
                                    <?php
                                    echo number_format($pago_transfer, 0, '.', '.');
                                    ?>                    
                                </h4>
                               <!-- <h4 class="total_ant_pagos"> Pago Tarjeta: $
                                    <?php 
                                    echo number_format($pago_tarjeta, 0, '.', '.');
                                    ?>
                                    (Equivale a $ <?php //Gran Total
                                    echo number_format(($pago_tarjeta / 1.1), 0, '.', '.');
                                    ?>)                     
                                </h4> -->

                        <br>
                        <h4 style="color:orange;">TOTALES:</h4>
                                <!-- <h4 class="total_ant">Total Anterior C/Desc. y Adici.: $
                                    <?php //Gran Total Bonificado Anterior
                                    echo number_format($total_anterior_bonif, 0, '.', '.');
                                    ?>                    
                                </h4> -->
                                <h4 class="total_ant">Total General Anterior: $
                                    <?php //Gran Total Anterior
                                    echo number_format($total_anterior_gen, 0, '.', '.');
                                    ?>                    
                                </h4>
                                <!-- Nuevo Total de la venta -->
                                <h4 class="total_ant" id="total_actual" style="color:black">Nuevo Total Actual: $ <?php echo number_format($gran_total, 0, '.', '.'); ?></h4>
                        <!-- Mostrar la opcion de tipo de pago solo si el nuevo total es mayor al anterior -->
                         <?php if($gran_total > $total_anterior_gen){ ?>
                                <label style="color:orange;" for="tipo_pago">Paga la Diferencia Con:</label>
                                <select class="total_ant" id="tipo_pago" name="tipo_pago_dif" onchange="calcularDiferencia()">
                                    <option value="Transferencia">Transferencia</option>
                                    <option value="Efectivo">Efectivo (Descuento)</option>
                                   <!-- <option value="Tarjeta">Tarjeta (+10% Adicional)</option> -->
                                </select> 
                          <?php  } ?>         
                         <!-- Si el nuevo total es menor al anterior hay que 
                          hace calculos para la devolucion correcta de dinero -->                            
                            <?php if($gran_total < $total_anterior_gen){  ?>                                
                            <!-- Si el total nuevo es menor o igual que el pago anterior 
                             en efectivo (transformado a formato normal no en descuento) significa que se puede pagar solo con ese monto -->
                             <?php if($gran_total <= ($pago_efec * $cd_efectivo)){  ?>                                
                             <!-- Si el resto es positivo se calcula cuanto le devolvemos en 
                              efectivo mas todos los demas montos-->
                                <?php $dif_pago_efec = $pago_efec - ($gran_total / $cd_efectivo); ?>                                
                             <!-- Si es menor a 0 Significa que sobró efectivo de la venta anterior
                               por eso se suma eso como esta en formato descuento mas los demas montos para devolver -->                                
                                    <?php $dif_devolver = $dif_pago_efec + $pago_tarjeta + $pago_transfer; ?>
                                <br>
                                <h4 style="color:orange;">Montos a Devolver</h4> 
                                    <h4 class="total_ant" >Devolver $  <?php echo number_format($dif_pago_efec, 0, '.', '.'); ?> en Efectivo</h4> 
                                    <h4 class="total_ant" >Devolver $  <?php echo number_format($pago_transfer, 0, '.', '.'); ?> en Transferencia</h4>                                  
                                  <!--  <h4 class="total_ant" >Devolver $  <?php echo number_format($pago_tarjeta, 0, '.', '.'); ?> en Tarjeta</h4>  -->                         
                                    

                                    <br>
                                    <h4 style="background-color:red;" class="total_ant" >Diferencia Total a Devolver: $  <?php echo number_format($dif_devolver, 2 , ',', '.'); ?></h4>
                                    
                                    <h3 style="background-color:red; color:#ffff;" class="diferencia_result">Atencion! La diferencia Resultó Plata a favor del Cliente.</h3>
                             <!-- Ahora si el nuevo total es mayor que el pago en efectivo anterior se convierte el total nuevo 
                              en precio de descuento para descontar el pago en efectivo que tambien esta en formato precio descuento -->    
                        <?php } else if($gran_total > ($pago_efec * $cd_efectivo)){  ?> 

                                        <?php $resto_desc_pago_efec = ($gran_total / $cd_efectivo) - $pago_efec; ?>                                
                                    <!-- Aqui la diferencia entre lo anterior, ya restamos el pago efectivo anterior al nuevo total 
                                    ambos en formato descuento, ahora el resto hay que volver a formato normal antes de restar los demas montos -->
                                            
                                            <br>
                                    <!-- Calculo el resto de restar el saldo disponible menos el total que falta cubrir despues de usar todo
                                    el saldo efectivo -->                                     
                                            <?php $resto_transfer = $pago_transfer - ($resto_desc_pago_efec * $cd_efectivo); ?>
                                        <!-- Si el resto es menor o igual a 0 significa que se uso todo el saldo de transfer --> 
                                            <?php if($resto_transfer <= 0){ ?>
                                                    <!-- Pasamos a restar el saldo de tarjeta menos lo que queda pagar despues de usar todo
                                                    el saldo efectivo y todo el saldo transfer --> 
                                                <?php $resto_tarjeta = ($pago_tarjeta / 1.1) - abs($resto_transfer); ?>
                                                        <!-- El resto que quedo del saldo de tarjeta lo transformamos a monto con adicional --> 
                                                        <?php $dif_devolver = abs($resto_tarjeta * 1.1); ?>
                                                        <!-- En este caso coincidiran los mintos al mostra cuanto se devuelve por separado y total 
                                                        Tambien multiplicamos por 1.1 para tener el valor con adicional a devolver --> 
                                                        <?php $resto_tarjeta = $resto_tarjeta * 1.1; ?>
                                                <!-- Si el resto da positivo significa que sobro saldo de transferencia --> 
                                                <?php } else if($resto_transfer > 0){ ?>
                                                        <!-- Se le asigna a la variable resto para mostrar lo que sobro para devolver por separado -->                                         
                                                        <?php $resto_tarjeta = $pago_tarjeta; ?>
                                                        <!-- Sumamos el resto que quedo del saldo transfer mas el total sin uso de la tarjeta para devolver --> 
                                                        <?php $dif_devolver = $resto_tarjeta + $resto_transfer; ?>
                                                <?php } ?>
                                        
                                        <h4 style="color:orange;">Montos a Devolver</h4>
                                    <!-- Monto a devolver en efectivo --> 
                                        <h4 class="total_ant" >Devolver $  <?php echo number_format(0, 0, '.', '.'); ?> en Efectivo</h4>

                                    <!-- Si es resto es igual o menor a 0 muestra 0 (TRANSFERENCIA)-->
                                        <?php if($resto_transfer <= 0){  ?>                               
                                            <h4 class="total_ant" >Devolver $  <?php echo number_format(0, 0, '.', '.'); ?> en Transferencia</h4>
                                        <!-- Si el resto es mayor a 0 osea sobro plata en transferencia y hay que mostrar -->
                                            <?php }else if($resto_transfer > 0) { ?>
                                                <h4 class="total_ant" >Devolver $  <?php echo number_format($resto_transfer , 0, '.', '.'); ?> en Transferencia</h4>
                                        <?php } ?>

                                    <!-- Mostramos el resto que quedo de (TARJETA)-->                                    
                                         <!--   <h4 class="total_ant" >Devolver $  <?php echo number_format($resto_tarjeta, 0, '.', '.'); ?> en Tarjeta (AdicionOK)</h4> -->
                                            

                                            <br>
                                            <h4 style="background-color:red;" class="total_ant" >Diferencia Total a Devolver: $  <?php echo number_format($dif_devolver, 0, '.', '.'); ?></h4>

                                            <h3 style="background-color:red; color:#ffff;" class="diferencia_result">Atencion! La diferencia Resultó Plata a favor del Cliente.</h3>
                        <?php  } ?>

                        <!-- Si el nuevo total es mayor al anterior muestra la diferencia a pagar -->
                            <?php } else {  ?>

                                <h4 style="background-color:green;" class="total_ant" id="diferencia">Diferencia a Cobrar: $  <?php echo number_format($gran_total - $total_anterior_gen, 0, '.', '.'); ?></h4>
                            
                            <?php } ?>
                        </td>       
                        </tr>
                    <?php endif; ?>

                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td colspan="5" align="right" style="color:#ffff;">
                        <?php if ($estado != 'Modificando_SF'): ?>                        
                            <br>
                            <h4 style="color:black" class="totalVenta">Total Actual: $
                                <?php //Gran Total
                                echo number_format($gran_total , 0, '.', '.');
                                ?>
                            </h4>
                           <!-- <h4 class="totalVenta" style="margin-top:3px;">Total Con Descuento Efectivo: $
                                <?php //Gran Total
                                echo number_format($gran_total / $cd_efectivo, 2);
                                ?>
                            </h4>
                            <h4 class="totalVenta" style="margin-top:3px;">Total En Tarjeta: $
                                <?php //Gran Total
                                echo number_format($gran_total * 1.1, 2);
                                ?>
                            </h4> -->
                        <?php endif; ?>

                        <h4></h4>
                        <br>
                        <input type="hidden" id="accion" name="accion" value=""> <!-- Este campo controlará a qué función se envía -->

                        <!-- Cancelar edicion de pedido -->
                        <?php if ($id_pedido > 0 && $tipo_compra == 'Pedido' && $estado == 'Modificando') { ?>
                            <a style="color:black" href="<?php echo base_url('cancelar_edicion/'.$id_pedido);?>" class="danger" onclick="return confirmarAccionPedido();">
                                Cancelar Modificación Pedido
                            </a>
                            <?php } else if ($perfil == 3 && $tipo_compra == 'Compra_Normal' && $estado == 'Modificando'){?>
                                <a style="color:black" href="<?php echo base_url('cancelar_edicion_Venta/'.$id_pedido);?>" class="danger" onclick="return confirmarAccionVenta();">
                                Cancelar Modificación Venta
                                </a>
                            <?php  } else if ($perfil == 3 && $estado == 'Modificando_SF'){?>
                                <a style="color:black" href="<?php echo base_url('cancelar_edicion_Venta_SF/'.$id_pedido);?>" class="danger" onclick="return confirmarAccionVenta_SF();">
                                Cancelar Cambios en Venta
                                </a>
                                <br><br>
                            <?php  } else {  ?>
                                <!-- Borrar carrito usa mensaje de confirmacion -->
                            <a style="color:black" href="<?php echo base_url('carrito_elimina/all');?>" class="danger" onclick="return confirmarAccionCompra();">
                                        Borrar Todo
                            </a>
                            <?php  } ?>
                        <!-- Submit boton. Actualiza los datos en el carrito -->
                        <button style="color:black" type="submit" class="success" onclick="setAccion('actualizar')">
                            Actualizar Importes
                        </button>                        
                                
                            <br><br>
                            <?php if(($tipo_compra == 'Pedido' || $perfil == 2) && ($estado == '' || $estado == 'Modificando')) { ?>
                        <!-- " Confirmar orden envia a carrito_controller/muestra_compra  -->
                        <a style="color:black" href="javascript:void(0);" class="success" onclick="setAccion('confirmar')">Continuar Compra</a>
                                
                        <?php }else if ($perfil == 3 && $tipo_compra == 'Compra_Normal' && $estado == 'Modificando'){ ?>            
                        <!-- Envia los cambios y Modifica e impacta los cambios de la venta modificada -->
                        <a style="color:black" href="javascript:void(0);" class="success" onclick="setAccion('modificar')">Modificar Venta</a>

                        <?php } else if($perfil == 3 && $estado == 'Modificando_SF') {?>
                        <!-- Envia los cambios y Modifica e impacta los cambios de la venta modificada -->
                        <a style="color:" href="javascript:void(0);" class="success" onclick="confirmarGuardarCambios()">Guardar Cambios</a>    
                        <?php } ?>
                    </td>
                </tr>
                <?php echo form_close();
            endif; ?>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmarGuardarCambios() {
    Swal.fire({
        title: "¿Confirmar Cambios?",
        text: "Asegurate de haber presionado en 'Actualizar Importes' antes de Continuar!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, Guardar Cambios",
        cancelButtonText: "Volver",
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33"
    }).then((result) => {
        if (result.isConfirmed) {
            // Establece la acción y envía el formulario
            document.getElementById('accion').value = 'GuardarCambios';
            document.getElementById('carrito_form').submit();
        }
    });
}
</script>

<script>
    function setAccion(accion) {
    // Asignamos la acción al campo oculto
    document.getElementById('accion').value = accion;

    // Enviamos el formulario
    document.getElementById('carrito_form').submit();
}

</script>


<script>
    function calcularDiferencia() {
        const tipoPago = document.getElementById('tipo_pago').value;
        const granTotal = <?php echo $gran_total; ?>;
        const totalAnterior = <?php echo $total_anterior_gen; ?>;
        const cd_efectivo = <?php echo $cd_efectivo; ?>;

        let diferencia = granTotal - totalAnterior;

        if (tipoPago === 'Efectivo') {
            // Aplicar descuento del 5% solo a la diferencia
            diferencia = diferencia / cd_efectivo;
        } else if (tipoPago === 'Tarjeta') {
            // Aplicar un adicional del 10% solo a la diferencia
            diferencia = diferencia * 1.1;
        }

        // Mostrar la diferencia con el descuento o adicional aplicado (si corresponde)
        document.getElementById('diferencia').innerText = `Diferencia: $${diferencia.toFixed(2)}`;
    }
</script>



<!-- SWEET ALERTS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
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

    function confirmarAccionVenta() {
        Swal.fire({
            title: "¿Estás seguro?",
            text: "Se cancelara la modificacion de la Venta y quedara como estaba.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, Cancelar Edicion",
            cancelButtonText: "Volver"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "<?php echo base_url('cancelar_edicion_Venta/'.$id_pedido); ?>";
            }
        });
        return false; // Evita que el enlace siga su curso normal
    }

    function confirmarAccionVenta_SF() {
        Swal.fire({
            title: "¿Estás seguro?",
            text: "Se cancelara la modificacion de la Venta y quedara como estaba.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, Cancelar Cambios",
            cancelButtonText: "Volver"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "<?php echo base_url('cancelar_edicion_Venta_SF/'.$id_pedido); ?>";
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
            cancelButtonText: "Volver"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "<?php echo base_url('cancelar_edicion/'.$id_pedido); ?>";
            }
        });
        return false; // Evita que el enlace siga su curso normal
    }

</script>

<br>