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

// Nombre del médico
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

// Historial
$sql = "
    SELECT 
        h.id_historial_medico,
        h.diagnostico,
        h.tratamiento,
        c.fecha,
        uPac.nombre_completo AS paciente
    FROM historial_medico h
    INNER JOIN citas c ON h.cita_id = c.id_citas
    INNER JOIN pacientes p ON c.paciente_id = p.id_pacientes
    INNER JOIN usuarios uPac ON p.usuario_id = uPac.id_usuarios
    WHERE c.medico_id = ?
    ORDER BY c.fecha DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_medico);
$stmt->execute();
$historial = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial médico del médico</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/estilos.css"
</head>
<body>

<?php include "../includes/menu.php"; ?>

<div class="content p-4">
    <div class="container">
        <h3 class="mb-3">📚 Historial médico registrado por: <span class="text-primary"><?= htmlspecialchars($medico['nombre_completo']); ?></span></h3>

        <a href="ver_medico.php?id=<?= $id_medico ?>" class="btn btn-secondary mb-3">⬅️ Volver al perfil</a>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-primary">
                    <tr>
                        <th>#</th>
                        <th>Fecha</th>
                        <th>Paciente</th>
                        <th>Diagnóstico</th>
                        <th>Tratamiento</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($historial && $historial->num_rows > 0): ?>
                    <?php $i=1; while ($fila = $historial->fetch_assoc()): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= $fila['fecha'] ?></td>
                            <td><?= htmlspecialchars($fila['paciente']) ?></td>
                            <td><?= nl2br(htmlspecialchars($fila['diagnostico'])) ?></td>
                            <td><?= nl2br(htmlspecialchars($fila['tratamiento'])) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            No hay historial médico registrado por este médico.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

</body>
</html>
