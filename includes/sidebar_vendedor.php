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
    <li><a class="nav-link" onclick="cargarDirecto('cliente/generar_proforma.php')">Generar proforma</a></li>
    <li><a class="nav-link" onclick="cargarDirecto('cliente/historial_proformas.php')">Mis proformas</a></li>
    <li><a class="nav-link">Buscar proformas</a></li>
    <li><a class="nav-link">Enviar por correo</a></li>
    <li><a class="nav-link">Exportar a PDF</a></li>
  </ul>

  <h5 class="ui horizontal divider header text-primary">Clientes</h5>
  <ul class="nav flex-column bg-white mb-0">
    <li><a class="nav-link">Registrar cliente</a></li>
  </ul>

  <h5 class="ui horizontal divider header text-primary">Catálogo</h5>
  <ul class="nav flex-column bg-white mb-0">
    <li><a class="nav-link" onclick="cargarDirecto('cliente/catalogo.php')">Ver catálogo</a></li>
    <li><a class="nav-link" onclick="cargarDirecto('cliente/detalle_producto.php')">Detalle de producto</a></li>
    <li><a class="nav-link">Filtrar productos</a></li>
  </ul>
</div>