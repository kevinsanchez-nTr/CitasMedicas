<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>



<!-- CAPA OSCURA CUANDO ABRE EL MENÚ -->
<div class="overlay" onclick="toggleMenu()"></div>

<!-- SIDEBAR -->
<div class="sidebar closed">
  <h4>🩺 Citas Médicas</h4>

  <a href="/citaMedicas/dashboard.php"><i class="bi bi-speedometer2"></i> Tablero</a>
  <a href="/citaMedicas/medicos/lista_medicos.php"><i class="bi bi-person-badge"></i> Médicos</a>
  <a href="/citaMedicas/paciente/lista_pacientes.php"><i class="bi bi-person-plus-fill"></i> Paciente</a>
  <a href="/citaMedicas/citas/lista_citas.php"><i class="bi bi-calendar-check"></i> Citas</a>
  <a href="/citaMedicas/reportes/reporte_citas_medico.php"><i class="bi bi-file-earmark-text"></i> Reportes</a>
  <a href="/citaMedicas/registro/lista_usuarios.php"><i class="bi bi-person-plus"></i> Registro</a>

  <a href="/citaMedicas/usuarios/editar_perfil.php"><i class="bi bi-person-gear"></i> Editar perfil</a>
  <a href="/citaMedicas/logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a>
</div>

<script>
function toggleMenu() {
  const sidebar = document.querySelector('.sidebar');
  const overlay = document.querySelector('.overlay');
  const content = document.querySelector('.content');

  sidebar.classList.toggle('open');
  sidebar.classList.toggle('closed');
  overlay.classList.toggle('active');
  content.classList.toggle('menu-closed');
}
</script>
