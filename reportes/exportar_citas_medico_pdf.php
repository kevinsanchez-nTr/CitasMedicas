<?php
require_once "../includes/conexion.php";
require_once "../libs/FPDF-master/fpdf.php";

$id_medico = $_GET['medico'] ?? 0;
if (!$id_medico) die("Médico inválido");

// Consulta
$sql = "
SELECT u1.nombre_completo AS paciente,
       u2.nombre_completo AS medico,
       c.fecha,
       c.motivo,
       e.descripcion AS estado,
       c.es_urgente,
       c.activo
FROM citas c
INNER JOIN pacientes p ON c.paciente_id = p.id_pacientes
INNER JOIN usuarios u1 ON p.usuario_id = u1.id_usuarios
INNER JOIN medicos m ON c.medico_id = m.id_medicos
INNER JOIN usuarios u2 ON m.usuario_id = u2.id_usuarios
INNER JOIN estado_cita e ON c.estado_id = e.id_estado_cita
WHERE m.id_medicos = $id_medico
ORDER BY c.fecha DESC
";

$result = $conn->query($sql);

// Crear PDF
$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();

// ======== BAJAR TODO EL ENCABEZADO ========
$Y_OFFSET = 20;  // ← Ajusta este valor si quieres bajarlo aún más

// === LOGO ===
$pdf->Image('../img/logo.jpeg', 10, 10 + $Y_OFFSET, 25);

// TÍTULO
$pdf->SetXY(0, 15 + $Y_OFFSET);
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 15, utf8_decode('REPORTE DE CITAS POR MÉDICO'), 0, 1, 'C');

// ESPACIO ANTES DE LA TABLA
$pdf->Ln(15);

// TABLA
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(200, 200, 200);

$headers = ["Paciente", "Medico", "Fecha", "Motivo", "Estado", "Urgente", "Activo"];
$widths  = [50, 40, 35, 70, 30, 20, 20];

foreach ($headers as $i => $h) {
    $pdf->Cell($widths[$i], 10, utf8_decode($h), 1, 0, 'C', true);
}
$pdf->Ln();

// Datos
$pdf->SetFont('Arial', '', 9);

while ($fila = $result->fetch_assoc()) {
    $pdf->Cell($widths[0], 8, utf8_decode($fila['paciente']), 1);
    $pdf->Cell($widths[1], 8, utf8_decode($fila['medico']), 1);
    $pdf->Cell($widths[2], 8, $fila['fecha'], 1);
    $pdf->Cell($widths[3], 8, utf8_decode($fila['motivo']), 1);
    $pdf->Cell($widths[4], 8, utf8_decode($fila['estado']), 1);

    $pdf->Cell($widths[5], 8, $fila['es_urgente'] ? "Sí" : "No", 1, 0, 'C');
    $pdf->Cell($widths[6], 8, $fila['activo'] ? "Activo" : "Inactivo", 1, 0, 'C');
    $pdf->Ln();
}

$pdf->Output();
?>
