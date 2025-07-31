<?php
session_start();
if (!isset($_SESSION["id_usuario"])) {
  header("Location: cliente/login.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Sistema de Cotizaciones</title>
  <link rel="stylesheet" href="assets/css/estilos.css"> <!-- El que vaya a hacer el css le cambia -->
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
    <a onclick="cargar('catalogo')">Catálogo</a>
    <a onclick="cargar('proforma')">Mi Proforma</a>
    <a onclick="cargar('historial_proformas')">Historial</a>
    <a onclick="cargar('perfil')">Perfil</a>
    <a href="auth/logout.php" style="float:right;">Salir</a>
  </nav>

  <div id="contenido">
    <h2>Bienvenido al Sistema</h2>
    <p>Selecciona una opción del menú para comenzar.</p>
  </div>

</body>
</html>
