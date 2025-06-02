<?php
session_start();
require_once "includes/conexion.php";

if (!isset($_SESSION['id_usuarios'])) {
  header("Location: index.php");
  exit();
}

$nombre = $_SESSION['nombre'];
$rol_id = $_SESSION['rol_id'] ?? null;

// Convertir rol_id a texto legible
$rol = match($rol_id) {
  1 => 'Administrador',
  2 => 'Médico',
  3 => 'Paciente',
  default => 'Desconocido'
};

// Función para contar registros activos en cualquier tabla
function contarActivos($conn, $tabla) {
  $sql = "SELECT COUNT(*) AS total FROM $tabla WHERE activo = 1";
  $resultado = $conn->query($sql);
  $fila = $resultado->fetch_assoc();
  return $fila['total'];
}

// Conteo real de médicos activos con rol_id = 2
$sqlMedicos = "SELECT COUNT(*) AS total FROM medicos m 
               INNER JOIN usuarios u ON m.usuario_id = u.id_usuarios 
               WHERE m.activo = 1 AND u.rol_id = 2";
$res = $conn->query($sqlMedicos);
$row = $res->fetch_assoc();
$totalMedicos = $row['total'] ?? 0;

// Conteos de otras tablas
$totalPacientes = contarActivos($conn, "pacientes");
$totalCitas = contarActivos($conn, "citas");
$totalReportes = contarActivos($conn, "historial_medico");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - Citas Médicas</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="css/estilos.css">
</head>
<body>

<?php include "includes/menu.php"; ?>

<div class="content">
  <h3 class="mb-4">Bienvenido, <?php echo htmlspecialchars($nombre); ?> <small class="text-muted">(<?php echo $rol; ?>)</small></h3>

  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card text-bg-success h-100">
        <div class="card-body card-summary">
          <div>
            <h6 class="card-title">Médicos</h6>
            <h4><?php echo $totalMedicos; ?></h4>
          </div>
          <i class="bi bi-person-badge"></i>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-bg-primary h-100">
        <div class="card-body card-summary">
          <div>
            <h6 class="card-title">Pacientes</h6>
            <h4><?php echo $totalPacientes; ?></h4>
          </div>
          <i class="bi bi-people-fill"></i>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-bg-warning h-100">
        <div class="card-body card-summary">
          <div>
            <h6 class="card-title">Citas</h6>
            <h4><?php echo $totalCitas; ?></h4>
          </div>
          <i class="bi bi-calendar-check"></i>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-bg-info h-100">
        <div class="card-body card-summary">
          <div>
            <h6 class="card-title">Reportes</h6>
            <h4><?php echo $totalReportes; ?></h4>
          </div>
          <i class="bi bi-file-earmark-text"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="js/scripts.js"></script>
</body>
</html>
