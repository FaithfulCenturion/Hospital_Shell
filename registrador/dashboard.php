<?php
require_once '../includes/auth.php';
verificarTipoUsuario('registrador');

// Check for form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $apellido = $_POST['apellido'] ?? '';
    $fechaNacimiento = $_POST['fecha_nacimiento'] ?? '';
    $cedula = $_POST['cedula'] ?? '';

    // TODO: Connect to DB and insert patient record here.
    // For now just simulate success:
    $mensaje = "Paciente registrado: $nombre $apellido, Cédula: $cedula";
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Registrador</title>
</head>

<body>

    <h2>Dashboard de Registrador</h2>

    <?php if (!empty($mensaje)): ?>
        <p style="color: green;"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>

    <h3>Registrar nuevo paciente</h3>
    <form method="post" autocomplete="off">
        <label for="nombre">Nombre:</label><br>
        <input type="text" id="nombre" name="nombre" required><br>

        <label for="apellido">Apellido:</label><br>
        <input type="text" id="apellido" name="apellido" required><br>

        <label for="fecha_nacimiento">Fecha de nacimiento:</label><br>
        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required><br>

        <label for="queja">Queja principal:</label><br>
        <textarea id="queja" name="queja" rows="3" cols="30" required></textarea><br><br>

        <button type="submit">Registrar paciente</button>
    </form>

    <footer>
        <p style="display: flex; justify-content: space-between; align-items: center; margin: 0;">
            <a href="../general/password_reset.php">Restablecer mi contraseña</a>
            <a href="../login/logout.php">Cerrar sesión</a>
        </p>
    </footer>

</body>

</html>