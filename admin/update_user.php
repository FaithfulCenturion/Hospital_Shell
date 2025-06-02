<?php
$pageTitle = 'Actualizar Usuario';
require_once '../includes/db.php';
require_once '../includes/auth.php';
include_once '../includes/header.php';
// Verifica que el usuario sea un administrador
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
        echo "<div class='alert alert-success text-center'>Usuario actualizado correctamente. <a href='dashboard.php'>Volver al panel</a></div>";
        exit;
    } else {
        echo "<div class='alert alert-danger'>Error al actualizar el usuario.</div>";
    }
}
?>

<a href="javascript:history.back()" class="btn btn-link mt-3 ms-3">← Volver</a>

<div class="container mt-5">
    <div class="card">
        <div class="card-header">
            <h3>Actualizar Usuario: <?= htmlspecialchars($usuario['nombre_usuario']) ?></h3>
        </div>
        <div class="card-body">
            <form method="post">
                <div class="mb-3">
                    <label for="correo_electronico" class="form-label">Email:</label>
                    <input type="email" class="form-control" name="correo_electronico" id="correo_electronico"
                        value="<?= htmlspecialchars($usuario['correo_electronico']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="tipo_usuario" class="form-label">Tipo de Usuario:</label>
                    <select class="form-select" name="tipo_usuario" id="tipo_usuario"
                        <?= ($isSelfEdit && $isLastAdmin) ? 'disabled' : '' ?> required>
                        <option value="administrador" <?= $usuario['tipo_usuario'] === 'administrador' ? 'selected' : '' ?>>Administrador</option>
                        <option value="doctor" <?= $usuario['tipo_usuario'] === 'doctor' ? 'selected' : '' ?>>Doctor</option>
                        <option value="registrador" <?= $usuario['tipo_usuario'] === 'registrador' ? 'selected' : '' ?>>Registrador</option>
                    </select>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="activo" id="activo"
                        <?= $usuario['activo'] ? 'checked' : '' ?> <?= ($isSelfEdit && $isLastAdmin) ? 'disabled' : '' ?>>
                    <label class="form-check-label" for="activo">Usuario activo</label>
                </div>

                <?php if ($isSelfEdit && $isLastAdmin): ?>
                    <input type="hidden" name="tipo_usuario" value="administrador">
                    <input type="hidden" name="activo" value="1">
                <?php endif; ?>

                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                <a href="dashboard.php" class="btn btn-secondary ms-2">Cancelar</a>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>