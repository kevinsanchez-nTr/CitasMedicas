<?php
session_start();
require_once "../includes/conexion.php";

// Cargar PhpSpreadsheet
require_once __DIR__ . "/../vendor/autoload.php";



use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

/* === FILTERS === */
$paciente = $_GET["paciente"] ?? "";
$medico   = $_GET["medico"] ?? "";
$estado   = $_GET["estado"] ?? "";
$urgente  = $_GET["urgente"] ?? "";
$activo   = $_GET["activo"] ?? "";
$desde    = $_GET["desde"] ?? "";
$hasta    = $_GET["hasta"] ?? "";

$filtro = " WHERE 1 ";
if ($paciente !== "") $filtro .= " AND u1.nombre_completo LIKE '%$paciente%'";
if ($medico   !== "") $filtro .= " AND u2.nombre_completo LIKE '%$medico%'";
if ($estado   !== "") $filtro .= " AND c.estado_id = $estado";
if ($urgente  !== "") $filtro .= " AND c.es_urgente = $urgente";
if ($activo   !== "") $filtro .= " AND c.activo = $activo";
if ($desde    !== "") $filtro .= " AND DATE(c.fecha) >= '$desde'";
if ($hasta    !== "") $filtro .= " AND DATE(c.fecha) <= '$hasta'";

$sql = "
SELECT c.id_citas, c.fecha, c.motivo, c.activo, c.es_urgente,
       u1.nombre_completo AS paciente,
       u2.nombre_completo AS medico,
       e.descripcion AS estado
FROM citas c
INNER JOIN pacientes p ON c.paciente_id = p.id_pacientes
INNER JOIN usuarios u1 ON p.usuario_id = u1.id_usuarios
INNER JOIN medicos m ON c.medico_id = m.id_medicos
INNER JOIN usuarios u2 ON m.usuario_id = u2.id_usuarios
INNER JOIN estado_cita e ON c.estado_id = e.id_estado_cita
$filtro
ORDER BY c.fecha DESC
";

$result = $conn->query($sql);

/* ================================
   CREAR EL EXCEL
================================ */

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// 🌟 Título
$sheet->mergeCells("A1:G1");
$sheet->setCellValue("A1", "Reporte de Citas Médicas");
$sheet->getStyle("A1")->getFont()->setSize(16)->setBold(true);
$sheet->getStyle("A1")->getAlignment()->setHorizontal("center");

// 🌟 Encabezados
$encabezados = ["#", "Paciente", "Médico", "Fecha", "Urgente", "Estado", "Motivo"];

$col = "A";
foreach ($encabezados as $titulo) {
    $sheet->setCellValue($col . "3", $titulo);
    $sheet->getStyle($col . "3")->applyFromArray([
        "font" => ["bold" => true, "color" => ["rgb" => "FFFFFF"]],
        "fill" => ["fillType" => Fill::FILL_SOLID, "color" => ["rgb" => "003366"]],
        "alignment" => ["horizontal" => "center"],
        "borders" => ["allBorders" => ["borderStyle" => Border::BORDER_THIN]]
    ]);
    $col++;
}

// 🌟 Data
$row = 4;
$i = 1;

while ($c = $result->fetch_assoc()) {
    $sheet->setCellValue("A$row", $i++);
    $sheet->setCellValue("B$row", $c["paciente"]);
    $sheet->setCellValue("C$row", $c["medico"]);
    $sheet->setCellValue("D$row", $c["fecha"]);
    $sheet->setCellValue("E$row", $c["es_urgente"] ? "Sí" : "No");
    $sheet->setCellValue("F$row", $c["estado"]);
    $sheet->setCellValue("G$row", $c["motivo"]);

    // Dar borde a toda la fila
    $sheet->getStyle("A$row:G$row")->applyFromArray([
        "borders" => ["allBorders" => ["borderStyle" => Border::BORDER_THIN]]
    ]);

    $row++;
}

// Autoajustar columnas
foreach (range('A', 'G') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Descargar archivo
$filename = "citas_medicas.xlsx";

header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment; filename=\"$filename\"");

$writer = new Xlsx($spreadsheet);
$writer->save("php://output");
exit;

?>
