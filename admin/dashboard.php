<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

verificarTipoUsuario('administrador');  // Only allow admin

// Fetch users from database
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
        <a href="/hospital-shell/login/register.php">
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
            <?php while ($user = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($user['nombre_usuario']) ?></td>
                    <td><?= htmlspecialchars($user['correo_electronico']) ?></td>
                    <td><?= htmlspecialchars($user['tipo_usuario']) ?></td>
                    <td>
                        <form method="post" action="reset_user_password.php"
                            onsubmit="return confirm('¿Seguro que quieres reiniciar la contraseña?');">
                            <input type="hidden" name="usuario_id" value="<?= $user['id'] ?>" />
                            <button type="submit" name="reset_password">Resetear</button>
                        </form>
                    </td>
                    <td><?= $user['activo'] ? 'Sí' : 'No' ?></td>
                    <td>
                        <a href="update_user.php?id=<?= $user['id'] ?>">Editar</a>
                    </td>
                    <td>
                        <?php if ($user['activo']): ?>
                            <form method="post" action="delete_user.php"
                                onsubmit="return confirm('¿Estás seguro que quieres desactivar este usuario?');"
                                style="display:inline;">
                                <input type="hidden" name="user_id" value="<?= $usuario['id'] ?>" />
                                <button type="submit">Eliminar</button>
                            </form>
                        <?php else: ?>
                            Inactivo
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
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