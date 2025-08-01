<?php
require_once("../../includes/verificar_rol.php");
verificarRol([2]);

include("../../includes/conexion.php");

$id_empresa = $_SESSION["id_empresa"];
$id_cotizacion = $_GET["id"] ?? null;

if (!$id_cotizacion || !is_numeric($id_cotizacion)) {
    echo "<p style='color:red;'>⚠️ Cotización no válida.</p>";
    exit;
}

// Traer solo productos de esta empresa en esa cotización
$sql = "
SELECT p.nombre, p.marca, p.modelo, dc.cantidad, dc.precio_unitario, dc.subtotal
FROM detalle_cotizacion dc
JOIN productos p ON dc.id_producto = p.id_producto
WHERE dc.id_cotizacion = ? AND p.id_empresa = ?
";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("ii", $id_cotizacion, $id_empresa);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<h2>🧾 Detalle de Cotización #<?= $id_cotizacion ?></h2>

<?php if ($resultado->num_rows > 0): ?>
  <table border="1" cellpadding="8">
    <tr>
      <th>Producto</th>
      <th>Marca</th>
      <th>Modelo</th>
      <th>Cantidad</th>
      <th>Precio Unitario</th>
      <th>Subtotal</th>
    </tr>
    <?php while ($row = $resultado->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row["nombre"]) ?></td>
        <td><?= htmlspecialchars($row["marca"]) ?></td>
        <td><?= htmlspecialchars($row["modelo"]) ?></td>
        <td><?= (int)$row["cantidad"] ?></td>
        <td>$<?= number_format($row["precio_unitario"], 2) ?></td>
        <td>$<?= number_format($row["subtotal"], 2) ?></td>
      </tr>
    <?php endwhile; ?>
  </table>
<?php else: ?>
  <p>Esta cotización no contiene productos de tu empresa.</p>
<?php endif; ?>
