<?php
$pageTitle = 'Dashboard del Doctor';
require_once '../includes/auth.php';
require_once '../includes/db.php';
include_once '../includes/header.php';
// Verifica que el usuario sea un doctor
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
                        <form method="post" action="atender_paciente.php" class="d-inline">
                            <input type="hidden" name="visita_id" value="<?= $p['visita_id'] ?>">
                            <button type="submit" class="btn btn-success btn-sm">Seleccionar</button>
                        </form>
                    </td>
                    <td>
                        <form method="POST" action="../general/cancelar_visita.php"
                            onsubmit="return confirm('¿Está seguro que desea cancelar esta visita?');" class="d-inline">
                            <input type="hidden" name="visita_id" value="<?= (int) $fila['visita_id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">
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

    <script src="../js/actualizarTiempoEspera.js"></script>
    <br><br>

    <?php include '../includes/footer.php'; ?>