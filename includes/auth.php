<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../index.php");
    exit;
}

// Optional: redirect based on role
function verificarTipoUsuario($tipos_permitido) {
    if(is_string($tipos_permitido)) {
        $tipos_permitido = [$tipos_permitido];
    }

    if(!in_array($_SESSION['tipo_usuario'], $tipos_permitido)) {
        header("Location: ../index.php");
        exit;
    }
}
?>
