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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container mt-5">
        <a href="javascript:history.back()" class="btn btn-outline-secondary mb-4">← Volver</a>

        <div class="card shadow">
            <div class="card-body">
                <h1 class="card-title text-center mb-4">Registro de Usuario</h1>

                <form action="register_process.php" method="post">
                    <div class="mb-3">
                        <label for="nombre_usuario" class="form-label">Usuario</label>
                        <input type="text" class="form-control" id="nombre_usuario" name="nombre_usuario" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label for="contraseña" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="contraseña" name="contraseña" required>
                    </div>

                    <div class="mb-3">
                        <label for="tipo_usuario" class="form-label">Tipo de usuario</label>
                        <select class="form-select" name="tipo_usuario" id="tipo_usuario" required>
                            <option value="registrador">Registrador</option>
                            <option value="doctor">Doctor</option>
                            <option value="administrador">Administrador</option>
                        </select>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Registrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>

</html>