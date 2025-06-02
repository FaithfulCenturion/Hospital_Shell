<?php
require_once '../includes/auth_general.php'; // Maneja el inicio de sesión y la verificación de inicio de sesión
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Restablecer contraseña</h4>
                </div>
                <div class="card-body">
                    <?php if ($mensaje): ?>
                        <div class="alert <?= str_contains($mensaje, 'éxito') ? 'alert-success' : 'alert-danger' ?>" role="alert">
                            <?= htmlspecialchars($mensaje) ?>
                        </div>
                    <?php endif; ?>

                    <form action="password_reset.php" method="POST">
                        <div class="mb-3">
                            <label for="old_password" class="form-label">Contraseña actual:</label>
                            <input type="password" class="form-control" id="old_password" name="old_password" required>
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">Nueva contraseña:</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirmar nueva contraseña:</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>

                        <button type="submit" class="btn btn-success w-100">Actualizar contraseña</button>
                    </form>
                </div>
            </div>

            <div class="text-center mt-3">
                <a href="javascript:history.back()" class="btn btn-link">← Volver</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>