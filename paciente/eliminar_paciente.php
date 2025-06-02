<?php
session_start();
require_once __DIR__ . '/../includes/conexion.php';

if (!isset($_SESSION['id_usuarios']) || $_SESSION['rol_id'] != 1) {
  header("Location: ../dashboard.php");
  exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header("Location: lista_pacientes.php");
  exit();
}

$id = $_GET['id'];

// Desactivar lógicamente
$conn->query("UPDATE usuarios SET activo = 0 WHERE id_usuarios = $id");
$conn->query("UPDATE pacientes SET activo = 0 WHERE usuario_id = $id");

// Redireccionar directamente sin mostrar pantalla blanca
header("Location: lista_pacientes.php?eliminado=1");
exit();
