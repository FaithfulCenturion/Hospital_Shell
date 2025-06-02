<?php
require_once '../includes/auth.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso no autorizado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="height: 100vh;">

    <div class="container text-center">
        <div class="card shadow-sm mx-auto" style="max-width: 500px;">
            <div class="card-body">
                <h1 class="display-5 text-danger">🚫 Acceso no autorizado</h1>
                <p class="mt-3">No tienes permiso para acceder a esta página.</p>
                <a href="javascript:history.back()" class="btn btn-outline-danger mt-3">← Volver atrás</a>
            </div>
        </div>
    </div>

</body>
</html>
