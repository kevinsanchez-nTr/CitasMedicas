<?php
session_start();
require_once "../includes/conexion.php";

$id = $_GET['id'] ?? 0;

// Obtener datos de la cita
$sql = "
SELECT c.*, 
       p.id_pacientes AS paciente_id,
       u1.nombre_completo AS paciente,
       m.id_medicos AS medico_id,
       u2.nombre_completo AS medico,
       e.id_estado_cita
FROM citas c
INNER JOIN pacientes p ON c.paciente_id = p.id_pacientes
INNER JOIN usuarios u1 ON p.usuario_id = u1.id_usuarios
INNER JOIN medicos m ON c.medico_id = m.id_medicos
INNER JOIN usuarios u2 ON m.usuario_id = u2.id_usuarios
INNER JOIN estado_cita e ON c.estado_id = e.id_estado_cita
WHERE c.id_citas = $id
";

$cita = $conn->query($sql)->fetch_assoc();

// Listas
$pacientes = $conn->query("
    SELECT p.id_pacientes, u.nombre_completo 
    FROM pacientes p 
    INNER JOIN usuarios u ON p.usuario_id = u.id_usuarios
");

$medicos = $conn->query("
    SELECT m.id_medicos, u.nombre_completo 
    FROM medicos m 
    INNER JOIN usuarios u ON m.usuario_id = u.id_usuarios
");

$estados = $conn->query("SELECT * FROM estado_cita");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar Cita</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../css/estilos.css" rel="stylesheet">
<style>.content{margin-top:10px!important;}</style>
</head>

<body>
<?php include "../includes/menu.php"; ?>

<div class="content container mt-3">

<h3><i class="bi bi-pencil-square"></i> Editar Cita</h3>

<form action="actualizar_cita.php" method="POST" class="bg-white p-4 rounded shadow">

    <input type="hidden" name="id" value="<?= $cita['id_citas'] ?>">

    <div class="row g-3">

        <div class="col-md-6">
            <label>Paciente</label>
            <select name="paciente_id" class="form-select" required>
                <option value="">Seleccione...</option>
                <?php while ($p = $pacientes->fetch_assoc()): ?>
                    <option value="<?= $p['id_pacientes'] ?>" 
                        <?= $p['id_pacientes']==$cita['paciente_id']?'selected':'' ?>>
                        <?= $p['nombre_completo'] ?>
                    </option>
                <?php endwhile ?>
            </select>
        </div>

        <div class="col-md-6">
            <label>Médico</label>
            <select name="medico_id" class="form-select" required>
                <option value="">Seleccione...</option>
                <?php while ($m = $medicos->fetch_assoc()): ?>
                    <option value="<?= $m['id_medicos'] ?>" 
                        <?= $m['id_medicos']==$cita['medico_id']?'selected':'' ?>>
                        <?= $m['nombre_completo'] ?>
                    </option>
                <?php endwhile ?>
            </select>
        </div>

        <div class="col-md-6">
            <label>Fecha y hora</label>
            <input type="datetime-local" name="fecha" class="form-control"
                   value="<?= date('Y-m-d\TH:i', strtotime($cita['fecha'])) ?>" required>
        </div>

        <div class="col-md-6">
            <label>Estado</label>
            <select name="estado_id" class="form-select">
                <?php while($e = $estados->fetch_assoc()): ?>
                    <option value="<?= $e['id_estado_cita'] ?>"
                        <?= $e['id_estado_cita']==$cita['estado_id']?'selected':'' ?>>
                        <?= $e['descripcion'] ?>
                    </option>
                <?php endwhile ?>
            </select>
        </div>

        <div class="col-12">
            <label>Motivo</label>
            <textarea name="motivo" class="form-control" rows="3"><?= $cita['motivo'] ?></textarea>
        </div>

        <div class="col-md-4">
            <label>Urgente</label>
            <select name="es_urgente" class="form-select">
                <option value="0" <?= $cita['es_urgente']==0?'selected':'' ?>>No</option>
                <option value="1" <?= $cita['es_urgente']==1?'selected':'' ?>>Sí</option>
            </select>
        </div>

        <div class="col-12 mt-3">
            <button class="btn btn-primary w-100">Actualizar Cita</button>
        </div>

        <div class="col-12">
            <a class="btn btn-secondary w-100 mt-2" href="lista_citas.php">Volver</a>
        </div>

    </div>
</form>

</div>
</body>
</html>
