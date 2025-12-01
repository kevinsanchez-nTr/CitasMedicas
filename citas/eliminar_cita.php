<?php
session_start();
require_once "../includes/conexion.php";

$id = $_GET["id"] ?? 0;

if ($id == 0) {
    $_SESSION["mensaje_error"] = "ID inválido.";
    header("Location: lista_citas.php");
    exit;
}

// Desactivar cita
$sql = "UPDATE citas SET activo = 0 WHERE id_citas = $id";

if ($conn->query($sql)) {
    $_SESSION["mensaje_exito"] = "La cita fue desactivada correctamente.";
} else {
    $_SESSION["mensaje_error"] = "Error al desactivar la cita.";
}

header("Location: lista_citas.php");
exit;
