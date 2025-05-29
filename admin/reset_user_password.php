<?php
session_start();
require_once '../includes/db.php';

// Check admin role (basic check)
if ($_SESSION['tipo_usuario'] !== 'administrador') {
    die("Acceso denegado.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'], $_POST['usuario_id'])) {
    $usuario_id = intval($_POST['usuario_id']);

    // Generate new random password
    $newPassword = bin2hex(random_bytes(4)); // 8 hex chars

    // Hash password for DB
    $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update password in DB
    $stmt = $conn->prepare("UPDATE usuarios SET contraseña = ? WHERE id = ?");
    $stmt->bind_param("si", $newPasswordHash, $usuario_id);
    if (!$stmt->execute()) {
        die("Error al actualizar la contraseña.");
    }

    // Fetch user info to display
    $stmt = $conn->prepare("SELECT nombre_usuario FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $stmt->bind_result($nombre_usuario);
    $stmt->fetch();
    $stmt->close();

    $conn->close();

} else {
    die("Solicitud inválida.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contraseña restablecida</title>
</head>
<body>
    <h1>Contraseña restablecida para usuario: <?= htmlspecialchars($nombre_usuario) ?></h1>
    <p>La nueva contraseña es: <strong><?= htmlspecialchars($newPassword) ?></strong></p>
    <p style="color: red;">Importante: Copia tu nueva contraseña ahora. No la volverás a ver.</p>
    <p><a href="dashboard.php">Volver al panel de administración</a></p>
</body>
</html>
