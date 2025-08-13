<?php
// cliente/lista_clientes.php
define('APP_DEBUG', true);
if (APP_DEBUG) { ini_set('display_errors', 1); error_reporting(E_ALL); }

/* Rutas seguras */
$ROOT = dirname(__DIR__, 1);
require_once($ROOT . "/includes/verificar_rol.php");
require_once($ROOT . "/includes/conexion.php");

/* Acceso: vendedor(2) y analista(6) */
verificarRol([2,6]);
$conexion->set_charset("utf8mb4");

/* Solo vendedor puede crear/editar/inactivar */
$ROL = (int)($_SESSION['id_rol'] ?? 0);
$ES_VENDEDOR = ($ROL === 2);

/* Filtros y paginación */
$q        = trim($_GET['q'] ?? '');
$estado   = trim($_GET['estado'] ?? '');
$perPage  = 10;
$page     = max(1,(int)($_GET['page'] ?? 1));
$offset   = ($page - 1) * $perPage;

$where = ["1=1"];
$args  = [];
$types = "";

if ($q!=='') {
  $where[]="(c.nombre_comercial LIKE CONCAT('%',?,'%')
             OR c.correo LIKE CONCAT('%',?,'%')
             OR c.telefono LIKE CONCAT('%',?,'%')
             OR c.direccion LIKE CONCAT('%',?,'%'))";
  array_push($args,$q,$q,$q,$q);
  $types .= "ssss";
}
if ($estado!=='') {
  $where[]="c.estado = ?";
  $args[]=$estado; $types.="s";
}
$whereSql = implode(" AND ", $where);

/* Total */
$sqlCount = "SELECT COUNT(*) n FROM clientes c WHERE $whereSql";
$st = $conexion->prepare($sqlCount);
if ($types) $st->bind_param($types, ...$args);
$st->execute();
$total = (int)$st->get_result()->fetch_assoc()['n'];
$st->close();
$totalPages = max(1,(int)ceil($total/$perPage));

/* Página */
$sql = "SELECT c.id_cliente, c.nombre_comercial, c.cedula,
               c.correo, c.telefono, c.direccion, c.estado
        FROM clientes c
        WHERE $whereSql
        ORDER BY c.id_cliente DESC
        LIMIT ? OFFSET ?";
$args2=$args; $types2=$types."ii"; array_push($args2,$perPage,$offset);
$st=$conexion->prepare($sql);
$st->bind_param($types2, ...$args2);
$st->execute(); $rs=$st->get_result();
?>
<h2>Clientes</h2>

<form onsubmit="cargarDirecto('cliente/lista_clientes.php?'+new URLSearchParams(new FormData(this)).toString());return false;"
      style="margin:10px 0;display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
  <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Buscar por nombre, correo, teléfono o dirección" style="padding:6px;width:360px;">
  <select name="estado" style="padding:6px;">
    <option value="">-- Estado --</option>
    <?php foreach (['activo','inactivo'] as $opt): ?>
      <option value="<?= $opt ?>" <?= $estado===$opt?'selected':'' ?>><?= ucfirst($opt) ?></option>
    <?php endforeach; ?>
  </select>
  <button class="btn btn-primary" type="submit">Filtrar</button>
  <?php if ($ES_VENDEDOR): ?>
  <?php endif; ?>
</form>

<table border="1" cellspacing="0" cellpadding="6" style="width:100%;border-collapse:collapse;">
  <thead>
    <tr>
      <th>ID</th>
      <th>Nombre comercial</th>
      <th>Correo</th>
      <th>Teléfono</th>
      <th>Dirección</th>
      <th>Estado</th>
      <?php if ($ES_VENDEDOR): ?><th>Acciones</th><?php endif; ?>
    </tr>
  </thead>
  <tbody>
  <?php if($rs->num_rows===0): ?>
    <tr><td colspan="<?= $ES_VENDEDOR?7:6 ?>" style="text-align:center;color:#666">Sin resultados.</td></tr>
  <?php else: while($c=$rs->fetch_assoc()): ?>
    <tr>
      <td><?= (int)$c['id_cliente'] ?></td>
      <td><?= htmlspecialchars($c['nombre_comercial']) ?></td>
      <td><?= htmlspecialchars($c['correo'] ?: '—') ?></td>
      <td><?= htmlspecialchars($c['telefono'] ?: '—') ?></td>
      <td><?= htmlspecialchars($c['direccion'] ?: '—') ?></td>
      <td><?= $c['estado']==='activo'?'✅ Activo':'⚠️ Inactivo' ?></td>
      <?php if ($ES_VENDEDOR): ?>
      <td>
        <a href="#"
           onclick="cargarDirecto('cliente/editar_cliente.php?id=<?= (int)$c['id_cliente'] ?>&page=<?= $page ?>');return false;"
           title="Editar">✏️</a>
        &nbsp;
      </td>
      <?php endif; ?>
    </tr>
  <?php endwhile; endif; ?>
  </tbody>
</table>

<?php if ($totalPages>1): ?>
  <div style="margin:12px 0;display:flex;gap:8px;align-items:center;">
  <?php for($i=1;$i<=$totalPages;$i++): ?>
    <?php if($i===$page): ?><strong><?= $i ?></strong>
    <?php else: ?>
      <a href="#"
         onclick="cargarDirecto('cliente/lista_clientes.php?<?= http_build_query(['q'=>$q,'estado'=>$estado,'page'=>$i]) ?>');return false;"><?= $i ?></a>
    <?php endif; ?>
  <?php endfor; ?>
  </div>
<?php endif;

$st->close();
$conexion->close();
