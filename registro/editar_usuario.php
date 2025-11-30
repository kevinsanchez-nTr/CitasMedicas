<?php
session_start();
require_once __DIR__ . '/../includes/conexion.php';

// Solo ADMIN puede entrar
if (!isset($_SESSION['id_usuarios']) || ($_SESSION['rol_id'] ?? null) != 1) {
    header("Location: ../dashboard.php");
    exit();
}

// Validar ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['mensaje_error'] = "❌ ID de usuario no válido.";
    header("Location: lista_usuarios.php");
    exit();
}

$id_usuario = intval($_GET['id']);
$error = "";

// Traer datos del usuario
$stmt = $conn->prepare("SELECT id_usuarios, nombre_completo, correo, telefono, direccion, rol_id 
                        FROM usuarios 
                        WHERE id_usuarios = ? AND activo = 1");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();
$stmt->close();

if (!$usuario) {
    $_SESSION['mensaje_error'] = "❌ Usuario no encontrado.";
    header("Location: lista_usuarios.php");
    exit();
}

// Guardamos el rol original para comparar luego
$rol_original = $usuario['rol_id'];

// Procesar envío del formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre_completo = trim($_POST['nombre_completo'] ?? '');
    $correo          = trim($_POST['correo'] ?? '');
    $telefono        = trim($_POST['telefono'] ?? '');
    $direccion       = trim($_POST['direccion'] ?? '');
    $rol_id          = intval($_POST['rol_id'] ?? 0);

    if ($nombre_completo === "" || $correo === "" || $rol_id === 0) {
        $error = "❌ Completa al menos Nombre, Correo y Rol.";
    } else {

        // Actualizar datos en tabla usuarios
        $update = $conn->prepare("UPDATE usuarios 
                                  SET nombre_completo = ?, correo = ?, telefono = ?, direccion = ?, rol_id = ?
                                  WHERE id_usuarios = ? AND activo = 1");
        $update->bind_param("ssssii", $nombre_completo, $correo, $telefono, $direccion, $rol_id, $id_usuario);

        if ($update->execute()) {

            // 🔁 Si el rol cambió, sincronizamos tablas medicos / pacientes
            if ($rol_original != $rol_id) {

                // 👉 Si AHORA es MÉDICO (rol_id = 2)
                if ($rol_id == 2) {

                    // Desactivar posible registro de paciente
                    $stmt = $conn->prepare("UPDATE pacientes SET activo = 0 WHERE usuario_id = ?");
                    $stmt->bind_param("i", $id_usuario);
                    $stmt->execute();
                    $stmt->close();

                    // Verificar si ya existe en medicos
                    $stmt = $conn->prepare("SELECT id_medicos FROM medicos WHERE usuario_id = ?");
                    $stmt->bind_param("i", $id_usuario);
                    $stmt->execute();
                    $resMed = $stmt->get_result();
                    $stmt->close();

                    if ($resMed->num_rows == 0) {
                        // Crear médico sin especialidad por ahora
                        $stmt = $conn->prepare(
                            "INSERT INTO medicos (usuario_id, especialidad_id, activo) VALUES (?, NULL, 1)"
                        );
                        $stmt->bind_param("i", $id_usuario);
                        $stmt->execute();
                        $stmt->close();
                    } else {
                        // Reactivar si ya existía
                        $stmt = $conn->prepare("UPDATE medicos SET activo = 1 WHERE usuario_id = ?");
                        $stmt->bind_param("i", $id_usuario);
                        $stmt->execute();
                        $stmt->close();
                    }

                // 👉 Si AHORA es PACIENTE (rol_id = 3)
                } elseif ($rol_id == 3) {

                    // Desactivar posible médico
                    $stmt = $conn->prepare("UPDATE medicos SET activo = 0 WHERE usuario_id = ?");
                    $stmt->bind_param("i", $id_usuario);
                    $stmt->execute();
                    $stmt->close();

                    // Verificar si ya existe en pacientes
                    $stmt = $conn->prepare("SELECT id_pacientes FROM pacientes WHERE usuario_id = ?");
                    $stmt->bind_param("i", $id_usuario);
                    $stmt->execute();
                    $resPac = $stmt->get_result();
                    $stmt->close();

                    if ($resPac->num_rows == 0) {
                        $stmt = $conn->prepare("INSERT INTO pacientes (usuario_id, activo) VALUES (?, 1)");
                        $stmt->bind_param("i", $id_usuario);
                        $stmt->execute();
                        $stmt->close();
                    } else {
                        $stmt = $conn->prepare("UPDATE pacientes SET activo = 1 WHERE usuario_id = ?");
                        $stmt->bind_param("i", $id_usuario);
                        $stmt->execute();
                        $stmt->close();
                    }

                // 👉 Si AHORA es ADMIN u otro rol (ej. 1 = Admin)
                } else {
                    // Desactivar en ambas tablas
                    $stmt = $conn->prepare("UPDATE medicos SET activo = 0 WHERE usuario_id = ?");
                    $stmt->bind_param("i", $id_usuario);
                    $stmt->execute();
                    $stmt->close();

                    $stmt = $conn->prepare("UPDATE pacientes SET activo = 0 WHERE usuario_id = ?");
                    $stmt->bind_param("i", $id_usuario);
                    $stmt->execute();
                    $stmt->close();
                }
            }

            $_SESSION['mensaje_exito'] = "✅ Usuario actualizado correctamente.";
            header("Location: lista_usuarios.php");
            exit();

        } else {
            $error = "❌ Error al actualizar el usuario.";
        }

        $update->close();
    }

    // Si hubo error, actualizamos los datos mostrados con lo que el usuario envió
    $usuario['nombre_completo'] = $nombre_completo;
    $usuario['correo']          = $correo;
    $usuario['telefono']        = $telefono;
    $usuario['direccion']       = $direccion;
    $usuario['rol_id']          = $rol_id;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>

<?php include "../includes/menu.php"; ?>

<div class="content">
    <h3 class="mb-4">✏️ Editar Usuario</h3>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="POST" class="card p-4 shadow-sm">
        <div class="mb-3">
            <label class="form-label">Nombre completo</label>
            <input type="text" name="nombre_completo" class="form-control"
                   value="<?= htmlspecialchars($usuario['nombre_completo']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Correo</label>
            <input type="email" name="correo" class="form-control"
                   value="<?= htmlspecialchars($usuario['correo']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Teléfono</label>
            <input type="text" name="telefono" class="form-control"
                   value="<?= htmlspecialchars($usuario['telefono']); ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Dirección</label>
            <input type="text" name="direccion" class="form-control"
                   value="<?= htmlspecialchars($usuario['direccion']); ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Rol</label>
            <select name="rol_id" class="form-select" required>
                <option value="1" <?= $usuario['rol_id'] == 1 ? 'selected' : ''; ?>>Administrador</option>
                <option value="2" <?= $usuario['rol_id'] == 2 ? 'selected' : ''; ?>>Médico</option>
                <option value="3" <?= $usuario['rol_id'] == 3 ? 'selected' : ''; ?>>Paciente</option>
            </select>
        </div>

        <div class="d-flex justify-content-between">
            <a href="lista_usuarios.php" class="btn btn-secondary">
                ⬅️ Regresar
            </a>
            <button type="submit" class="btn btn-primary">
                💾 Guardar cambios
            </button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
