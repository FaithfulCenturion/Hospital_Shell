<?php
session_start();

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

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Login - Hospital Shell</title>
</head>
<body>
    <h1>Iniciar Sesión</h1>
    <form action="./login/login_process.php" method="post">
        <label for="nombre_usuario">Usuario:</label><br />
        <input type="text" id="nombre_usuario" name="nombre_usuario" required /><br /><br />

        <label for="contraseña">Contraseña:</label><br />
        <input type="password" id="contraseña" name="contraseña" required /><br /><br />

        <button type="submit">Entrar</button>
    </form>

    <p>¿No tienes cuenta? <a href="login/register.php">Regístrate aquí</a></p>
</body>
</html>
