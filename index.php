<?php
session_start();
if (!isset($_SESSION["id_usuario"])) {
  header("Location: cliente/landing.php");
  exit();
}

$rol = $_SESSION["id_rol"];
$nombre = $_SESSION["nombre"];
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Sistema de Cotizaciones</title>
  <link rel="stylesheet" href="assets/css/estilos.css">
  <script src="assets/js/spa.js" defer></script>
  <style>
    body { font-family: Arial, sans-serif; margin: 0; }
    nav {
      background-color: #003366;
      padding: 10px;
    }
    nav a {
      color: white;
      text-decoration: none;
      margin-right: 15px;
      font-weight: bold;
      cursor: pointer;
    }
    nav a:hover {
      text-decoration: underline;
    }
    #contenido {
      padding: 20px;
    }
  </style>
</head>
<body>

  <nav>
    <?php if ($rol == 1): // ADMINISTRADOR ?>
      <a onclick="cargarDirecto('admin/lista_empresas.php')">Empresas</a>
      <a onclick="cargarDirecto('admin/lista_usuarios.php')">Usuarios</a>
      <a onclick="cargarDirecto('admin/todas_cotizaciones.php')">Cotizaciones</a>

    <?php elseif ($rol == 2): // PROVEEDOR ?>
      <a onclick="cargarDirecto('Electrodomesticos/proveedor/lista_productos.php')">Mis Productos</a>
      <a onclick="cargarDirecto('Electrodomesticos/proveedor/cotizaciones_recibidas.php')">Cotizaciones</a>
      <a onclick="cargarDirecto('Electrodomesticos/proveedor/perfil_empresa.php')">Perfil Empresa</a>

    <?php elseif ($rol == 3): // CLIENTE ?>
      <a onclick="cargar('catalogo')">Catálogo</a>
      <a onclick="cargar('proforma')">Mi Proforma</a>
      <a onclick="cargar('historial_proformas')">Historial</a>
      <a onclick="cargar('perfil')">Perfil</a>

    <?php endif; ?>

    <a href="auth/logout.php" style="float:right;">Salir</a>
  </nav>

<div id="contenido">
  <h2>Bienvenido, <?= htmlspecialchars($nombre) ?> 👋</h2>
  <?php if ($rol == 1): ?>
    <p>Como administrador, puedes gestionar empresas proveedoras, usuarios registrados y revisar todas las cotizaciones.</p>
  <?php elseif ($rol == 2): ?>
    <p>Como proveedor, puedes gestionar tus productos, ver las cotizaciones que has recibido y editar tu perfil de empresa.</p>
  <?php elseif ($rol == 3): ?>
    <p>Como cliente, puedes explorar el catálogo, generar proformas y revisar tu historial.</p>
  <?php endif; ?>
</div>


</body>
</html>
