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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>


<body class="bg-light">

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📊 Reporte de Estadísticas de Atención</h2>
        <a href="javascript:history.back()" class="btn btn-secondary">← Volver</a>
    </div>

    <form method="get" class="row g-3 mb-4">
        <div class="col-md-4">
            <label for="fecha_inicio" class="form-label">Desde:</label>
            <input type="date" id="fecha_inicio" name="fecha_inicio"
                   value="<?= htmlspecialchars($fecha_inicio) ?>" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label for="fecha_fin" class="form-label">Hasta:</label>
            <input type="date" id="fecha_fin" name="fecha_fin"
                   value="<?= htmlspecialchars($fecha_fin) ?>" class="form-control" required>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Consultar</button>
        </div>
    </form>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($stats): ?>
        <div class="card">
            <div class="card-header bg-success text-white">
                Resultados del <?= htmlspecialchars($fecha_inicio) ?> al <?= htmlspecialchars($fecha_fin) ?>
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item">Total de pacientes atendidos: <strong><?= $stats['total'] ?></strong></li>
                <li class="list-group-item">Tiempo de espera promedio: <strong><?= $stats['promedio'] ?> minutos</strong></li>
                <li class="list-group-item">Tiempo de espera más largo: <strong><?= $stats['maxima'] ?> minutos</strong></li>
            </ul>
        </div>
    <?php endif; ?>

    <div class="mt-4 text-center">
        <a href="../<?= $_SESSION['tipo_usuario'] ?>/dashboard.php" class="btn btn-outline-primary">
            ← Volver al dashboard
        </a>
    </div>
</div>

</body>
</html>