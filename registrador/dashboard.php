<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

verificarTipoUsuario('registrador');

// Consigue los pacientes que esperan hoy
$sql = "
    SELECT v.id AS visita_id, p.nombre, p.apellido, p.fecha_nacimiento, v.fecha_llegada
    FROM visitas v
    JOIN pacientes p ON v.paciente_id = p.id
    WHERE v.estado = 'esperando'
    ORDER BY v.fecha_llegada ASC
";
$result = $conn->query($sql);

function tiempoEspera($fecha_llegada)
{
    $espera = time() - strtotime($fecha_llegada);
    $min = floor($espera / 60);
    return $min . ' min';
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Registrador del panel de control</title>
    <style>
        table {
            border-collapse: collapse;
            margin: 20px 0;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #999;
            padding: 8px;
            text-align: left;
        }

        th {
            background: #444;
            color: white;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn {
            padding: 8px 16px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }

        .btn:hover {
            background: #218838;
        }
    </style>
</head>

<body>

    <div class="top-bar">
        <h2>Pacientes en espera</h2>
        <div>
            <a class="btn" href="registrar_paciente.php">Registrar nuevo paciente</a>
            <a class="btn" href="buscar_paciente.php" style="background: #007bff; margin-left: 10px;">Buscar paciente
                existente</a>
        </div>
    </div>

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


    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Fecha de nacimiento</th>
                    <th>Hora de llegada</th>
                    <th>Tiempo en espera</th>
                    <th>Cancelar visita</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($fila = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($fila['nombre']) . ' ' . htmlspecialchars($fila['apellido']) ?></td>
                        <td><?= htmlspecialchars($fila['fecha_nacimiento']) ?></td>
                        <td><?= date('H:i', strtotime($fila['fecha_llegada'])) ?></td>
                        <td>
                            <span class="tiempo-espera"
                                data-fecha-llegada="<?= htmlspecialchars($fila['fecha_llegada']) ?>"></span>
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
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No hay pacientes en espera.</p>
    <?php endif; ?>

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