<?php
require_once "../includes/conexion.php";
require_once __DIR__ . "/../vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

$id_medico = $_GET['medico'] ?? 0;
if (!$id_medico) die("Médico inválido");

// Consulta de datos
$sql = "
SELECT 
    u1.nombre_completo AS paciente,
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

// Crear Excel
$document = new Spreadsheet();
$hoja = $document->getActiveSheet();
$hoja->setTitle("Citas Médico");

// =================== AGREGAR LOGO ===================
$logo = new Drawing();
$logo->setName('Logo');
$logo->setDescription('Logo Cita Médica');
$logo->setPath('../img/logo.jpeg'); // Ruta del logo
$logo->setHeight(70); // Tamaño
$logo->setCoordinates('A1'); // Celda donde inicia
$logo->setOffsetX(10);
$logo->setOffsetY(5);
$logo->setWorksheet($hoja);

// =================== TÍTULO ===================
$hoja->setCellValue("A4", "REPORTE DE CITAS POR MÉDICO");
$hoja->mergeCells("A4:G4"); // Abarca toda la fila
$hoja->getStyle("A4")->getFont()->setBold(true)->setSize(18);
$hoja->getStyle("A4")->getAlignment()->setHorizontal("center");
$hoja->getRowDimension(4)->setRowHeight(30);

// =================== ENCABEZADOS ===================
$headers = ["Paciente", "Médico", "Fecha", "Motivo", "Estado", "Urgente", "Activo"];

$col = "A";
foreach ($headers as $h) {
    $hoja->setCellValue($col . "6", $h);
    $col++;
}

// Estilo encabezados
$hoja->getStyle("A6:G6")->getFont()->setBold(true);
$hoja->getStyle("A6:G6")->getFill()->setFillType("solid")->getStartColor()->setRGB("D0D0D0");

// =================== ESCRIBIR DATOS ===================
$fila = 7;

while ($row = $result->fetch_assoc()) {
    $hoja->setCellValue("A{$fila}", $row['paciente']);
    $hoja->setCellValue("B{$fila}", $row['medico']);
    $hoja->setCellValue("C{$fila}", $row['fecha']);
    $hoja->setCellValue("D{$fila}", $row['motivo']);
    $hoja->setCellValue("E{$fila}", $row['estado']);
    $hoja->setCellValue("F{$fila}", $row['es_urgente'] ? "Sí" : "No");
    $hoja->setCellValue("G{$fila}", $row['activo'] ? "Activo" : "Inactivo");
    $fila++;
}

// =================== AUTOAJUSTE DE COLUMNAS ===================
foreach (range('A', 'G') as $columna) {
    $hoja->getColumnDimension($columna)->setAutoSize(true);
}

// =================== DESCARGAR ARCHIVO ===================
$writer = new Xlsx($document);
$filename = "reporte_citas_medico.xlsx";

header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Cache-Control: max-age=0");

$writer->save("php://output");
exit;
?>
