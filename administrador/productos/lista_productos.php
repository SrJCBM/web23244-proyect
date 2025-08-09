<?php
require_once("../../includes/verificar_rol.php");
verificarRol([1]); // admin
require_once("../../includes/conexion.php");

/** Parámetros de paginación */
$perPage = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

/** Total de productos */
$total = 0;
$resCount = $conexion->query("SELECT COUNT(*) AS total FROM productos");
if ($resCount && $row = $resCount->fetch_assoc()) {
  $total = (int)$row['total'];
}
$totalPages = max(1, (int)ceil($total / $perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

/** Mapa de empresas (nombre por id) */
$empresaMap = [];
$q = $conexion->query("SELECT id_empresa, nombre FROM empresas_proveedoras ORDER BY nombre ASC");
if ($q instanceof mysqli_result) {
  while ($r = $q->fetch_assoc()) $empresaMap[(int)$r['id_empresa']] = $r['nombre'];
} else {
  $q2 = $conexion->query("SELECT id_empresa, COALESCE(nombre, nombre_empresa, razon_social) AS nombre FROM empresas ORDER BY nombre ASC");
  if ($q2 instanceof mysqli_result) {
    while ($r = $q2->fetch_assoc()) $empresaMap[(int)$r['id_empresa']] = $r['nombre'];
  }
}

/** Página de productos */
$stmt = $conexion->prepare("
  SELECT id_producto, id_empresa, nombre, marca, modelo, precio_base, stock
  FROM productos
  ORDER BY id_producto ASC
  LIMIT ? OFFSET ?
");
$stmt->bind_param("ii", $perPage, $offset);
$stmt->execute();
$rs = $stmt->get_result();

/** Helper para dibujar la paginación */
function renderPagination($page, $totalPages) {
  if ($totalPages <= 1) return;
  echo '<div style="margin:12px 0; display:flex; gap:8px; align-items:center;">';

  $prev = max(1, $page - 1);
  $next = min($totalPages, $page + 1);

  echo '<button onclick="cargarDirecto(\'administrador/productos/lista_productos.php?page='.$prev.'\')">&laquo; Anterior</button>';

  // Si hay pocas páginas, muéstralas todas; si no, muestra ventana alrededor
  $start = max(1, $page - 2);
  $end   = min($totalPages, $page + 2);
  if ($start > 1) {
    echo '<button onclick="cargarDirecto(\'administrador/productos/lista_productos.php?page=1\')">1</button>';
    if ($start > 2) echo '<span>…</span>';
  }
  for ($i = $start; $i <= $end; $i++) {
    if ($i == $page) {
      echo '<button disabled style="font-weight:bold;">'.$i.'</button>';
    } else {
      echo '<button onclick="cargarDirecto(\'administrador/productos/lista_productos.php?page='.$i.'\')">'.$i.'</button>';
    }
  }
  if ($end < $totalPages) {
    if ($end < $totalPages - 1) echo '<span>…</span>';
    echo '<button onclick="cargarDirecto(\'administrador/productos/lista_productos.php?page='.$totalPages.'\')">'.$totalPages.'</button>';
  }

  echo '<button onclick="cargarDirecto(\'administrador/productos/lista_productos.php?page='.$next.'\')">Siguiente &raquo;</button>';
  echo '</div>';
}
?>
<h2>📦 Productos (Admin)</h2>

<div style="margin-bottom:12px;">
  <a href="#" onclick="cargarDirecto('administrador/productos/crear_producto.php')">➕ Crear producto</a>
</div>

<?php renderPagination($page, $totalPages); ?>

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
          <td><?= htmlspecialchars($empresaMap[(int)$p['id_empresa']] ?? ('Empresa #'.(int)$p['id_empresa'])) ?></td>
          <td><?= htmlspecialchars($p['nombre']) ?></td>
          <td><?= htmlspecialchars($p['marca']) ?></td>
          <td><?= htmlspecialchars($p['modelo']) ?></td>
          <td><?= number_format((float)$p['precio_base'], 2) ?></td>
          <td><?= (int)$p['stock'] ?></td>
          <td>
            <a href="#"
               title="Editar"
               onclick="cargarDirecto('administrador/productos/editar_producto.php?id=<?= (int)$p['id_producto'] ?>&page=<?= $page ?>'); return false;">✏️</a>
            &nbsp;
            <a href="#"
               title="Eliminar"
               onclick="if(!confirm('¿Eliminar este producto? Esta acción no se puede deshacer.')) return false;
                        cargarDirecto('administrador/productos/eliminar_producto.php?id=<?= (int)$p['id_producto'] ?>&page=<?= $page ?>'); return false;">🗑️</a>
          </td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="8" style="text-align:center;">Sin productos en esta página.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<?php renderPagination($page, $totalPages); ?>

<?php
$stmt->close();
$conexion->close();
