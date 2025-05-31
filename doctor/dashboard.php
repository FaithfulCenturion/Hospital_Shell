<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
verificarTipoUsuario('doctor');

// Conexión a la base de datos
$sql = "
    SELECT v.id AS visita_id, p.nombre, p.apellido, p.fecha_nacimiento, v.queja_principal, v.fecha_llegada
    FROM visitas v
    JOIN pacientes p ON v.paciente_id = p.id
    WHERE v.estado = 'esperando'
    ORDER BY v.fecha_llegada ASC
";
$result = $conn->query($sql);

if (!$result) {
    die("Error al obtener pacientes: " . $conn->error);
}

$pacientes = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Panel del Doctor</title>
</head>

<body>
    <h2>Pacientes Registrados</h2>

    <?php if (isset($_GET['msg'])): ?>
        <p style="color: green; font-weight: bold;">
            <?php
            // Map known messages to user-friendly text
            $messages = [
                'visita_cancelada' => '✅ Visita cancelada correctamente.',
                'otro_evento' => 'Otro mensaje aquí...',
            ];

            echo $messages[$_GET['msg']] ?? 'Mensaje desconocido.';
            ?>
        </p>
    <?php endif; ?>

    <table border="1" cellpadding="8" style="border-collapse: collapse; width: 100%;">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Fecha de nacimiento</th>
                <th>Queja principal</th>
                <th>Tiempo en espera</th>
                <th>Acción</th>
                <th>Cancelar visita</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pacientes as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['nombre']) ?></td>
                    <td><?= htmlspecialchars($p['apellido']) ?></td>
                    <td><?= htmlspecialchars($p['fecha_nacimiento']) ?></td>
                    <td><?= htmlspecialchars($p['queja_principal']) ?></td>
                    <td>
                        <span class="tiempo-espera"
                            data-fecha-llegada="<?= htmlspecialchars($p['fecha_llegada']) ?>"></span>
                    </td>
                    <td>
                        <form method="post" action="atender_paciente.php" style="margin: 0;">
                            <input type="hidden" name="visita_id" value="<?= $p['visita_id'] ?>">
                            <button type="submit">Seleccionar</button>
                        </form>
                    </td>
                    <td>
                        <form method="POST" action="../general/cancelar_visita.php"
                            onsubmit="return confirm('¿Está seguro que desea cancelar esta visita?');">
                            <input type="hidden" name="visita_id" value="<?= (int) $fila['visita_id'] ?>">
                            <button type="submit"
                                style="background:#dc3545; color:white; border:none; padding:5px 10px; cursor:pointer;">
                                Cancelar
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="7">
                    <a href="../general/reporte_estadicticas.php" class="btn"
                        style="margin-left: 10px;" target="_blank">
                        Ver Reporte de Visitas
                    </a>
                </td>
            </tr>
        </tbody>
    </table>


    <br><br>

    <footer>
        <p style="display: flex; justify-content: space-between; align-items: center; margin: 0;">
            <a href="../general/password_reset.php">Restablecer mi contraseña</a>
            <a href="../login/logout.php">Cerrar sesión</a>
        </p>
    </footer>

    <script src="../js/actualizarTiempoEspera.js"></script>
</body>

</html>