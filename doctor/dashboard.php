<?php
require_once '../includes/auth.php';
verificarTipoUsuario('doctor');
?>


doctor

<footer>
    <p style="display: flex; justify-content: space-between; align-items: center; margin: 0;">
        <a href="../general/password_reset.php">Restablecer mi contraseña</a>
        <a href="../login/logout.php">Cerrar sesión</a>
    </p>
</footer>
