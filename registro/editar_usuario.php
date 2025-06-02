<?php
session_start();
require_once __DIR__ . '/../includes/conexion.php';

if (!isset($_SESSION['id_usuarios']) || $_SESSION['rol_id'] != 1) {
  header("Location: ../dashboard.php");
  exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header("Location: lista_usuarios.php");
  exit();
}

$id_usuario = $_GET['id'];

$sql = "SELECT * FROM usuarios WHERE id_usuarios = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
  $_SESSION['mensaje_error'] = '❌ Usuario no encontrado.';
  header("Location: lista_usuarios.php");
  exit();
}

$usuario = $resultado->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre = $_POST['nombre_completo'];
  $correo = $_POST['correo'];
  $telefono = $_POST['telefono'];
  $direccion = $_POST['direccion'];
  $rol_id = $_POST['rol_id'];
  $nueva_password = $_POST['nueva_password'];

  if (!empty($nueva_password)) {
    $hashed = password_hash($nueva_password, PASSWORD_DEFAULT);
    $update = $conn->prepare("UPDATE usuarios SET nombre_completo=?, correo=?, telefono=?, direccion=?, rol_id=?, password=? WHERE id_usuarios=?");
    $update->bind_param("ssssisi", $nombre, $correo, $telefono, $direccion, $rol_id, $hashed, $id_usuario);
  } else {
    $update = $conn->prepare("UPDATE usuarios SET nombre_completo=?, correo=?, telefono=?, direccion=?, rol_id=? WHERE id_usuarios=?");
    $update->bind_param("ssssii", $nombre, $correo, $telefono, $direccion, $rol_id, $id_usuario);
  }

  if ($update->execute()) {
    $_SESSION['mensaje_exito'] = '✅ Usuario actualizado correctamente.';
    header("Location: lista_usuarios.php");
    exit();
  } else {
    $error = "❌ Error al actualizar el usuario.";
  }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Usuario</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
<?php include "../includes/menu.php"; ?>

<div class="content">
  <div class="container">
    <div class="bg-white p-4 rounded shadow-sm">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-pencil-square text-primary"></i> Editar usuario</h4>
        <a href="lista_usuarios.php" class="btn btn-outline-primary"><i class="bi bi-list-ul"></i> Lista de Usuarios</a>
      </div>

      <div class="text-center mb-4">
        <img src="../img/editar_usuario.gif" alt="Editar perfil" style="max-width: 150px;">
      </div>

      <?php if (isset($_SESSION['mensaje_exito'])): ?>
        <div class="alert alert-success text-center"><?php echo $_SESSION['mensaje_exito']; unset($_SESSION['mensaje_exito']); ?></div>
      <?php endif; ?>
      <?php if (isset($error)): ?>
        <div class="alert alert-danger text-center"><?php echo $error; ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Nombre completo</label>
            <input type="text" name="nombre_completo" class="form-control" value="<?= htmlspecialchars($usuario['nombre_completo']) ?>" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Correo electrónico</label>
            <input type="email" name="correo" class="form-control" value="<?= htmlspecialchars($usuario['correo']) ?>" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Teléfono</label>
            <input type="text" name="telefono" class="form-control" value="<?= htmlspecialchars($usuario['telefono']) ?>">
          </div>

          <div class="col-md-6">
            <label class="form-label">Dirección</label>
            <input type="text" name="direccion" class="form-control" value="<?= htmlspecialchars($usuario['direccion']) ?>">
          </div>

          <div class="col-md-6">
            <label class="form-label">Nueva contraseña (opcional)</label>
            <input type="password" name="nueva_password" class="form-control">
          </div>

          <div class="col-md-6">
            <label class="form-label">Rol de usuario</label>
            <select name="rol_id" class="form-select" required>
              <option value="">Seleccione un rol</option>
              <option value="1" <?= $usuario['rol_id'] == 1 ? 'selected' : '' ?>>Administrador</option>
              <option value="2" <?= $usuario['rol_id'] == 2 ? 'selected' : '' ?>>Médico</option>
              <option value="3" <?= $usuario['rol_id'] == 3 ? 'selected' : '' ?>>Paciente</option>
            </select>
          </div>
        </div>

        <div class="d-flex justify-content-end mt-4">
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-circle"></i> Guardar cambios
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

</body>
</html>
