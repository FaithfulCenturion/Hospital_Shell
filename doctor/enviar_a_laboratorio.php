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
    SET estado = 'en laboratorio',
        fecha_envio_laboratorio = NOW()  
    WHERE id = ?
");
$update->bind_param('i', $visita_id);
$update->execute();
$update->close();

// Optional: redirect back to attending screen or dashboard
header("Location: dashboard.php");
exit;
