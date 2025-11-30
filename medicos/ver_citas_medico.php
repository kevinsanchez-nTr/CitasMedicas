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

// Obtener nombre del médico
$stmtMed = $conn->prepare("
    SELECT u.nombre_completo 
    FROM medicos m
    INNER JOIN usuarios u ON m.usuario_id = u.id_usuarios
    WHERE m.id_medicos = ?
");
$stmtMed->bind_param("i", $id_medico);
$stmtMed->execute();
$medico = $stmtMed->get_result()->fetch_assoc();
$stmtMed->close();

if (!$medico) {
    $_SESSION['mensaje_error'] = "❌ Médico no encontrado.";
    header("Location: lista_medicos.php");
    exit();
}

// Citas del médico
$sql = "
    SELECT 
        c.id_citas,
        c.fecha,
        c.motivo,
        c.es_urgente,
        ec.descripcion AS estado,
        uPac.nombre_completo AS paciente
    FROM citas c
    INNER JOIN pacientes p ON c.paciente_id = p.id_pacientes
    INNER JOIN usuarios uPac ON p.usuario_id = uPac.id_usuarios
    INNER JOIN estado_cita ec ON c.estado_id = ec.id_estado_cita
    WHERE c.medico_id = ?
    ORDER BY c.fecha DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_medico);
$stmt->execute();
$citas = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Citas del Médico</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/estilos.css"
</head>
<body>

<?php include "../includes/menu.php"; ?>

<div class="content p-4">
    <div class="container">
        <h3 class="mb-3">📅 Citas del médico: <span class="text-primary"><?= htmlspecialchars($medico['nombre_completo']); ?></span></h3>

        <a href="ver_medico.php?id=<?= $id_medico ?>" class="btn btn-info mb-3">⬅️ Volver al perfil</a>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-primary">
                    <tr>
                        <th>#</th>
                        <th>Fecha</th>
                        <th>Paciente</th>
                        <th>Motivo</th>
                        <th>Urgente</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($citas && $citas->num_rows > 0): ?>
                    <?php $i=1; while ($fila = $citas->fetch_assoc()): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= $fila['fecha'] ?></td>
                            <td><?= htmlspecialchars($fila['paciente']) ?></td>
                            <td><?= htmlspecialchars($fila['motivo']) ?></td>
                            <td>
                                <?php if ($fila['es_urgente']): ?>
                                    <span class="badge bg-danger">Urgente</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Normal</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($fila['estado']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">No hay citas registradas para este médico.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
