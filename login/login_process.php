<?php
session_start();
require_once '../includes/db.php';

// Recopilar y desinfectar datos POST
$nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
$contraseña = $_POST['contraseña'] ?? '';

if (empty($nombre_usuario) || empty($contraseña)) {
    die("Por favor completa todos los campos.");
}

// Recuperar al usuario de la base de datos
$stmt = $conn->prepare("SELECT id, nombre_usuario, contraseña, tipo_usuario FROM usuarios WHERE nombre_usuario = ? AND activo = 1");
$stmt->bind_param("s", $nombre_usuario);
$stmt->execute();

$result = $stmt->get_result();
if ($result && $result->num_rows === 1) {
    $usuario = $result->fetch_assoc();

    if (password_verify($contraseña, $usuario['contraseña'])) {
        // Inicio de sesión exitoso: establecer variables de sesión
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nombre_usuario'] = $usuario['nombre_usuario'];
        $_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];

        //  Redirigir al panel de usuario
        switch ($_SESSION['tipo_usuario']) {
            case 'administrador':
                header("Location: ../admin/dashboard.php");
                break;
            case 'doctor':
                header("Location: ../doctor/dashboard.php");
                break;
            case 'registrador':
                header("Location: ../registrador/dashboard.php");
                break;
            default:
                echo "Tipo de usuario desconocido.";
                break;
        }
        exit;
    } else {
        echo "Contraseña incorrecta. <a href='../index.php'>Intenta de nuevo</a>.";
    }
} else {
    echo "Usuario no encontrado o inactivo. <a href='../index.php'>Intenta de nuevo</a>.";
}

$stmt->close();

$conn->close();
?>