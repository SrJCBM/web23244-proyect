<div class="vertical-nav bg-white" id="sidebar">
  <div class="media">
    <div class="media-body">
      <h4 class="m-0"><p class="text-white">Sistema</p></h4>
      <p class="font-weight-light text-white mb-0"><?= htmlspecialchars($nombre) ?></p>
    </div>
  </div>

  <center><a href="auth/logout.php" class="btn-cerrar-sesion">Cerrar sesión</a></center>

  <h5 class="ui horizontal divider header text-primary">Productos</h5>
  <ul class="nav flex-column bg-white mb-0">
    <li><a class="nav-link" onclick="cargarDirecto('administrador/productos/crear_producto.php')">Crear producto</a></li>
    <li><a class="nav-link" onclick="cargarDirecto('administrador/productos/lista_productos.php')">Editar / Consultar</a></li>
    <li><a class="nav-link">Componentes / Accesorios</a></li>
    <li><a class="nav-link">Configurar accesorios</a></li>
    <li><a class="nav-link">Precios y stock</a></li>
  </ul>

  <h5 class="ui horizontal divider header text-primary">Proveedores</h5>
  <ul class="nav flex-column bg-white mb-0">
    <li><a class="nav-link" onclick="cargarDirecto('administrador/lista_empresas.php')">Lista de proveedores</a></li>
    <li><a class="nav-link" onclick="cargarDirecto('administrador/registro_empresa.php')">Registrar proveedor</a></li>
    <li><a class="nav-link" onclick="cargarDirecto('Electrodomesticos/proveedor/perfil_empresa.php')">Perfil de proveedor</a></li>
    <li><a class="nav-link">Asociar productos</a></li>
  </ul>
</div>