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
    <li><a class="nav-link" onclick="cargarDirecto('cliente/proforma_wizard.php')">Generar proforma</a></li>
    <li><a class="nav-link" onclick="cargarDirecto('cliente/historial_proformas.php')">Ver proformas</a></li>
  </ul>


  <h5 class="ui horizontal divider header text-primary">Catálogo</h5>
  <ul class="nav flex-column bg-white mb-0">
    <li><a class="nav-link" onclick="cargarDirecto('administrador/productos/lista_productos.php')">Ver productos</a></li>
	  <li><a class="nav-link" onclick="cargarDirecto('administrador/componentes/lista_opciones.php')">Ver componentes</a></li>
	  <li><a class="nav-link" onclick="cargarDirecto('cliente/lista_clientes.php')">Ver clientes</a></li>
	  <li><a class="nav-link" onclick="cargarDirecto('administrador/lista_empresas.php')">Ver proveedores</a></li>
  </ul>
</div>
