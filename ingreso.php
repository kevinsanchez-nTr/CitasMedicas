<?php
// Conexión directa fuera del proyecto
$conexion = new mysqli("localhost", "root", "", "citasmedicas");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$mensaje = "";

// Si se envió el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre_completo'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $password = $_POST['password'] ?? '';
    $rol_id = $_POST['rol_id'] ?? 3;

    if (empty($nombre) || empty($correo) || empty($password)) {
        $mensaje = '<div class="alert alert-danger">Todos los campos obligatorios deben completarse.</div>';
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $conexion->prepare("INSERT INTO usuarios (nombre_completo, correo, telefono, direccion, password, rol_id, activo, fecha_registro)
                                    VALUES (?, ?, ?, ?, ?, ?, 1, NOW())");
        $stmt->bind_param("sssssi", $nombre, $correo, $telefono, $direccion, $hash, $rol_id);

        if ($stmt->execute()) {
            $mensaje = '<div class="alert alert-success">✅ Usuario registrado correctamente.</div>';
        } else {
            $mensaje = '<div class="alert alert-danger">❌ Error al registrar usuario: ' . $stmt->error . '</div>';
        }

        $stmt->close();
    }

    $conexion->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registro de Usuario</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0">👤 Registro de nuevo usuario</h5>
        </div>
        <div class="card-body">

          <?= $mensaje ?>

          <form method="POST">
            <div class="mb-3">
              <label class="form-label">Nombre completo:</label>
              <input type="text" name="nombre_completo" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Correo electrónico:</label>
              <input type="email" name="correo" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Teléfono:</label>
              <input type="text" name="telefono" class="form-control">
            </div>

            <div class="mb-3">
              <label class="form-label">Dirección:</label>
              <input type="text" name="direccion" class="form-control">
            </div>

            <div class="mb-3">
              <label class="form-label">Contraseña:</label>
              <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Rol:</label>
              <select name="rol_id" class="form-select" required>
                <option value="">Seleccione un rol</option>
                <option value="1">Administrador</option>
                <option value="2">Médico</option>
                <option value="3">Paciente</option>
              </select>
            </div>

            <div class="text-end">
              <button type="submit" class="btn btn-success">
                <i class="bi bi-person-plus"></i> Registrar Usuario
              </button>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>
