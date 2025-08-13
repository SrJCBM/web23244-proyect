<?php
require_once("../includes/verificar_rol.php");
verificarRol([1,5,6]); // Admin / otros roles con permiso

require_once("../includes/conexion.php");

// OPCIONAL: errores en desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/** -----------------------
 * Paginación (4 por página)
 * ---------------------- */
$perPage = 4;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// Total
$total = 0;
if ($rc = $conexion->query("SELECT COUNT(*) AS total FROM empresas_proveedoras")) {
  $row = $rc->fetch_assoc();
  $total = (int)($row['total'] ?? 0);
}
$totalPages = max(1, (int)ceil($total / $perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

/** ----------------------------
 * Función para pintar paginación
 * --------------------------- */
function renderPaginationEmpresas($page, $totalPages){
  if ($totalPages <= 1) return;

  $prev = max(1, $page - 1);
  $next = min($totalPages, $page + 1);

  echo '<div class="pagination">';

  // Anterior
  echo '<button '.($page==1?'disabled':'').' ';
  echo 'onclick="if(window.cargarDirecto){cargarDirecto(\'administrador/lista_empresas.php?page='.$prev.'\');return false;}">';
  echo '&laquo; Anterior</button>';

  // Ventana de páginas
  $start = max(1, $page - 2);
  $end   = min($totalPages, $page + 2);

  if ($start > 1) {
    echo '<a class="page" href="administrador/lista_empresas.php?page=1" ';
    echo 'onclick="if(window.cargarDirecto){cargarDirecto(\'administrador/lista_empresas.php?page=1\');return false;}">1</a>';
    if ($start > 2) echo '<span>…</span>';
  }
  for ($i = $start; $i <= $end; $i++) {
    if ($i == $page) {
      echo '<button disabled>'.$i.'</button>';
    } else {
      echo '<a class="page" href="administrador/lista_empresas.php?page='.$i.'" ';
      echo 'onclick="if(window.cargarDirecto){cargarDirecto(\'administrador/lista_empresas.php?page='.$i.'\');return false;}">'.$i.'</a>';
    }
  }
  if ($end < $totalPages) {
    if ($end < $totalPages - 1) echo '<span>…</span>';
    echo '<a class="page" href="administrador/lista_empresas.php?page='.$totalPages.'" ';
    echo 'onclick="if(window.cargarDirecto){cargarDirecto(\'administrador/lista_empresas.php?page='.$totalPages.'\');return false;}">'.$totalPages.'</a>';
  }

  // Siguiente
  echo '<button '.($page==$totalPages?'disabled':'').' ';
  echo 'onclick="if(window.cargarDirecto){cargarDirecto(\'administrador/lista_empresas.php?page='.$next.'\');return false;}">Siguiente &raquo;</button>';

  echo '</div>';
}

/** -----------------------------------
 * Consulta paginada de empresas (4 pp)
 * ---------------------------------- */
$stmt = $conexion->prepare("
  SELECT id_empresa, nombre, ruc, direccion, correo_contacto, telefono, estado, created_at
  FROM empresas_proveedoras
  ORDER BY created_at DESC
  LIMIT ? OFFSET ?
");
if (!$stmt) { echo "<p>Error al preparar consulta: ".htmlspecialchars($conexion->error)."</p>"; }
$stmt->bind_param("ii", $perPage, $offset);
$stmt->execute();
$resultado = $stmt->get_result();

?>
<h2>🏭 Empresas Proveedoras</h2>

<?php renderPaginationEmpresas($page, $totalPages); ?>

<?php if ($resultado && $resultado->num_rows > 0): ?>
  <table border="1" cellpadding="8" cellspacing="0" style="width:100%; border-collapse:collapse;">
    <tr>
      <th>Nombre</th>
      <th>RUC</th>
      <th>Dirección</th>
      <th>Correo</th>
      <th>Teléfono</th>
      <th>Estado</th>
      <th>Acciones</th>
      <th>Editar / Eliminar</th>
    </tr>
    <?php while ($row = $resultado->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row["nombre"]) ?></td>
        <td><?= htmlspecialchars($row["ruc"]) ?></td>
        <td><?= htmlspecialchars($row["direccion"]) ?></td>
        <td><?= htmlspecialchars($row["correo_contacto"]) ?></td>
        <td><?= htmlspecialchars($row["telefono"]) ?></td>
        <td><?= $row["estado"] === "activa" ? "🟢 Activa" : "🔴 Inactiva" ?></td>
        <td>
          <?php if ($row["estado"] === "activa"): ?>
            <button onclick="cambiarEstadoEmpresa(<?= (int)$row['id_empresa'] ?>, 'inactiva')">Inactivar</button>
          <?php else: ?>
            <button onclick="cambiarEstadoEmpresa(<?= (int)$row['id_empresa'] ?>, 'activa')">Reactivar</button>
          <?php endif; ?>
        </td>
        <td class="nowrap">
          <a href="#"
             title="Editar"
             onclick="cargarDirecto('administrador/editar_empresa.php?id=<?= (int)$row['id_empresa'] ?>&page=<?= $page ?>'); return false;">✏️</a>
          &nbsp;
          <a href="#"
             title="Eliminar"
             onclick="if(!confirm('¿Eliminar esta empresa? Esta acción no se puede deshacer.')) return false;
                      cargarDirecto('administrador/eliminar_empresa.php?id=<?= (int)$row['id_empresa'] ?>&page=<?= $page ?>'); return false;">🗑️</a>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>
<?php else: ?>
  <p>No hay empresas registradas.</p>
<?php endif; ?>

<?php renderPaginationEmpresas($page, $totalPages); ?>

<?php
$stmt->close();
$conexion->close();
?>

<script src="assets/js/empresa.js"></script>
