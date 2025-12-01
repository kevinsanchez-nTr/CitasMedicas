<?php
session_start();
require_once "../includes/conexion.php";

$id = $_GET["id"] ?? 0;

$sql = "
SELECT c.*, 
       u1.nombre_completo AS paciente,
       u2.nombre_completo AS medico,
       e.descripcion AS estado
FROM citas c
INNER JOIN pacientes p ON c.paciente_id = p.id_pacientes
INNER JOIN usuarios u1 ON p.usuario_id = u1.id_usuarios
INNER JOIN medicos m ON c.medico_id = m.id_medicos
INNER JOIN usuarios u2 ON m.usuario_id = u2.id_usuarios
INNER JOIN estado_cita e ON c.estado_id = e.id_estado_cita
WHERE c.id_citas = $id
";

$cita = $conn->query($sql)->fetch_assoc();

if (!$cita) {
    $_SESSION['mensaje_error'] = "Cita no encontrada.";
    header("Location: lista_citas.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Ver Cita</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../css/estilos.css" rel="stylesheet">
<style>
.content { margin-top:10px !important; }
</style>
</head>

<body>
<?php include "../includes/menu.php"; ?>

<div class="content container">

<h3><i class="bi bi-eye"></i> Detalles de la Cita</h3>

<div class="bg-white p-4 rounded shadow">

    <p><strong>Paciente:</strong> <?= $cita["paciente"] ?></p>
    <p><strong>Médico:</strong> <?= $cita["medico"] ?></p>
    <p><strong>Fecha y hora:</strong> <?= $cita["fecha"] ?></p>
    <p><strong>Motivo:</strong><br><?= $cita["motivo"] ?></p>

    <p><strong>Estado:</strong>
        <span class="badge bg-info text-dark"><?= $cita["estado"] ?></span>
    </p>

    <p><strong>Urgente:</strong>
        <?= $cita["es_urgente"] ? "<span class='badge bg-danger'>Sí</span>" : "<span class='badge bg-secondary'>No</span>" ?>
    </p>

    <p><strong>Activo:</strong>
        <?= $cita["activo"] ? "<span class='badge bg-success'>Activo</span>" : "<span class='badge bg-danger'>Inactivo</span>" ?>
    </p>

    <a href="lista_citas.php" class="btn btn-secondary mt-3">← Volver</a>
</div>

</div>
</body>
</html>
