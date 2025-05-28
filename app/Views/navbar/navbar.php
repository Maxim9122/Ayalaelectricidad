<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Multirrubro Blass</title>
  <link rel="icon" href="<?php echo base_url('./assets/img/iconMB2.png');?>">
  <link rel="stylesheet" href="<?php echo base_url('./assets/css/navbar.css');?>">
  <link rel="stylesheet" href="<?php echo base_url('./assets/css/clock.css');?>">
  <link rel="stylesheet" href="<?php echo base_url('./assets/css/mensajesTemporales.css');?>">

  <script src="<?php echo base_url('./assets/js/a25933befb.js');?>" crossorigin="anonymous"></script>
  
</head>

<style>
.cart-container {
    position: relative;
    display: inline-block;
}

.cart-dropdown {
    display: none;
    position: absolute;
    right: 0;
    background: white;
    border: 3px solid #00f0ff; /* azul marino fluor */
    padding: 10px;
    min-width: 200px;
    box-shadow: 0 2px 17px rgba(57, 120, 139, 0.7);
    z-index: 1000;
    border-radius: 8px; /* opcional, para suavizar el borde */
}

.cart-container:hover .cart-dropdown {
    display: block;
}

.cart-item {
    display: flex;
    justify-content: space-between;
    padding: 5px 0;
    border-bottom: 1px solid #eee;
}

.cart-item:last-child {
    border-bottom: none;
}

</style>

<body>

<?php $session = session();
          $nombre= $session->get('nombre');
          $perfil=$session->get('perfil_id');
          $id=$session->get('id');
          $estado =$session->get('estado'); 
          ?>

<section class="navBarSection">
    <div class="headernav">
        <div class="logoDiv">
            <div class="clock">
                <div id="day" class="day"></div>
                <div id="hours"></div>
                <span class="colon" id="colon">:</span>
                <div id="minutes"></div>
            </div>
        </div>

        <!-- Botón de hamburguesa -->
        <button class="toggleNavBar" id="toggleNavBar">
            &#9776; <!-- Icono de hamburguesa -->
        </button>

        <div id="navBar" class="navBar">
            <ul class="navList flex">

        <?php if( ($perfil =='1')) { ?>          
          
          <li class="nnavItem">
            <a href="<?= base_url('pedidos')?>" class="btn">PEDIDOS</a>
          </li>
       
          <li class="nnavItem">
            <a class="btn signUp" href="<?php echo base_url('compras');?>">VENTAS</a>
          </li>
          <li class="nnavItem">
            <a href="<?= base_url('usuarios-list')?>" class="btn signUp">US/Empleado</a>
          </li>
          <li class="nnavItem">
            <a class="btn signUp" href="<?php echo base_url('clientes');?>">CLIENTES</a>
          </li>
          <li class="nnavItem">
            <a href="<?= base_url('Lista_Productos')?>" class="btn">ABM_PRODUCTOS</a>
          </li>
          <li class="nnavItem">
            <a href="<?= base_url('ListaCategorias')?>" class="btn">P_Categorias</a>
          </li>
          <li class="nnavItem">
          <a href="<?= base_url('/logout')?>" class="btn" onclick="return confirmarAccionSalir(event);">Salir</a>
          </li>

          <?php } else if( (($perfil == 2 || $perfil == 3)) ) { ?>
          <li class="navItem">
          

        <?php if ($estado): ?>
        <?php 
        $mensaje = "ATENCIÓN! Se está Procesando una Venta o Pedido";
        $color = "orange"; // Color por defecto
        $link = ""; // Variable para el enlace

        switch ($estado) {
            case 'Modificando':
                $mensaje = "ATENCIÓN! Se está Modificando una Venta o Pedido";
                $color = "#FF6700"; // Naranja neón
                $link = base_url('CarritoList'); // Ruta del enlace
                break;
            case 'Modificando_SF':
                $mensaje = "ATENCIÓN! Se está Modificando una Venta o Pedido";
                $color = "#FF6700"; // Naranja neón
                $link = base_url('CarritoList'); // Ruta del enlace
                break;
            case 'Cobrando':
                $mensaje = "ATENCIÓN! Se está Cobrando una Venta o Pedido";
                $color = "#00FF00"; // Verde neón
                $link = base_url('casiListo'); // Ruta del enlace
                break;
        }
        ?>

        <h5 class="resaltado" style="
        color: white; 
        font-weight: bold; 
        border: 1px solid <?php echo $color; ?>; 
        padding: 7px; 
        display: inline-block; 
        border-radius: 5px; 
        text-align: center;
        text-transform: uppercase;
        box-shadow: 0 0 3px <?php echo $color; ?>, 0 0 5px <?php echo $color; ?>;">
        
        
            <a href="<?php echo $link; ?>" style="color: white; text-decoration: none;">
                <?php echo $mensaje; ?>
            </a>
        

        </h5>
        <?php endif; ?>



          </li>
          <?php if($perfil == 3) { ?>
          <li class="nnavItem">
            <a class="btn" href="<?php echo base_url('caja');?>">CAJA</a>            
          </li>
          <li class="nnavItem">
            <a class="btn signUp" href="<?php echo base_url('compras');?>">VENTAS</a>
          </li>          
          <li class="nnavItem">
            <a class="btn signUp" href="<?php echo base_url('clientes');?>">CLIENTES</a>
          </li>
            <?php } ?>
          <li class="nnavItem">
            <a href="<?= base_url('/catalogo')?>" class="btn">Productos</a>
          </li>

          <li class="navItem cart-container">
              <a href="<?= base_url('CarritoList') ?>">
                  <img class="navImg" src="<?= base_url('assets/img/icons/iconMB2.png') ?>">
              </a>
              <div class="cart-dropdown">
                  <?php 
                  $cart = \Config\Services::cart();
                  $items = $cart->contents(); // Obtiene los items del carrito
                  ?>
                  
                  <?php if (empty($items)): ?>
                      <p>El carrito está vacío</p>
                  <?php else: ?>
                      <?php foreach ($items as $item): ?>
                          <div class="cart-item">
                              <span class="item-name"><?= esc($item['name']) ?></span>
                              <span class="item-quantity"><?= esc($item['qty']) ?></span>
                          </div>
                          <hr>
                      <?php endforeach; ?>
                  <?php endif; ?>
              </div>
          </li>

          <li class="nnavItem">
            <a class="btn" href="<?php echo base_url('pedidos');?>">Pedidos</a>
            <li class="navItem">            
          </li>
          <li class="nnavItem">            
          <a href="<?= base_url('/logout')?>" class="btn" onclick="return confirmarAccionSalir(event);">Salir</a>            
          </li>
          <?php } else { ?>
          
          <li class="navItem">
            <button class="btn loginBtn">
              <a href="<?= base_url('/login')?>" class="login">Ir al Login</a>
            </button>
          </li>
          
         <?php } ?> 
         </ul>
        </div>
    </div>
</section>

<style>
  .resaltado {
    color: orange;
    border: 2px solid orange;
    padding: 10px;
    display: inline-block;
    border-radius: 5px;
    text-align: center;
}
</style>

<script>
  // Obtén el botón de hamburguesa y la barra de navegación
const toggleButton = document.querySelector('.toggleNavBar');
const navBar = document.querySelector('.navBar');
const body = document.querySelector('body');

// Función para activar la barra de navegación y desplazar el contenido
toggleButton.addEventListener('click', function() {
    navBar.classList.toggle('active'); // Abre o cierra la barra de navegación
    body.classList.toggle('navbar-active'); // Desplaza el contenido hacia abajo
});

</script>



  <script>
    // Obtener elementos del DOM
    const toggleNavBar = document.getElementById('toggleNavBar');
    const navBar = document.getElementById('navBar');

    // Función para alternar la visibilidad del menú
    toggleNavBar.addEventListener('click', () => {
        navBar.classList.toggle('active');
    });

    // Cerrar el menú si se hace clic fuera de él
    document.addEventListener('click', (event) => {
        if (!navBar.contains(event.target) && !toggleNavBar.contains(event.target)) {
            navBar.classList.remove('active');
        }
    });
  </script>


  <script>

    function handleScroll() {
      var headernav = document.querySelector('.headernav');
      var scrollPosition = window.scrollY;

      if (scrollPosition > 0) {
          headernav.classList.add('scrolled');
      } else {
          headernav.classList.remove('scrolled');
      }
    }

    window.addEventListener('scroll', handleScroll);
  </script>

<script>
    //Funciones del Reloj
    let showColon = true;

function updateClock() {
    const hoursElement = document.getElementById('hours');
    const minutesElement = document.getElementById('minutes');
    const colonElement = document.getElementById('colon');
    const dayElement = document.getElementById('day');

    // Obtener la fecha y hora actuales
    const now = new Date();
    const hours = now.getHours().toString().padStart(2, '0');
    const minutes = now.getMinutes().toString().padStart(2, '0');
    const days = ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];

    // Alternar visibilidad del colon
    colonElement.textContent = showColon ? ':' : ' '; // Espacio no separable
    showColon = !showColon;

    // Actualizar horas, minutos y día
    hoursElement.textContent = hours;
    minutesElement.textContent = minutes;
    dayElement.textContent = days[now.getDay()];
}

// Actualizar el reloj cada medio segundo
setInterval(updateClock, 500);
updateClock(); // Llamar inicialmente
</script>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  function confirmarAccionSalir(event) {
      event.preventDefault(); // Detiene la navegación automática

      Swal.fire({
          title: "¿Desea Cerrar Sesión y Salir?",
          icon: "question",
          showCancelButton: true,
          confirmButtonText: "Sí, Salir",
          cancelButtonText: "Cancelar",
          customClass: {
              popup: 'small-swal' // Clases personalizadas
          }
      }).then((result) => {
          if (result.isConfirmed) {
              window.location.href = "<?= base_url('/logout') ?>";
          }
      });

      return false; // Evita la navegación si no se confirma
  }
</script>

<style>
  /* Reducir tamaño del cuadro de diálogo */
  .small-swal {
      width: 300px !important; /* Ancho más pequeño */
      font-size: 14px !important; /* Texto más pequeño */
      padding: 10px !important;
  }
</style>
</body>
</html>
