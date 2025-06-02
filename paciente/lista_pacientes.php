<?php
session_start();
require_once __DIR__ . '/../includes/conexion.php';

if (!isset($_SESSION['id_usuarios']) || $_SESSION['rol_id'] != 1) {
  header("Location: ../dashboard.php");
  exit();
}

// Consulta pacientes activos
$sql = "SELECT u.id_usuarios, u.nombre_completo, u.correo, u.telefono, u.direccion,
               p.fecha_nacimiento, p.grupo_sanguineo
        FROM usuarios u
        INNER JOIN pacientes p ON u.id_usuarios = p.usuario_id
        WHERE u.rol_id = 3 AND u.activo = 1 AND p.activo = 1";

$resultado = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Lista de Pacientes</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>

<?php include "../includes/menu.php"; ?>

<div class="content">

  <!-- ✅ Alerta por edición -->
  <?php if (isset($_GET['editado']) && $_GET['editado'] == 1): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      ✅ Paciente editado correctamente.
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
  <?php endif; ?>

  <!-- ✅ Alerta por eliminación -->
  <?php if (isset($_GET['eliminado']) && $_GET['eliminado'] == 1): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      ✅ Paciente eliminado visualmente.
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
  <?php endif; ?>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>👥 Lista de Pacientes</h3>
    <a href="registro_paciente.php" class="btn btn-primary">
      <i class="bi bi-person-plus"></i> Registrar Paciente
    </a>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered table-hover align-middle">
      <thead class="table-primary">
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
        <?php $i = 1; while ($fila = $resultado->fetch_assoc()): ?>
          <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($fila['nombre_completo']) ?></td>
            <td><?= htmlspecialchars($fila['correo']) ?></td>
            <td><?= htmlspecialchars($fila['telefono']) ?></td>
            <td><span class="badge text-bg-info">Paciente</span></td>
            <td>
              <a href="datos_paciente.php?id=<?= $fila['id_usuarios'] ?>" class="btn btn-sm btn-secondary" title="Datos">
                <i class="bi bi-eye"></i> Datos
              </a>
              <a href="editar_paciente.php?id=<?= $fila['id_usuarios'] ?>" class="btn btn-sm btn-warning" title="Editar">
                <i class="bi bi-pencil-square"></i> Editar
              </a>
              <a href="eliminar_paciente.php?id=<?= $fila['id_usuarios'] ?>" class="btn btn-sm btn-danger"
                 onclick="return confirm('¿Estás seguro de querer ocultar este paciente?')">
                <i class="bi bi-trash3-fill"></i> Eliminar
              </a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ✅ Bootstrap JS para cerrar alertas -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
