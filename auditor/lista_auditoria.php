<?php
// auditor/lista_auditoria.php
define('APP_DEBUG', true);
if (APP_DEBUG) { ini_set('display_errors', 1); error_reporting(E_ALL); }

// Calcula la raíz del proyecto y evita problemas de rutas relativas
$ROOT = dirname(__DIR__, 1); // sube 1 nivel desde /auditor
require_once($ROOT . "/includes/verificar_rol.php");
require_once($ROOT . "/includes/conexion.php");

// Permisos: admin(1), supervisor(5), auditor(4), analista(6)
verificarRol([1,5,4,6]);
$conexion->set_charset("utf8mb4");

// -------- Filtros --------
$q        = trim($_GET['q'] ?? '');
$entidad  = trim($_GET['entidad'] ?? '');
$accion   = trim($_GET['accion'] ?? '');
$actor    = (int)($_GET['actor'] ?? 0);

$perPage  = 20;
$page     = max(1, (int)($_GET['page'] ?? 1));
$offset   = ($page - 1) * $perPage;

$where = ["1=1"];
$args  = [];
$types = "";

if ($q !== '') {
  $where[] = "(a.entidad LIKE CONCAT('%',?,'%')
              OR a.accion LIKE CONCAT('%',?,'%')
              OR CAST(a.entidad_id AS CHAR) LIKE CONCAT('%',?,'%')
              OR CAST(a.detalle AS CHAR) LIKE CONCAT('%',?,'%'))";
  array_push($args, $q, $q, $q, $q);
  $types .= "ssss";
}
if ($entidad !== '') { $where[] = "a.entidad = ?"; $args[]=$entidad; $types.="s"; }
if ($accion  !== '') { $where[] = "a.accion  = ?"; $args[]=$accion;  $types.="s"; }
if ($actor   >  0)   { $where[] = "a.actor_id = ?"; $args[]=$actor;   $types.="i"; }

$whereSql = implode(" AND ", $where);

// -------- Total --------
$sqlC = "SELECT COUNT(*) n
         FROM auditoria a
         LEFT JOIN usuarios u ON u.id_usuario=a.actor_id
         WHERE $whereSql";
$st = $conexion->prepare($sqlC);
if ($types) $st->bind_param($types, ...$args);
$st->execute();
$total = (int)($st->get_result()->fetch_assoc()['n'] ?? 0);
$st->close();
$totalPages = max(1, (int)ceil($total / $perPage));

// -------- Página --------
$sql = "SELECT a.id_audit, a.fecha, a.actor_id, a.accion, a.entidad, a.entidad_id, a.detalle,
               COALESCE(u.nombre_completo, u.nickname, CONCAT('USR#',a.actor_id)) AS actor_nombre
        FROM auditoria a
        LEFT JOIN usuarios u ON u.id_usuario=a.actor_id
        WHERE $whereSql
        ORDER BY a.fecha DESC, a.id_audit DESC
        LIMIT ? OFFSET ?";
$args2 = $args; $types2 = $types . "ii";
array_push($args2, $perPage, $offset);

$st = $conexion->prepare($sql);
$st->bind_param($types2, ...$args2);
$st->execute();
$rs = $st->get_result();
?>
<h2>Logs de actividades</h2>

<form onsubmit="cargarDirecto('auditor/lista_auditoria.php?'+new URLSearchParams(new FormData(this)).toString());return false;"
      style="margin:10px 0;display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
  <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Buscar (incluye detalle JSON)" style="padding:6px;width:300px;">
  <select name="entidad" style="padding:6px;">
    <option value="">-- Entidad --</option>
    <?php foreach (['producto','opcion','usuario','cliente','empresa','categoria','proforma'] as $e): ?>
      <option value="<?= $e ?>" <?= $entidad===$e?'selected':'' ?>><?= $e ?></option>
    <?php endforeach; ?>
  </select>
  <select name="accion" style="padding:6px;">
    <option value="">-- Acción --</option>
    <?php foreach (['crear','editar','eliminar','inactivar','activar','cambiar_estado'] as $a): ?>
      <option value="<?= $a ?>" <?= $accion===$a?'selected':'' ?>><?= $a ?></option>
    <?php endforeach; ?>
  </select>
  <input type="number" name="actor" value="<?= $actor>0?(int)$actor:'' ?>" placeholder="ID actor" style="padding:6px;width:110px;">
  <button class="btn btn-primary" type="submit">Filtrar</button>
</form>

<table border="1" cellspacing="0" cellpadding="6" style="width:100%;border-collapse:collapse;">
  <thead>
    <tr>
      <th>Fecha</th>
      <th>Actor</th>
      <th>Entidad</th>
      <th>ID</th>
      <th>Acción</th>
      <th>Detalle</th>
    </tr>
  </thead>
  <tbody>
  <?php if (!$rs || $rs->num_rows===0): ?>
    <tr><td colspan="6" style="text-align:center;color:#666">Sin resultados.</td></tr>
  <?php else: while($r=$rs->fetch_assoc()): ?>
    <tr>
      <td><?= htmlspecialchars($r['fecha']) ?></td>
      <td><?= htmlspecialchars($r['actor_nombre'] ?? '—') ?><?= $r['actor_id']? " (ID ".$r['actor_id'].")":"" ?></td>
      <td><?= htmlspecialchars($r['entidad']) ?></td>
      <td><?= (int)$r['entidad_id'] ?></td>
      <td><?= htmlspecialchars($r['accion']) ?></td>
      <td><pre style="white-space:pre-wrap;margin:0;max-width:680px;overflow:auto;"><?= htmlspecialchars($r['detalle']) ?></pre></td>
    </tr>
  <?php endwhile; endif; ?>
  </tbody>
</table>

<?php if ($totalPages>1): ?>
  <div style="margin:12px 0;display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
    <?php for($i=1;$i<=$totalPages;$i++): ?>
      <?php if ($i===$page): ?>
        <strong><?= $i ?></strong>
      <?php else: ?>
        <a href="#"
           onclick="cargarDirecto('auditor/lista_auditoria.php?<?= http_build_query(['q'=>$q,'entidad'=>$entidad,'accion'=>$accion,'actor'=>$actor,'page'=>$i]) ?>');return false;"><?= $i ?></a>
      <?php endif; ?>
    <?php endfor; ?>
  </div>
<?php endif;

$st->close();
$conexion->close();
