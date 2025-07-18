<?php
$pageTitle = 'Dashboard del Registrador';
require_once '../includes/auth.php';
require_once '../includes/db.php';
include_once '../includes/header.php';
// Verifica que el usuario sea un registrador

verificarTipoUsuario('registrador');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['marcar_llegada'])) {
    $visita_id = (int) ($_POST['visita_id'] ?? 0);

    // Confirmar que la visita existe, está esperando y fecha_llegada es NULL o vacío
    $stmt = $conn->prepare("
        SELECT id FROM visitas
        WHERE id = ? AND estado = 'esperando' AND fecha_llegada IS NULL
    ");
    $stmt->bind_param('i', $visita_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Actualizar fecha_llegada a NOW()
        $update = $conn->prepare("UPDATE visitas SET fecha_llegada = NOW() WHERE id = ?");
        $update->bind_param('i', $visita_id);
        if ($update->execute()) {
            // Redirigir para evitar resubmission y mostrar mensaje
            header("Location: " . $_SERVER['PHP_SELF'] . "?msg=marcado_llegada");
            exit;
        } else {
            $error_msg = "Error al marcar la llegada: " . $conn->error;
        }
        $update->close();
    } else {
        $error_msg = "No se pudo marcar la llegada para esta visita.";
    }
    $stmt->close();
}

// Consigue los pacientes que esperan hoy
$sql = "
    SELECT 
        v.id AS visita_id,
        p.nombre AS paciente_nombre,
        p.apellido AS paciente_apellido,
        p.fecha_nacimiento,
        v.fecha_llegada,
        v.estado,
        v.fecha_envio_laboratorio,
        v.hora_de_cita,
        u.nombre_usuario AS doctor_nombre
    FROM visitas v
    JOIN pacientes p ON v.paciente_id = p.id
    JOIN usuarios u ON v.atendido_por = u.id
    WHERE v.estado IN ('esperando', 'en laboratorio')
        AND DATE(v.hora_de_cita) = CURDATE()
    ORDER BY 
        u.nombre_usuario,
        v.fecha_llegada ASC
";
$result = $conn->query($sql);
?>

<div class="top-bar">
    <h2>Pacientes en espera</h2>
    <div class="btn-group mb-3">
        <a class="btn btn-success me-2" href="registrar_paciente.php">Registrar nuevo paciente</a>
        <a class="btn btn-secondary" href="buscar_paciente.php">Buscar paciente
            existente</a>
    </div>
</div>

<?php if (!empty($error_msg)): ?>
    <p style="color: red; font-weight: bold;"><?= htmlspecialchars($error_msg) ?></p>
<?php endif; ?>

<?php if (isset($_GET['msg'])): ?>
    <p style="color: green; font-weight: bold;">
        <?php
        // Map known messages to user-friendly text
        $messages = [
            'visita_cancelada' => '✅ Visita cancelada correctamente.',
            'marcado_para_resultados' => 'Ese paciente ha sido marcado como listo para el médico.',
            'doctor_cambiado' => '✅ Doctor asignado cambiado correctamente.'
        ];

        echo $messages[$_GET['msg']] ?? 'Mensaje desconocido.';
        ?>
    </p>
<?php endif; ?>


<?php if ($result->num_rows > 0): ?>
    <?php
    // Primero: visitas grupales del médico
    $doctores = [];
    while ($fila = $result->fetch_assoc()) {
        $doctor = $fila['doctor_nombre'];
        $doctores[$doctor][] = $fila;
    }

    // Segundo: ordenar la lista de pacientes de cada médico por estado y luego por fecha_llegada
    foreach ($doctores as &$visitas) {
        usort($visitas, function ($a, $b) {
            // 'esperando' viene antes de 'en laboratorio'
            $estadoA = $a['estado'] === 'esperando' ? 0 : 1;
            $estadoB = $b['estado'] === 'esperando' ? 0 : 1;

            if ($estadoA === $estadoB) {
                return strtotime($a['fecha_llegada']) <=> strtotime($b['fecha_llegada']);
            }
            return $estadoA <=> $estadoB;
        });
    }
    unset($visitas); // Romper la referencia por seguridad

    // Tercero: Ordenar los doctores por cantidad de pacientes (menos a más)
    uasort($doctores, fn($a, $b) => count($a) <=> count($b));
    ?>

    <?php foreach ($doctores as $doctor => $visitas): ?>
        <h4 class="mt-5"><?= htmlspecialchars($doctor) ?></h4>
        <table class="table table-striped table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Nombre</th>
                    <th>Fecha de nacimiento</th>
                    <th>Hora de cita</th>
                    <th>Hora de llegada</th>
                    <th>Tiempo en espera</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($visitas as $fila): ?>
                    <tr class="<?= $fila['estado'] === 'en laboratorio' ? 'table-secondary' : '' ?>">
                        <td><?= htmlspecialchars($fila['paciente_nombre']) . ' ' . htmlspecialchars($fila['paciente_apellido']) ?>
                        </td>
                        <td><?= htmlspecialchars($fila['fecha_nacimiento']) ?></td>
                        <td><?= date('H:i', strtotime($fila['hora_de_cita'])) ?></td>
                        <td>
                            <?= $fila['estado'] === 'en laboratorio'
                                ? date('H:i', strtotime($fila['fecha_envio_laboratorio']))
                                : date('H:i', strtotime($fila['fecha_llegada'])) ?>
                        </td>
                        <td>
                            <span class="tiempo-espera" data-fecha-llegada="<?= htmlspecialchars(
                                $fila['estado'] === 'en laboratorio' && !empty($fila['fecha_envio_laboratorio'])
                                ? $fila['fecha_envio_laboratorio']
                                : $fila['fecha_llegada']
                            ) ?>"></span>
                        </td>
                        <td>
                            <?php if ($fila['estado'] === 'en laboratorio'): ?>
                                <form method="POST" action="../general/marcar_para_resultados.php"
                                    onsubmit="return confirm('¿Marcar este paciente como esperando resultados?');" class="d-inline">
                                    <input type="hidden" name="visita_id" value="<?= (int) $fila['visita_id'] ?>">
                                    <button type="submit" class="btn btn-warning btn-sm">Listo para Resultados</button>
                                </form>
                            <?php elseif ($fila['estado'] === 'esperando' && empty($fila['fecha_llegada'])): ?>
                                <form method="POST" action="" onsubmit="return confirm('¿Marcar llegada del paciente?');"
                                    class="d-inline">
                                    <input type="hidden" name="visita_id" value="<?= (int) $fila['visita_id'] ?>">
                                    <button type="submit" name="marcar_llegada" class="btn btn-outline-success btn-sm">Marcar
                                        llegada</button>
                                </form>
                            <?php else: ?>
                                <a href="registrar_paciente.php?visita_id=<?= (int) $fila['visita_id'] ?>&modo=cambiar_doctor"
                                    class="btn btn-info btn-sm me-2">Cambiar Doctor</a>
                                |
                                <form method="POST" action="../general/cancelar_visita.php"
                                    onsubmit="return confirm('¿Está seguro que desea cancelar esta visita?');" class="d-inline">
                                    <input type="hidden" name="visita_id" value="<?= (int) $fila['visita_id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm ms-2">Cancelar</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endforeach; ?>
<?php else: ?>
    <p>No hay pacientes en espera.</p>
<?php endif; ?>

<br><br>
<script src="../js/actualizarTiempoEspera.js"></script>

<?php include '../includes/footer.php'; ?>