<?php
session_start();
require_once __DIR__ . '/../includes/conexion.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['mensaje_error'] = "ID de paciente inválido.";
    header("Location: lista_pacientes.php");
    exit();
}

$id_paciente = intval($_GET['id']);

// Obtener usuario y estado actual del PACIENTE
$sql = "SELECT usuario_id, activo FROM pacientes WHERE id_pacientes = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_paciente);
$stmt->execute();
$pac = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$pac) {
    $_SESSION['mensaje_error'] = "Paciente no encontrado.";
    header("Location: lista_pacientes.php");
    exit();
}

$usuario_id    = $pac['usuario_id'];
$estado_actual = (int)$pac['activo'];
$nuevo_estado  = ($estado_actual === 1) ? 0 : 1;

// Actualizar PACIENTE
$stmt1 = $conn->prepare("UPDATE pacientes SET activo = ? WHERE id_pacientes = ?");
$stmt1->bind_param("ii", $nuevo_estado, $id_paciente);
$stmt1->execute();
$stmt1->close();

// Actualizar USUARIO
$stmt2 = $conn->prepare("UPDATE usuarios SET activo = ? WHERE id_usuarios = ?");
$stmt2->bind_param("ii", $nuevo_estado, $usuario_id);
$stmt2->execute();
$stmt2->close();

$_SESSION['mensaje_exito'] = ($nuevo_estado === 1)
    ? "Paciente ACTIVADO correctamente."
    : "Paciente DESACTIVADO correctamente.";

header("Location: lista_pacientes.php");
exit();
?>
