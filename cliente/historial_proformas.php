<?php
session_start();
require_once("../includes/verificar_rol.php");
// Permite ver el historial a vendedor(2), admin(1), supervisor(5), analista(6)
verificarRol([2,1,5,6]);

require_once("../includes/conexion.php");
$conexion->set_charset("utf8mb4");

// === Rol: solo vendedor genera PDF ===
$ROL = (int)($_SESSION['id_rol'] ?? 0);
$PUEDE_PDF = ($ROL === 2); // solo vendedor

/* -------- Filtros -------- */
$q       = trim($_GET['q'] ?? '');
$estado  = trim($_GET['estado'] ?? '');
$perPage = 20;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

/* -------- Construcción del WHERE (no filtra por usuario) -------- */
$where = "1=1";

if ($q !== '') {
  $qEsc = $conexion->real_escape_string($q);

  // si viene "PRO-123" o "123" intenta extraer el número
  $idPro = 0;
  if (preg_match('/\d+/', preg_replace('/^PRO-/i','',$q), $m)) {
    $idPro = (int)$m[0];
  }

  $blocks = [];
  $blocks[] = "c.nombre_comercial LIKE '%{$qEsc}%'";
  $blocks[] = "cat.nombre LIKE '%{$qEsc}%'";
  // buscar por nombre de usuario también
  $blocks[] = "u.nombre_completo LIKE '%{$qEsc}%'";
  $blocks[] = "u.nickname LIKE '%{$qEsc}%'";
  if ($idPro > 0) $blocks[] = "p.id_cotizacion = {$idPro}";

  $where .= " AND (".implode(" OR ", $blocks).")";
}

if ($estado !== '') {
  $estadoEsc = $conexion->real_escape_string($estado);
  $where .= " AND p.estado = '{$estadoEsc}'";
}

/* -------- Total para paginación -------- */
$sqlCount = "
  SELECT COUNT(*) AS n
  FROM cotizaciones p
  JOIN clientes c          ON c.id_cliente     = p.id_cliente
  LEFT JOIN categorias cat ON cat.id_categoria = p.id_categoria
  LEFT JOIN usuarios  u    ON u.id_usuario     = p.id_usuario
  WHERE {$where}
";
$total = 0;
if ($resC = $conexion->query($sqlCount)) {
  $rowC  = $resC->fetch_assoc();
  $total = (int)$rowC['n'];
}
$totalPages = max(1, (int)ceil($total / $perPage));

/* -------- Página de resultados -------- */
$sql = "
  SELECT
    p.id_cotizacion,
    p.fecha_emision,
    p.total,
    p.estado,
    p.pdf_path,
    c.nombre_comercial                         AS cliente,
    COALESCE(cat.nombre,'')                    AS categoria,
    COALESCE(u.nombre_completo, u.nickname,
             CONCAT('USR#', u.id_usuario))     AS usuario_crea
  FROM cotizaciones p
  JOIN clientes c          ON c.id_cliente     = p.id_cliente
  LEFT JOIN categorias cat ON cat.id_categoria = p.id_categoria
  LEFT JOIN usuarios  u    ON u.id_usuario     = p.id_usuario
  WHERE {$where}
  ORDER BY p.fecha_emision DESC, p.id_cotizacion DESC
  LIMIT {$perPage} OFFSET {$offset}
";
$rs = $conexion->query($sql);
?>
<h2>Todas las proformas</h2>

<!-- Filtros -->
<form method="get"
      onsubmit="cargarDirecto('cliente/historial_proformas.php?'+new URLSearchParams(new FormData(this)).toString()); return false;"
      style="margin:10px 0; display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
  <input type="text" name="q" value="<?= htmlspecialchars($q) ?>"
         placeholder="Buscar: cliente, categoría, usuario o PRO-#"
         style="padding:6px; width:320px">
  <select name="estado" style="padding:6px">
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
    <th>Usuario</th>
    <th>Acciones</th>
  </tr>
  <?php if (!$rs || $rs->num_rows === 0): ?>
    <tr><td colspan="8" style="text-align:center; color:#666">Sin resultados.</td></tr>
  <?php else: ?>
    <?php while($r = $rs->fetch_assoc()): ?>
      <tr>
        <td>PRO-<?= (int)$r['id_cotizacion'] ?></td>
        <td><?= htmlspecialchars($r['fecha_emision']) ?></td>
        <td><?= htmlspecialchars($r['cliente']) ?></td>
        <td><?= htmlspecialchars($r['categoria']) ?></td>
        <td>$<?= number_format((float)$r['total'], 2) ?></td>
        <td><?= htmlspecialchars($r['estado']) ?></td>
        <td><?= htmlspecialchars($r['usuario_crea']) ?></td>
        <td>
          <a href="#"
             onclick="cargarDirecto('cliente/proforma_detalle.php?id=<?= (int)$r['id_cotizacion'] ?>'); return false;">Ver</a>
          <?php if ($PUEDE_PDF): ?>
            &nbsp;|&nbsp;
            <a href="cliente/proforma_pdf.php?id=<?= (int)$r['id_cotizacion'] ?>"
               target="_blank" rel="noopener">PDF</a>
          <?php endif; ?>
        </td>
      </tr>
    <?php endwhile; ?>
  <?php endif; ?>
</table>

<?php if ($totalPages > 1): ?>
  <div style="margin:12px 0; display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
    <?php for ($i=1; $i <= $totalPages; $i++): ?>
      <?php if ($i === $page): ?>
        <strong><?= $i ?></strong>
      <?php else: ?>
        <a href="#"
           onclick="cargarDirecto('cliente/historial_proformas.php?<?= http_build_query(['q'=>$q,'estado'=>$estado,'page'=>$i]) ?>'); return false;">
           <?= $i ?>
        </a>
      <?php endif; ?>
    <?php endfor; ?>
  </div>
<?php endif; ?>
