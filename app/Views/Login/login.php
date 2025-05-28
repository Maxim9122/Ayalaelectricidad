<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingreso</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <script src="https://kit.fontawesome.com/a25933befb.js" crossorigin="anonymous"></script>   
    <style>
        .input-field {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-field input {
            width: 100%;
            padding-right: 40px; /* Espacio para el icono */
        }

        .toggle-password {
            position: absolute;
            right: 10px;
            cursor: pointer;
            color: #666;
        }

        .toggle-password:hover {
            color: #333;
        }
    </style>
</head>
<body>
<div class="containerLogin">

    <?php  
    $session = session();
    $id = $session->get('id');
    if (!$id) { ?>
        <div class="form-content">
            <h1 id="title">Ingreso</h1>
            
            <?php if (session()->getFlashdata('msg')): ?>
                <div class="alert alert-danger"><?= session()->getFlashdata('msg') ?></div>
            <?php endif; ?>
            
            <form action="<?php echo base_url('enviarlogin'); ?>" method="post">
                <div class="input-group">
                    
                    <div class="input-field">
                        <i class="fa-solid fa-envelope"></i>
                        <input type="text" placeholder="Correo" name="email">
                    </div>

                    <div class="input-field">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" id="password" placeholder="Contraseña" name="pass">
                        <span class="toggle-password" onclick="togglePassword()">
                            <i id="eye-icon" class="fa-solid fa-eye-slash"></i> 
                        </span>
                    </div>

                </div>
                <div>
                    <button type="submit" class="button2">Ingresar</button>
                </div>
            </form>
        </div>

    <?php } else { ?>
        <h1 class="titulo-vidrio">¡Usted ya está logueado!</h1>
    <?php } ?>

</div>

<script>
    function togglePassword() {
        let passwordInput = document.getElementById("password");
        let eyeIcon = document.getElementById("eye-icon");

        if (passwordInput.type === "password") {
            passwordInput.type = "text"; // Mostrar contraseña
            eyeIcon.classList.remove("fa-eye-slash");
            eyeIcon.classList.add("fa-eye");
        } else {
            passwordInput.type = "password"; // Ocultar contraseña
            eyeIcon.classList.remove("fa-eye");
            eyeIcon.classList.add("fa-eye-slash");
        }
    }
</script>

</body>
</html>