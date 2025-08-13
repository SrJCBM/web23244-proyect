<?php
// administrador/lista_empresas.php
define('APP_DEBUG', true);
if (APP_DEBUG) { ini_set('display_errors', 1); error_reporting(E_ALL); }

require_once("../includes/verificar_rol.php");
// Pueden ver: admin(1), vendedor(2), supervisor(5), analista(6)
verificarRol([1,2,5,6]);
require_once("../includes/conexion.php");

$conexion->set_charset("utf8mb4");

$ROL = (int)($_SESSION['id_rol'] ?? 0);
$ES_ADMIN = in_array($ROL,[1,5]); // admin o supervisor -> CRUD

// ---- Filtros + paginación ----
$q        = trim($_GET['q'] ?? '');
$perPage  = 10;
$page     = max(1, (int)($_GET['page'] ?? 1));
$offset   = ($page - 1) * $perPage;

$where = ["1=1"];
$args  = [];
$types = "";

if ($q !== '') {
  $where[] = "(e.nombre LIKE CONCAT('%',?,'%')
               OR e.ruc LIKE CONCAT('%',?,'%')
               OR e.direccion LIKE CONCAT('%',?,'%')
               OR e.correo_contacto LIKE CONCAT('%',?,'%')
               OR e.telefono LIKE CONCAT('%',?,'%'))";
  array_push($args,$q,$q,$q,$q,$q);
  $types .= "sssss";
}
$whereSql = implode(" AND ", $where);

// ---- Total ----
$sqlCount = "SELECT COUNT(*) n FROM empresas_proveedoras e WHERE $whereSql";
$stmt = $conexion->prepare($sqlCount);
if ($types) $stmt->bind_param($types, ...$args);
$stmt->execute();
$total = (int)$stmt->get_result()->fetch_assoc()['n'];
$stmt->close();
$totalPages = max(1, (int)ceil($total / $perPage));

// ---- Página ----
$sql = "SELECT id_empresa, nombre, ruc, direccion, correo_contacto, telefono, estado, created_at
        FROM empresas_proveedoras e
        WHERE $whereSql
        ORDER BY created_at DESC, id_empresa DESC
        LIMIT ? OFFSET ?";
$args2=$args; $types2=$types."ii"; array_push($args2,$perPage,$offset);
$stmt = $conexion->prepare($sql);
$stmt->bind_param($types2, ...$args2);
$stmt->execute();
$rs = $stmt->get_result();
?>
<h2>Empresas Proveedoras</h2>

<!-- Filtros -->
<form onsubmit="cargarDirecto('administrador/lista_empresas.php?'+new URLSearchParams(new FormData(this)).toString());return false;"
      style="margin:10px 0;display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
  <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Buscar por nombre, RUC, dirección, correo o teléfono" style="padding:6px;width:360px;">
  <button class="btn btn-primary" type="submit">Buscar</button>
  <?php if ($ES_ADMIN): ?>
    <a href="#" onclick="cargarDirecto('administrador/registro_empresa.php');return false;">➕ Crear empresa</a>
  <?php endif; ?>
</form>

<table border="1" cellpadding="8" cellspacing="0" style="width:100%;border-collapse:collapse;">
  <thead>
    <tr>
      <th>Nombre</th>
      <th>RUC</th>
      <th>Dirección</th>
      <th>Correo</th>
      <th>Teléfono</th>
      <th>Estado</th>
      <?php if ($ES_ADMIN): ?><th>Acciones</th><?php endif; ?>
    </tr>
  </thead>
  <tbody>
    <?php if($rs->num_rows===0): ?>
      <tr><td colspan="<?= $ES_ADMIN?7:6 ?>" style="text-align:center;color:#666">Sin resultados.</td></tr>
    <?php else: while($e=$rs->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($e['nombre']) ?></td>
        <td><?= htmlspecialchars($e['ruc']) ?></td>
        <td><?= htmlspecialchars($e['direccion']) ?></td>
        <td><?= htmlspecialchars($e['correo_contacto']) ?></td>
        <td><?= htmlspecialchars($e['telefono']) ?></td>
        <td><?= $e['estado']==='activa'?'🟢 Activa':'🔴 Inactiva' ?></td>
        <?php if ($ES_ADMIN): ?>
        <td class="nowrap">
          <a href="#"
             title="Editar"
             onclick="cargarDirecto('administrador/editar_empresa.php?id=<?= (int)$e['id_empresa'] ?>&page=<?= $page ?>');return false;">✏️</a>
          &nbsp;
          <a href="#"
             title="Eliminar"
             onclick="if(!confirm('¿Eliminar esta empresa?'))return false;cargarDirecto('administrador/eliminar_empresa.php?id=<?= (int)$e['id_empresa'] ?>&page=<?= $page ?>');return false;">🗑️</a>
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
      <a href="#" onclick="cargarDirecto('administrador/lista_empresas.php?<?= http_build_query(['q'=>$q,'page'=>$i]) ?>');return false;"><?= $i ?></a>
    <?php endif; ?>
  <?php endfor; ?>
  </div>
<?php endif;

$stmt->close();
$conexion->close();
