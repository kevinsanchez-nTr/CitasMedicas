<?php
session_start();
require_once __DIR__ . '/../includes/conexion.php';

// Solo admin
if (!isset($_SESSION['id_usuarios']) || ($_SESSION['rol_id'] ?? null) != 1) {
    header("Location: ../dashboard.php");
    exit();
}

$mensaje = "";
$error = "";

// Validar ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['mensaje_error'] = "❌ ID de médico no válido.";
    header("Location: lista_medicos.php");
    exit();
}

$id_medico = intval($_GET['id']);

// Obtener datos del médico + usuario
$stmt = $conn->prepare("
    SELECT 
        m.id_medicos,
        m.usuario_id,
        m.especialidad_id,
        m.junta_medica,
        m.experiencia_anios,
        m.consultorio,
        m.horario_atencion,
        m.biografia,
        u.nombre_completo,
        u.correo,
        u.telefono,
        u.direccion
    FROM medicos m
    INNER JOIN usuarios u ON m.usuario_id = u.id_usuarios
    WHERE m.id_medicos = ?
");
$stmt->bind_param("i", $id_medico);
$stmt->execute();
$medico = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$medico) {
    $_SESSION['mensaje_error'] = "❌ Médico no encontrado.";
    header("Location: lista_medicos.php");
    exit();
}

// Especialidades
$especialidades = $conn->query("SELECT * FROM especialidades ORDER BY nombre ASC");

// Procesar actualización
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $telefono          = trim($_POST['telefono'] ?? '');
    $direccion         = trim($_POST['direccion'] ?? '');
    $especialidad_id   = $_POST['especialidad_id'] !== "" ? intval($_POST['especialidad_id']) : null;

    $junta_medica      = trim($_POST['junta_medica'] ?? '');
    $experiencia_anios = $_POST['experiencia_anios'] !== '' ? intval($_POST['experiencia_anios']) : null;
    $consultorio       = trim($_POST['consultorio'] ?? '');
    $horario_atencion  = trim($_POST['horario_atencion'] ?? '');
    $biografia         = trim($_POST['biografia'] ?? '');

    // Actualizar usuario
    $stmt1 = $conn->prepare("UPDATE usuarios SET telefono = ?, direccion = ? WHERE id_usuarios = ?");
    $stmt1->bind_param("ssi", $telefono, $direccion, $medico['usuario_id']);
    $ok1 = $stmt1->execute();
    $stmt1->close();

    // Actualizar médico
    $stmt2 = $conn->prepare("
        UPDATE medicos 
        SET especialidad_id = ?, junta_medica = ?, experiencia_anios = ?, 
            consultorio = ?, horario_atencion = ?, biografia = ?
        WHERE id_medicos = ?
    ");
    $stmt2->bind_param(
        "isisssi",
        $especialidad_id,
        $junta_medica,
        $experiencia_anios,
        $consultorio,
        $horario_atencion,
        $biografia,
        $id_medico
    );
    $ok2 = $stmt2->execute();
    $stmt2->close();

    if ($ok1 && $ok2) {
        $mensaje = "✅ Médico actualizado correctamente.";
        // Actualizar arreglo local para mostrar cambios
        $medico['telefono']          = $telefono;
        $medico['direccion']         = $direccion;
        $medico['especialidad_id']   = $especialidad_id;
        $medico['junta_medica']      = $junta_medica;
        $medico['experiencia_anios'] = $experiencia_anios;
        $medico['consultorio']       = $consultorio;
        $medico['horario_atencion']  = $horario_atencion;
        $medico['biografia']         = $biografia;
    } else {
        $error = "❌ Error al actualizar el médico.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Médico</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        :root {
            --azul-menu: #00B8F0;
            --azul-oscuro: #008ABD;
        }
        .btn-azul { background-color: var(--azul-menu); color:white; }
        .btn-azul:hover { background-color: var(--azul-oscuro); color:white; }
        .titulo-azul { color: var(--azul-menu); }
    </style>
</head>
<body>

<?php include "../includes/menu.php"; ?>

<div class="content p-4">
    <div class="container">
        <div class="card shadow-lg">
            <div class="card-header" style="background-color:blue;color:white;">
                <h4 class="mb-0">✏️ Editar Médico</h4>
            </div>
            <div class="card-body">

                <?php if ($mensaje): ?>
                    <div class="alert alert-success"><?= $mensaje ?></div>
                <?php elseif ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <h5 class="titulo-azul mb-3">Datos básicos</h5>

                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre completo</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($medico['nombre_completo']) ?>" disabled>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Correo</label>
                            <input type="email" class="form-control" value="<?= htmlspecialchars($medico['correo']) ?>" disabled>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="telefono" class="form-control" value="<?= htmlspecialchars($medico['telefono']) ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Dirección</label>
                            <input type="text" name="direccion" class="form-control" value="<?= htmlspecialchars($medico['direccion']) ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Especialidad</label>
                            <select name="especialidad_id" class="form-select">
                                <option value="">Sin especialidad</option>
                                <?php 
                                $especialidades->data_seek(0);
                                while ($esp = $especialidades->fetch_assoc()): ?>
                                    <option value="<?= $esp['id_especialidades']; ?>"
                                        <?= $esp['id_especialidades'] == $medico['especialidad_id'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($esp['nombre']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <hr>

                    <h5 class="titulo-azul mb-3">Información profesional</h5>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Número de Junta Médica</label>
                            <input type="text" name="junta_medica" class="form-control" 
                                   value="<?= htmlspecialchars($medico['junta_medica'] ?? '') ?>">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Años de experiencia</label>
                            <input type="number" name="experiencia_anios" class="form-control"
                                   value="<?= htmlspecialchars($medico['experiencia_anios'] ?? '') ?>">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Consultorio</label>
                            <input type="text" name="consultorio" class="form-control"
                                   value="<?= htmlspecialchars($medico['consultorio'] ?? '') ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Horario de atención</label>
                            <input type="text" name="horario_atencion" class="form-control"
                                   value="<?= htmlspecialchars($medico['horario_atencion'] ?? '') ?>"
                                   placeholder="Ej: Lunes a Viernes 8:00 - 16:00">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Biografía / Perfil profesional</label>
                            <textarea name="biografia" rows="4" class="form-control"
                                      placeholder="Resumen de experiencia, formación, enfoque de atención."><?= htmlspecialchars($medico['biografia'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="lista_medicos.php" class="btn btn-danger">⬅️ Volver</a>
                        <button type="submit" class="btn btn-success">💾 Guardar cambios</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

</body>
</html>
