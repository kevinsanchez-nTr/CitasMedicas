<?php
session_start();
header('Content-Type: application/json');
require_once "../includes/conexion.php";

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['ok' => false, 'msg' => 'ID inválido']);
    exit;
}

$res = $conn->query("SELECT activo FROM citas WHERE id_citas = $id");
if (!$res || $res->num_rows == 0) {
    echo json_encode(['ok' => false, 'msg' => 'Cita no encontrada']);
    exit;
}

$actual = $res->fetch_assoc()['activo'];
$nuevo  = $actual ? 0 : 1;

if ($conn->query("UPDATE citas SET activo = $nuevo WHERE id_citas = $id")) {
    echo json_encode(['ok' => true, 'activo' => $nuevo]);
} else {
    echo json_encode(['ok' => false, 'msg' => 'Error al actualizar']);
}
