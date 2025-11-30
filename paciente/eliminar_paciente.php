<?php
session_start();
require_once __DIR__ . '/../includes/conexion.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['mensaje_error'] = "ID inválido.";
    header("Location: lista_pacientes.php");
    exit();
}

$id_paciente = intval($_GET['id']);

// Obtener ID del usuario asociado
$sql = "SELECT usuario_id FROM pacientes WHERE id_pacientes = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_paciente);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$res) {
    $_SESSION['mensaje_error'] = "Paciente no encontrado.";
    header("Location: lista_pacientes.php");
    exit();
}

$usuario_id = $res['usuario_id'];

// 🔥 DESACTIVAR PACIENTE
$stmt1 = $conn->prepare("UPDATE pacientes SET activo = 0 WHERE id_pacientes = ?");
$stmt1->bind_param("i", $id_paciente);
$stmt1->execute();
$stmt1->close();

// 🔥 DESACTIVAR USUARIO (opcional pero recomendado)
$stmt2 = $conn->prepare("UPDATE usuarios SET activo = 0 WHERE id_usuarios = ?");
$stmt2->bind_param("i", $usuario_id);
$stmt2->execute();
$stmt2->close();

$_SESSION['mensaje_exito'] = "Paciente desactivado correctamente.";
header("Location: lista_pacientes.php");
exit();
?>
