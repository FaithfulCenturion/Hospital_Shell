<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

verificarTipoUsuario(['registrador', 'doctor']);

switch ($_SESSION['tipo_usuario'] ?? '') {
    case 'doctor':
        $dashboard = '../doctor/dashboard.php';
        break;
    case 'registrador':
        $dashboard = '../registrador/dashboard.php';
        break;
    // add more roles if needed
    default:
        $dashboard = '../login/login.php'; // fallback or unauthorized
        break;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['visita_id']) && is_numeric($_POST['visita_id'])) {
    $visita_id = (int) $_POST['visita_id'];

    // Comprobar si la visita existe y actualmente está 'esperando'
    $stmtCheck = $conn->prepare("SELECT estado FROM visitas WHERE id = ?");
    $stmtCheck->bind_param("i", $visita_id);
    $stmtCheck->execute();
    $stmtCheck->bind_result($estadoActual);
    if ($stmtCheck->fetch() && $estadoActual === 'esperando') {
        $stmtCheck->close();

        // Actualizar estado to 'cancelado'
        $stmt = $conn->prepare("UPDATE visitas SET estado = 'cancelado' WHERE id = ?");
        $stmt->bind_param("i", $visita_id);
        if ($stmt->execute()) {
            $stmt->close();
            // Redirigir nuevamente con un mensaje de éxito o simplemente redirigir
            header("Location: $dashboard?msg=visita_cancelada");
            exit;
        } else {
            $stmt->close();
            // Manejar errores de base de datos
            die("Error al cancelar la visita: " . $conn->error);
        }
    } else {
        $stmtCheck->close();
        // Visita no encontrada o no esperando
        die("La visita no existe o no puede ser cancelada.");
    }
} else {
    // Solicitud no válida
    header("Location: $dashboard?msg=visita_cancelada");
    exit;
}
