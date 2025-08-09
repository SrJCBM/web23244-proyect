<?php
require_once("../../includes/verificar_rol.php");
verificarRol([1]); // solo administrador

require_once("../../includes/conexion.php");

/**
 * Intentamos obtener un mapa id_empresa => nombre
 * Probamos primero `empresas_proveedoras`. Si no existe, intentamos `empresas`.
 */
$empresaMap = [];

$q = $conexion->query("SELECT id_empresa, nombre FROM empresas_proveedoras ORDER BY nombre ASC");
if ($q instanceof mysqli_result) {
  while ($row = $q->fetch_assoc()) {
    $empresaMap[(int)$row['id_empresa']] = $row['nombre'];
  }
} else {
  // fallback si la tabla anterior no existe
  $q2 = $conexion->query("SELECT id_empresa, COALESCE(nombre, nombre_empresa, razon_social) AS nombre FROM empresas ORDER BY nombre ASC");
  if ($q2 instanceof mysqli_result) {
    while ($row = $q2->fetch_assoc()) {
      $empresaMap[(int)$row['id_empresa']] = $row['nombre'];
    }
  }
}

// Traemos los productos (todos)
$sql = "SELECT id_producto, id_empresa, nombre, marca, modelo, precio_base, stock
        FROM productos
        ORDER BY id_producto DESC";
$rs = $conexion->query($sql);
?>
<h2>📦 Productos (Admin)</h2>

<div style="margin-bottom:12px;">
  <a href="#" onclick="cargarDirecto('administrador/productos/crear_producto.php')">➕ Crear producto</a>
</div>

<table border="1" cellspacing="0" cellpadding="6" style="width:100%; border-collapse:collapse;">
  <thead>
    <tr>
      <th>ID</th>
      <th>Empresa</th>
      <th>Nombre</th>
      <th>Marca</th>
      <th>Modelo</th>
      <th>Precio</th>
      <th>Stock</th>
      <th>Acciones</th>
    </tr>
  </thead>
  <tbody>
    <?php if ($rs && $rs->num_rows > 0): ?>
      <?php while($p = $rs->fetch_assoc()): ?>
        <tr>
          <td><?= (int)$p['id_producto'] ?></td>
          <td>
            <?php
              $idEmp = (int)$p['id_empresa'];
              echo htmlspecialchars($empresaMap[$idEmp] ?? ('Empresa #'.$idEmp));
            ?>
          </td>
          <td><?= htmlspecialchars($p['nombre']) ?></td>
          <td><?= htmlspecialchars($p['marca']) ?></td>
          <td><?= htmlspecialchars($p['modelo']) ?></td>
          <td><?= number_format((float)$p['precio_base'], 2) ?></td>
          <td><?= (int)$p['stock'] ?></td>
          <td>
            <a href="#"
               title="Editar"
               onclick="cargarDirecto('administrador/productos/editar_producto.php?id=<?= (int)$p['id_producto'] ?>'); return false;">✏️</a>
            &nbsp;
            <a href="#"
               title="Eliminar"
               onclick="if(!confirm('¿Eliminar este producto? Esta acción no se puede deshacer.')) return false;
                        cargarDirecto('administrador/productos/eliminar_producto.php?id=<?= (int)$p['id_producto'] ?>'); return false;">🗑️</a>
          </td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="8" style="text-align:center;">Sin productos registrados.</td></tr>
    <?php endif; ?>
  </tbody>
</table>
