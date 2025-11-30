<?php
session_start();
require_once __DIR__ . '/includes/conexion.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $correo = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($correo === '' || $password === '') {
        $error = "❌ Completa todos los campos.";
    } else {

        // Buscar usuario
        $stmt = $conn->prepare("
            SELECT id_usuarios, nombre_completo, password, rol_id, activo
            FROM usuarios
            WHERE correo = ?
        ");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuario = $result->fetch_assoc();
        $stmt->close();

        if (!$usuario) {
            $error = "❌ Correo no encontrado.";
        } 
        elseif ($usuario['activo'] != 1) {
            $error = "❌ Este usuario está inactivo.";
        }
        elseif (!password_verify($password, $usuario['password'])) {
            $error = "❌ Contraseña incorrecta.";
        } 
        else {
            // ----------------------------------------
            // ✅ LOGIN COMPLETO SIN MFA
            // ----------------------------------------
            $_SESSION['id_usuarios'] = $usuario['id_usuarios'];
            $_SESSION['nombre'] = $usuario['nombre_completo'];
            $_SESSION['rol_id'] = $usuario['rol_id'];

            // Redirecciones según rol
            switch ($usuario['rol_id']) {
                case 1:  // Admin
                    header("Location: dashboard.php");
                    break;

                case 2:  // Médico
                    header("Location: medicos/lista_medicos.php");
                    break;

                case 3:  // Paciente
                    header("Location: paciente/lista_pacientes.php");
                    break;

                default:
                    header("Location: dashboard.php");
            }
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login Simple – Citas Médicas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow-lg mx-auto" style="max-width: 450px;">
        <div class="card-header bg-success text-white text-center">
            <h4>🔐 Login Simple (SIN MFA)</h4>
        </div>
        <div class="card-body">

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">Correo</label>
                    <input type="email" name="correo" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-success w-100">Entrar sin verificación</button>

                <p class="text-center mt-3 small text-muted">
                    Este login es solo para pruebas en desarrollo.
                </p>

            </form>
        </div>
    </div>
</div>

</body>
</html>
