<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
verificarTipoUsuario('doctor');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['visita_id'])) {
    die("Acceso no válido.");
}

$visita_id = intval($_POST['visita_id']);

$update = $conn->prepare("
    UPDATE visitas 
    SET estado = 'atendido',  
        fecha_entrada = NOW() 
    WHERE id = ?
");
$update->bind_param('i', $visita_id);
$update->execute();
$update->close();

// Obtener información del paciente
$sql = "
    SELECT p.nombre, p.apellido, p.fecha_nacimiento, p.cedula, v.fecha_llegada, v.notas
    FROM visitas v
    JOIN pacientes p ON v.paciente_id = p.id
    WHERE v.id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $visita_id);
$stmt->execute();
$result = $stmt->get_result();
$paciente = $result->fetch_assoc();
$stmt->close();

if (!$paciente) {
    die("Paciente no encontrado.");
}

$birthdate = new DateTime($paciente['fecha_nacimiento']);
$today = new DateTime();
$age = $birthdate->diff($today)->y;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Atendiendo a <?= htmlspecialchars($paciente['nombre']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div class="container mt-5">
        <a href="dashboard.php" class="btn btn-link mb-3">← Volver al panel</a>

        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Atendiendo a <?= htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido']) ?>
                </h4>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <strong>Fecha de nacimiento:</strong> <?= htmlspecialchars($paciente['fecha_nacimiento']) ?>
                        (<?= $age ?> años)
                    </li>
                    <li class="list-group-item">
                        <strong>Cédula:</strong> <?= htmlspecialchars($paciente['cedula']) ?>
                    </li>
                    <li class="list-group-item">
                        <strong>Fecha de llegada:</strong> <?= htmlspecialchars($paciente['fecha_llegada']) ?>
                    </li>
                    <li class="list-group-item">
                        <strong>Queja principal:</strong><br>
                        <?= nl2br(htmlspecialchars($paciente['notas'])) ?>
                    </li>
                </ul>
                <form method="post" action="enviar_a_laboratorio.php" class="mt-4">
                    <input type="hidden" name="visita_id" value="<?= $visita_id ?>">
                    <button type="submit" class="btn btn-warning">Enviar a Laboratorio</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.querySelector('form').addEventListener('submit', function (e) {
            const buttons = this.querySelectorAll('button[type="submit"]');
            buttons.forEach(btn => {
                btn.disabled = true;
                btn.innerText = 'Procesando...'; 
            });
        });
    </script>

</body>

</html>