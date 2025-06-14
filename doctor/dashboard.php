<?php
$pageTitle = 'Dashboard del Doctor';
require_once '../includes/auth.php';
require_once '../includes/db.php';
include_once '../includes/header.php';
// Verifica que el usuario sea un doctor
verificarTipoUsuario('doctor');

$doctor_id = $_SESSION['usuario_id'];

// Conexión a la base de datos
$sql = "
    SELECT 
        v.id AS visita_id, 
        p.nombre, 
        p.apellido, 
        p.fecha_nacimiento, 
        v.notas, 
        v.fecha_llegada, 
        v.estado, 
        v.fecha_esperando_resultados,
        v.hora_de_cita
    FROM visitas v
    JOIN pacientes p ON v.paciente_id = p.id
    WHERE v.estado IN ('esperando', 'esperando resultados') AND v.atendido_por = ? AND fecha_llegada IS NOT NULL
    ORDER BY 
        CASE WHEN v.estado = 'esperando' THEN 0 ELSE 1 END,
        v.fecha_llegada ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $doctor_id);  // 'i' for integer
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Error al obtener pacientes: " . $conn->error);
}

$messages = [
    'visita_cancelada' => '✅ Visita cancelada correctamente.',
    'otro_evento' => 'Otro mensaje aquí...',
];

$pacientes = $result->fetch_all(MYSQLI_ASSOC);
?>

<h2>Pacientes Registrados</h2>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success">
        <?= $messages[$_GET['msg']] ?? 'Mensaje desconocido.' ?>
    </div>
<?php endif; ?>

<table class="table table-striped table-bordered table-hover">
    <thead class="table-dark">
        <tr>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>Edad</th>
            <th>Hora de cita</th>
            <th>Notas</th>
            <th>Estado</th>
            <th>Tiempo en espera</th>
            <th>Acción</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pacientes as $p): ?>
            <tr class="<?= $p['estado'] === 'esperando resultados' ? 'table-warning' : '' ?>">
                <td><?= htmlspecialchars($p['nombre']) ?></td>
                <td><?= htmlspecialchars($p['apellido']) ?></td>
                <td>
                    <?php
                    $birthDate = new DateTime($p['fecha_nacimiento']);
                    $today = new DateTime();
                    $ageInterval = $today->diff($birthDate);

                    if ($ageInterval->y >= 1) {
                        echo $ageInterval->y . ' años';
                    } else if ($ageInterval->m >= 1) {
                        echo $ageInterval->m . ' meses';
                    } else {
                        echo $ageInterval->d . ' días';
                    }
                    ?>
                </td>
                <td><?= htmlspecialchars(date('H:i', strtotime($p['hora_de_cita']))) ?></td>
                <td><?= htmlspecialchars($p['notas']) ?></td>
                <td>
                    <?php if ($p['estado'] === 'esperando resultados'): ?>
                        <span class="badge bg-warning text-dark">Esperando Resultados</span>
                    <?php else: ?>
                        <span class="badge bg-primary">Esperando</span>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="tiempo-espera" data-fecha-llegada="<?= htmlspecialchars(
                        $p['estado'] === 'esperando resultados'
                        ? $p['fecha_esperando_resultados']
                        : $p['fecha_llegada']
                    ) ?>"></span>
                </td>
                <td>
                    <form method="post" action="atender_paciente.php" class="d-inline">
                        <input type="hidden" name="visita_id" value="<?= $p['visita_id'] ?>">
                        <button type="submit" class="btn btn-success btn-sm me-2">Seleccionar</button>
                    </form>
                        |
                    <form method="POST" action="../general/cancelar_visita.php"
                        onsubmit="return confirm('¿Está seguro que desea cancelar esta visita?');" class="d-inline">
                        <input type="hidden" name="visita_id" value="<?= (int) $p['visita_id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm ms-2">
                            Cancelar
                        </button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <tr class="table-info clickable-row" data-href="../general/reporte_estadicticas.php"
            title="Ver Reporte de Visitas">
            <td colspan="8" style="text-align: center; font-weight: bold; cursor: pointer;">
                Ver Reporte de Visitas 📊
            </td>
        </tr>
    </tbody>
</table>

<script src="../js/actualizarTiempoEspera.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const rows = document.querySelectorAll(".clickable-row");
        rows.forEach(row => {
            row.addEventListener("click", () => {
                const href = row.getAttribute("data-href");
                if (href) window.open(href, "_blank");
            });
        });
    });
</script>
<br><br>

<?php include '../includes/footer.php'; ?>