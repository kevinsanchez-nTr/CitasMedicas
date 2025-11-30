<?php
session_start();
require_once __DIR__ . '/../includes/conexion.php';

$mensaje = "";
$error = "";

// Procesar el formulario al enviar
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre_completo'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $password = $_POST['password'] ?? '';
    $especialidad_id = $_POST['especialidad_id'] !== "" ? intval($_POST['especialidad_id']) : null;

    if ($nombre === '' || $correo === '' || $password === '') {
        $error = "❌ Debes completar todos los campos obligatorios.";
    } else {
        // Verificar si el correo ya existe
        $check = $conn->prepare("SELECT id_usuarios FROM usuarios WHERE correo = ?");
        $check->bind_param("s", $correo);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $error = "❌ El correo ya está registrado.";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Insertar usuario (rol_id = 2 para Médico)
            $stmt = $conn->prepare("INSERT INTO usuarios 
                (nombre_completo, correo, telefono, direccion, password, rol_id, activo)
                VALUES (?, ?, ?, ?, ?, 2, 1)");
            $stmt->bind_param("sssss", $nombre, $correo, $telefono, $direccion, $password_hash);

            if ($stmt->execute()) {
                $id_usuario = $conn->insert_id;

                // Crear registro en tabla medicos
                $stmt2 = $conn->prepare("INSERT INTO medicos (usuario_id, especialidad_id, activo) VALUES (?, ?, 1)");
                $stmt2->bind_param("ii", $id_usuario, $especialidad_id);
                $stmt2->execute();
                $stmt2->close();

                $mensaje = "✅ Médico registrado correctamente.";
            } else {
                $error = "❌ Error al registrar el médico.";
            }
            $stmt->close();
        }
    }
}

// Obtener especialidades para el select
$especialidades = $conn->query("SELECT * FROM especialidades ORDER BY nombre ASC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Médico</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>

<?php include "../includes/menu.php"; ?>

<div class="content p-4">
    <div class="container">
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">👨‍⚕️ Registrar Médico</h4>
            </div>
            <div class="card-body">

                <?php if ($mensaje): ?>
                    <div class="alert alert-success"><?= $mensaje ?></div>
                <?php elseif ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre completo *</label>
                            <input type="text" name="nombre_completo" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Correo *</label>
                            <input type="email" name="correo" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="telefono" class="form-control">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Dirección</label>
                            <input type="text" name="direccion" class="form-control">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contraseña *</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Especialidad</label>
                            <select name="especialidad_id" class="form-select">
                                <option value="">Sin especialidad</option>
                                <?php while ($esp = $especialidades->fetch_assoc()): ?>
                                    <option value="<?= $esp['id_especialidades']; ?>">
                                        <?= htmlspecialchars($esp['nombre']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="lista_medicos.php" class="btn btn-secondary">⬅️ Volver</a>
                        <button type="submit" class="btn btn-primary">💾 Registrar Médico</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

</body>
</html>
