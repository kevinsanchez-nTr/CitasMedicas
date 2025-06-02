<?php
session_start();
require_once __DIR__ . '/../includes/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $nombre = $_POST['nombre_completo'];
  $correo = $_POST['correo'];
  $telefono = $_POST['telefono'];
  $direccion = $_POST['direccion'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  $rol_id = 3; // paciente

  // Validar si el correo ya existe
  $check = $conn->prepare("SELECT id_usuarios FROM usuarios WHERE correo = ?");
  $check->bind_param("s", $correo);
  $check->execute();
  $result = $check->get_result();

  if ($result->num_rows > 0) {
    $_SESSION['mensaje_error'] = "⚠️ Este correo ya está registrado.";
    header("Location: registro_paciente.php");
    exit();
  } else {
    // Insertar en la tabla usuarios
    $stmt = $conn->prepare("INSERT INTO usuarios (nombre_completo, correo, telefono, direccion, password, rol_id, activo) VALUES (?, ?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("sssssi", $nombre, $correo, $telefono, $direccion, $password, $rol_id);

    if ($stmt->execute()) {
      $usuario_id = $stmt->insert_id;

      // Insertar relación en tabla pacientes (sin fecha ni grupo)
      $stmt_paciente = $conn->prepare("INSERT INTO pacientes (usuario_id, activo) VALUES (?, 1)");
      $stmt_paciente->bind_param("i", $usuario_id);
      $stmt_paciente->execute();
      $stmt_paciente->close();

      $_SESSION['mensaje_exito'] = "✅ Paciente registrado correctamente.";
      header("Location: ../dashboard.php");
      exit();
    } else {
      $_SESSION['mensaje_error'] = "❌ Error al registrar el paciente.";
      header("Location: registro_paciente.php");
      exit();
    }

    $stmt->close();
  }

  $check->close();
  $conn->close();
} else {
  header("Location: registro_paciente.php");
  exit();
}
