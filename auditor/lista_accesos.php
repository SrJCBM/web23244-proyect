<?php
require_once("../includes/verificar_rol.php");
verificarRol([1,4]); // admin o auditor
require_once("../includes/conexion.php");

$perPage = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// Filtros
$f_usuario = isset($_GET['usuario']) ? trim($_GET['usuario']) : '';
$f_exito   = isset($_GET['exito']) && $_GET['exito'] !== '' ? (int)$_GET['exito'] : null; // 1/0
$f_desde   = isset($_GET['desde']) ? trim($_GET['desde']) : '';
$f_hasta   = isset($_GET['hasta']) ? trim($_GET['hasta']) : '';

$wheres = [];
$params = [];
$types  = "";

// Filtrar por nombre/nickname/correo o id
if ($f_usuario !== '') {
  $wheres[] = "(u.nombre_completo LIKE CONCAT('%', ?, '%')
             OR u.nickname LIKE CONCAT('%', ?, '%')
             OR u.correo   LIKE CONCAT('%', ?, '%')
             OR a.username_intentado LIKE CONCAT('%', ?, '%')
             OR a.id_usuario = CAST(? AS UNSIGNED))";
  $params[] = $f_usuario; $params[] = $f_usuario; $params[] = $f_usuario; $params[] = $f_usuario; $params[] = $f_usuario;
  $types   .= "sssss";
}
if ($f_exito !== null) {
  $wheres[] = "a.exito = ?";
  $params[] = $f_exito;
  $types   .= "i";
}
if ($f_desde !== '') {
  $wheres[] = "a.inicio >= ?";
  $params[] = $f_desde . " 00:00:00";
  $types   .= "s";
}
if ($f_hasta !== '') {
  $wheres[] = "a.inicio <= ?";
  $params[] = $f_hasta . " 23:59:59";
  $types   .= "s";
}

$whereSql = $wheres ? ("WHERE " . implode(" AND ", $wheres)) : "";

// Total
$sqlCount = "SELECT COUNT(*) AS total
             FROM aud_accesos a
             LEFT JOIN usuarios u ON u.id_usuario = a.id_usuario
             $whereSql";
$stmt = $conexion->prepare($sqlCount);
if ($types !== "") $stmt->bind_param($types, ...$params);
$stmt->execute();
$total = (int)($stmt->get_result()->fetch_assoc()['total'] ?? 0);
$stmt->close();

$totalPages = max(1, (int)ceil($total / $perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

// Página
$sql = "SELECT a.*, u.nombre_completo, u.nickname, u.correo
        FROM aud_accesos a
        LEFT JOIN usuarios u ON u.id_usuario = a.id_usuario
        $whereSql
        ORDER BY a.inicio DESC
        LIMIT ? OFFSET ?";
$stmt = $conexion->prepare($sql);

if ($types !== "") {
  $typesPage = $types . "ii";
  $paramsPage = array_merge($params, [$perPage, $offset]);
  $stmt->bind_param($typesPage, ...$paramsPage);
} else {
  $stmt->bind_param("ii", $perPage, $offset);
}

$stmt->execute();
$rs = $stmt->get_result();

function renderPagination($page, $totalPages) {
  if ($totalPages <= 1) return;
  $prev = max(1, $page - 1); $next = min($totalPages, $page + 1);
  echo '<div style="margin:12px 0; display:flex; gap:8px; align-items:center;">';
  echo '<button onclick="cargarDirecto(\'auditor/lista_accesos.php?page='.$prev.'&'.http_build_query($_GET).'\')">&laquo; Anterior</button>';
  for ($i = max(1,$page-2); $i <= min($totalPages,$page+2); $i++) {
    echo '<button '.($i==$page?'disabled style="font-weight:bold;"':'').' onclick="cargarDirecto(\'auditor/lista_accesos.php?page='.$i.'&'.http_build_query($_GET).'\')">'.$i.'</button>';
  }
  echo '<button onclick="cargarDirecto(\'auditor/lista_accesos.php?page='.$next.'&'.http_build_query($_GET).'\')">Siguiente &raquo;</button>';
  echo '</div>';
}
?>
<h2>Historial de accesos</h2>

<form onsubmit="event.preventDefault(); const q=new URLSearchParams(new FormData(this)).toString(); cargarDirecto('auditor/lista_accesos.php?'+q);" style="display:grid; grid-template-columns: repeat(5,1fr); gap:8px; margin-bottom:12px;">
  <input type="text"   name="usuario" placeholder="Usuario/correo/nickname/id" value="<?= htmlspecialchars($f_usuario) ?>">
  <select name="exito">
    <option value="">(Todos)</option>
    <option value="1" <?= $f_exito===1?'selected':''?>>Éxito</option>
    <option value="0" <?= $f_exito===0?'selected':''?>>Fallido</option>
  </select>
  <input type="date"   name="desde" value="<?= htmlspecialchars($f_desde) ?>">
  <input type="date"   name="hasta" value="<?= htmlspecialchars($f_hasta) ?>">
  <button type="submit">Filtrar</button>
</form>

<?php renderPagination($page, $totalPages); ?>

<table border="1" cellspacing="0" cellpadding="6" style="width:100%; border-collapse:collapse;">
  <thead>
    <tr>
      <th>Inicio</th>
      <th>Fin</th>
      <th>Duración</th>
      <th>Éxito</th>
      <th>Motivo</th>
      <th>Usuario</th>
      <th>Username intentado</th>
      <th>IP</th>
      <th>User-Agent</th>
      <th>Sesión</th>
    </tr>
  </thead>
  <tbody>
  <?php if ($rs && $rs->num_rows): ?>
    <?php while($a = $rs->fetch_assoc()): 
      $dur = ($a['fin']) ? (strtotime($a['fin']) - strtotime($a['inicio'])) : null;
      $durTxt = $dur!==null ? gmdate("H:i:s", max(0,$dur)) : '—';
      $uTxt = $a['nombre_completo'] ? ($a['nombre_completo'].' ('.$a['nickname'].')') : '—';
    ?>
      <tr>
        <td><?= htmlspecialchars($a['inicio']) ?></td>
        <td><?= htmlspecialchars($a['fin'] ?? '—') ?></td>
        <td><?= $durTxt ?></td>
        <td><?= $a['exito'] ? '✔' : '✖' ?></td>
        <td><?= htmlspecialchars($a['motivo'] ?? '') ?></td>
        <td><?= htmlspecialchars($uTxt) ?></td>
        <td><?= htmlspecialchars($a['username_intentado'] ?? '—') ?></td>
        <td><?= htmlspecialchars($a['ip']) ?></td>
        <td><?= htmlspecialchars($a['user_agent']) ?></td>
        <td><small><?= htmlspecialchars($a['sesion_id']) ?></small></td>
      </tr>
    <?php endwhile; ?>
  <?php else: ?>
      <tr><td colspan="10" style="text-align:center;">Sin datos</td></tr>
  <?php endif; ?>
  </tbody>
</table>

<?php renderPagination($page, $totalPages); ?>

<?php
$stmt->close();
$conexion->close();
