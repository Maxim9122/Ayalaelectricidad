<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sesiones</title>
    
    <!-- Estilos -->
    <link rel="stylesheet" href="<?php echo base_url('./assets/css/sesiones.css'); ?>">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 0;
            padding: 0;
            background: #000;
            color: white;
        }

        .titulo-vidrio {
            font-size: 24px;
            margin: 20px 0;
            color: white;
        }

        .sesiones-container {
            max-width: 900px;
            margin: auto;
            background: rgba(88, 87, 87, 0.7);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        .sesiones-tabla {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            color: white;
            font-size: 12px;
        }

        .sesiones-tabla th, .sesiones-tabla td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        .sesiones-tabla th {
            background: #343a40;
            color: white;
        }

        /* Estilo para la paginación */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: white !important;
        }
        .dataTables_wrapper .dataTables_filter label {
            color: white !important;
        }
        .dataTables_wrapper .dataTables_filter input {
            color: white !important;
            background: #222 !important;
            border: 1px solid #fff;
        }
        .dataTables_wrapper .dataTables_filter input::placeholder {
            color: white !important;
        }
        .dataTables_wrapper .dataTables_filter input::-webkit-search-cancel-button {
            filter: invert(100%);
            background: red !important;
            border-radius: 50%;
        }
        /* Cambiar color de los números en la selección de registros por página */
        .dataTables_length label,
        .dataTables_length select {
            color: white !important;
        }
        
        /* Cambiar color del texto dentro del <select> */
        .dataTables_length select option {
            color: white !important;
            background: #333 !important; /* Fondo oscuro para contraste */
        }

    </style>
    
    <!-- Scripts cargados en el head para evitar problemas de referencia -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
</head>
<body>
    <h1 class="titulo-vidrio">Lista De Ingresos al Sistema</h1>
    <div class="sesiones-container">
        <table class="sesiones-tabla" id="users-list">
            <thead>
                <tr>
                    <th>Nro Sesión</th>
                    <th>Inicio de Sesión</th>
                    <th>Fin de Sesión</th>
                    <th>Estado</th>
                    <th>Usuarios</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sesiones as $sesion): ?>
                    <tr>
                        <td><?= $sesion->id_sesion ?></td>
                        <td><?= $sesion->inicio_sesion ?></td>
                        <td><?= $sesion->fin_sesion ?></td>
                        <td><?= $sesion->estado ?></td>
                        <td><?= $sesion->nombre . ' ' . $sesion->apellido?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
  $(document).ready(function () {
    $('#users-list').DataTable({
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
        $('#users-list_filter input').attr('placeholder', 'Usuairos,Estado.. ');
      }
    });
  });
</script>

</body>
</html>
