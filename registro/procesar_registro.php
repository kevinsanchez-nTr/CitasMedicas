<?php
session_start(); // Necesario para usar $_SESSION
require_once __DIR__ . '/../includes/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre_completo'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $direccion = $_POST['direccion'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $rol_id = $_POST['rol_id'];

    // Validar si ya existe el correo
    $check = $conn->prepare("SELECT id_usuarios FROM usuarios WHERE correo = ?");
    $check->bind_param("s", $correo);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['mensaje_error'] = "❌ El correo ya está registrado";
        header("Location: registro.php");
        exit();
    } else {
        // Insertar en la tabla usuarios
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre_completo, correo, telefono, direccion, password, rol_id, activo) VALUES (?, ?, ?, ?, ?, ?, 1)");
        $stmt->bind_param("sssssi", $nombre, $correo, $telefono, $direccion, $password, $rol_id);
        
        if ($stmt->execute()) {
            $usuario_id = $stmt->insert_id;

            // Insertar en médicos si rol es 2
            if ($rol_id == 2) {
                $especialidad_id = 1; // temporal
                $stmt_medico = $conn->prepare("INSERT INTO medicos (usuario_id, especialidad_id, activo) VALUES (?, ?, 1)");
                $stmt_medico->bind_param("ii", $usuario_id, $especialidad_id);
                $stmt_medico->execute();
                $stmt_medico->close();
            }

            // Insertar en pacientes si rol es 3
            // Insertar en pacientes sin valores predeterminados
if ($rol_id == 3) {
    $stmt_paciente = $conn->prepare("INSERT INTO pacientes (usuario_id, activo) VALUES (?, 1)");
    $stmt_paciente->bind_param("i", $usuario_id);
    $stmt_paciente->execute();
    $stmt_paciente->close();
}


            $_SESSION['mensaje_exito'] = "✅ Usuario registrado correctamente";
            header("Location: registro.php");
            exit();
        } else {
            $_SESSION['mensaje_error'] = "❌ Error al registrar el usuario";
            header("Location: registro.php");
            exit();
        }

        $stmt->close();
    }

    $check->close();
    $conn->close();
} else {
    header("Location: registro.php");
    exit();
}
