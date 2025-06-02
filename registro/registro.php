<?php
session_start();
require_once __DIR__ . '/../includes/conexion.php';

if (!isset($_SESSION['id_usuarios']) || $_SESSION['rol_id'] != 1) {
  header("Location: ../dashboard.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registrar Usuario</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
<?php include "../includes/menu.php"; ?>

<div class="content p-4">

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

  <div class="card shadow-sm">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">👤 Registro de nuevo usuario</h4>
        <a href="lista_usuarios.php" class="btn btn-outline-primary">
          <i class="bi bi-list"></i> Lista de Usuarios
        </a>  
      </div>

      <center>
        <img src="../img/registro_usuario.gif" alt="Registro animado" class="img-fluid" style="max-width: 200px;">
      </center>

      <form action="procesar_registro.php" method="POST">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Nombre completo</label>
            <input type="text" name="nombre_completo" class="form-control" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Correo electrónico</label>
            <input type="email" name="correo" class="form-control" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Teléfono</label>
            <input type="text" name="telefono" class="form-control">
          </div>

          <div class="col-md-6">
            <label class="form-label">Dirección</label>
            <input type="text" name="direccion" class="form-control">
          </div>

          <div class="col-md-6">
            <label class="form-label">Contraseña</label>
            <input type="password" name="password" class="form-control" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Rol de usuario</label>
            <select name="rol_id" class="form-select" required>
              <option value="" disabled selected>Seleccione un rol</option>
              <option value="1">Administrador</option>
              <option value="2">Médico</option>
              <option value="3">Paciente</option>
            </select>
          </div>

          <div class="col-12 text-end">
            <button type="submit" class="btn btn-success">
              <i class="bi bi-check-circle"></i> Registrar Usuario
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

</body>
</html>
