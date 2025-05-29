<?php
require_once '../includes/auth.php';
verificarTipoUsuario('doctor'); // Adjust this to your role structure

require_once '../includes/db.php'; // your PDO connection

try {
    $stmt = $pdo->query("SELECT nombre, apellido, fecha_nacimiento, queja, fecha_registro 
                         FROM pacientes 
                         ORDER BY fecha_registro DESC");
    $pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener pacientes: " . $e->getMessage());
}
?>

<h2>Pacientes Registrados</h2>
<table border="1" cellpadding="8">
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

<footer>
    <p style="display: flex; justify-content: space-between; align-items: center; margin: 0;">
        <a href="../general/password_reset.php">Restablecer mi contraseña</a>
        <a href="../login/logout.php">Cerrar sesión</a>
    </p>
</footer>
