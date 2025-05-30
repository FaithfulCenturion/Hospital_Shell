<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
verificarTipoUsuario('doctor');

// Conexión a la base de datos
$sql = "
    SELECT v.id AS visita_id, p.nombre, p.apellido, p.fecha_nacimiento, v.fecha_llegada
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
<table border="1" cellpadding="8" style="border-collapse: collapse; width: 100%;">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>Fecha de nacimiento</th>
            <th>Queja principal</th>
            <th>Fecha de registro</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pacientes as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['nombre']) ?></td>
                <td><?= htmlspecialchars($p['apellido']) ?></td>
                <td><?= htmlspecialchars($p['fecha_nacimiento']) ?></td>
                <td><?= htmlspecialchars($p['queja']) ?></td>
                <td><?= htmlspecialchars($p['fecha_registro']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>


<br><br>

<footer>
    <p style="display: flex; justify-content: space-between; align-items: center; margin: 0;">
        <a href="../general/password_reset.php">Restablecer mi contraseña</a>
        <a href="../login/logout.php">Cerrar sesión</a>
    </p>
</footer>
</body>
</html>