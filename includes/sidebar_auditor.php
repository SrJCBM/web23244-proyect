<div class="vertical-nav bg-white" id="sidebar">
  <div class="media">
    <div class="media-body">
      <h4 class="m-0"><p class="text-white">Sistema</p></h4>
      <p class="font-weight-light text-white mb-0"><?= htmlspecialchars($nombre) ?></p>
    </div>
  </div>

  <center>
    <a href="auth/logout.php" class="btn-cerrar-sesion">Cerrar sesión</a>
  </center>

  <h5 class="ui horizontal divider header text-primary">Auditoría</h5>
  <ul class="nav flex-column bg-white mb-0">
    <li class="nav-item"><a class="nav-link" onclick="cargarDirecto('auditor/lista_accesos.php')">Historial de accesos</a></li>
    <li class="nav-item"><a class="nav-link">Logs de actividades</a></li>
  </ul>
</div>
