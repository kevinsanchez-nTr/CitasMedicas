<?php
session_start();
require_once __DIR__ . '/../includes/conexion.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['mensaje_error'] = "ID de usuario inválido.";
    header("Location: lista_usuarios.php");
    exit();
}

$id_usuario = intval($_GET['id']);

// Obtener rol y estado actual del USUARIO
$sql = "SELECT rol_id, activo FROM usuarios WHERE id_usuarios = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$u = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$u) {
    $_SESSION['mensaje_error'] = "Usuario no encontrado.";
    header("Location: lista_usuarios.php");
    exit();
}

$rol_id       = (int)$u['rol_id'];
$estado_actual = (int)$u['activo'];
$nuevo_estado  = ($estado_actual === 1) ? 0 : 1;

// Actualizar USUARIO
$stmt2 = $conn->prepare("UPDATE usuarios SET activo = ? WHERE id_usuarios = ?");
$stmt2->bind_param("ii", $nuevo_estado, $id_usuario);
$stmt2->execute();
$stmt2->close();

// Si es MÉDICO → actualizar tabla medicos
if ($rol_id === 2) {
    $stmt3 = $conn->prepare("UPDATE medicos SET activo = ? WHERE usuario_id = ?");
    $stmt3->bind_param("ii", $nuevo_estado, $id_usuario);
    $stmt3->execute();
    $stmt3->close();
}

// Si es PACIENTE → actualizar tabla pacientes
if ($rol_id === 3) {
    $stmt4 = $conn->prepare("UPDATE pacientes SET activo = ? WHERE usuario_id = ?");
    $stmt4->bind_param("ii", $nuevo_estado, $id_usuario);
    $stmt4->execute();
    $stmt4->close();
}

$_SESSION['mensaje_exito'] = ($nuevo_estado === 1)
    ? "Usuario ACTIVADO correctamente."
    : "Usuario DESACTIVADO correctamente.";

header("Location: lista_usuarios.php");
exit();
?>
