<?php
session_start();
require_once __DIR__ . '/../includes/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Sanitizar y obtener datos
    $nombre           = trim($_POST['nombre_completo'] ?? '');
    $correo           = trim($_POST['correo'] ?? '');
    $telefono         = trim($_POST['telefono'] ?? '');
    $direccion        = trim($_POST['direccion'] ?? '');
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
    $grupo_sanguineo  = trim($_POST['grupo_sanguineo'] ?? '');
    $password_raw     = $_POST['password'] ?? '';

    // Validación básica
    if ($nombre === '' || $correo === '' || $password_raw === '' ||
        $fecha_nacimiento === '' || $grupo_sanguineo === '') {

        $_SESSION['mensaje_error'] = "❌ Completa todos los campos del formulario.";
        header("Location: registro_paciente.php");
        exit();
    }

    // Encriptar contraseña
    $password = password_hash($password_raw, PASSWORD_DEFAULT);

    // Validar correo duplicado
    $check = $conn->prepare("SELECT id_usuarios FROM usuarios WHERE correo = ?");
    $check->bind_param("s", $correo);
    $check->execute();
    $result = $check->get_result();
    $check->close();

    if ($result->num_rows > 0) {
        $_SESSION['mensaje_error'] = "❌ El correo ya está registrado.";
        header("Location: registro_paciente.php");
        exit();
    }

    // Insertar en usuarios (rol 3 = Paciente)
    $stmt = $conn->prepare("
        INSERT INTO usuarios 
        (nombre_completo, correo, telefono, direccion, password, rol_id, activo)
        VALUES (?, ?, ?, ?, ?, 3, 1)
    ");
    $stmt->bind_param("sssss", $nombre, $correo, $telefono, $direccion, $password);

    if (!$stmt->execute()) {
        $_SESSION['mensaje_error'] = "❌ Error al registrar el usuario.";
        header("Location: registro_paciente.php");
        exit();
    }

    // Obtener ID del usuario recién registrado
    $usuario_id = $stmt->insert_id;
    $stmt->close();

    if ($usuario_id > 0) {

        // Insertar en tabla pacientes
        $stmt2 = $conn->prepare("
            INSERT INTO pacientes (usuario_id, fecha_nacimiento, grupo_sanguineo, activo)
            VALUES (?, ?, ?, 1)
        ");

        $stmt2->bind_param("iss", $usuario_id, $fecha_nacimiento, $grupo_sanguineo);
        $stmt2->execute();
        $stmt2->close();

        $_SESSION['mensaje_exito'] = "✅ Paciente registrado correctamente.";
        header("Location: lista_pacientes.php");
        exit();
    }

    $_SESSION['mensaje_error'] = "❌ Error al registrar el paciente.";
    header("Location: registro_paciente.php");
    exit();

} else {
    // Acceso incorrecto
    header("Location: registro_paciente.php");
    exit();
}
?>
