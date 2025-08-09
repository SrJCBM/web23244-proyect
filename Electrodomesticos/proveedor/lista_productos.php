<?php
session_start();

// 🔒 Por ahora dejamos acceso con rol 2 (proveedor).
// TODO (al migrar a Administrador): cambiar a [1] y (si quieres) mover a administrador/lista_productos_global.php
require_once("../../includes/verificar_rol.php");
verificarRol([2]);

require_once("../../includes/conexion.php");

// ✅ Mostrar TODOS los productos (sin filtrar por empresa ni estado).
// Si más adelante quieres filtrar por estado, descomenta la consulta B.

// Consulta A: TODOS
$sql = "SELECT * FROM productos ORDER BY fecha_creacion ASC, id_producto ASC";

// Consulta B (opcional): solo activos
// $sql = "SELECT * FROM productos WHERE estado='activo' ORDER BY fecha_creacion DESC, id_producto DESC";

$stmt = $conexion->prepare($sql);
$stmt->execute();
$resultado = $stmt->get_result();

// ¿Quieres mostrar el link de “Crear producto”? ponlo en true/false
$mostrarCrear = true; // cambia a false si NO quieres que aparezca
?>

<h2>📦 Productos (vista global temporal)</h2>


<?php if ($resultado->num_rows > 0): ?>
  <table border="1" cellpadding="8" cellspacing="0">
    <tr>
      <th>ID</th>
      <th>Nombre</th>
      <th>Marca</th>
      <th>Modelo</th>
      <th>Precio (USD)</th>
      <th>Stock</th>
      <th>Estado</th>
      <th>Fecha</th>
      <th>ID Empresa</th>
      <th>Acciones</th>
    </tr>
    <?php while ($row = $resultado->fetch_assoc()): ?>
      <tr>
        <td><?= (int)$row['id_producto'] ?></td>
        <td><?= htmlspecialchars($row['nombre']) ?></td>
        <td><?= htmlspecialchars($row['marca']) ?></td>
        <td><?= htmlspecialchars($row['modelo']) ?></td>
        <td><?= number_format((float)$row['precio_base'], 2) ?></td>
        <td><?= (int)$row['stock'] ?></td>
        <td><?= htmlspecialchars($row['estado']) ?></td>
        <td><?= htmlspecialchars($row['fecha_creacion']) ?></td>
        <td>#<?= (int)$row['id_empresa'] ?></td>
        <td>
          <!-- Usa cargarDirecto y .php para evitar prefijos "cliente/" -->
          <button onclick="cargarDirecto('Electrodomesticos/proveedor/editar_producto.php?id=<?= (int)$row['id_producto'] ?>')">✏️</button>
          <button onclick="eliminarProducto(<?= (int)$row['id_producto'] ?>)">🗑️</button>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>
<?php else: ?>
  <p>No hay productos.</p>
<?php endif; ?>

<script>
function eliminarProducto(id) {
  if (confirm("¿Estás seguro de eliminar este producto?")) {
    fetch(`Electrodomesticos/proveedor/eliminar_producto.php?id=${id}`, { credentials: 'same-origin' })
      .then(res => res.text())
      .then(html => { document.getElementById("contenido").innerHTML = html; })
      .catch(err => console.error("Error al eliminar:", err));
  }
}
</script>
