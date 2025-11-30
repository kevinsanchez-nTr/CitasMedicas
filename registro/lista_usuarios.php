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
    u.id_usuarios,
    u.nombre_completo,
    u.correo,
    u.telefono,
    u.rol_id,
    u.activo AS estado_usuario,
    r.nombre AS rol_nombre
FROM usuarios u
INNER JOIN roles r ON u.rol_id = r.id_roles
ORDER BY u.nombre_completo ASC
";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios</title>

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
    <h3 class="text-primary"><i class="bi bi-people"></i> Gestión de Usuarios</h3>

    <a href="registro.php" class="btn btn-primary mb-3"><i class="bi bi-person-plus"></i> Registrar Usuario</a>

    <table class="table table-bordered table-hover">
        <thead class="table-primary">
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Teléfono</th>
                <th>Rol</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php $i = 1; while ($u = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($u['nombre_completo']) ?></td>
                    <td><?= htmlspecialchars($u['correo']) ?></td>
                    <td><?= htmlspecialchars($u['telefono']) ?></td>

                    <td><span class="badge bg-info text-dark"><?= $u['rol_nombre'] ?></span></td>

                    <td>
                        <?php if ($u['estado_usuario'] == 1): ?>
                            <span class="estado-activo">Activo</span>
                        <?php else: ?>
                            <span class="estado-inactivo">Inactivo</span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <a href="editar_usuario.php?id=<?= $u['id_usuarios'] ?>" class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil-square"></i> Editar
                        </a>

                        <?php if ($u['estado_usuario'] == 1): ?>
                            <a href="activar_desactivar_usuario.php?id=<?= $u['id_usuarios'] ?>" 
                               onclick="return confirm('¿Desactivar usuario?')"
                               class="btn btn-danger btn-sm"><i class="bi bi-x-circle"></i> Desactivar</a>
                        <?php else: ?>
                            <a href="activar_desactivar_usuario.php?id=<?= $u['id_usuarios'] ?>" 
                               onclick="return confirm('¿Activar usuario?')"
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
