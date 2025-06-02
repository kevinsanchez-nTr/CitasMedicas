<?php
session_start();
require_once "includes/conexion.php";
require_once "enviar_token.php";

if (isset($_SESSION['id_usuarios'])) {
  header("Location: dashboard.php");
  exit();
}

if (isset($_POST['login'])) {
  $correo = $_POST['correo'];
  $password = $_POST['password'];

  $sql = "SELECT * FROM usuarios WHERE correo = ? AND activo = 1 LIMIT 1";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $correo);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($usuario = $result->fetch_assoc()) {
    if (password_verify($password, $usuario['password'])) {
      $_SESSION['id_usuarios_prelogin'] = $usuario['id_usuarios'];
      $_SESSION['nombre_prelogin'] = $usuario['nombre_completo'];
      $_SESSION['rol_id_prelogin'] = $usuario['rol_id'];


      $token = rand(100000, 999999);

      $insert = $conn->prepare("INSERT INTO multifactor_tokens (usuario_id, token) VALUES (?, ?)");
      $insert->bind_param("is", $usuario['id_usuarios'], $token);
      $insert->execute();

      // Dentro del bloque PHP de login...
if (enviarCodigo($usuario['correo'], $token)) {
  $_SESSION['mensaje_exito'] = "✅ Código enviado al correo.";
  header("Location: verificar_token.php");
  exit();
} else {
  $error = "Error al enviar el código.";
}

    } else {
      $error = "Contraseña incorrecta.";
    }
  } else {
    $error = "Correo no encontrado o usuario inactivo.";
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Login Médico</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    @import url("https://fonts.googleapis.com/css?family=Montserrat:700");

    body {
  background: #0b0583;
  margin: 0;
  min-height: 100vh;
  font-family: 'Segoe UI', sans-serif;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  position: relative;
}

h2, h3 {
  font-family: 'Segoe UI', sans-serif;
  letter-spacing: 0.5px;
}

    .login-container {
      background: white;
      border-radius: 15px;
      box-shadow: 0 0 30px rgba(0, 0, 0, 0.15);
      max-width: 900px;
      width: 100%;
      display: flex;
      overflow: hidden;
      position: relative;
      z-index: 1;
      
    }

    .login-image {
      background: url('https://mir-s3-cdn-cf.behance.net/project_modules/disp/8b1ff273013043.5bfbd9beefa90.gif') no-repeat center;
      background-size: 100%;
      flex: 1;
      min-height: 400px;
    }

    .login-form {
      flex: 1;
      padding: 2rem;
    }

    .btn-animated {
      position: relative;
      overflow: hidden;
      transition: 0.4s;
    }

    .btn-animated::after {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: rgba(255, 255, 255, 0.3);
      transform: skewX(-45deg);
      transition: left 0.7s ease;
    }

    .btn-animated:hover::after {
      left: 200%;
    }

    .toggle-password {
      cursor: pointer;
      position: absolute;
      right: 10px;
      top: 10px;
      color: #999;
    }

    .cube {
      position: absolute;
      width: 10px;
      height: 10px;
      border: solid 1px #003298;
      transform-origin: top left;
      transform: scale(0) rotate(0deg) translate(-50%, -50%);
      animation: cube 12s ease-in forwards infinite;
      z-index: 0;
    }

    .cube:nth-child(2n) { border-color: #0051f4; }
    .cube:nth-child(1) { top: 80vh; left: 45vw; animation-delay: 0s; }
    .cube:nth-child(2) { top: 40vh; left: 25vw; animation-delay: 2s; }
    .cube:nth-child(3) { top: 50vh; left: 75vw; animation-delay: 4s; }
    .cube:nth-child(4) { top: 10vh; left: 90vw; animation-delay: 6s; }
    .cube:nth-child(5) { top: 85vh; left: 10vw; animation-delay: 8s; }
    .cube:nth-child(6) { top: 10vh; left: 50vw; animation-delay: 10s; }

    @keyframes cube {
      from {
        transform: scale(0) rotate(0deg) translate(-50%, -50%);
        opacity: 1;
      }
      to {
        transform: scale(20) rotate(960deg) translate(-50%, -50%);
        opacity: 0;
      }
    }
  </style>
</head>
<body>

<!-- Cubos de fondo -->
<div class="cube"></div>
<div class="cube"></div>
<div class="cube"></div>
<div class="cube"></div>
<div class="cube"></div>
<div class="cube"></div>

<!-- Login visual -->
<div class="login-container">
  <div class="login-image d-none d-md-block"></div>
  <div class="login-form">
    <div class="text-center mb-4 border-bottom pb-2">
  <h2 class="text-primary fw-bold mb-0">
    🩺 Citas Médicas
  </h2>
  <small class="text-muted">Iniciar Sesión</small>
</div>

    <?php if (isset($error)): ?>
      <div class="alert alert-danger text-center"><?php echo $error; ?></div>
    <?php endif; ?>
    <form action="index.php" method="POST">
      <div class="mb-3">

        <label for="correo" class="form-label">Correo electrónico</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-envelope"></i></span>
          <input type="email" name="correo" class="form-control" placeholder="ejemplo@gmail.com" required>
        </div>
      </div>

      <div class="mb-3 position-relative">
        <label for="password" class="form-label">Contraseña</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
          <input type="password" name="password" class="form-control" id="passwordInput" required>
          <span class="toggle-password" onclick="togglePassword()">
            <i class="bi bi-eye-fill" id="toggleIcon"></i>
          </span>
        </div>
      </div>

      <div class="d-grid mt-4">
        <button type="submit" name="login" class="btn btn-primary btn-animated">Iniciar sesión</button>
      </div>
    </form>
  </div>
</div>

<script>
  function togglePassword() {
    const input = document.getElementById("passwordInput");
    const icon = document.getElementById("toggleIcon");
    if (input.type === "password") {
      input.type = "text";
      icon.classList.replace("bi-eye-fill", "bi-eye-slash-fill");
    } else {
      input.type = "password";
      icon.classList.replace("bi-eye-slash-fill", "bi-eye-fill");
    }
  }
</script>

</body>
</html>
