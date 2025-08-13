<div class="vertical-nav bg-white" id="sidebar">
  <div class="media">
    <div class="media-body">
      <h4 class="m-0"><p class="text-white">Sistema</p></h4>
      <p class="font-weight-light text-white mb-0">(Admin)</p>
    </div>
  </div>

  <center><a href="auth/logout.php" class="btn-cerrar-sesion">Cerrar sesión</a></center>

  <h5 class="ui horizontal divider header text-primary">Gestión de Usuarios</h5>
  <ul class="nav flex-column bg-white mb-0">
    <li><a class="nav-link" onclick="cargarDirecto('administrador/lista_usuarios.php')">Usuarios</a></li>
	<li><a class="nav-link" onclick="cargarDirecto('administrador/registro_usuario.php')">Registrar nuevo usuario</a></li>
  </ul>

  <h5 class="ui horizontal divider header text-primary">Empresas y Proveedores</h5>
  <ul class="nav flex-column bg-white mb-0">
    <li><a class="nav-link" onclick="cargarDirecto('administrador/lista_empresas.php')">Lista de proveedores</a></li>
    <li><a class="nav-link" onclick="cargarDirecto('administrador/registro_empresa.php')">Registrar empresa</a></li>
  </ul>

  <h5 class="ui horizontal divider header text-primary">Cotizaciones</h5>
  <ul class="nav flex-column bg-white mb-0">
    <li><a class="nav-link" onclick="cargarDirecto('cliente/historial_proformas.php')">Historial de cotizaciones</a></li>
  </ul>

  <h5 class="ui horizontal divider header text-primary">Productos</h5>
  <ul class="nav flex-column bg-white mb-0">
    <li><a class="nav-link" onclick="cargarDirecto('administrador/productos/lista_productos.php')">Lista productos</a></li>
	  <li><a class="nav-link" onclick="cargarDirecto('administrador/componentes/lista_opciones.php')">Lista componentes</a></li>
  </ul>

  <h5 class="ui horizontal divider header text-primary">Sistema</h5>
  <ul class="nav flex-column bg-white mb-0">
    <li><a class="nav-link">Auditoría y logs</a></li>
  </ul>
</div>
