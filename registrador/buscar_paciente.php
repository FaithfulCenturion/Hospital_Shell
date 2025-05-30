<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

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

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Buscar Paciente</title>
    <style>
        input[type="text"] {
            padding: 6px;
            width: 250px;
        }

        .btn {
            padding: 6px 12px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
        }

        .btn:hover {
            background: #0069d9;
        }

        table {
            margin-top: 20px;
            border-collapse: collapse;
            width: 100%;
        }

        th, td {
            border: 1px solid #999;
            padding: 8px;
        }

        th {
            background-color: #444;
            color: white;
        }
    </style>
</head>
<body>

<h2>Buscar Paciente</h2>

<form method="get">
    <input type="text" name="q" placeholder="Buscar por nombre, apellido o cédula" value="<?= htmlspecialchars($busqueda) ?>">
    <button class="btn" type="submit">Buscar</button>
</form>

<?php if (!empty($pacientes)): ?>
    <table>
        <thead>
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
                        <a class="btn" href="registrar_paciente.php?paciente_id=<?= $p['id'] ?>">Crear visita</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php elseif ($busqueda): ?>
    <p>No se encontraron pacientes con ese criterio.</p>
<?php endif; ?>

</body>
</html>
