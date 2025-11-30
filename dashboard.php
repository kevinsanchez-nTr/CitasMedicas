<?php
session_start();
require_once __DIR__ . "/includes/conexion.php";

/* ====================== CONSULTAS ====================== */

// Totales generales
$total_usuarios   = $conn->query("SELECT COUNT(*) AS t FROM usuarios")->fetch_assoc()['t'];
$total_medicos    = $conn->query("SELECT COUNT(*) AS t FROM medicos")->fetch_assoc()['t'];
$total_pacientes  = $conn->query("SELECT COUNT(*) AS t FROM pacientes")->fetch_assoc()['t'];
$total_citas      = $conn->query("SELECT COUNT(*) AS t FROM citas")->fetch_assoc()['t'];

// Activos / Inactivos
$usuarios_activos   = $conn->query("SELECT COUNT(*) AS t FROM usuarios WHERE activo = 1")->fetch_assoc()['t'];
$usuarios_inactivos = $conn->query("SELECT COUNT(*) AS t FROM usuarios WHERE activo = 0")->fetch_assoc()['t'];

$med_activos        = $conn->query("SELECT COUNT(*) AS t FROM medicos WHERE activo = 1")->fetch_assoc()['t'];
$med_inactivos      = $conn->query("SELECT COUNT(*) AS t FROM medicos WHERE activo = 0")->fetch_assoc()['t'];

$pac_activos        = $conn->query("SELECT COUNT(*) AS t FROM pacientes WHERE activo = 1")->fetch_assoc()['t'];
$pac_inactivos      = $conn->query("SELECT COUNT(*) AS t FROM pacientes WHERE activo = 0")->fetch_assoc()['t'];

// Usuarios por rol
$admins    = $conn->query("SELECT COUNT(*) AS t FROM usuarios WHERE rol_id = 1")->fetch_assoc()['t'];
$med_roles = $conn->query("SELECT COUNT(*) AS t FROM usuarios WHERE rol_id = 2")->fetch_assoc()['t'];
$pac_roles = $conn->query("SELECT COUNT(*) AS t FROM usuarios WHERE rol_id = 3")->fetch_assoc()['t'];

// Tendencias por mes
$tendencias = $conn->query("
    SELECT DATE_FORMAT(fecha_registro, '%Y-%m') AS mes, COUNT(*) AS cantidad
    FROM usuarios
    GROUP BY mes ORDER BY mes ASC
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Dashboard - Citas Médicas</title>

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<!-- Estilos generales -->
<link rel="stylesheet" href="css/estilos.css">

<style>
/* ==== Ajustes globales sin márgenes ==== */
body {
    margin: 0 !important;
    padding: 0 !important;
    background-color: #f0f4ff;
    font-family: 'Segoe UI', sans-serif;
}

/* ==== Corrige el espacio del Dashboard ==== */
.content {
    margin-left: 230px !important;
    padding: 15px !important;
    padding-top: 5px !important;
}

/* ==== Tarjetas pequeñas ==== */
.card-mini {
    padding: 10px 15px !important;
    border-radius: 8px !important;
    height: 90px;
}

/* ==== Gráficas ==== */
canvas { max-height: 180px; }
.chart-card { height: 260px; }
.chart-title {
    font-size: 15px;
    font-weight: bold;
}

/* ==== Corrige h3/h4 que metían margen extra ==== */
h3, h4 { margin-top: 0 !important; }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>
<body>

<?php include "includes/menu.php"; ?>

<!-- CONTENIDO PRINCIPAL -->
<div class="content">

    <h3 class="mb-3"><i class="bi bi-speedometer2"></i> Dashboard General</h3>

    <!-- TARJETAS PRINCIPALES -->
    <div class="row g-3">

        <div class="col-md-3">
            <div class="card card-mini bg-dark text-white shadow-sm">
                <h6>Usuarios Totales</h6>
                <h3><?= $total_usuarios ?></h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-mini bg-info text-dark shadow-sm">
                <h6>Médicos Registrados</h6>
                <h3><?= $total_medicos ?></h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-mini bg-primary text-white shadow-sm">
                <h6>Pacientes Registrados</h6>
                <h3><?= $total_pacientes ?></h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-mini bg-warning text-dark shadow-sm">
                <h6>Citas Totales</h6>
                <h3><?= $total_citas ?></h3>
            </div>
        </div>

    </div>

    <!-- ACTIVOS / INACTIVOS -->
    <h4 class="mt-4 mb-2">Estado de Usuarios, Médicos y Pacientes</h4>

    <div class="row g-3">

        <div class="col-md-2">
            <div class="card card-mini bg-success text-white shadow-sm">
                <h6>Usuarios Activos</h6>
                <h3><?= $usuarios_activos ?></h3>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card card-mini bg-danger text-white shadow-sm">
                <h6>Usuarios Inactivos</h6>
                <h3><?= $usuarios_inactivos ?></h3>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card card-mini bg-success text-white shadow-sm">
                <h6>Médicos Activos</h6>
                <h3><?= $med_activos ?></h3>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card card-mini bg-danger text-white shadow-sm">
                <h6>Médicos Inactivos</h6>
                <h3><?= $med_inactivos ?></h3>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card card-mini bg-success text-white shadow-sm">
                <h6>Pacientes Activos</h6>
                <h3><?= $pac_activos ?></h3>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card card-mini bg-danger text-white shadow-sm">
                <h6>Pacientes Inactivos</h6>
                <h3><?= $pac_inactivos ?></h3>
            </div>
        </div>

    </div>

    <!-- GRAFICAS -->
    <div class="row mt-4 g-3">

        <!-- Usuarios por Rol -->
        <div class="col-md-6">
            <div class="card chart-card shadow-sm">
                <div class="chart-title p-2 bg-primary text-white">Usuarios por Rol</div>
                <div class="p-2"><canvas id="grafUsuarios"></canvas></div>
            </div>
        </div>

        <!-- Médicos -->
        <div class="col-md-6">
            <div class="card chart-card shadow-sm">
                <div class="chart-title p-2 bg-success text-white">Médicos Activos / Inactivos</div>
                <div class="p-2"><canvas id="grafMedicos"></canvas></div>
            </div>
        </div>

    </div>

    <div class="row mt-3 g-3">

        <!-- Pacientes -->
        <div class="col-md-6">
            <div class="card chart-card shadow-sm">
                <div class="chart-title p-2 bg-info text-dark">Pacientes Activos / Inactivos</div>
                <div class="p-2"><canvas id="grafPacientes"></canvas></div>
            </div>
        </div>

        <!-- General -->
        <div class="col-md-6">
            <div class="card chart-card shadow-sm">
                <div class="chart-title p-2 bg-dark text-white">General: Activos vs Inactivos</div>
                <div class="p-2"><canvas id="grafGeneral"></canvas></div>
            </div>
        </div>

    </div>

</div>

<!-- GRAFICOS -->
<script>
// Usuarios por Rol
new Chart(document.getElementById("grafUsuarios"), {
type: 'pie',
data: {
    labels: ['Admin', 'Médicos', 'Pacientes'],
    datasets: [{
        data: [<?= $admins ?>, <?= $med_roles ?>, <?= $pac_roles ?>],
        backgroundColor: ['#212529','#0dcaf0','#0d6efd']
    }]
}
});

// Médicos
new Chart(document.getElementById("grafMedicos"), {
type: 'doughnut',
data: {
    labels: ['Activos', 'Inactivos'],
    datasets: [{
        data: [<?= $med_activos ?>, <?= $med_inactivos ?>],
        backgroundColor: ['#198754','#dc3545']
    }]
}
});

// Pacientes
new Chart(document.getElementById("grafPacientes"), {
type: 'doughnut',
data: {
    labels: ['Activos', 'Inactivos'],
    datasets: [{
        data: [<?= $pac_activos ?>, <?= $pac_inactivos ?>],
        backgroundColor: ['#0dcaf0','#dc3545']
    }]
}
});

// General
new Chart(document.getElementById("grafGeneral"), {
type: 'bar',
data: {
    labels: ['Usuarios', 'Médicos', 'Pacientes'],
    datasets: [
        {
            label: 'Activos',
            data: [<?= $usuarios_activos ?>, <?= $med_activos ?>, <?= $pac_activos ?>],
            backgroundColor: '#198754'
        },
        {
            label: 'Inactivos',
            data: [<?= $usuarios_inactivos ?>, <?= $med_inactivos ?>, <?= $pac_inactivos ?>],
            backgroundColor: '#dc3545'
        }
    ]
},
options: { responsive: true }
});
</script>

</body>
</html>
