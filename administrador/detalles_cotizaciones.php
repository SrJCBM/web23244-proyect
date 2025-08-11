<?php
require_once("../includes/verificar_rol.php");
verificarRol([1,2,6]); 
include("../includes/conexion.php");

// Traer todas las cotizaciones con información del cliente
$sql = "
SELECT c.id_cotizacion, c.fecha_emision, c.total, c.estado, u.nombre_completo AS cliente
FROM cotizaciones c
JOIN usuarios u ON c.id_usuario = u.id_usuario
ORDER BY c.fecha_emision DESC
";

$resultado = $conexion->query($sql);
?>

<h2>📄 Todas las Cotizaciones del Sistema</h2>

<?php if ($resultado->num_rows > 0): ?>
  <table border="1" cellpadding="6" cellspacing="0">
    <tr>
      <th>ID</th>
      <th>Cliente</th>
      <th>Fecha</th>
      <th>Total</th>
      <th>Estado</th>
    </tr>
    <?php while ($row = $resultado->fetch_assoc()): ?>
      <tr>
        <td>
          <a href="#" onclick="cargarDirecto('admin/detalle_cotizacion.php?id=<?= $row['id_cotizacion'] ?>')">
            <?= $row["id_cotizacion"] ?>
          </a>
        </td>
        <td><?= htmlspecialchars($row["cliente"]) ?></td>
        <td><?= $row["fecha_emision"] ?></td>
        <td>$<?= number_format($row["total"], 2) ?></td>
        <td><?= $row["estado"] ?></td>
      </tr>
    <?php endwhile; ?>
  </table>
<?php else: ?>
  <p>No hay cotizaciones registradas en el sistema.</p>
<?php endif; ?>
