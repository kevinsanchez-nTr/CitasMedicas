<?php
session_start();
require_once __DIR__ . '/../includes/conexion.php';

if (!isset($_SESSION['id_usuarios']) || ($_SESSION['rol_id'] ?? null) != 1) {
    header("Location: ../dashboard.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("UPDATE medicos SET activo = 0 WHERE id_medicos = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['mensaje_exito'] = "✅ Médico eliminado correctamente.";
    } else {
        $_SESSION['mensaje_error'] = "❌ Error al eliminar el médico.";
    }

    $stmt->close();
}

header("Location: lista_medicos.php");
exit();
