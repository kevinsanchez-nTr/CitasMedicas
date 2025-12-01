<?php
session_start();
require_once "../includes/conexion.php";

/* ========= CAPTURA DE FILTROS ========= */
$paciente   = $_GET["paciente"]   ?? "";
$medico     = $_GET["medico"]     ?? "";
$estado     = $_GET["estado"]     ?? "";
$urgente    = $_GET["urgente"]    ?? "";
$activo     = $_GET["activo"]     ?? "";
$desde      = $_GET["desde"]      ?? "";
$hasta      = $_GET["hasta"]      ?? "";
$pagina     = max(1, intval($_GET["pagina"] ?? 1));
$por_pagina = 10;

/* ========= GENERAR CONSULTA DINÁMICA ========= */
$filtro = " WHERE 1 ";

if ($paciente !== "") $filtro .= " AND u1.nombre_completo LIKE '%$paciente%'";
if ($medico   !== "") $filtro .= " AND u2.nombre_completo LIKE '%$medico%'";
if ($estado   !== "") $filtro .= " AND c.estado_id = $estado";
if ($urgente  !== "") $filtro .= " AND c.es_urgente = $urgente";
if ($activo   !== "") $filtro .= " AND c.activo = $activo";
if ($desde    !== "") $filtro .= " AND DATE(c.fecha) >= '$desde'";
if ($hasta    !== "") $filtro .= " AND DATE(c.fecha) <= '$hasta'";

/* ========= TOTAL PARA PAGINACIÓN ========= */
$sql_count = "
SELECT COUNT(*) AS total
FROM citas c
INNER JOIN pacientes p ON c.paciente_id = p.id_pacientes
INNER JOIN usuarios u1 ON p.usuario_id = u1.id_usuarios
INNER JOIN medicos m ON c.medico_id = m.id_medicos
INNER JOIN usuarios u2 ON m.usuario_id = u2.id_usuarios
INNER JOIN estado_cita e ON c.estado_id = e.id_estado_cita
$filtro
";
$total_reg = $conn->query($sql_count)->fetch_assoc()['total'] ?? 0;
$total_pag = max(1, ceil($total_reg / $por_pagina));
if ($pagina > $total_pag) $pagina = $total_pag;
$offset = ($pagina - 1) * $por_pagina;

/* ========= CONSULTA PRINCIPAL ========= */
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
LIMIT $offset, $por_pagina
";
$result = $conn->query($sql);

/* ========= URLs EXPORTAR (manteniendo filtros) ========= */
$params = $_GET;
unset($params['pagina']);
$queryString = http_build_query($params);
$qs = $queryString ? "?$queryString" : "";
$pdf_url   = "exportar_citas_pdf.php"   . $qs;
$excel_url = "exportar_citas_excel.php" . $qs;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Lista de Citas</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="../css/estilos.css">

<style>
.filter-box {
    background: white;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
</style>

</head>
<body>

<?php include "../includes/menu.php"; ?>

<div class="content container">

    <h3 class="mb-3"><i class="bi bi-journal-text"></i> Lista de Citas Médicas</h3>

    <!-- Botones superiores -->
    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="registro_cita.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Registrar Cita
        </a>
        <a href="<?= $pdf_url ?>" class="btn btn-danger">
            <i class="bi bi-filetype-pdf"></i> Exportar PDF
        </a>
        <a href="<?= $excel_url ?>" class="btn btn-success">
            <i class="bi bi-filetype-xls"></i> Exportar Excel
        </a>
    </div>

    <!-- alertas -->
    <?php if (isset($_SESSION['mensaje_exito'])): ?>
        <div class="alert alert-success"><?= $_SESSION['mensaje_exito']; unset($_SESSION['mensaje_exito']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['mensaje_error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['mensaje_error']; unset($_SESSION['mensaje_error']); ?></div>
    <?php endif; ?>

    <!-- ============= FILTROS AVANZADOS ============= -->
    <div class="filter-box">

        <form method="GET" class="row g-3" id="formFiltros">

            <div class="col-md-3 col-sm-6">
                <label>Paciente</label>
                <input type="text" name="paciente" value="<?= htmlspecialchars($paciente) ?>" class="form-control">
            </div>

            <div class="col-md-3 col-sm-6">
                <label>Médico</label>
                <input type="text" name="medico" value="<?= htmlspecialchars($medico) ?>" class="form-control">
            </div>

            <div class="col-md-2 col-sm-6">
                <label>Estado</label>
                <select name="estado" class="form-select">
                    <option value="">Todos</option>
                    <?php
                    $es = $conn->query("SELECT * FROM estado_cita");
                    while($e = $es->fetch_assoc()):
                    ?>
                        <option value="<?= $e['id_estado_cita'] ?>" 
                            <?= $estado == $e['id_estado_cita'] ? 'selected':'' ?>>
                            <?= $e['descripcion'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="col-md-2 col-sm-6">
                <label>Urgente</label>
                <select name="urgente" class="form-select">
                    <option value="">Todos</option>
                    <option value="1" <?= $urgente==="1"?"selected":"" ?>>Sí</option>
                    <option value="0" <?= $urgente==="0"?"selected":"" ?>>No</option>
                </select>
            </div>

            <div class="col-md-2 col-sm-6">
                <label>Activas</label>
                <select name="activo" class="form-select">
                    <option value="">Todas</option>
                    <option value="1" <?= $activo==="1"?"selected":"" ?>>Activas</option>
                    <option value="0" <?= $activo==="0"?"selected":"" ?>>Inactivas</option>
                </select>
            </div>

            <div class="col-md-2 col-sm-6">
                <label>Desde</label>
                <input type="date" name="desde" value="<?= $desde ?>" class="form-control">
            </div>

            <div class="col-md-2 col-sm-6">
                <label>Hasta</label>
                <input type="date" name="hasta" value="<?= $hasta ?>" class="form-control">
            </div>

            <div class="col-12 text-end">
                <button class="btn btn-primary"><i class="bi bi-search"></i> Buscar</button>
                <a href="lista_citas.php" class="btn btn-secondary">Limpiar</a>
            </div>

        </form>
    </div>

    <!-- ================= TABLA ================= -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Paciente</th>
                    <th>Médico</th>
                    <th>Fecha</th>
                    <th>Motivo</th>
                    <th>Estado</th>
                    <th>Urgente</th>
                    <th>Activo</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
            <?php 
            if ($result && $result->num_rows > 0):
                $i = $offset + 1;
                while($c = $result->fetch_assoc()):
            ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($c['paciente']) ?></td>
                    <td><?= htmlspecialchars($c['medico']) ?></td>
                    <td><?= $c['fecha'] ?></td>
                    <td><?= htmlspecialchars($c['motivo']) ?></td>

                    <td>
                        <span class="badge bg-info text-dark"><?= htmlspecialchars($c['estado']) ?></span>
                    </td>

                    <td><?= $c["es_urgente"] ? "<span class='badge bg-danger'>Sí</span>" : "<span class='badge bg-secondary'>No</span>" ?></td>

                    <td>
                        <button type="button"
                                class="btn btn-sm btn-toggle-cita <?= $c['activo'] ? "btn-success" : "btn-danger" ?>"
                                data-id="<?= $c['id_citas'] ?>">
                            <?= $c['activo'] ? "Activa" : "Inactiva" ?>
                        </button>
                    </td>

                    <td>
                        <a href="ver_cita.php?id=<?= $c['id_citas'] ?>" class="btn btn-info btn-sm">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="editar_cita.php?id=<?= $c['id_citas'] ?>" class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                    </td>
                </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="9" class="text-center text-muted">No se encontraron resultados.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- PAGINACIÓN -->
    <?php if ($total_pag > 1): ?>
        <nav>
            <ul class="pagination justify-content-center">
                <?php
                $params_pag = $_GET;
                for($p=1; $p <= $total_pag; $p++):
                    $params_pag['pagina'] = $p;
                    $link = "?" . http_build_query($params_pag);
                ?>
                    <li class="page-item <?= $p==$pagina ? 'active' : '' ?>">
                        <a class="page-link" href="<?= $link ?>"><?= $p ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// ====== Toggle estado (AJAX) ======
document.querySelectorAll('.btn-toggle-cita').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        if (!id) return;

        fetch('toggle_cita_ajax.php?id=' + id)
            .then(res => res.json())
            .then(data => {
                if (!data.ok) {
                    alert('No se pudo actualizar el estado');
                    return;
                }
                if (data.activo == 1) {
                    btn.classList.remove('btn-danger');
                    btn.classList.add('btn-success');
                    btn.textContent = 'Activa';
                } else {
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-danger');
                    btn.textContent = 'Inactiva';
                }
            })
            .catch(() => alert('Error de conexión con el servidor'));
    });
});

// ====== “Búsqueda en tiempo real” para paciente y médico (auto submit) ======
const formFiltros = document.getElementById('formFiltros');
['paciente','medico'].forEach(name => {
    const input = formFiltros.elements[name];
    if (!input) return;
    let timeout;
    input.addEventListener('keyup', () => {
        clearTimeout(timeout);
        timeout = setTimeout(() => formFiltros.submit(), 500);
    });
});

// También se puede auto–submit al cambiar selects:
['estado','urgente','activo','desde','hasta'].forEach(name => {
    const el = formFiltros.elements[name];
    if (!el) return;
    el.addEventListener('change', () => formFiltros.submit());
});
</script>

</body>
</html>
