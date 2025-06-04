<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

// Allow only admin and doctor roles
if (!in_array($_SESSION['tipo_usuario'], ['admin', 'doctor'])) {
    header('Location: ../login/unauthorized.php');
    exit;
}

// Get visits data (you can customize the query)
$sql = "
    SELECT v.id AS visita_id, p.nombre, p.apellido, v.fecha_llegada, v.notas, v.estado
    FROM visitas v
    JOIN pacientes p ON v.paciente_id = p.id
    ORDER BY v.fecha_llegada DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Visitas</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
        }

        th {
            background-color: #333;
            color: white;
        }

        h2 {
            margin-top: 0;
        }
    </style>
</head>

<body>
    <a href="javascript:history.back()">← Volver al panel</a> <br><br>

    <h2>Reporte de Visitas</h2>

    <?php if ($result && $result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Nombre del paciente</th>
                    <th>Fecha de llegada</th>
                    <th>Queja principal</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($fila = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($fila['nombre']) . ' ' . htmlspecialchars($fila['apellido']) ?></td>
                        <td><?= date('Y-m-d H:i', strtotime($fila['fecha_llegada'])) ?></td>
                        <td><?= htmlspecialchars($fila['notas']) ?></td>
                        <td><?= htmlspecialchars($fila['estado']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No hay visitas registradas.</p>
    <?php endif; ?>

    <br><br>

    <footer>
        <p style="display: flex; justify-content: space-between; align-items: center; margin: 0;">
            <a href="password_reset.php">Restablecer mi contraseña</a>
            <a href="../login/logout.php">Cerrar sesión</a>
        </p>
    </footer>

</body>

</html>