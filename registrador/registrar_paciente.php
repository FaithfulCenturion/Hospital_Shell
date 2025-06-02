<?php
$pageTitle = 'Registrar Paciente';
require_once '../includes/auth.php';
require_once '../includes/db.php';
include_once '../includes/header.php';
// Verifica que el usuario sea un registrador
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

    // Variables temporales para almacenar el resultado
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
        $paciente_id = null; // retroceder
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

<div class="container mt-4">
    <a href="javascript:history.back()" class="btn btn-outline-secondary mb-3">← Volver</a>

    <h2 class="mb-4">
        Registrar <?= $campos_desactivado ? 'paciente' : 'nuevo paciente' ?>
    </h2>

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-info"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <form method="post" autocomplete="off" class="row g-3">
        <div class="col-md-6">
            <label for="nombre" class="form-label">Nombre:</label>
            <input type="text" id="nombre" name="nombre" class="form-control"
                value="<?= htmlspecialchars($prellenado['nombre']) ?>" <?= $campos_desactivado ? 'readonly' : '' ?>
                required>
        </div>

        <div class="col-md-6">
            <label for="apellido" class="form-label">Apellido:</label>
            <input type="text" id="apellido" name="apellido" class="form-control"
                value="<?= htmlspecialchars($prellenado['apellido']) ?>" <?= $campos_desactivado ? 'readonly' : '' ?>
                required>
        </div>

        <div class="col-md-6">
            <label for="fecha_nacimiento" class="form-label">Fecha de nacimiento:</label>
            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="form-control"
                value="<?= htmlspecialchars($prellenado['fecha_nacimiento']) ?>"
                <?= $campos_desactivado ? 'readonly' : '' ?> required>
        </div>

        <div class="col-md-6">
            <label for="genero" class="form-label">Género:</label>
            <select id="genero" name="genero" class="form-select" required <?= $campos_desactivado ? 'disabled' : '' ?>>
                <option value="">Seleccione...</option>
                <option value="M" <?= isset($genero) && $genero === 'M' ? 'selected' : '' ?>>Masculino</option>
                <option value="F" <?= isset($genero) && $genero === 'F' ? 'selected' : '' ?>>Femenino</option>
                <option value="Otro" <?= isset($genero) && $genero === 'Otro' ? 'selected' : '' ?>>Otro</option>
            </select>
        </div>

        <div class="col-md-6">
            <label for="cedula" class="form-label">Cédula:</label>
            <input type="text" id="cedula" name="cedula" class="form-control"
                value="<?= htmlspecialchars($prellenado['cedula']) ?>" <?= $campos_desactivado ? 'readonly' : '' ?>
                required>
        </div>

        <div class="col-md-12">
            <label for="queja" class="form-label">Queja principal:</label>
            <textarea id="queja" name="queja" rows="3" class="form-control" required></textarea>
        </div>

        <?php if ($campos_desactivado): ?>
            <?php foreach ($prellenado as $key => $value): ?>
                <input type="hidden" name="<?= $key ?>" value="<?= htmlspecialchars($value) ?>">
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="col-12">
            <button type="submit" class="btn btn-primary">Registrar paciente</button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>