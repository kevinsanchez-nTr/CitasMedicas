<?php
session_start();
require_once "../includes/conexion.php";
require_once "../libs/FPDF-master/fpdf.php";


/* === mismos filtros que lista_citas === */
$paciente   = $_GET["paciente"]   ?? "";
$medico     = $_GET["medico"]     ?? "";
$estado     = $_GET["estado"]     ?? "";
$urgente    = $_GET["urgente"]    ?? "";
$activo     = $_GET["activo"]     ?? "";
$desde      = $_GET["desde"]      ?? "";
$hasta      = $_GET["hasta"]      ?? "";

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

$res = $conn->query($sql);

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial','B',14);
        $this->Cell(0,10,utf8_decode('Reporte de Citas Médicas'),0,1,'C');
        $this->Ln(3);
        $this->SetFont('Arial','B',9);
        $this->Cell(10,7,'ID',1,0,'C');
        $this->Cell(55,7,'Paciente',1,0,'C');
        $this->Cell(55,7,'Medico',1,0,'C');
        $this->Cell(30,7,'Fecha',1,0,'C');
        $this->Cell(60,7,'Motivo',1,0,'C');
        $this->Cell(20,7,'Estado',1,0,'C');
        $this->Cell(15,7,'Urg',1,0,'C');
        $this->Cell(15,7,'Act',1,1,'C');
    }
}

$pdf = new PDF('L','mm','A4');
$pdf->AddPage();
$pdf->SetFont('Arial','',8);

if ($res) {
    while($c = $res->fetch_assoc()) {
        $pdf->Cell(10,6,$c['id_citas'],1,0,'C');
        $pdf->Cell(55,6,utf8_decode(substr($c['paciente'],0,30)),1,0,'L');
        $pdf->Cell(55,6,utf8_decode(substr($c['medico'],0,30)),1,0,'L');
        $pdf->Cell(30,6,$c['fecha'],1,0,'C');
        $pdf->Cell(60,6,utf8_decode(substr($c['motivo'],0,40)),1,0,'L');
        $pdf->Cell(20,6,utf8_decode(substr($c['estado'],0,12)),1,0,'C');
        $pdf->Cell(15,6,($c['es_urgente'] ? 'Si' : 'No'),1,0,'C');
        $pdf->Cell(15,6,($c['activo'] ? 'Act' : 'Ina'),1,1,'C');
    }
}

$pdf->Output('D','citas_'.date('Ymd_His').'.pdf');
