<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

// Solo permitir acceso a doctores y administradores
if (!in_array($_SESSION['tipo_usuario'], ['doctor', 'administrador'])) {
    header('Location: ../login/unauthorized.php');
    exit;
}

$stats = null;
$error = '';
$fecha_inicio = date('Y-m-d', strtotime('-7 days'));
$fecha_fin = date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['fecha_inicio'], $_GET['fecha_fin'])) {
    $fecha_inicio = $_GET['fecha_inicio'];
    $fecha_fin = $_GET['fecha_fin'];

    // Validación simple
    if (!$fecha_inicio || !$fecha_fin || $fecha_inicio > $fecha_fin) {
        $error = "Por favor ingrese un rango de fechas válido.";
    } else {
        $sql = "
            SELECT 
                COUNT(*) AS total_pacientes,
                AVG(TIMESTAMPDIFF(SECOND, fecha_llegada, fecha_entrada)) AS espera_promedio,
                MAX(TIMESTAMPDIFF(SECOND, fecha_llegada, fecha_entrada)) AS espera_maxima
            FROM visitas
            WHERE estado = 'atendido'
              AND DATE(fecha_entrada) BETWEEN ? AND ?
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $fecha_inicio, $fecha_fin);
        $stmt->execute();
        $stmt->bind_result($total, $promedio, $maxima);

        if ($stmt->fetch()) {
            $stats = [
                'total' => $total,
                'promedio' => round($promedio / 60, 1), // minutos
                'maxima' => round($maxima / 60, 1),     // minutos
            ];
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Estadísticas</title>
</head>

<body>
    <a href="javascript:history.back()">← Volver al panel</a> <br><br>

    <h2>📊 Reporte de Estadísticas de Atención</h2>

    <form method="get">
        <label for="fecha_inicio">Desde:</label>
        <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?= htmlspecialchars($fecha_inicio) ?>"
            required>
        <label for="fecha_fin">Hasta:</label>
        <input type="date" id="fecha_fin" name="fecha_fin" value="<?= htmlspecialchars($fecha_fin) ?>" required>
        <button type="submit">Consultar</button>
    </form>

    <?php if ($error): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php elseif ($stats): ?>
        <h3>Resultados del <?= htmlspecialchars($fecha_inicio) ?> al <?= htmlspecialchars($fecha_fin) ?></h3>
        <ul>
            <li>Total de pacientes atendidos: <strong><?= $stats['total'] ?></strong></li>
            <li>Tiempo de espera promedio: <strong><?= $stats['promedio'] ?> minutos</strong></li>
            <li>Tiempo de espera más largo: <strong><?= $stats['maxima'] ?> minutos</strong></li>
        </ul>
    <?php endif; ?>

    <br>
    <a href="../<?= $_SESSION['tipo_usuario'] ?>/dashboard.php">← Volver al dashboard</a>

</body>

</html>