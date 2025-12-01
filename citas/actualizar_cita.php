<?php
session_start();
require_once "../includes/conexion.php";

$id = $_POST["id"];
$pac = $_POST["paciente_id"];
$med = $_POST["medico_id"];
$fecha = $_POST["fecha"];
$estado = $_POST["estado_id"];
$motivo = $_POST["motivo"];
$urgente = $_POST["es_urgente"];

// Validación: evitar conflicto de horario con el mismo médico
$sql_valida = "
SELECT * FROM citas
WHERE medico_id = $med
AND fecha = '$fecha'
AND id_citas != $id
AND activo = 1
";

$existe = $conn->query($sql_valida);

if ($existe->num_rows > 0) {
    $_SESSION["mensaje_error"] = "El médico ya tiene una cita en ese mismo horario.";
    header("Location: editar_cita.php?id=$id");
    exit;
}

// Actualizar cita
$sql = "
UPDATE citas SET 
    paciente_id = $pac,
    medico_id = $med,
    fecha = '$fecha',
    estado_id = $estado,
    motivo = '$motivo',
    es_urgente = $urgente
WHERE id_citas = $id
";

if ($conn->query($sql)) {
    $_SESSION["mensaje_exito"] = "Cita actualizada correctamente.";
} else {
    $_SESSION["mensaje_error"] = "Error al actualizar la cita.";
}

header("Location: lista_citas.php");
exit;
