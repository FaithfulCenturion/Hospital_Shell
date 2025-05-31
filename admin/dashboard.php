<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

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

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Dashboard de Admin - Usuarios</title>
    <style>
        table {
            border-collapse: collapse;
            width: 80%;
            margin: 20px auto;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 8px 12px;
            text-align: left;
        }

        th {
            background: #555;
            color: #fff;
        }
    </style>
</head>

<body>
    <h1>Usuarios actuales</h1>
    <div style="text-align: center; margin-bottom: 20px;">
        <a href="../login/register.php">
            <button style="padding: 10px 20px; font-size: 16px;">Crear nuevo usuario</button>
        </a>
    </div>

    <table>
        <thead>
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
                            <button type="submit" name="reset_password">Resetear</button>
                        </form>
                    </td>
                    <td><?= $usuario['activo'] ? 'Sí' : 'No' ?></td>
                    <td>
                        <a href="update_usuario.php?id=<?= $usuario['id'] ?>">Editar</a>
                    </td>
                    <td>
                        <?php if ($usuario['activo']): ?>
                            <?php if (!$isLastAdmin): ?>
                                <form method="post" action="delete_user.php"
                                    onsubmit="return confirm('¿Estás seguro que quieres desactivar este usuario?');"
                                    style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?= $usuario['id'] ?>" />
                                    <button type="submit">Eliminar</button>
                                </form>
                            <?php else: ?>
                                <button type="button" disabled title="No se puede eliminar el último administrador" style="opacity: 0.5; cursor: not-allowed;" >Eliminar</button>
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

<footer>
    <p style="display: flex; justify-content: space-between; align-items: center; margin: 0;">
        <a href="../general/password_reset.php">Restablecer mi contraseña</a>
        <a href="../login/logout.php">Cerrar sesión</a>
    </p>
</footer>


</html>

<?php
$conn->close();
?>