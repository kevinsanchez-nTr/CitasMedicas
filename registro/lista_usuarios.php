<?php
session_start();
require_once __DIR__ . '/../includes/conexion.php';

if (!isset($_SESSION['id_usuarios']) || $_SESSION['rol_id'] != 1) {
  header("Location: ../dashboard.php");
  exit();
}

// Obtener usuarios activos
$sql = "SELECT u.id_usuarios, u.nombre_completo, u.correo, 
               COALESCE(u.telefono, '') AS telefono, 
               COALESCE(u.direccion, '') AS direccion, 
               r.nombre AS rol
        FROM usuarios u
        JOIN roles r ON u.rol_id = r.id_roles
        WHERE u.activo = 1";

$resultado = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Usuarios</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
<?php include "../includes/menu.php"; ?>

<div class="content">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-people-fill"></i> Gestión de Usuarios</h4>
    <a href="registro.php" class="btn btn-primary">
      <i class="bi bi-person-plus-fill"></i> Registrar Usuario
    </a>
  </div>

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

  <div class="card shadow-sm">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Nombre</th>
            <th>Correo</th>
            <th>Teléfono</th>
            <th>Rol</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php $n = 1; while ($row = $resultado->fetch_assoc()): ?>
            <tr>
              <td><?= $n++ ?></td>
              <td><?= htmlspecialchars($row['nombre_completo']) ?></td>
              <td><?= htmlspecialchars($row['correo']) ?></td>
              <td><?= htmlspecialchars($row['telefono']) ?></td>
              <td><?= htmlspecialchars($row['rol']) ?></td>
              <td>
                <a href="editar_usuario.php?id=<?= $row['id_usuarios'] ?>" class="btn btn-sm btn-warning me-2">
                  <i class="bi bi-pencil-square"></i> Editar
                </a>
                <a href="eliminar_usuario.php?id=<?= $row['id_usuarios'] ?>" class="btn btn-sm btn-danger"
                   onclick="return confirm('¿Estás seguro de eliminar este usuario?')">
                  <i class="bi bi-trash"></i> Eliminar
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>
