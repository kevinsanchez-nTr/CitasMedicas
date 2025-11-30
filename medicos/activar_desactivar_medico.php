<?php
session_start();
require_once __DIR__ . '/../includes/conexion.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['mensaje_error'] = "ID de médico inválido.";
    header("Location: lista_medicos.php");
    exit();
}

$id_medico = intval($_GET['id']);

// Obtener usuario y estado actual del MÉDICO
$sql = "SELECT usuario_id, activo FROM medicos WHERE id_medicos = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_medico);
$stmt->execute();
$med = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$med) {
    $_SESSION['mensaje_error'] = "Médico no encontrado.";
    header("Location: lista_medicos.php");
    exit();
}

$usuario_id    = $med['usuario_id'];
$estado_actual = (int)$med['activo'];
$nuevo_estado  = ($estado_actual === 1) ? 0 : 1;

// Actualizar MÉDICO
$stmt1 = $conn->prepare("UPDATE medicos SET activo = ? WHERE id_medicos = ?");
$stmt1->bind_param("ii", $nuevo_estado, $id_medico);
$stmt1->execute();
$stmt1->close();

// Actualizar USUARIO
$stmt2 = $conn->prepare("UPDATE usuarios SET activo = ? WHERE id_usuarios = ?");
$stmt2->bind_param("ii", $nuevo_estado, $usuario_id);
$stmt2->execute();
$stmt2->close();

$_SESSION['mensaje_exito'] = ($nuevo_estado === 1)
    ? "Médico ACTIVADO correctamente."
    : "Médico DESACTIVADO correctamente.";

header("Location: lista_medicos.php");
exit();
?>
