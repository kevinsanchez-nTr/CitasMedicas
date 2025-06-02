<?php
session_start();
require_once __DIR__ . '/../includes/conexion.php';

if (!isset($_SESSION['id_usuarios']) || $_SESSION['rol_id'] != 1) {
  header("Location: ../dashboard.php");
  exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header("Location: lista_pacientes.php");
  exit();
}

$id = $_GET['id'];

// Obtener datos del usuario y del paciente
$sql = "SELECT u.nombre_completo, u.correo, u.telefono, u.direccion,
               p.fecha_nacimiento, p.grupo_sanguineo
        FROM usuarios u
        LEFT JOIN pacientes p ON u.id_usuarios = p.usuario_id
        WHERE u.id_usuarios = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
  echo "<script>alert('Paciente no encontrado.'); window.location='lista_pacientes.php';</script>";
  exit();
}

$paciente = $res->fetch_assoc();
$esEditable = empty($paciente['fecha_nacimiento']) || empty($paciente['grupo_sanguineo']);

// Guardar si se permite y se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $esEditable) {
  $fecha_nacimiento = $_POST['fecha_nacimiento'];
  $grupo_sanguineo = $_POST['grupo_sanguineo'];

  $existe = $conn->query("SELECT id_pacientes FROM pacientes WHERE usuario_id = $id")->num_rows > 0;

  if ($existe) {
    $stmt = $conn->prepare("UPDATE pacientes SET fecha_nacimiento=?, grupo_sanguineo=? WHERE usuario_id=?");
  } else {
    $stmt = $conn->prepare("INSERT INTO pacientes (fecha_nacimiento, grupo_sanguineo, usuario_id, activo) VALUES (?, ?, ?, 1)");
  }

  $stmt->bind_param("ssi", $fecha_nacimiento, $grupo_sanguineo, $id);
  if ($stmt->execute()) {
    $_SESSION['mensaje_exito'] = "✅ Datos del paciente actualizados correctamente.";
    header("Location: datos_paciente.php?id=$id");
    exit();
  } else {
    $error = "❌ Error al guardar los datos.";
  }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Datos del Paciente</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
<?php include "../includes/menu.php"; ?>

<div class="content">
  <div class="container mt-4">

    <!-- ✅ Alerta de éxito -->
    <?php if (isset($_SESSION['mensaje_exito'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $_SESSION['mensaje_exito']; unset($_SESSION['mensaje_exito']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
      </div>
    <?php endif; ?>

    <h3 class="mb-4">👁 Datos del Paciente</h3>

    <a href="lista_pacientes.php" class="btn btn-outline-primary mb-3">
      <i class="bi bi-arrow-left-circle"></i> Volver a la lista de pacientes
    </a>

    <form method="POST" class="row g-3">
      <div class="col-md-6">
        <label>Nombre completo:</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($paciente['nombre_completo']) ?>" readonly>
      </div>
      <div class="col-md-6">
        <label>Correo electrónico:</label>
        <input type="email" class="form-control" value="<?= htmlspecialchars($paciente['correo']) ?>" readonly>
      </div>
      <div class="col-md-6">
        <label>Teléfono:</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($paciente['telefono']) ?>" readonly>
      </div>
      <div class="col-md-6">
        <label>Dirección:</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($paciente['direccion']) ?>" readonly>
      </div>
      <div class="col-md-6">
        <label>Fecha de nacimiento:</label>
        <input type="date" name="fecha_nacimiento" class="form-control"
               value="<?= $paciente['fecha_nacimiento'] ?>"
               <?= !empty($paciente['fecha_nacimiento']) ? 'readonly' : '' ?>>
      </div>
      <div class="col-md-6">
        <label>Grupo sanguíneo:</label>
        <select name="grupo_sanguineo" class="form-control" <?= !empty($paciente['grupo_sanguineo']) ? 'disabled' : '' ?>>
          <option value="">Seleccione...</option>
          <?php
          $grupos = ['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'];
          foreach ($grupos as $grupo) {
            $selected = ($paciente['grupo_sanguineo'] == $grupo) ? 'selected' : '';
            echo "<option value='$grupo' $selected>$grupo</option>";
          }
          ?>
        </select>
      </div>

      <?php if ($esEditable): ?>
        <div class="col-12">
          <button type="submit" class="btn btn-success">
            <i class="bi bi-floppy"></i> Guardar datos
          </button>
        </div>
      <?php endif; ?>
    </form>
  </div>
</div>

<!-- Bootstrap JS para que funcione la alerta -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
