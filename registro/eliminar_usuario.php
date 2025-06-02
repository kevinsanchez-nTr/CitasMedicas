<?php
session_start();
require_once __DIR__ . '/../includes/conexion.php';

if (!isset($_SESSION['id_usuarios']) || $_SESSION['rol_id'] != 1) {
  header("Location: ../dashboard.php");
  exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  $_SESSION['mensaje_error'] = '❌ ID inválido.';
  header("Location: lista_usuarios.php");
  exit();
}

$id = $_GET['id'];

// Desactivar usuario (eliminación lógica)
$sql = "UPDATE usuarios SET activo = 0 WHERE id_usuarios = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
  $_SESSION['mensaje_exito'] = '✅ Usuario eliminado correctamente.';
} else {
  $_SESSION['mensaje_error'] = '❌ Error al eliminar el usuario.';
}

$stmt->close();
$conn->close();
header("Location: lista_usuarios.php");
exit();
