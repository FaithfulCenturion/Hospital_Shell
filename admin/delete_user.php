<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

verificarTipoUsuario('administrador');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id'] ?? 0);

    if ($user_id <= 0) {
        die("ID de usuario inválido.");
    }

    // Prevent admin from deleting themselves
    if ($user_id === $_SESSION['usuario_id']) {
        die("No puedes desactivar tu propia cuenta.");
    }

    // Set activo = 0 (soft delete)
    $stmt = $conn->prepare("UPDATE usuarios SET activo = 0 WHERE id = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        header("Location: dashboard.php?msg=Usuario desactivado");
        exit;
    } else {
        die("Error al desactivar usuario: " . $conn->error);
    }
} else {
    // Only allow POST requests
    header("Location: dashboard.php");
    exit;
}
?>
