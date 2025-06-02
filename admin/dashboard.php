<?php
$pageTitle = 'Dashboard del Admin';
require_once '../includes/auth.php';
require_once '../includes/db.php';
include_once '../includes/header.php';
// Verifica que el usuario sea un administrador

verificarTipoUsuario('administrador');  // Only allow admin

// Check how many active administrators exist
$sql_admins = "SELECT COUNT(*) AS total_admins FROM usuarios WHERE tipo_usuario = 'administrador' AND activo = 1";
$result_admins = $conn->query($sql_admins);
$row_admins = $result_admins->fetch_assoc();
$total_admins = (int) $row_admins['total_admins'];

// Fetch usuarios from database
$sql = "SELECT id, nombre_usuario, correo_electronico, tipo_usuario, activo FROM usuarios";
$result = $conn->query($sql);

if (!$result) {
    die("Error al obtener usuarios: " . $conn->error);
}
?>

    <h1>Usuarios actuales</h1>
    <div class="text-center mb-3">
        <a href="../login/register.php">
            <button class="btn btn-primary">Crear nuevo usuario</button>
        </a>
    </div>

    <table class="table table-striped table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>Nombre de usuario</th>
                <th>Correo Electronico</th>
                <th>Tipo de usuario</th>
                <th>Restablecer contraseña</th>
                <th>Activo</th>
                <th>Actualizar</th>
                <th>Eliminar</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($usuario = $result->fetch_assoc()):
                $isLastAdmin = $usuario['tipo_usuario'] === 'administrador' && $usuario['activo'] && $total_admins === 1;
                ?>
                <tr>
                    <td><?= htmlspecialchars($usuario['nombre_usuario']) ?></td>
                    <td><?= htmlspecialchars($usuario['correo_electronico']) ?></td>
                    <td><?= htmlspecialchars($usuario['tipo_usuario']) ?></td>
                    <td>
                        <form method="post" action="reset_usuario_password.php"
                            onsubmit="return confirm('¿Seguro que quieres reiniciar la contraseña?');">
                            <input type="hidden" name="usuario_id" value="<?= $usuario['id'] ?>" />
                            <button type="submit" class="btn btn-outline-secondary btn-sm" name="reset_password">Resetear</button>
                        </form>
                    </td>
                    <td><?= $usuario['activo'] ? 'Sí' : 'No' ?></td>
                    <td>
                        <a class="btn btn-outline-secondary btn-sm" href="update_usuario.php?id=<?= $usuario['id'] ?>">Editar</a>
                    </td>
                    <td>
                        <?php if ($usuario['activo']): ?>
                            <?php if (!$isLastAdmin): ?>
                                <form method="post" action="delete_user.php"
                                    onsubmit="return confirm('¿Estás seguro que quieres desactivar este usuario?');"
                                    class="d-inline">
                                    <input type="hidden" name="user_id" value="<?= $usuario['id'] ?>" />
                                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                </form>
                            <?php else: ?>
                                <button type="button" disabled title="No se puede eliminar el último administrador" class="btn btn-danger btn-sm" >Eliminar</button>
                            <?php endif; ?>
                        <?php else: ?>
                            Inactivo
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            <tr>
                <td colspan="7">
                    <a href="../general/reporte_estadicticas.php" class="btn" style="margin-left: 10px;"
                        target="_blank">
                        Ver Reporte de Visitas
                    </a>
                </td>
            </tr>
        </tbody>
    </table>
</body>

<br><br>

<?php include '../includes/footer.php'; 
$conn->close();
?>