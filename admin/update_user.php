<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
verificarTipoUsuario('administrador');

if (!isset($_GET['id'])) {
    die("ID de usuario no especificado.");
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Usuario no encontrado.");
}

$usuario = $result->fetch_assoc();

// Contar administradores activos
$adminCountQuery = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE tipo_usuario = 'administrador' AND activo = 1");
$adminCount = $adminCountQuery->fetch_assoc()['total'];
$isLastAdmin = $usuario['tipo_usuario'] === 'administrador' && $usuario['activo'] && $adminCount <= 1;

$isSelfEdit = $_SESSION['usuario_id'] === $usuario['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo_electronico'] ?? '');
    $tipo = $_POST['tipo_usuario'] ?? '';
    $activo = isset($_POST['activo']) ? 1 : 0;

    if ($isSelfEdit && $isLastAdmin) {
        if ($tipo !== 'administrador' || !$activo) {
            die("No puedes cambiar tu rol ni desactivar tu cuenta si eres el último administrador activo.");
        }
    }

    $update = $conn->prepare("UPDATE usuarios SET correo_electronico = ?, tipo_usuario = ?, activo = ? WHERE id = ?");
    $update->bind_param("ssii", $correo, $tipo, $activo, $id);

    if ($update->execute()) {
        echo "Usuario actualizado correctamente. <a href='dashboard.php'>Volver al panel</a>";
        exit;
    } else {
        echo "Error al actualizar el usuario.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Actualizar Usuario</title>
</head>

<body>
    <h2>Actualizar Usuario: <?= htmlspecialchars($usuario['nombre_usuario']) ?></h2>
    <form method="post">
        <label>Email:</label><br>
        <input type="email" name="correo_electronico" value="<?= htmlspecialchars($usuario['correo_electronico']) ?>"
            required><br><br>

        <label>Tipo de Usuario:</label><br>
        <select name="tipo_usuario" required <?= ($isSelfEdit && $isLastAdmin) ? 'disabled' : '' ?>>
            <option value="administrador" <?= $usuario['tipo_usuario'] === 'administrador' ? 'selected' : '' ?>>
                Administrador</option>
            <option value="doctor" <?= $usuario['tipo_usuario'] === 'doctor' ? 'selected' : '' ?>>Doctor</option>
            <option value="registrador" <?= $usuario['tipo_usuario'] === 'registrador' ? 'selected' : '' ?>>Registrador
            </option>
        </select><br><br>

        <label>
            <input type="checkbox" name="activo" <?= $usuario['activo'] ? 'checked' : '' ?> <?= ($isSelfEdit && $isLastAdmin) ? 'disabled' : '' ?>>
            Usuario activo
        </label><br><br>
        <?php if ($isSelfEdit && $isLastAdmin): ?>
            <input type="hidden" name="tipo_usuario" value="administrador">
            <input type="hidden" name="activo" value="1">
        <?php endif; ?>

        <button type="submit">Guardar Cambios</button>
    </form>

    <p><a href="dashboard.php">Volver al Panel de Administración</a></p>
</body>

</html>