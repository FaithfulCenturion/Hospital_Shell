<?php
session_start();
$pageTitle = 'Login - Hospital Shell';
include_once '../includes/header.php';

if (isset($_SESSION['usuario_id']) && isset($_SESSION['tipo_usuario'])) {
    // Redirigir según el tipo de usuario
    switch ($_SESSION['tipo_usuario']) {
        case 'administrador':
            header('Location: admin/dashboard.php');
            exit;
        case 'doctor':
            header('Location: doctor/dashboard.php');
            exit;
        case 'registrador':
            header('Location: registrador/dashboard.php');
            exit;
        default:
            // Si tipo_usuario no coincide, redirigir a login o página general
            header('Location: login/logout.php'); // O donde consideres adecuado
            exit;
    }
}
?>

<div class="container mt-5" style="max-width: 400px;">
    <div class="card shadow">
        <div class="card-body">
            <h2 class="card-title text-center mb-4">Iniciar Sesión</h2>
            <form action="./login/login_process.php" method="post" novalidate>
                <div class="mb-3">
                    <label for="nombre_usuario" class="form-label">Usuario:</label>
                    <input type="text" id="nombre_usuario" name="nombre_usuario" class="form-control" required>
                </div>

                <div class="mb-4">
                    <label for="contraseña" class="form-label">Contraseña:</label>
                    <input type="password" id="contraseña" name="contraseña" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">Entrar</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
