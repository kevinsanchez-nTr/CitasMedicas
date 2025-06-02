<?php
session_start();
require_once __DIR__ . '/../includes/conexion.php';

if (!isset($_SESSION['id_usuarios'])) {
  header("Location: ../../index.php");
  exit();
}

$id = $_SESSION['id_usuarios'];
$nombre = $_SESSION['nombre'];

// Obtener datos actuales
$sql = "SELECT * FROM usuarios WHERE id_usuarios = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nuevo_nombre = $_POST['nombre'];
  $nuevo_correo = $_POST['correo'];
  $nueva_password = $_POST['nueva_password'];

  if (!empty($nueva_password)) {
    $hashed = password_hash($nueva_password, PASSWORD_DEFAULT);
    $update = $conn->prepare("UPDATE usuarios SET nombre_completo=?, correo=?, password=? WHERE id_usuarios=?");
    $update->bind_param("sssi", $nuevo_nombre, $nuevo_correo, $hashed, $id);
  } else {
    $update = $conn->prepare("UPDATE usuarios SET nombre_completo=?, correo=? WHERE id_usuarios=?");
    $update->bind_param("ssi", $nuevo_nombre, $nuevo_correo, $id);
  }

  if ($update->execute()) {
    $_SESSION['nombre'] = $nuevo_nombre;
    $_SESSION['mensaje_exito'] = '✅ Perfil actualizado correctamente.';
    header("Location: editar_perfil.php");
    exit();
  } else {
    $_SESSION['mensaje_error'] = '❌ Error al actualizar el perfil.';
    header("Location: editar_perfil.php");
    exit();
  }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Perfil</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
<?php include "../includes/menu.php"; ?>

<div class="content">
  <div class="form-edit d-flex flex-wrap">

    <div class="col-md-6 p-3 d-none d-md-block">
      <img src="../img/editarperfil.gif" alt="Editar perfil" class="img-fluid rounded">
    </div>

    <div class="col-md-6 p-3">
      <h5 class="mb-3">🛠 Editar mi perfil</h5>

      <?php if (isset($_SESSION['mensaje_exito'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['mensaje_exito']; unset($_SESSION['mensaje_exito']); ?></div>
      <?php endif; ?>
      <?php if (isset($_SESSION['mensaje_error'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['mensaje_error']; unset($_SESSION['mensaje_error']); ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="mb-3">
          <label class="form-label">Nombre completo</label>
          <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($usuario['nombre_completo']) ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Correo electrónico</label>
          <input type="email" name="correo" class="form-control" value="<?= htmlspecialchars($usuario['correo']) ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Nueva contraseña (opcional)</label>
          <input type="password" name="nueva_password" class="form-control">
        </div>
        <div class="d-grid">
          <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </div>
      </form>
    </div>
  </div>
</div>

</body>
</html>
