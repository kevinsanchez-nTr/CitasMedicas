<?php
session_start();
require_once __DIR__ . '/../includes/conexion.php';

// Solo admin
if (!isset($_SESSION['id_usuarios']) || ($_SESSION['rol_id'] ?? null) != 1) {
    header("Location: ../dashboard.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['mensaje_error'] = "❌ ID de médico no válido.";
    header("Location: lista_medicos.php");
    exit();
}

$id_medico = intval($_GET['id']);

// Info principal
$stmt = $conn->prepare("
    SELECT 
        m.id_medicos,
        m.junta_medica,
        m.experiencia_anios,
        m.consultorio,
        m.horario_atencion,
        m.biografia,
        u.nombre_completo,
        u.correo,
        u.telefono,
        u.direccion,
        u.fecha_registro,
        COALESCE(e.nombre, 'Sin especialidad') AS especialidad
    FROM medicos m
    INNER JOIN usuarios u ON m.usuario_id = u.id_usuarios
    LEFT JOIN especialidades e ON m.especialidad_id = e.id_especialidades
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

// helper
function contar($conn, $sql, $id_medico) {
    $q = $conn->prepare($sql);
    $q->bind_param("i", $id_medico);
    $q->execute();
    $r = $q->get_result()->fetch_assoc()['total'] ?? 0;
    $q->close();
    return $r;
}

// estadísticas
$total_citas = contar($conn, "SELECT COUNT(*) AS total FROM citas WHERE medico_id = ?", $id_medico);
$citas_completadas = contar($conn, "SELECT COUNT(*) AS total FROM citas WHERE medico_id = ? AND estado_id = 4", $id_medico);
$citas_pendientes  = contar($conn, "SELECT COUNT(*) AS total FROM citas WHERE medico_id = ? AND estado_id = 1", $id_medico);
$citas_canceladas  = contar($conn, "
    SELECT COUNT(*) AS total 
    FROM cancelaciones ca
    INNER JOIN citas c ON ca.cita_id = c.id_citas
    WHERE c.medico_id = ?
", $id_medico);
$recetas_emitidas = contar($conn, "
    SELECT COUNT(*) AS total
    FROM recetas_medicas r
    INNER JOIN citas c ON r.cita_id = c.id_citas
    WHERE c.medico_id = ?
", $id_medico);
$citas_urgentes = contar($conn, "
    SELECT COUNT(*) AS total
    FROM citas
    WHERE medico_id = ? AND es_urgente = 1
", $id_medico);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil del Médico</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        :root {
            --azul-menu: blue;
            --azul-oscuro: #008ABD;
            --azul-claro: #5FDFFF;
        }
        .card-header-azul { background-color: var(--azul-menu); color:white; }
        .titulo-azul { color: var(--azul-menu); }
        .btn-azul { background-color: var(--azul-menu); color:white; }
        .btn-azul:hover { background-color: var(--azul-oscuro); color:white; }
        .stat-card { color:white; border-radius:10px; padding:20px 5px; }
        .bg-azul { background-color: var(--azul-menu); }
        .bg-azul-oscuro { background-color: var(--azul-oscuro); }
        .bg-azul-claro { background-color: var(--azul-claro); color:black; }
    </style>
</head>
<body>

<?php include "../includes/menu.php"; ?>

<div class="content p-4">
    <div class="container">

        <!-- PERFIL -->
        <div class="card shadow-lg mb-4" style="border-color:var(--azul-menu);">
            <div class="card-header card-header-azul">
                <h4 class="mb-0">👨‍⚕️ Información del Médico</h4>
            </div>
            <div class="card-body">

                <h3 class="titulo-azul"><?= htmlspecialchars($medico['nombre_completo']); ?></h3>
                <p class="text-muted mb-1">
                    Especialidad: <strong><?= $medico['especialidad'] ?></strong>
                </p>

                <?php if (!empty($medico['junta_medica']) || !empty($medico['experiencia_anios'])): ?>
                    <p class="mb-3">
                        <?php if (!empty($medico['junta_medica'])): ?>
                            Junta Médica: <strong><?= htmlspecialchars($medico['junta_medica']) ?></strong><br>
                        <?php endif; ?>
                        <?php if (!empty($medico['experiencia_anios'])): ?>
                            Experiencia: <strong><?= intval($medico['experiencia_anios']) ?> años</strong>
                        <?php endif; ?>
                    </p>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Correo:</label>
                        <input class="form-control" value="<?= $medico['correo'] ?>" disabled>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Teléfono:</label>
                        <input class="form-control" value="<?= $medico['telefono'] ?>" disabled>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Dirección:</label>
                        <input class="form-control" value="<?= $medico['direccion'] ?>" disabled>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Fecha de Registro:</label>
                        <input class="form-control" value="<?= $medico['fecha_registro'] ?>" disabled>
                    </div>

                    <?php if (!empty($medico['consultorio']) || !empty($medico['horario_atencion'])): ?>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Consultorio:</label>
                            <input class="form-control" value="<?= htmlspecialchars($medico['consultorio'] ?? '') ?>" disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Horario de atención:</label>
                            <input class="form-control" value="<?= htmlspecialchars($medico['horario_atencion'] ?? '') ?>" disabled>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($medico['biografia'])): ?>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Biografía / Perfil profesional:</label>
                            <textarea class="form-control" rows="3" disabled><?= htmlspecialchars($medico['biografia']) ?></textarea>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="d-flex flex-wrap gap-2 mt-2">
                    <a href="lista_medicos.php" class="btn btn-secondary">⬅️ Volver</a>
                    <a href="editar_medico.php?id=<?= $id_medico ?>" class="btn btn-azul">✏️ Editar Información</a>
                    <a href="ver_citas_medico.php?id=<?= $id_medico ?>" class="btn btn-outline-primary">
                        📅 Ver citas
                    </a>
                    <a href="pacientes_atendidos.php?id=<?= $id_medico ?>" class="btn btn-outline-success">
                        🧍 Pacientes atendidos
                    </a>
                    <a href="historial_medico_medico.php?id=<?= $id_medico ?>" class="btn btn-outline-dark">
                        📚 Historial médico
                    </a>
                </div>

            </div>
        </div>

        <!-- ESTADÍSTICAS -->
        <h4 class="titulo-azul mb-3">📊 Estadísticas del Médico</h4>

        <div class="row text-center">
            <div class="col-md-4 mb-3">
                <div class="stat-card bg-azul">
                    <h2><?= $total_citas ?></h2>
                    <p class="m-0">Citas Totales</p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card bg-azul-oscuro">
                    <h2><?= $citas_completadas ?></h2>
                    <p class="m-0">Citas Completadas</p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card bg-azul-claro">
                    <h2><?= $citas_pendientes ?></h2>
                    <p class="m-0">Citas Pendientes</p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card bg-danger">
                    <h2><?= $citas_canceladas ?></h2>
                    <p class="m-0">Canceladas</p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card bg-success">
                    <h2><?= $recetas_emitidas ?></h2>
                    <p class="m-0">Recetas Emitidas</p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card bg-dark">
                    <h2><?= $citas_urgentes ?></h2>
                    <p class="m-0">Urgencias Atendidas</p>
                </div>
            </div>
        </div>

    </div>
</div>

</body>
</html>
