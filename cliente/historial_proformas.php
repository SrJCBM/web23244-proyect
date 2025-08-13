<?php
session_start();
require_once("../includes/verificar_rol.php"); verificarRol([2,1,5]);
require_once("../includes/conexion.php");

$idUsuario = $_SESSION['id_usuario'] ?? 0;

// ---- Filtros (GET) ----
$q       = trim($_GET['q'] ?? '');        // texto libre: cliente, categoría, PRO-#, etc
$estado  = trim($_GET['estado'] ?? '');   // borrador | emitida | enviada | ...
$perPage = 20;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

// Armar WHERE dinámico
$where = ["p.id_usuario = ?"];
$args  = [$idUsuario];
$types = "i";

if ($q !== '') {
  // buscar por cliente, categoría o id de proforma
  $where[] = "(c.nombre_comercial LIKE CONCAT('%',?,'%')
               OR cat.nombre LIKE CONCAT('%',?,'%')
               OR p.id_cotizacion = CAST(REPLACE(UPPER(?),'PRO-','') AS UNSIGNED))";
  array_push($args, $q, $q, $q);
  $types .= "sss";
}

if ($estado !== '') {
  $where[] = "p.estado = ?";
  $args[]  = $estado;
  $types  .= "s";
}

$whereSql = implode(" AND ", $where);

// ---- Total para paginación ----
$sqlCount = "
  SELECT COUNT(*) AS n
  FROM cotizaciones p
  JOIN clientes c ON c.id_cliente=p.id_cliente
  LEFT JOIN categorias cat ON cat.id_categoria=p.id_categoria
  WHERE $whereSql
";
$stmt = $conexion->prepare($sqlCount);
$stmt->bind_param($types, ...$args);
$stmt->execute();
$total = (int)$stmt->get_result()->fetch_assoc()['n'];
$stmt->close();

$totalPages = max(1, (int)ceil($total / $perPage));

// ---- Página de resultados ----
$sql = "
  SELECT p.id_cotizacion, p.fecha_emision, p.total, p.estado, p.pdf_path,
         c.nombre_comercial AS cliente,
         COALESCE(cat.nombre,'') AS categoria
  FROM cotizaciones p
  JOIN clientes c ON c.id_cliente=p.id_cliente
  LEFT JOIN categorias cat ON cat.id_categoria=p.id_categoria
  WHERE $whereSql
  ORDER BY p.fecha_emision DESC
  LIMIT ? OFFSET ?
";
$args2  = array_merge($args, [$perPage, $offset]);
$types2 = $types . "ii";

$stmt = $conexion->prepare($sql);
$stmt->bind_param($types2, ...$args2);
$stmt->execute();
$rs = $stmt->get_result();
?>

<h2>Mis proformas</h2>

<!-- Filtros -->
<form method="get" onsubmit="cargarDirecto('cliente/historial_proformas.php?'+new URLSearchParams(new FormData(this)).toString()); return false;"
      style="margin:10px 0; display:flex; gap:10px; align-items:center;">
  <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Buscar: cliente, categoría o PRO-#"
         style="padding:6px; width:320px;">
  <select name="estado" style="padding:6px;">
    <option value="">-- Estado --</option>
    <?php foreach (['borrador','emitida','enviada','activa','aceptada','rechazada','cancelada'] as $opt): ?>
      <option value="<?= $opt ?>" <?= $estado===$opt?'selected':'' ?>><?= $opt ?></option>
    <?php endforeach; ?>
  </select>
  <button class="btn btn-primary" type="submit">Buscar</button>
</form>

<table border="1" cellpadding="6" cellspacing="0" style="width:100%;border-collapse:collapse">
  <tr>
    <th>PRO-#</th>
    <th>Fecha</th>
    <th>Cliente</th>
    <th>Categoría</th>
    <th>Total</th>
    <th>Estado</th>
    <th>Acciones</th>
  </tr>
  <?php if ($rs->num_rows === 0): ?>
    <tr><td colspan="7" style="text-align:center; color:#666">Sin resultados.</td></tr>
  <?php else: ?>
    <?php while($r=$rs->fetch_assoc()): ?>
      <tr>
        <td>PRO-<?= (int)$r['id_cotizacion'] ?></td>
        <td><?= htmlspecialchars($r['fecha_emision']) ?></td>
        <td><?= htmlspecialchars($r['cliente']) ?></td>
        <td><?= htmlspecialchars($r['categoria']) ?></td>
        <td>$<?= number_format($r['total'],2) ?></td>
        <td><?= htmlspecialchars($r['estado']) ?></td>
        <td>
          <!-- Ver detalle dentro del SPA -->
          <a href="#" onclick="cargarDirecto('cliente/proforma_detalle.php?id=<?= (int)$r['id_cotizacion'] ?>'); return false;">Ver</a>
          &nbsp;|&nbsp;
          <!-- PDF EN PESTAÑA NUEVA (NO USAR cargarDirecto para PDF) -->
          <a href="cliente/proforma_pdf.php?id=<?= (int)$r['id_cotizacion'] ?>" target="_blank" rel="noopener">PDF</a>
        </td>
      </tr>
    <?php endwhile; ?>
  <?php endif; ?>
</table>

<!-- Paginación -->
<?php if ($totalPages > 1): ?>
  <div style="margin:12px 0; display:flex; gap:8px; align-items:center;">
    <?php for ($i=1;$i<=$totalPages;$i++): ?>
      <?php if ($i === $page): ?>
        <strong><?= $i ?></strong>
      <?php else: ?>
        <a href="#" onclick="cargarDirecto('cliente/historial_proformas.php?<?= http_build_query(['q'=>$q,'estado'=>$estado,'page'=>$i]) ?>'); return false;"><?= $i ?></a>
      <?php endif; ?>
    <?php endfor; ?>
  </div>
<?php endif; ?>

