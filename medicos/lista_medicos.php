<?php
session_start();
require_once __DIR__ . '/../includes/conexion.php';

// Solo administrador
if (!isset($_SESSION['id_usuarios']) || ($_SESSION['rol_id'] ?? null) != 1) {
    header("Location: ../dashboard.php");
    exit();
}

$sql = "
SELECT 
    m.id_medicos,
    u.id_usuarios,
    u.nombre_completo,
    u.correo,
    u.telefono,
    u.direccion,
    u.activo AS estado_usuario,
    COALESCE(e.nombre, 'Sin especialidad') AS especialidad
FROM medicos m
INNER JOIN usuarios u ON m.usuario_id = u.id_usuarios
LEFT JOIN especialidades e ON m.especialidad_id = e.id_especialidades
WHERE u.rol_id = 2
ORDER BY u.nombre_completo ASC;
";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Médicos</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilos.css">

    <style>
        .estado-activo {background:#28a745;color:white;padding:4px 10px;border-radius:5px;font-size:12px;}
        .estado-inactivo {background:#dc3545;color:white;padding:4px 10px;border-radius:5px;font-size:12px;}
    </style>
</head>

<body>

<?php include "../includes/menu.php"; ?>

<div class="content p-4">

    <h3 class="text-primary"><i class="bi bi-person-badge"></i> Lista de Médicos</h3>

    <a href="registro_medico.php" class="btn btn-success mb-3">
        <i class="bi bi-plus-circle"></i> Registrar Médico
    </a>

    <table class="table table-bordered table-hover">
        <thead class="table-primary">
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Teléfono</th>
                <th>Dirección</th>
                <th>Especialidad</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php $i = 1; while ($m = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($m['nombre_completo']) ?></td>
                    <td><?= htmlspecialchars($m['correo']) ?></td>
                    <td><?= htmlspecialchars($m['telefono']) ?></td>
                    <td><?= htmlspecialchars($m['direccion']) ?></td>
                    <td><?= htmlspecialchars($m['especialidad']) ?></td>

                    <td>
                        <?php if ($m['estado_usuario'] == 1): ?>
                            <span class="estado-activo">Activo</span>
                        <?php else: ?>
                            <span class="estado-inactivo">Inactivo</span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <a href="ver_medico.php?id=<?= $m['id_medicos'] ?>" class="btn btn-info btn-sm">
                            <i class="bi bi-eye"></i> Ver
                        </a>

                        <a href="editar_medico.php?id=<?= $m['id_medicos'] ?>" class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil-square"></i> Editar
                        </a>

                        <?php if ($m['estado_usuario'] == 1): ?>
                            <a href="activar_desactivar_medico.php?id=<?= $m['id_medicos'] ?>"
                               onclick="return confirm('¿Desactivar médico?')"
                               class="btn btn-danger btn-sm"><i class="bi bi-x-circle"></i> Desactivar</a>
                        <?php else: ?>
                            <a href="activar_desactivar_medico.php?id=<?= $m['id_medicos'] ?>"
                               onclick="return confirm('¿Activar médico?')"
                               class="btn btn-success btn-sm"><i class="bi bi-check-circle"></i> Activar</a>
                        <?php endif; ?>
                    </td>

                </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>

</div>

</body>
</html>
