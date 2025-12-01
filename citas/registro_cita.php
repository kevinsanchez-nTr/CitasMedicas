<?php
session_start();
require_once __DIR__ . '/../includes/conexion.php';

// Obtener pacientes
$pacientes = $conn->query("
    SELECT p.id_pacientes, u.nombre_completo 
    FROM pacientes p
    INNER JOIN usuarios u ON p.usuario_id = u.id_usuarios
    WHERE u.activo = 1 AND p.activo = 1
");

// Obtener médicos
$medicos = $conn->query("
    SELECT m.id_medicos, u.nombre_completo
    FROM medicos m
    INNER JOIN usuarios u ON m.usuario_id = u.id_usuarios
    WHERE u.activo = 1 AND m.activo = 1
");

// Estados de cita
$estados = $conn->query("
    SELECT * FROM estado_cita
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Registrar Cita Médica</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="../css/estilos.css">

</head>
<body>

<?php include "../includes/menu.php"; ?>

<div class="content">

    <h3 class="mb-4"><i class="bi bi-calendar-plus"></i> Registrar Nueva Cita</h3>

    <form action="procesar_cita.php" method="POST" class="card p-4 shadow-sm">

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Paciente:</label>
                <select name="paciente_id" class="form-select" required>
                    <option value="">Seleccione un paciente</option>
                    <?php while ($p = $pacientes->fetch_assoc()): ?>
                        <option value="<?= $p['id_pacientes'] ?>"><?= $p['nombre_completo'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Médico:</label>
                <select name="medico_id" class="form-select" required>
                    <option value="">Seleccione un médico</option>
                    <?php while ($m = $medicos->fetch_assoc()): ?>
                        <option value="<?= $m['id_medicos'] ?>"><?= $m['nombre_completo'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Fecha y hora de la cita:</label>
                <input type="datetime-local" name="fecha" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label for="estado_id" class="form-label">Estado de la cita:</label>
<select name="estado_id" id="estado_id" class="form-select" required>
    <option value="">Seleccione un estado</option>
    <?php while ($row = $estados->fetch_assoc()): ?>
        <option value="<?= $row['id_estado_cita'] ?>">
            <?= $row['descripcion'] ?>
        </option>
    <?php endwhile; ?>
</select>

            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Motivo de la cita:</label>
            <textarea name="motivo" class="form-control" required></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">¿Es urgente?</label>
            <select name="es_urgente" class="form-select" required>
                <option value="0">No</option>
                <option value="1">Sí</option>
            </select>
        </div>

        <button type="submit" class="btn btn-success">
            <i class="bi bi-save"></i> Guardar Cita
        </button>

        <a href="lista_citas.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>

    </form>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
