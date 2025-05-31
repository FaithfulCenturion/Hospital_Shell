<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
verificarTipoUsuario('registrador');


$mensaje = '';
$campos_desactivado = false;

$prellenado = [
    'nombre' => '',
    'apellido' => '',
    'fecha_nacimiento' => '',
    'cedula' => '',
    'genero' => '',
];

$paciente_id = null;

if (isset($_GET['paciente_id']) && is_numeric($_GET['paciente_id'])) {
    $paciente_id = (int) $_GET['paciente_id'];
    $stmt = $conn->prepare("SELECT nombre, apellido, fecha_nacimiento, cedula, genero FROM pacientes WHERE id = ?");
    $stmt->bind_param("i", $paciente_id);

    $stmt->execute();

    // Temporary variables to hold the result
    $nombre = $apellido = $fechaNacimiento = $cedula = $genero = null;
    $stmt->bind_result($nombre, $apellido, $fechaNacimiento, $cedula, $genero);

    if ($stmt->fetch()) {
        $prellenado = [
            'nombre' => $nombre,
            'apellido' => $apellido,
            'fecha_nacimiento' => $fechaNacimiento,
            'cedula' => $cedula,
            'genero' => $genero,
        ];
        $campos_desactivado = true;
    } else {
        $paciente_id = null; // fallback
    }
    $stmt->close();
}

// Comprobar el envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $apellido = $_POST['apellido'] ?? '';
    $fechaNacimiento = $_POST['fecha_nacimiento'] ?? '';
    $queja = $_POST['queja'] ?? '';
    $cedula = $_POST['cedula'] ?? '';
    $genero = $_POST['genero'] ?? '';

    // Validar campos obligatorios
    if ($nombre && $apellido && $fechaNacimiento && $queja && $cedula && $genero) {
        // Si se trata de un paciente nuevo, insertar en pacientes
        if (!$campos_desactivado || !$paciente_id) {
            $stmt = $conn->prepare("INSERT INTO pacientes (nombre, apellido, fecha_nacimiento, cedula, genero) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $nombre, $apellido, $fechaNacimiento, $cedula, $genero);
            if ($stmt->execute()) {
                $paciente_id = $stmt->insert_id;
            } else {
                $mensaje = "⚠️ Error al registrar paciente: " . $conn->error;
                $stmt->close();
                return;
            }
            $stmt->close();
        }

        // Insertar en visitas (tanto para pacientes nuevos como existentes)
        $estado = 'esperando';
        $stmt2 = $conn->prepare("INSERT INTO visitas (paciente_id, fecha_llegada, queja_principal, estado) VALUES (?, NOW(), ?, ?)");
        $stmt2->bind_param("iss", $paciente_id, $queja, $estado);
        if ($stmt2->execute()) {
            //$mensaje = "✅ Paciente registrado correctamente: $nombre $apellido";
            header("Location: dashboard.php"); // Redirigir al dashboard después de registrar
            exit;
        } else {
            $mensaje = "⚠️ Error al registrar la visita: " . $conn->error;
        }
        $stmt2->close();

    } else {
        $mensaje = "❌ Por favor complete todos los campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Registrar Paciente</title>
</head>

<body>
    <a href="javascript:history.back()"
        style="position: absolute; top: 10px; left: 10px; text-decoration: none; font-weight: bold;">← Volver</a>


    <h2>
        Registrar <?= $campos_desactivado ? 'paciente' : 'nuevo paciente' ?>
    </h2>

    <?php if (!empty($mensaje)): ?>
        <p style="color: green;"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>

    <form method="post" autocomplete="off">
        <label for="nombre">Nombre:</label><br>
        <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($prellenado['nombre']) ?>"
            <?= $campos_desactivado ? 'readonly' : '' ?> required><br>

        <label for="apellido">Apellido:</label><br>
        <input type="text" id="apellido" name="apellido" value="<?= htmlspecialchars($prellenado['apellido']) ?>"
            <?= $campos_desactivado ? 'readonly' : '' ?> required><br>

        <label for="fecha_nacimiento">Fecha de nacimiento:</label><br>
        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento"
            value="<?= htmlspecialchars($prellenado['fecha_nacimiento']) ?>" <?= $campos_desactivado ? 'readonly' : '' ?>
            required><br>

        <label for="genero">Género:</label><br>
        <select id="genero" name="genero" required <?= $campos_desactivado ? 'disabled' : '' ?>>
            <option value="">Seleccione...</option>
            <option value="M" <?= isset($genero) && $genero === 'M' ? 'selected' : '' ?>>Masculino</option>
            <option value="F" <?= isset($genero) && $genero === 'F' ? 'selected' : '' ?>>Femenino</option>
            <option value="Otro" <?= isset($genero) && $genero === 'Otro' ? 'selected' : '' ?>>Otro</option>
        </select><br>


        <label for="cedula">Cédula:</label><br>
        <input type="text" id="cedula" name="cedula" value="<?= htmlspecialchars($prellenado['cedula']) ?>"
            <?= $campos_desactivado ? 'readonly' : '' ?> required><br>

        <label for="queja">Queja principal:</label><br>
        <textarea id="queja" name="queja" rows="3" cols="30" required></textarea><br><br>

        <?php if ($campos_desactivado): ?>
            <input type="hidden" name="nombre" value="<?= htmlspecialchars($prellenado['nombre']) ?>">
            <input type="hidden" name="apellido" value="<?= htmlspecialchars($prellenado['apellido']) ?>">
            <input type="hidden" name="fecha_nacimiento" value="<?= htmlspecialchars($prellenado['fecha_nacimiento']) ?>">
            <input type="hidden" name="genero" value="<?= htmlspecialchars($prellenado['genero']) ?>">
            <input type="hidden" name="cedula" value="<?= htmlspecialchars($prellenado['cedula']) ?>">
        <?php endif; ?>

        <button type="submit">Registrar paciente</button>
    </form>

    <br><br>

    <footer>
        <p style="display: flex; justify-content: space-between; align-items: center; margin: 0;">
            <a href="../general/password_reset.php">Restablecer mi contraseña</a>
            <a href="../login/logout.php">Cerrar sesión</a>
        </p>
    </footer>

</body>

</html>