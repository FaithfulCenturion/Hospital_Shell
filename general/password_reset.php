<?php
require_once '../includes/auth_general.php'; // handles session start and login check
require_once '../includes/db.php';

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario_id = $_SESSION['usuario_id'];
    $old_password = $_POST['old_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $mensaje = "Por favor completa todos los campos.";
    } elseif ($new_password !== $confirm_password) {
        $mensaje = "Las nuevas contraseñas no coinciden.";
    } else {
        $stmt = $conn->prepare("SELECT contraseña FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $stmt->bind_result($contraseña_hash);
        $stmt->fetch();
        $stmt->close();

        if (!$contraseña_hash || !password_verify($old_password, $contraseña_hash)) {
            $mensaje = "La contraseña actual es incorrecta.";
        } else {
            $nueva_contraseña_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE usuarios SET contraseña = ? WHERE id = ?");
            $stmt->bind_param("si", $nueva_contraseña_hash, $usuario_id);

            if ($stmt->execute()) {
                $mensaje = "Contraseña actualizada con éxito.";
            } else {
                $mensaje = "Error al actualizar la contraseña: " . $conn->error;
            }
            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restablecer contraseña</title>
</head>
<body>
    <h2>Restablecer contraseña</h2>

    <?php if ($mensaje): ?>
        <p><strong><?php echo htmlspecialchars($mensaje); ?></strong></p>
    <?php endif; ?>

    <form action="password_reset.php" method="POST">
        <label for="old_password">Contraseña actual:</label><br>
        <input type="password" id="old_password" name="old_password" required><br><br>

        <label for="new_password">Nueva contraseña:</label><br>
        <input type="password" id="new_password" name="new_password" required><br><br>

        <label for="confirm_password">Confirmar nueva contraseña:</label><br>
        <input type="password" id="confirm_password" name="confirm_password" required><br><br>

        <input type="submit" value="Actualizar contraseña">
    </form>
</body>
</html>
