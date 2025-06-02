<?php
session_start();
$pageTitle = 'Contraseña restablecida';
require_once '../includes/db.php';
include_once '../includes/header.php';

// Comprobar rol de administrador (comprobación básica)
if ($_SESSION['tipo_usuario'] !== 'administrador') {
    die("Acceso denegado.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'], $_POST['usuario_id'])) {
    $usuario_id = intval($_POST['usuario_id']);

    // Generar nueva contraseña aleatoria
    $newPassword = bin2hex(random_bytes(4)); // 8 hex chars

    // Contraseña hash para la base de datos
    $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

    // Actualizar contraseña en la base de datos
    $stmt = $conn->prepare("UPDATE usuarios SET contraseña = ? WHERE id = ?");
    $stmt->bind_param("si", $newPasswordHash, $usuario_id);
    if (!$stmt->execute()) {
        die("Error al actualizar la contraseña.");
    }

    // Obtener información del usuario para mostrar
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

<div class="container py-5">
    <a href="javascript:history.back()" class="btn btn-link mb-4">← Volver</a>

    <div class="card shadow-sm mx-auto" style="max-width: 500px;">
        <div class="card-body">
            <h4 class="card-title text-success">✅ Contraseña restablecida</h4>
            <p class="mb-2"><strong>Usuario:</strong> <?= htmlspecialchars($nombre_usuario) ?></p>

            <div class="alert alert-info">
                <strong>Nueva contraseña:</strong> <?= htmlspecialchars($newPassword) ?><br>
                <small class="text-danger">⚠️ Copia tu nueva contraseña ahora. No la volverás a ver.</small>
            </div>

            <a href="dashboard.php" class="btn btn-primary mt-3">Volver al panel de administración</a>
        </div>
    </div>
</div>

</body>
</html>
