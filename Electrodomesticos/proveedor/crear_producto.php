<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Simulación de sesión
if (!isset($_SESSION["id_usuario"])) {
  $_SESSION["id_usuario"] = 1;
  $_SESSION["rol"] = "proveedor";
  $_SESSION["id_empresa"] = 1;
}

if ($_SESSION["rol"] !== "proveedor") {
  echo "Acceso no autorizado.";
  exit;
}

include '../../includes/conexion.php';

$id_empresa = $_SESSION["id_empresa"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $nombre = $_POST["nombre"];
  $descripcion = $_POST["descripcion"];
  $marca = $_POST["marca"];
  $modelo = $_POST["modelo"];
  $precio = $_POST["precio"];
  $stock = $_POST["stock"];
  $fecha = date("Y-m-d");

  $sql = "INSERT INTO productos (nombre, descripcion, marca, modelo, precio_base, stock, id_empresa, fecha_creacion)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

  $stmt = $conexion->prepare($sql);
  $stmt->bind_param("ssssdiss", $nombre, $descripcion, $marca, $modelo, $precio, $stock, $id_empresa, $fecha);

  if ($stmt->execute()) {
    echo "<p style='color:green;'>Producto guardado correctamente.</p>";
    echo "<a href='#' onclick=\"cargarDirecto('Electrodomesticos/proveedor/lista_productos.php')\">Ver productos</a>";
  } else {
    echo "<p style='color:red;'>Error: " . $stmt->error . "</p>";
  }

  $stmt->close();
  $conexion->close();
  exit; // evita que se siga cargando el formulario
}
?>

<!-- FORMULARIO -->
<h2>Crear Producto</h2>

<form id="formProducto">
  <label>Nombre:</label><br>
  <input type="text" name="nombre" required><br><br>

  <label>Descripción:</label><br>
  <textarea name="descripcion" required></textarea><br><br>

  <label>Marca:</label><br>
  <input type="text" name="marca" required><br><br>

  <label>Modelo:</label><br>
  <input type="text" name="modelo" required><br><br>

  <label>Precio (USD):</label><br>
  <input type="number" name="precio" step="0.01" required><br><br>

  <label>Stock:</label><br>
  <input type="number" name="stock" required><br><br>

  <button type="submit">Guardar Producto</button>
</form>

<!-- JavaScript para enviar con fetch -->
<script>
document.getElementById("formProducto").addEventListener("submit", function(e) {
  e.preventDefault();

  const formData = new FormData(this);

  fetch(window.location.href, {
    method: "POST",
    body: formData
  })
  .then(response => response.text())
  .then(html => {
    document.getElementById("contenido").innerHTML = html;
  })
  .catch(err => {
    document.getElementById("contenido").innerHTML = "<p>Error al guardar el producto.</p>";
    console.error(err);
  });
});
</script>
