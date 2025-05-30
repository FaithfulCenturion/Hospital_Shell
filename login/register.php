<!-- register.php -->
 <?php
 require_once '../includes/auth.php';
 
verificarTipoUsuario('administrador');  // Only allow admin
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Registro - Hospital Shell</title>
</head>

<body>
<a href="javascript:history.back()" style="position: absolute; top: 10px; left: 10px; text-decoration: none; font-weight: bold;">← Volver</a>
    <h1>Registro de Usuario</h1>
    <form action="register_process.php" method="post">
        <label for="nombre_usuario">Usuario:</label><br />
        <input type="text" id="nombre_usuario" name="nombre_usuario" required /><br /><br />

        <label for="email">Correo Electrónico:</label><br />
        <input type="email" id="email" name="email" required /><br /><br />

        <label for="contraseña">Contraseña:</label><br />
        <input type="password" id="contraseña" name="contraseña" required /><br /><br />

        <label for="tipo_usuario">Tipo de usuario:</label>
        <select name="tipo_usuario" id="tipo_usuario" required>
            <option value="registrador">Registrador</option>
            <option value="doctor">Doctor</option>
            <option value="administrador">Administrador</option>
        </select>
        <br><br>

        <button type="submit">Registrar</button>
    </form>
</body>

</html>