<?php
session_start();
require_once __DIR__ . '/../includes/conexion.php';

// Solo admin
if (!isset($_SESSION['id_usuarios']) || ($_SESSION['rol_id'] ?? null) != 1) {
    header("Location: ../dashboard.php");
    exit();
}

// Validar id del paciente
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['mensaje_error'] = "❌ ID de paciente inválido.";
    header("Location: lista_pacientes.php");
    exit();
}

$id_paciente = intval($_GET['id']);

// Obtener datos del paciente
$sql = "
    SELECT 
        p.id_pacientes,
        u.nombre_completo,
        u.correo,
        u.telefono,
        u.direccion,
        u.fecha_registro,
        p.fecha_nacimiento,
        p.grupo_sanguineo
    FROM pacientes p
    INNER JOIN usuarios u ON p.usuario_id = u.id_usuarios
    WHERE p.id_pacientes = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_paciente);
$stmt->execute();
$paciente = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$paciente) {
    $_SESSION['mensaje_error'] = "❌ El paciente no existe.";
    header("Location: lista_pacientes.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Datos del Paciente</title>

    <!-- LINKS QUE PEDISTE -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilos.css">

    <style>
        :root {
            --azul-menu: #00B8F0;
            --azul-oscuro: #008ABD;
        }

        .card-header-azul {
            background-color: var(--azul-menu);
            color: white;
        }

        .titulo-azul {
            color: var(--azul-menu);
        }

        .btn-azul {
            background-color: var(--azul-menu);
            color: white;
        }

        .btn-azul:hover {
            background-color: var(--azul-oscuro);
            color: white;
        }
    </style>
</head>

<body>

<?php include "../includes/menu.php"; ?>

<div class="content p-4">
    <div class="container">

        <!-- CARD PRINCIPAL -->
        <div class="card shadow-lg mb-4" style="border-color: var(--azul-menu);">
            <div class="card-header card-header-azul">
                <h4 class="mb-0">🧑‍⚕️ Información del Paciente</h4>
            </div>

            <div class="card-body">

                <h3 class="titulo-azul"><?= htmlspecialchars($paciente['nombre_completo']) ?></h3>

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Correo:</label>
                        <input class="form-control" value="<?= $paciente['correo'] ?>" disabled>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Teléfono:</label>
                        <input class="form-control" value="<?= $paciente['telefono'] ?>" disabled>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">Dirección:</label>
                        <input class="form-control" value="<?= $paciente['direccion'] ?>" disabled>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Fecha de nacimiento:</label>
                        <input class="form-control" value="<?= $paciente['fecha_nacimiento'] ?>" disabled>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Grupo sanguíneo:</label>
                        <input class="form-control" value="<?= $paciente['grupo_sanguineo'] ?>" disabled>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Fecha de registro:</label>
                        <input class="form-control" value="<?= $paciente['fecha_registro'] ?>" disabled>
                    </div>

                </div>

                <!-- BOTONES -->
                <div class="d-flex flex-wrap gap-2 mt-3">

                    <a href="lista_pacientes.php" class="btn btn-secondary">
                        ⬅️ Volver
                    </a>

                    <a href="editar_paciente.php?id=<?= $id_paciente ?>" class="btn btn-azul">
                        ✏️ Editar datos
                    </a>

                    <a href="ver_citas_paciente.php?id=<?= $id_paciente ?>" class="btn btn-outline-primary">
                        📅 Citas del paciente
                    </a>

                    <a href="historial_paciente.php?id=<?= $id_paciente ?>" class="btn btn-outline-dark">
                        📚 Historial clínico
                    </a>

                    <a href="recetas_paciente.php?id=<?= $id_paciente ?>" class="btn btn-outline-success">
                        💊 Recetas recibidas
                    </a>

                    <a href="documentos_paciente.php?id=<?= $id_paciente ?>" class="btn btn-outline-info">
                        📄 Documentos médicos
                    </a>

                </div>

            </div>
        </div>

    </div>
</div>

</body>
</html>
