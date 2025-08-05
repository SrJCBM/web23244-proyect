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

  <h5 class="ui horizontal divider header text-primary">Solicitudes y Cotizaciones</h5>
  <ul class="nav flex-column bg-white mb-0">
    <li class="nav-item"><a class="nav-link" onclick="cargar('catalogo')">Catálogo</a></li>
	  
	  <!-- Para añadir la redirección a las opciones, añadir un onclick, algo de este estilo:
     onclick="cargarDirecto('administrador/lista_empresas.php')" al lado derecho de class="nav-link" -->

	  
    <li class="nav-item"><a class="nav-link" onclick="cargar('proforma')">Mi Proforma</a></li>
    <li class="nav-item"><a class="nav-link" onclick="cargar('historial_proformas')">Historial</a></li>
	<li class="nav-item"><a class="nav-link" onclick="cargar('perfil')">Perfil</a></li>
  </ul>

  <h5 class="ui horizontal divider header text-primary">Insertar texto de sección</h5>
  <ul class="nav flex-column bg-white mb-0">
    <li class="nav-item"><a class="nav-link">Insertar texto de opción</a></li>
    <li class="nav-item"><a class="nav-link">Insertar texto de opción</a></li>
  </ul>

  <h5 class="ui horizontal divider header text-primary">Insertar texto de sección</h5>
  <ul class="nav flex-column bg-white mb-0">
    <li class="nav-item"><a class="nav-link">Insertar texto de opción</a></li>
  </ul>
</div>
