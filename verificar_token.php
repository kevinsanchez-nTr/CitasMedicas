<?php
session_start();
require_once "includes/conexion.php";
require_once "enviar_token.php";

if (!isset($_SESSION['id_usuarios_prelogin'])) {
  header("Location: index.php");
  exit();
}

$mensaje = "";
$nombre = $_SESSION['nombre_prelogin'];
$usuario_id = $_SESSION['id_usuarios_prelogin'];

if (isset($_POST['verificar'])) {
  $token_ingresado = $_POST['token'];

  $sql = "SELECT * FROM multifactor_tokens 
          WHERE usuario_id = ? 
          AND token = ? 
          AND expirado = 0 
          AND fecha_envio >= NOW() - INTERVAL 5 MINUTE
          ORDER BY fecha_envio DESC LIMIT 1";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("is", $usuario_id, $token_ingresado);
  $stmt->execute();
  $resultado = $stmt->get_result();

  if ($row = $resultado->fetch_assoc()) {
  $update = $conn->prepare("UPDATE multifactor_tokens SET expirado = 1 WHERE id_multifactor_tokens = ?");
  $update->bind_param("i", $row['id_multifactor_tokens']);
  $update->execute();

  $_SESSION['id_usuarios'] = $usuario_id;
  $_SESSION['nombre'] = $_SESSION['nombre_prelogin'];
  $_SESSION['rol_id'] = $_SESSION['rol_id_prelogin'];

  unset($_SESSION['id_usuarios_prelogin']);
  unset($_SESSION['nombre_prelogin']);
  unset($_SESSION['rol_id_prelogin']);

  header("Location: dashboard.php");
  exit();
}
 else {
    $mensaje = "Código incorrecto o expirado.";
  }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Verificar código</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    @import url("https://fonts.googleapis.com/css?family=Montserrat:700");

    body {
      background: #0b0583;
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      position: relative;
    }

    .verify-container {
      background: white;
      border-radius: 20px;
      box-shadow: 0 0 30px rgba(0, 0, 0, 0.1);
      max-width: 850px;
      width: 100%;
      display: flex;
      overflow: hidden;
      z-index: 1;
    }

    .verify-image {
      background: url('https://cdn-icons-png.flaticon.com/512/3774/3774299.png') no-repeat center;
      background-size: 75%;
      flex: 1;
      min-height: 400px;
    }

    .verify-form {
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

<!-- Cubos fondo -->
<div class="cube"></div>
<div class="cube"></div>
<div class="cube"></div>
<div class="cube"></div>
<div class="cube"></div>
<div class="cube"></div>

<!-- Contenedor principal -->
<div class="verify-container">
  <div class="verify-image d-none d-md-block"></div>
  <div class="verify-form">
    <h4 class="text-primary text-center">🔒 Verificación en dos pasos</h4>
    <p class="text-center">Hola <strong><?php echo htmlspecialchars($nombre); ?></strong>, ingresa el código que recibiste en tu correo electrónico.</p>
    <p class="text-center text-muted">Este código expira en: <strong id="countdown">05:00</strong></p>
<?php if (isset($_SESSION['mensaje_exito'])): ?>
  <div class="alert alert-success text-center">
    <?php 
      echo $_SESSION['mensaje_exito']; 
      unset($_SESSION['mensaje_exito']);
    ?>
  </div>
<?php endif; ?>

    <form method="POST" id="verificarForm">
      <div class="mb-3">
        <label class="form-label">Código de verificación</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-shield-lock-fill"></i></span>
          <input type="text" name="token" class="form-control" placeholder="Ej: 123456" maxlength="6" required>
        </div>
      </div>

      <div class="d-grid">
        <button type="submit" name="verificar" id="btnVerificar" class="btn btn-success btn-animated">Verificar código</button>
      </div>
    </form>

    <div id="mensaje-expirado" class="alert alert-warning mt-3 text-center d-none">
      ⏱ El código ha expirado.<br>
      <a href="reenviar_codigo.php" class="btn btn-outline-primary mt-2">Reenviar código</a>
    </div>

    <?php if (!empty($mensaje)): ?>
      <div class="alert alert-danger mt-3 text-center"><?php echo $mensaje; ?></div>
    <?php endif; ?>
  </div>
</div>

<script>
  let tiempoRestante = 300;
  const countdownElement = document.getElementById('countdown');
  const btnVerificar = document.getElementById('btnVerificar');
  const mensajeExpirado = document.getElementById('mensaje-expirado');

  const intervalo = setInterval(() => {
    const minutos = Math.floor(tiempoRestante / 60).toString().padStart(2, '0');
    const segundos = (tiempoRestante % 60).toString().padStart(2, '0');
    countdownElement.textContent = `${minutos}:${segundos}`;

    if (tiempoRestante <= 0) {
      clearInterval(intervalo);
      btnVerificar.disabled = true;
      mensajeExpirado.classList.remove('d-none');
    }

    tiempoRestante--;
  }, 1000);
</script>

</body>
</html>

