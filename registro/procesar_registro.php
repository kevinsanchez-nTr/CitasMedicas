<?php
session_start();
require_once __DIR__ . '/../includes/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Datos del formulario
    $nombre     = trim($_POST['nombre_completo'] ?? '');
    $correo     = trim($_POST['correo'] ?? '');
    $telefono   = trim($_POST['telefono'] ?? '');
    $direccion  = trim($_POST['direccion'] ?? '');
    $password   = $_POST['password'] ?? '';
    $rol_id     = intval($_POST['rol_id'] ?? 0);

    // Validación básica
    if ($nombre === '' || $correo === '' || $password === '' || $rol_id === 0) {
        $_SESSION['mensaje_error'] = "❌ Completa todos los campos obligatorios.";
        header("Location: registro.php");
        exit();
    }

    // Encriptar contraseña
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Validar si ya existe el correo
    $check = $conn->prepare("SELECT id_usuarios FROM usuarios WHERE correo = ?");
    $check->bind_param("s", $correo);
    $check->execute();
    $result = $check->get_result();
    $check->close();

    if ($result->num_rows > 0) {
        $_SESSION['mensaje_error'] = "❌ El correo ya está registrado";
        header("Location: registro.php");
        exit();
    }

    // Insertar en tabla usuarios
    $stmt = $conn->prepare("INSERT INTO usuarios 
        (nombre_completo, correo, telefono, direccion, password, rol_id, activo) 
        VALUES (?, ?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("sssssi", $nombre, $correo, $telefono, $direccion, $password_hash, $rol_id);

    if ($stmt->execute()) {

        // ID del usuario recién creado
        $id_usuario_nuevo = $conn->insert_id;
        $stmt->close();

        // 🔁 Crear registro en medicos o pacientes según el rol
        if ($rol_id == 2) {
            // 👉 ROL: MÉDICO
            // especialidad_id se deja NULL por defecto
            $stmt2 = $conn->prepare(
                "INSERT INTO medicos (usuario_id, especialidad_id, activo) VALUES (?, NULL, 1)"
            );
            $stmt2->bind_param("i", $id_usuario_nuevo);
            $stmt2->execute();
            $stmt2->close();

        } elseif ($rol_id == 3) {
            // 👉 ROL: PACIENTE
            $stmt2 = $conn->prepare(
                "INSERT INTO pacientes (usuario_id, activo) VALUES (?, 1)"
            );
            $stmt2->bind_param("i", $id_usuario_nuevo);
            $stmt2->execute();
            $stmt2->close();
        }

        // Mensaje y redirección
        $_SESSION['mensaje_exito'] = "✅ Usuario registrado correctamente.";
        header("Location: lista_usuarios.php");
        exit();

    } else {
        $stmt->close();
        $_SESSION['mensaje_error'] = "❌ Error al registrar el usuario.";
        header("Location: registro.php");
        exit();
    }

} else {
    // Si intentan entrar directo sin POST
    header("Location: registro.php");
    exit();
}
