<div class="vertical-nav bg-white" id="sidebar">
  <div class="media">
    <div class="media-body">
      <h4 class="m-0"><p class="text-white">Sistema</p></h4>
      <p class="font-weight-light text-white mb-0"><?= htmlspecialchars($nombre) ?></p>
    </div>
  </div>

  <center><a href="auth/logout.php" class="btn-cerrar-sesion">Cerrar sesión</a></center>

  <h5 class="ui horizontal divider header text-primary">Proformas</h5>
  <ul class="nav flex-column bg-white mb-0">
    <li><a class="nav-link" onclick="cargarDirecto('administrador/todas_cotizaciones.php')">Historial global</a></li>
    <li><a class="nav-link" onclick="cargarDirecto('administrador/detalles_cotizaciones.php')">Detalles</a></li>
    <li><a class="nav-link">Filtros avanzados</a></li>
  </ul>

  <h5 class="ui horizontal divider header text-primary">Catálogo</h5>
  <ul class="nav flex-column bg-white mb-0">
    <li><a class="nav-link" onclick="cargarDirecto('Electrodomesticos/proveedor/lista_productos.php')">Productos</a></li>
    <li><a class="nav-link" onclick="cargarDirecto('administrador/lista_empresas.php')">Proveedores</a></li>
    <li><a class="nav-link">Componentes</a></li>
  </ul>

  <h5 class="ui horizontal divider header text-primary">Reportes</h5>
  <ul class="nav flex-column bg-white mb-0">
    <li><a class="nav-link">Reportes estadísticos</a></li>
    <li><a class="nav-link">Exportar (PDF / Excel)</a></li>
  </ul>
</div>
