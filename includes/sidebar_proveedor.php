<div class="vertical-nav bg-white" id="sidebar">
  <div class="media">
    <div class="media-body">
      <h4 class="m-0"><p class="text-white">Sistema</p></h4>
      <p class="font-weight-light text-white mb-0">
        <?= htmlspecialchars($nombre) ?>
      </p>
    </div>
  </div>

  <center>
    <a href="auth/logout.php" class="btn-cerrar-sesion">Cerrar sesión</a>
  </center>

<h5 class="ui horizontal divider header text-primary">Productos</h5>
<ul class="nav flex-column bg-white mb-0">
  	  
	  <!-- Para añadir la redirección a las opciones, añadir un onclick, algo de este estilo:
     onclick="cargarDirecto('administrador/lista_empresas.php')" al lado derecho de class="nav-link" -->

	  
  <!-- Listar productos (global temporal) -->
  <li class="nav-item">
    <a class="nav-link" onclick="cargarDirecto('Electrodomesticos/proveedor/lista_productos.php')">
      📦 Listar productos
    </a>
    <!-- TODO: cuando migren a Administrador, cambiar a:
         onclick="cargarDirecto('administrador/lista_productos_global.php')" 
         y proteger con verificarRol([1]) en ese archivo -->
  </li>
</ul>

<h5 class="ui horizontal divider header text-primary">Módulo Comercial</h5>
<ul class="nav flex-column bg-white mb-0">
  <li class="nav-item">
    <a class="nav-link" onclick="cargarDirecto('Electrodomesticos/proveedor/cotizaciones_recibidas.php')">
      ✉️ Cotizaciones
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link" onclick="cargarDirecto('Electrodomesticos/proveedor/perfil_empresa.php')">
      🏢 Perfil Empresa
    </a>
  </li>
</ul>

</div>
