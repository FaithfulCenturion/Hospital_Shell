<?php
require_once '../includes/db.php';

// Recopilar y desinfectar datos POST
$nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
$email = trim($_POST['email'] ?? '');
$contraseña = $_POST['contraseña'] ?? '';
$tipo_usuario = $_POST['tipo_usuario'];
$activo = 1;

// Validación básica
if (empty($nombre_usuario) || empty($email) || empty($contraseña) || empty($tipo_usuario))  {
    die("Por favor completa todos los campos.");
}

// Comprobar si el nombre de usuario o el correo electrónico ya existen
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE nombre_usuario = ? OR correo_electronico = ?");
$stmt->bind_param("ss", $nombre_usuario, $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    die("El nombre de usuario o correo electrónico ya está en uso.");
}
$stmt->close();

// Hash de la contraseña de forma segura
$contraseña_hash = password_hash($contraseña, PASSWORD_DEFAULT);

// Insertar nuevo usuario
$stmt = $conn->prepare("INSERT INTO usuarios (nombre_usuario, correo_electronico, contraseña, tipo_usuario, activo) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("ssssi", $nombre_usuario, $email, $contraseña_hash, $tipo_usuario, $activo);

if ($stmt->execute()) {
    echo "Registro exitoso. <a href='../index.php'>Inicia sesión aquí</a>.";
} else {
    echo "Error al registrar usuario: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
