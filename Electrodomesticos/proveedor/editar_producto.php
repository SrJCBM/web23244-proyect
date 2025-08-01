<?php
require_once("../../includes/verificar_rol.php");
verificarRol([2]);
$id_empresa = $_SESSION["id_empresa"];

include '../../includes/conexion.php';

// Validar existencia del ID del producto
if (!isset($_GET["id"])) {
  echo "ID de producto no especificado.";
  exit;
}

$id_producto = intval($_GET["id"]);

// Si llega un POST, se procesa el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $nombre = $_POST["nombre"];
  $descripcion = $_POST["descripcion"];
  $marca = $_POST["marca"];
  $modelo = $_POST["modelo"];
  $precio = $_POST["precio"];
  $stock = $_POST["stock"];

  $sql = "UPDATE productos
          SET nombre = ?, descripcion = ?, marca = ?, modelo = ?, precio_base = ?, stock = ?
          WHERE id_producto = ? AND id_empresa = ?";

  $stmt = $conexion->prepare($sql);
  $stmt->bind_param("ssssdiii", $nombre, $descripcion, $marca, $modelo, $precio, $stock, $id_producto, $id_empresa);

  if ($stmt->execute()) {
    echo "<p style='color:green;'>Producto actualizado correctamente.</p>";
    echo "<a href='#' onclick=\"cargarDirecto('Electrodomesticos/proveedor/lista_productos.php')\">🔙 Volver a la lista</a>";
  } else {
    echo "<p style='color:red;'>Error al actualizar: " . $stmt->error . "</p>";
  }

  $stmt->close();
  $conexion->close();
  exit;
}

// Si llega por GET, se cargan los datos
$sql = "SELECT * FROM productos WHERE id_producto = ? AND id_empresa = ? LIMIT 1";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ii", $id_producto, $id_empresa);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
  echo "Producto no encontrado.";
  exit;
}

$producto = $resultado->fetch_assoc();
?>

<h2>✏️ Editar Producto</h2>

<form id="formEditar" data-spa>
  <label>Nombre:</label><br>
  <input type="text" name="nombre" value="<?= htmlspecialchars($producto['nombre']) ?>" required><br><br>

  <label>Descripción:</label><br>
  <textarea name="descripcion" required><?= htmlspecialchars($producto['descripcion']) ?></textarea><br><br>

  <label>Marca:</label><br>
  <input type="text" name="marca" value="<?= htmlspecialchars($producto['marca']) ?>" required><br><br>

  <label>Modelo:</label><br>
  <input type="text" name="modelo" value="<?= htmlspecialchars($producto['modelo']) ?>" required><br><br>

  <label>Precio (USD):</label><br>
  <input type="number" name="precio" value="<?= $producto['precio_base'] ?>" step="0.01" required><br><br>

  <label>Stock:</label><br>
  <input type="number" name="stock" value="<?= $producto['stock'] ?>" required><br><br>

  <button type="submit">Guardar Cambios</button>
</form>

<script>
document.getElementById("formEditar").addEventListener("submit", function(e) {
  e.preventDefault();

  const formData = new FormData(this);

  fetch(window.location.href, {
    method: "POST",
    body: formData
  })
  .then(res => res.text())
  .then(html => {
    document.getElementById("contenido").innerHTML = html;
  })
  .catch(err => {
    console.error("Error al editar:", err);
    document.getElementById("contenido").innerHTML = "<p>Error al guardar cambios.</p>";
  });
});
</script>
