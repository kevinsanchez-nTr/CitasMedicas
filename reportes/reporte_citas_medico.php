<?php
require_once "../includes/conexion.php";
session_start();

// ID de médico
$id_medico = isset($_GET['medico']) ? intval($_GET['medico']) : 0;

// Obtener lista de médicos
$lista_medicos = $conn->query("
    SELECT m.id_medicos, u.nombre_completo 
    FROM medicos m 
    INNER JOIN usuarios u ON m.usuario_id = u.id_usuarios
");

// Obtener citas si se seleccionó un médico
$resultado = null;
if ($id_medico > 0) {
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
    WHERE c.medico_id = $id_medico
    ORDER BY c.fecha DESC
    ";
    $resultado = $conn->query($sql);
}

// Obtener datos para gráficas
$citas_activas   = $id_medico ? $conn->query("SELECT COUNT(*) FROM citas WHERE activo = 1 AND medico_id = $id_medico")->fetch_row()[0] : 0;
$citas_inactivas = $id_medico ? $conn->query("SELECT COUNT(*) FROM citas WHERE activo = 0 AND medico_id = $id_medico")->fetch_row()[0] : 0;

$citas_urgentes     = $id_medico ? $conn->query("SELECT COUNT(*) FROM citas WHERE es_urgente = 1 AND medico_id = $id_medico")->fetch_row()[0] : 0;
$citas_no_urgentes  = $id_medico ? $conn->query("SELECT COUNT(*) FROM citas WHERE es_urgente = 0 AND medico_id = $id_medico")->fetch_row()[0] : 0;

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte de Citas por Médico</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="../css/estilos.css">

<style>
/* === TARJETA CONTENEDORA === */
.card-box{
    background:white;
    padding:25px;
    border-radius:10px;
    box-shadow:0 0 10px rgba(0,0,0,0.1);
}

/* === TARJETAS DE GRÁFICAS === */
.card-grafica {
    background: #ffffff;
    border-radius: 18px;
    padding: 25px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: transform .2s ease, box-shadow .2s ease;
    height: 420px;
}
.card-grafica:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.12);
}
.titulo-grafica {
    font-size: 18px;
    font-weight: 600;
    color: #2c3e50;
    text-align: center;
    margin-bottom: 15px;
}
.canvas-dashboard {
    width: 85% !important;
    max-width: 350px !important;
    margin: 0 auto;
    display: block;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>
<body>

<?php include "../includes/menu.php"; ?>

<div class="content">

<h3 class="mb-4"><i class="bi bi-bar-chart"></i> Reporte de Citas por Médico</h3>

<!-- ====================== FILTRO ====================== -->
<form method="GET" class="row g-3 card-box mb-4">
    <div class="col-md-6">
        <label class="form-label fw-bold">Seleccione un Médico:</label>
        <select name="medico" class="form-select">
            <option value="">Todos</option>
            <?php while ($m = $lista_medicos->fetch_assoc()): ?>
                <option value="<?= $m['id_medicos'] ?>" <?= ($id_medico == $m['id_medicos']) ? 'selected' : '' ?>>
                    <?= $m['nombre_completo'] ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="col-md-6 d-flex align-items-end">
        <button class="btn btn-primary w-100">
            <i class="bi bi-search"></i> Ver Reporte
        </button>
    </div>
</form>

<!-- ====================== GRÁFICAS ====================== -->
<?php if ($id_medico > 0 && $resultado && $resultado->num_rows > 0): ?>

<div class="row mt-4">

    <div class="col-md-6 mb-4">
        <div class="card-grafica">
            <h5 class="titulo-grafica">Estado de las Citas</h5>
            <canvas id="grafEstado" class="canvas-dashboard"></canvas>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card-grafica">
            <h5 class="titulo-grafica">Urgencia</h5>
            <canvas id="grafUrgencia" class="canvas-dashboard"></canvas>
        </div>
    </div>

</div>

<?php endif; ?>

<!-- ====================== BOTONES DE EXPORTAR ====================== -->
<?php if ($id_medico > 0): ?>
<div class="mb-3">
    <a href="exportar_citas_medico_pdf.php?medico=<?= $id_medico ?>" class="btn btn-danger">
        <i class="bi bi-file-pdf"></i> Exportar PDF
    </a>
    <a href="exportar_citas_medico_excel.php?medico=<?= $id_medico ?>" class="btn btn-success">
        <i class="bi bi-file-excel"></i> Exportar Excel
    </a>
</div>
<?php endif; ?>

<!-- ====================== TABLA ====================== -->
<div class="card-box mt-3">
<table class="table table-bordered table-hover">
    <thead class="table-primary">
        <tr>
            <th>Paciente</th>
            <th>Médico</th>
            <th>Fecha</th>
            <th>Motivo</th>
            <th>Estado</th>
            <th>Urgente</th>
            <th>Activo</th>
        </tr>
    </thead>
    <tbody>

    <?php if ($id_medico > 0 && $resultado && $resultado->num_rows > 0): ?>
        <?php foreach ($resultado as $c): ?>
        <tr>
            <td><?= $c['paciente'] ?></td>
            <td><?= $c['medico'] ?></td>
            <td><?= $c['fecha'] ?></td>
            <td><?= $c['motivo'] ?></td>
            <td><?= $c['estado'] ?></td>
            <td><?= $c['es_urgente'] ? 'Sí' : 'No' ?></td>
            <td><?= $c['activo'] ? 'Activo' : 'Inactivo' ?></td>
        </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="7" class="text-center text-muted">Seleccione un médico para ver el reporte.</td></tr>
    <?php endif; ?>

    </tbody>
</table>
</div>

</div>

<!-- ====================== SCRIPTS DE GRÁFICAS ====================== -->
<?php if ($resultado && $resultado->num_rows > 0): ?>
<script>

// Datos enviados desde PHP
const activos = <?= $citas_activas ?>;
const inactivos = <?= $citas_inactivas ?>;

const urgentes = <?= $citas_urgentes ?>;
const noUrgentes = <?= $citas_no_urgentes ?>;

// Colores premium
const coloresEstado = ["#27ae60", "#e74c3c"];
const coloresUrgencia = ["#f39c12", "#3498db"];

// ------------------- Gráfica Estado -------------------
new Chart(document.getElementById("grafEstado"), {
    type: 'doughnut',
    data: {
        labels: ['Activas', 'Inactivas'],
        datasets: [{
            data: [activos, inactivos],
            backgroundColor: coloresEstado,
            hoverOffset: 10
        }]
    },
    options: {
        cutout: '60%',
        plugins: { legend: { position: 'bottom' } }
    }
});

// ------------------- Gráfica Urgencia -------------------
new Chart(document.getElementById("grafUrgencia"), {
    type: 'doughnut',
    data: {
        labels: ['Urgentes', 'No urgentes'],
        datasets: [{
            data: [urgentes, noUrgentes],
            backgroundColor: coloresUrgencia,
            hoverOffset: 10
        }]
    },
    options: {
        cutout: '60%',
        plugins: { legend: { position: 'bottom' } }
    }
});

</script>
<?php endif; ?>

</body>
</html>
