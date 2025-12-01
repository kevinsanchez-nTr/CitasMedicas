<?php
session_start();
require_once "../includes/conexion.php";

$paciente_id = $_POST['paciente_id'];
$medico_id   = $_POST['medico_id'];
$fecha       = $_POST['fecha'];
$motivo      = $_POST['motivo'];
$estado_id   = $_POST['estado_id'];
$urgente     = $_POST['es_urgente'];

$horaNueva = date("Y-m-d H:i:s", strtotime($fecha));

/* --- VALIDAR QUE EL MÉDICO NO TENGA OTRA CITA A LA MISMA HORA --- */
$validar = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM citas
    WHERE medico_id = ?
      AND fecha = ?
      AND activo = 1
");
$validar->bind_param("is", $medico_id, $horaNueva);
$validar->execute();
$res = $validar->get_result()->fetch_assoc();

if ($res['total'] > 0) {
    $_SESSION['mensaje_error'] = "❌ El médico ya tiene una cita registrada en esa hora.";
    header("Location: registro_cita.php");
    exit();
}

/* --- INSERTAR CITA --- */
$sql = $conn->prepare("
    INSERT INTO citas (paciente_id, medico_id, fecha, motivo, estado_id, es_urgente, activo)
    VALUES (?, ?, ?, ?, ?, ?, 1)
");

$sql->bind_param("iissii", $paciente_id, $medico_id, $horaNueva, $motivo, $estado_id, $urgente);

if ($sql->execute()) {
    $_SESSION['mensaje_exito'] = "✅ Cita registrada correctamente.";
} else {
    $_SESSION['mensaje_error'] = "❌ Error al registrar cita.";
}

header("Location: lista_citas.php");
exit();
