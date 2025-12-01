<?php
session_start();
require_once "../includes/conexion.php";

$id = $_GET["id"] ?? 0;

$cita = $conn->query("SELECT activo FROM citas WHERE id_citas = $id")->fetch_assoc();

if (!$cita) {
    $_SESSION["mensaje_error"] = "Cita no encontrada.";
    header("Location: lista_citas.php");
    exit;
}

$nuevo_estado = $cita["activo"] ? 0 : 1;

$conn->query("UPDATE citas SET activo = $nuevo_estado WHERE id_citas = $id");

$_SESSION["mensaje_exito"] = $nuevo_estado ? 
    "Cita activada correctamente." :
    "Cita desactivada correctamente.";

header("Location: lista_citas.php");
exit;
