<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
verificarTipoUsuario('doctor');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['visita_id'])) {
    die("Acceso no válido.");
}

$visita_id = intval($_POST['visita_id']);

$doctor_id = $_SESSION['usuario_id'];
$update = $conn->prepare("
    UPDATE visitas 
    SET estado = 'atendido', 
        atendido_por = ?, 
        fecha_atendido = NOW() 
    WHERE id = ?
");
$update->bind_param('ii', $doctor_id, $visita_id);
$update->execute();
$update->close();

// Obtener información del paciente
$sql = "
    SELECT p.nombre, p.apellido, p.fecha_nacimiento, p.cedula, v.fecha_llegada, v.queja
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
</head>
<body>

<h2>Atendiendo a <?= htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido']) ?></h2>

<ul>
    <li><strong>Fecha de nacimiento:</strong> <?= htmlspecialchars($paciente['fecha_nacimiento']) ?> (<?= $age ?> años)</li>
    <li><strong>Cédula:</strong> <?= htmlspecialchars($paciente['cedula']) ?></li>
    <li><strong>Fecha de llegada:</strong> <?= htmlspecialchars($paciente['fecha_llegada']) ?></li>
    <li><strong>Queja principal:</strong> <?= nl2br(htmlspecialchars($paciente['queja'])) ?></li>
</ul>

<br>
<a href="dashboard.php">← Volver al panel</a>

</body>
</html>
