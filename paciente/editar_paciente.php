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

$id_usuario = $_GET['id'];

// Obtener datos del paciente y usuario
$sql = "SELECT u.nombre_completo, u.correo, u.telefono, u.direccion, 
               p.fecha_nacimiento, p.grupo_sanguineo
        FROM usuarios u
        LEFT JOIN pacientes p ON u.id_usuarios = p.usuario_id
        WHERE u.id_usuarios = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
  header("Location: lista_pacientes.php");
  exit();
}

$paciente = $resultado->fetch_assoc();

// Guardar cambios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $fecha = $_POST['fecha_nacimiento'];
  $grupo = $_POST['grupo_sanguineo'];

  $check = $conn->prepare("SELECT id_pacientes FROM pacientes WHERE usuario_id = ?");
  $check->bind_param("i", $id_usuario);
  $check->execute();
  $checkResult = $check->get_result();

  if ($checkResult->num_rows > 0) {
    $update = $conn->prepare("UPDATE pacientes SET fecha_nacimiento=?, grupo_sanguineo=? WHERE usuario_id=?");
  } else {
    $update = $conn->prepare("INSERT INTO pacientes (fecha_nacimiento, grupo_sanguineo, usuario_id, activo) VALUES (?, ?, ?, 1)");
  }

  $update->bind_param("ssi", $fecha, $grupo, $id_usuario);
  if ($update->execute()) {
    header("Location: lista_pacientes.php?editado=1");
    exit();
  } else {
    $error = "❌ Error al guardar los cambios.";
  }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Paciente</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
<?php include "../includes/menu.php"; ?>

<div class="content">
  <div class="container mt-4">
    <a href="lista_pacientes.php" class="btn btn-outline-primary mb-3">
      <i class="bi bi-arrow-left-circle"></i> Volver a la lista
    </a>

    <h4 class="mb-4"><i class="bi bi-pencil-square"></i> Editar datos clínicos del paciente</h4>

    <?php if (isset($error)): ?>
      <div class="alert alert-danger text-center"><?= $error ?></div>
    <?php endif; ?>

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
        <input type="date" name="fecha_nacimiento" class="form-control" required
               value="<?= htmlspecialchars($paciente['fecha_nacimiento']) ?>">
      </div>
      <div class="col-md-6">
        <label>Grupo sanguíneo:</label>
        <select name="grupo_sanguineo" class="form-select" required>
          <option value="">Seleccione...</option>
          <?php
          $grupos = ["A+", "A-", "B+", "B-", "AB+", "AB-", "O+", "O-"];
          foreach ($grupos as $grupo) {
            $selected = ($paciente['grupo_sanguineo'] == $grupo) ? 'selected' : '';
            echo "<option value=\"$grupo\" $selected>$grupo</option>";
          }
          ?>
        </select>
      </div>

      <div class="col-12 text-end">
        <button type="submit" class="btn btn-success">
          <i class="bi bi-check-circle"></i> Guardar cambios
        </button>
      </div>
    </form>
  </div>
</div>

</body>
</html>
