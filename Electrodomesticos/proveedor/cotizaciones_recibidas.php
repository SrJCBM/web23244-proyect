<?php
require_once("../../includes/verificar_rol.php");
verificarRol([2]); // Solo proveedores

include("../../includes/conexion.php");

$id_empresa = $_SESSION["id_empresa"];

// Consulta: obtener cotizaciones que incluyan productos de esta empresa
$sql = "
SELECT DISTINCT c.id_cotizacion, c.fecha_emision, c.total, c.estado, u.nombre_completo AS cliente
FROM cotizaciones c
JOIN detalle_cotizacion dc ON c.id_cotizacion = dc.id_cotizacion
JOIN productos p ON dc.id_producto = p.id_producto
JOIN usuarios u ON c.id_usuario = u.id_usuario
WHERE p.id_empresa = ?
ORDER BY c.fecha_emision DESC
";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_empresa);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<h2>📑 Cotizaciones Recibidas</h2>

<?php if ($resultado->num_rows > 0): ?>
  <table border="1" cellpadding="8" cellspacing="0">
    <tr>
      <th>ID</th>
      <th>Fecha</th>
      <th>Cliente</th>
      <th>Total</th>
      <th>Estado</th>
      <th>Acciones</th>
    </tr>
    <?php while ($row = $resultado->fetch_assoc()): ?>
      <tr>
        <td><?= $row["id_cotizacion"] ?></td>
        <td><?= $row["fecha_emision"] ?></td>
        <td><?= htmlspecialchars($row["cliente"]) ?></td>
        <td>$<?= number_format($row["total"], 2) ?></td>
        <td><?= $row["estado"] ?></td>
        <td>
          <button onclick="cargarDirecto('Electrodomesticos/proveedor/cotizacion_detalle.php?id=<?= $row["id_cotizacion"] ?>')">
            Ver Detalle
          </button>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>
<?php else: ?>
  <p>No has recibido cotizaciones aún.</p>
<?php endif; ?>
