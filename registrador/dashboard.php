<?php
$pageTitle = 'Dashboard del Registrador';
require_once '../includes/auth.php';
require_once '../includes/db.php';
include_once '../includes/header.php';
// Verifica que el usuario sea un registrador

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
?>

    <div class="top-bar">
        <h2>Pacientes en espera</h2>
        <div class="btn-group mb-3">
            <a class="btn btn-success me-2" href="registrar_paciente.php">Registrar nuevo paciente</a>
            <a class="btn btn-secondary" href="buscar_paciente.php">Buscar paciente
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
        <table class="table table-striped table-bordered table-hover">
            <thead class="table-dark">
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
                                onsubmit="return confirm('¿Está seguro que desea cancelar esta visita?');" class="d-inline">
                                <input type="hidden" name="visita_id" value="<?= (int) $fila['visita_id'] ?>">
                                <button type="submit"
                                    class="btn btn-danger btn-sm">
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
    <script src="../js/actualizarTiempoEspera.js"></script>

<?php include '../includes/footer.php'; ?>