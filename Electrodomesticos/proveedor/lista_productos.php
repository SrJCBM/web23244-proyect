<?php
session_start();

require_once("../../includes/verificar_rol.php");
verificarRol([2]);
$id_empresa = $_SESSION["id_empresa"];

include '../../includes/conexion.php';
// Consulta: solo productos activos de esta empresa
$sql = "SELECT * FROM productos WHERE id_empresa = ? AND estado = 'activo' ORDER BY fecha_creacion DESC";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_empresa);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<h2>📦 Mis Productos</h2>

<a onclick="cargarDirecto('Electrodomesticos/proveedor/crear_producto.php')">➕ Crear nuevo producto</a><br><br>

<?php if ($resultado->num_rows > 0): ?>
  <table border="1" cellpadding="8" cellspacing="0">
    <tr>
      <th>Nombre</th>
      <th>Marca</th>
      <th>Modelo</th>
      <th>Precio (USD)</th>
      <th>Stock</th>
      <th>Fecha</th>
      <th>Acciones</th>
    </tr>
    <?php while ($row = $resultado->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['nombre']) ?></td>
        <td><?= htmlspecialchars($row['marca']) ?></td>
        <td><?= htmlspecialchars($row['modelo']) ?></td>
        <td><?= number_format($row['precio_base'], 2) ?></td>
        <td><?= (int)$row['stock'] ?></td>
        <td><?= $row['fecha_creacion'] ?></td>
        <td>
          <button onclick="cargar('Electrodomesticos/proveedor/editar_producto?id=<?= $row['id_producto'] ?>')">✏️</button>
          <button onclick="eliminarProducto(<?= $row['id_producto'] ?>)">🗑️</button>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>
<?php else: ?>
  <p>No tienes productos registrados aún.</p>
<?php endif; ?>

<script>

function eliminarProducto(id) {
  if (confirm("¿Estás seguro de eliminar este producto?")) {
    fetch(`Electrodomesticos/proveedor/eliminar_producto.php?id=${id}`)
      .then(res => res.text())
      .then(html => {
        document.getElementById("contenido").innerHTML = html;
      })
      .catch(err => {
        console.error("Error al eliminar:", err);
      });
  }
}
</script>
