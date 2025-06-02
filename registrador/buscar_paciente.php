<?php
$pageTitle = 'Buscar Paciente';
require_once '../includes/auth.php';
require_once '../includes/db.php';
include_once '../includes/header.php';
// Verifica que el usuario sea un registrador
verificarTipoUsuario('registrador');

$busqueda = $_GET['q'] ?? '';
$pacientes = [];

if ($busqueda) {
    $stmt = $conn->prepare("SELECT id, nombre, apellido, fecha_nacimiento, cedula FROM pacientes WHERE nombre LIKE CONCAT('%', ?, '%') OR apellido LIKE CONCAT('%', ?, '%') OR cedula LIKE CONCAT('%', ?, '%')");
    $stmt->bind_param("sss", $busqueda, $busqueda, $busqueda);
    $stmt->execute();
    $result = $stmt->get_result();
    $pacientes = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>

<div class="container mt-4">
    <a href="javascript:history.back()" class="btn btn-outline-secondary mb-3">← Volver</a>

    <h2 class="mb-4">Buscar Paciente</h2>

    <form method="get" class="mb-4 row g-2">
        <div class="col-sm-8">
            <input type="text" name="q" class="form-control" placeholder="Buscar por nombre, apellido o cédula" value="<?= htmlspecialchars($busqueda) ?>">
        </div>
        <div class="col-sm-4">
            <button class="btn btn-primary w-100" type="submit">Buscar</button>
        </div>
    </form>

    <?php if (!empty($pacientes)): ?>
        <table class="table table-bordered table-striped">
            <thead class="table-light">
                <tr>
                    <th>Nombre</th>
                    <th>Fecha de nacimiento</th>
                    <th>Cédula</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pacientes as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['nombre'] . ' ' . $p['apellido']) ?></td>
                        <td><?= htmlspecialchars($p['fecha_nacimiento']) ?></td>
                        <td><?= htmlspecialchars($p['cedula']) ?></td>
                        <td>
                            <a class="btn btn-success btn-sm" href="registrar_paciente.php?paciente_id=<?= $p['id'] ?>">Crear visita</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif ($busqueda): ?>
        <div class="alert alert-warning">No se encontraron pacientes con ese criterio.</div>
    <?php endif; ?>
</div>

</body>
</html>
