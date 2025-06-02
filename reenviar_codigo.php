<?php
session_start();
require_once "includes/conexion.php";
require_once "enviar_token.php";

// Asegurar que hay un usuario autenticado en la fase pre-login
if (!isset($_SESSION['id_usuarios_prelogin'])) {
  header("Location: index.php");
  exit();
}

$usuario_id = $_SESSION['id_usuarios_prelogin'];

// Obtener el correo del usuario
$sql = "SELECT correo FROM usuarios WHERE id_usuarios = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($usuario = $resultado->fetch_assoc()) {
  $correo = $usuario['correo'];
  $nuevo_token = rand(100000, 999999);

  // Expirar todos los tokens anteriores de este usuario
  $expirar = $conn->prepare("UPDATE multifactor_tokens SET expirado = 1 WHERE usuario_id = ? AND expirado = 0");
  $expirar->bind_param("i", $usuario_id);
  $expirar->execute();

  // Insertar el nuevo token
  $insertar = $conn->prepare("INSERT INTO multifactor_tokens (usuario_id, token) VALUES (?, ?)");
  $insertar->bind_param("is", $usuario_id, $nuevo_token);
  $insertar->execute();

  // Enviar el nuevo código al correo del usuario
  if (enviarCodigo($correo, $nuevo_token)) {
    echo "<script>alert('📩 Se ha enviado un nuevo código a tu correo.'); window.location='verificar_token.php';</script>";
  } else {
    echo "<script>alert('⚠️ Error al enviar el código. Intenta más tarde.'); window.location='verificar_token.php';</script>";
  }
} else {
  header("Location: index.php");
  exit();
}
?>
