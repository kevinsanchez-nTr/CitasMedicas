<?php
session_start();
require_once __DIR__ . '/../includes/conexion.php';

// Solo admin puede registrar
if (!isset($_SESSION['id_usuarios']) || $_SESSION['rol_id'] != 1) {
  header("Location: ../dashboard.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registrar Paciente</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
<?php include "../includes/menu.php"; ?>

<div class="content">
  <div class="container mt-5">
    <h3 class="mb-4">🩺 Registro de nuevo paciente</h3>

    <!-- ✅ Mensajes de éxito/error -->
    <?php if (isset($_SESSION['mensaje_exito'])): ?>
      <div class="alert alert-success text-center">
        <?= $_SESSION['mensaje_exito']; unset($_SESSION['mensaje_exito']); ?>
      </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['mensaje_error'])): ?>
      <div class="alert alert-danger text-center">
        <?= $_SESSION['mensaje_error']; unset($_SESSION['mensaje_error']); ?>
      </div>
    <?php endif; ?>

    <center>
      <img src="../img/paciente.png" alt="Registro animado" class="img-fluid" style="max-width: 150px;">
    </center>

    <form action="procesar_paciente.php" method="POST" class="row g-3">

      <div class="col-md-6">
        <label for="nombre" class="form-label">Nombre completo</label>
        <input type="text" name="nombre_completo" class="form-control" required>
      </div>

      <div class="col-md-6">
        <label for="correo" class="form-label">Correo electrónico</label>
        <input type="email" name="correo" class="form-control" required>
      </div>

      <div class="col-md-6">
        <label for="telefono" class="form-label">Teléfono</label>
        <input type="text" name="telefono" class="form-control">
      </div>

      <div class="col-md-6">
        <label for="direccion" class="form-label">Dirección</label>
        <input type="text" name="direccion" class="form-control">
      </div>

      <div class="col-md-6">
        <label for="password" class="form-label">Contraseña</label>
        <input type="password" name="password" class="form-control" required>
      </div>

      <div class="col-md-6">
        <label for="fecha_nacimiento" class="form-label">Fecha de nacimiento</label>
        <input type="date" name="fecha_nacimiento" class="form-control" required>
      </div>

      <div class="col-md-6">
        <label for="grupo_sanguineo" class="form-label">Grupo sanguíneo</label>
        <select name="grupo_sanguineo" class="form-select" required>
          <option value="">Seleccione...</option>
          <option value="O+">O+</option>
          <option value="O-">O-</option>
          <option value="A+">A+</option>
          <option value="A-">A-</option>
          <option value="B+">B+</option>
          <option value="B-">B-</option>
          <option value="AB+">AB+</option>
          <option value="AB-">AB-</option>
        </select>
      </div>

      <input type="hidden" name="rol_id" value="3">

      <div class="col-12 text-end">
        <button type="submit" class="btn btn-success">
          <i class="bi bi-person-plus-fill"></i> Registrar paciente
        </button>
      </div>

    </form>
  </div>
</div>
</body>
</html>
