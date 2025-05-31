<?php
require_once '../includes/auth.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso no autorizado</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fdf2f2;
            color: #b10000;
            text-align: center;
            padding: 50px;
        }

        .container {
            max-width: 500px;
            margin: auto;
            background-color: #fff0f0;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px #d3d3d3;
        }

        a {
            color: #b10000;
            font-weight: bold;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚫 Acceso no autorizado</h1>
        <p>No tienes permiso para acceder a esta página.</p>
        <p><a href="javascript:history.back()">← Volver atrás</a></p>
    </div>
</body>
</html>
