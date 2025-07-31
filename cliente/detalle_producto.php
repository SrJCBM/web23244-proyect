<?php
session_start();
include("../includes/conexion.php");

if (!isset($_GET["id"])) {
    echo "<p>Producto no especificado.</p>";
    exit;
}

$id = intval($_GET["id"]);
$sql = "SELECT * FROM productos WHERE id_producto = ? AND estado = 'activo'";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo "<p>Producto no encontrado.</p>";
    exit;
}

$producto = $res->fetch_assoc();
?>

<h2>Detalle del Producto</h2>

<div style="border:1px solid #ccc; padding:10px;">
  <strong><?= htmlspecialchars($producto["nombre"]) ?></strong><br>
  Marca: <?= htmlspecialchars($producto["marca"]) ?><br>
  Modelo: <?= htmlspecialchars($producto["modelo"]) ?><br>
  Descripción: <?= nl2br(htmlspecialchars($producto["descripcion"])) ?><br>
  Precio: $<?= number_format($producto["precio_base"], 2) ?><br><br>

  <form method="post" action="proforma.php">
    <input type="hidden" name="id_producto" value="<?= $producto["id_producto"] ?>">
    Cantidad:
    <input type="number" name="cantidad" value="1" min="1" required>
    <button type="submit" name="agregar_desde_detalle">Agregar a Proforma</button>
  </form>
</div>
