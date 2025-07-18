<?php
$pageTitle = 'Registrar Paciente';
require_once '../includes/auth.php';
require_once '../includes/db.php';
include_once '../includes/header.php';
// Verifica que el usuario sea un registrador
verificarTipoUsuario('registrador');

$modoCambiarDoctor = isset($_GET['modo']) && $_GET['modo'] === 'cambiar_doctor';
$visita_id = $modoCambiarDoctor && isset($_GET['visita_id']) ? (int) $_GET['visita_id'] : null;

// Obtener la lista de doctores
$doctores = [];
$stmt = $conn->prepare("SELECT id, nombre_usuario FROM usuarios WHERE tipo_usuario = 'doctor'");
$stmt->execute();
$result = $stmt->get_result();
$doctores = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$mensaje = '';
$campos_desactivado = false;

if ($modoCambiarDoctor) {
    if (!$visita_id) {
        die("ID de visita inválido para cambiar doctor.");
    }

    // Obtener datos de la visita y paciente para el modo cambiar doctor
    $stmt = $conn->prepare("
        SELECT v.id AS visita_id, v.atendido_por, p.id AS paciente_id, p.nombre, p.apellido, v.fecha_llegada
        FROM visitas v
        JOIN pacientes p ON v.paciente_id = p.id
        WHERE v.id = ? AND fecha_llegada IS NOT NULL
    ");
    $stmt->bind_param('i', $visita_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $visita = $result->fetch_assoc();
    $stmt->close();

    if (!$visita) {
        die("Visita no encontrada.");
    }
}

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
    if ($modoCambiarDoctor) {
        // Solo actualizar doctor para la visita especificada
        $nuevo_doctor_id = $_POST['doctor_id'] ?? null;
        if (!$nuevo_doctor_id || !is_numeric($nuevo_doctor_id)) {
            $mensaje = "❌ Por favor seleccione un doctor válido.";
        } else {
            $stmt = $conn->prepare("UPDATE visitas SET atendido_por = ? WHERE id = ?");
            $stmt->bind_param("ii", $nuevo_doctor_id, $visita_id);
            if ($stmt->execute()) {
                $stmt->close();
                header("Location: dashboard.php?msg=doctor_cambiado");
                exit;
            } else {
                $mensaje = "⚠️ Error al cambiar doctor: " . $conn->error;
            }
            $stmt->close();
        }
    } else {
        $nombre = $_POST['nombre'] ?? '';
        $apellido = $_POST['apellido'] ?? '';
        $fechaNacimiento = $_POST['fecha_nacimiento'] ?? '';
        $notas = $_POST['notas'] ?? '';
        $cedula = $_POST['cedula'] ?? '';
        $genero = $_POST['genero'] ?? '';

        // Validar campos obligatorios
        if ($nombre && $apellido && $fechaNacimiento && $notas && $cedula && $genero) {
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
            $doctor_id = $_POST['doctor_id'] ?? null;
            $hora_de_cita = $_POST['hora_de_cita'] ?? null;

            if (!$doctor_id || !is_numeric($doctor_id)) {
                $mensaje = "❌ Por favor seleccione un doctor válido.";
                return; //Detener el procesamiento
            } else if (!$hora_de_cita) {
                $mensaje = "❌ Por favor ingrese la hora de la cita.";
                return; //Detener el procesamiento
            } else {
                // Convierta la cadena hora_de_cita en un objeto DateTime
                $horaCitaDT = new DateTime($hora_de_cita);
                $hora_de_cita_mysql = $horaCitaDT->format('Y-m-d H:i:s');

                $hoy = new DateTime('today', new DateTimeZone('America/Guayaquil'));
                // Comprueba si la cita es hoy
                $esHoy = $horaCitaDT->format('Y-m-d') === $hoy->format('Y-m-d');

                if ($esHoy) {
                    $stmt2 = $conn->prepare("
                    INSERT INTO visitas (paciente_id, fecha_llegada, notas, estado, atendido_por, hora_de_cita)
                    VALUES (?, NOW(), ?, ?, ?, ?)");
                } else {
                    $stmt2 = $conn->prepare("
                    INSERT INTO visitas (paciente_id, notas, estado, atendido_por, hora_de_cita)
                    VALUES (?, ?, ?, ?, ?)");
                }

                $stmt2->bind_param("issis", $paciente_id, $notas, $estado, $doctor_id, $hora_de_cita_mysql);

                if ($stmt2->execute()) {
                    //$mensaje = "✅ Paciente registrado correctamente: $nombre $apellido";
                    header("Location: dashboard.php"); // Redirigir al dashboard después de registrar
                    exit;
                } else {
                    $mensaje = "⚠️ Error al registrar la visita: " . $conn->error;
                }
                $stmt2->close();
            }
        } else {
            $mensaje = "❌ Por favor complete todos los campos.";
        }
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

    <form method="post" autocomplete="off" class="row g-3" novalidate>
        <?php if ($modoCambiarDoctor): ?>
            <h3>Cambiar doctor para paciente: <?= htmlspecialchars($visita['nombre'] . ' ' . $visita['apellido']) ?></h3>

            <div class="col-md-6">
                <label for="doctor_id" class="form-label">Doctor asignado:</label>
                <select id="doctor_id_cambiar" name="doctor_id" class="form-select" required>
                    <option value="">Seleccione un doctor...</option>
                    <?php foreach ($doctores as $doc): ?>
                        <option value="<?= $doc['id'] ?>" <?= $doc['id'] == $visita['atendido_por'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($doc['nombre_usuario']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary">Guardar cambio de doctor</button>
                <a href="dashboard.php" class="btn btn-secondary">Cancelar</a>
            </div>

        <?php else: ?>
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
                    value="<?= htmlspecialchars($prellenado['fecha_nacimiento']) ?>" <?= $campos_desactivado ? 'readonly' : '' ?> required>
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

            <div class="col-md-6">
                <label for="doctor_id" class="form-label">Asignar a Doctor:</label>
                <select id="doctor_id_nuevo" name="doctor_id" class="form-select" required>
                    <option value="">Seleccione un doctor...</option>
                    <?php foreach ($doctores as $doc): ?>
                        <option value="<?= $doc['id'] ?>">
                            <?= htmlspecialchars($doc['nombre_usuario']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-12">
                <label for="notas" class="form-label">Notas:</label>
                <textarea id="notas" name="notas" rows="3" class="form-control" required></textarea>
            </div>

            <?php
            $zonaHoraria = new DateTimeZone('America/Guayaquil');
            $ahora = new DateTime('now', $zonaHoraria);
            $minCita = $ahora->format('Y-m-d\TH:i');
            $horaCitaPorDefecto = $ahora->modify('+15 minutes')->format('Y-m-d\TH:i');
            ?>

            <div class="col-md-6">
                <label for="hora_de_cita" class="form-label">Hora de la cita:</label>
                <input type="datetime-local" id="hora_de_cita" name="hora_de_cita" class="form-control"
                    value="<?= $horaCitaPorDefecto ?>" min="<?= $minCita ?>" required>
            </div>

            <?php if ($campos_desactivado): ?>
                <?php foreach ($prellenado as $key => $value): ?>
                    <input type="hidden" name="<?= $key ?>" value="<?= htmlspecialchars($value) ?>">
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="col-12">
                <button type="submit" class="btn btn-primary">Registrar paciente</button>
            </div>
        <?php endif; ?>
    </form>
</div>

<script>
    document.querySelector('form').addEventListener('submit', function (e) {
        const doctorSelect = document.querySelector('select[name="doctor_id"]');
        const horaCitaInput = document.getElementById('hora_de_cita');

        // Asegúrese de que se seleccione un médico
        if (!doctorSelect.value) {
            alert('❌ Por favor seleccione un doctor.');
            doctorSelect.focus();
            e.preventDefault();
            return;
        }

        // Asegúrese de que la cita sea en el futuro
        const horaCita = new Date(horaCitaInput.value);
        const ahora = new Date();

        if (horaCita <= ahora) {
            alert('❌ La hora de la cita debe ser en el futuro.');
            horaCitaInput.focus();
            e.preventDefault();
            return;
        }

        // Deshabilitar los botones de envío
        this.querySelectorAll('button[type="submit"]').forEach(btn => {
            btn.disabled = true;
            btn.innerText = 'Procesando...';
        });
    });
</script>

<?php include '../includes/footer.php'; ?>