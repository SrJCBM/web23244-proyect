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
  <link rel="stylesheet" href="assets/css/sidebar.css">
  <link rel="stylesheet" href="assets/css/admin-crud.css">
  <script src="assets/js/spa.js" defer></script>
  <script src="assets/js/sidebar.js" defer></script>
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

<?php
if ($rol == 1) {
  include 'includes/sidebar_admin.php';
} elseif ($rol == 2) {
  include 'includes/sidebar_proveedor.php';
} elseif ($rol == 3) {
  include 'includes/sidebar_cliente.php';
}
?>

<button id="sidebarToggle" onclick="toggleSidebar()">☰</button>
<div id="contenido-wrapper">
<div id="contenido">
  <h2>Bienvenido, <?= htmlspecialchars($nombre) ?> 👋</h2>
  <?php if ($rol == 1): ?>
    <p>Como administrador, puedes gestionar empresas proveedoras, usuarios registrados y revisar todas las cotizaciones.</p>
  <?php elseif ($rol == 2): ?>
    <p>Como proveedor, puedes gestionar tus productos, ver las cotizaciones que has recibido y editar tu perfil de empresa.</p>
  <?php elseif ($rol == 3): ?>
    <p>Como cliente, puedes explorar el catálogo, generar proformas y revisar tu historial.</p>
  <?php endif; ?>
</div></div>


</body>

</html>
