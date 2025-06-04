<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
verificarTipoUsuario('registrador');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['visita_id'])) {
    $visita_id = (int) $_POST['visita_id'];

    $stmt = $conn->prepare("UPDATE visitas SET estado = 'esperando resultados', fecha_esperando_resultados = NOW()  WHERE id = ?");
    $stmt->bind_param("i", $visita_id);
    $stmt->execute();
    $stmt->close();

    header("Location: ../registrador/dashboard.php?msg=marcado_para_resultados");
    exit;
} else {
    die("Acceso no válido.");
}
