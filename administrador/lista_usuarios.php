<?php
// administrador/lista_usuarios.php
define('APP_DEBUG', true);
if (APP_DEBUG) { ini_set('display_errors', 1); error_reporting(E_ALL); }

require_once("../includes/verificar_rol.php");
// Solo admin puede ver/gestionar usuarios
verificarRol([1]);
require_once("../includes/conexion.php");

$conexion->set_charset("utf8mb4");

// ---- Filtros + paginación ----
$q        = trim($_GET['q'] ?? '');
$perPage  = 10;
$page     = max(1, (int)($_GET['page'] ?? 1));
$offset   = ($page - 1) * $perPage;

$where = ["1=1"];
$args  = [];
$types = "";

if ($q !== '') {
  $where[] = "(u.nombre_completo LIKE CONCAT('%',?,'%')
               OR u.correo LIKE CONCAT('%',?,'%')
               OR u.nickname LIKE CONCAT('%',?,'%'))";
  array_push($args, $q, $q, $q);
  $types .= "sss";
}
$whereSql = implode(" AND ", $where);

// ---- Toggle estado (seguro) ----
function goList($extra=''){
  $url = "administrador/lista_usuarios.php";
  if ($extra!=='') { $url .= (strpos($url,'?')!==false?'&':'?').$extra; }
  header("Location: ../index.php?view=$url");
  exit;
}
if (isset($_GET['toggle_estado'])) {
  $id = (int)$_GET['toggle_estado'];
  if ($id === (int)($_SESSION['id_usuario'] ?? 0)) { goList('msg=yo_mismo'); }

  // Rol/estado actual
  $st = $conexion->prepare("SELECT id_rol, estado FROM usuarios WHERE id_usuario=?");
  $st->bind_param("i",$id); $st->execute();
  $st->bind_result($rrol,$rest); $ok=$st->fetch(); $st->close();
  if (!$ok) goList('msg=notfound');

  // Proteger último admin activo
  if ((int)$rrol===1 && $rest==='activo') {
    $c = $conexion->query("SELECT COUNT(*) n FROM usuarios WHERE id_rol=1 AND estado='activo'");
    $n = (int)$c->fetch_assoc()['n'];
    if ($n<=1) goList('msg=ultimo_admin');
  }
  $up = $conexion->prepare("UPDATE usuarios SET estado=IF(estado='activo','inactivo','activo') WHERE id_usuario=? LIMIT 1");
  $up->bind_param("i",$id);
  $up->execute();
  $up->close();
  goList('msg=ok');
}

// ---- Total ----
$sqlCount = "SELECT COUNT(*) n
             FROM usuarios u
             LEFT JOIN roles r ON r.id_rol=u.id_rol
             WHERE $whereSql";
$stmt = $conexion->prepare($sqlCount);
if ($types) $stmt->bind_param($types, ...$args);
$stmt->execute();
$total = (int)$stmt->get_result()->fetch_assoc()['n'];
$stmt->close();
$totalPages = max(1, (int)ceil($total / $perPage));

// ---- Página ----
$sql = "SELECT u.id_usuario, u.nombre_completo, u.correo, u.nickname,
               u.estado, u.id_rol, COALESCE(r.nombre_rol,'—') AS rol
        FROM usuarios u
        LEFT JOIN roles r ON r.id_rol=u.id_rol
        WHERE $whereSql
        ORDER BY u.nombre_completo ASC
        LIMIT ? OFFSET ?";
$args2=$args; $types2=$types."ii"; array_push($args2,$perPage,$offset);
$stmt = $conexion->prepare($sql);
$stmt->bind_param($types2, ...$args2);
$stmt->execute();
$rs = $stmt->get_result();

// ---- Mensajes opcionales ----
$msg = $_GET['msg'] ?? '';
$MAP = [
  'ok'=>'Estado actualizado.',
  'yo_mismo'=>'No puedes cambiar tu propio estado.',
  'notfound'=>'Usuario no encontrado.',
  'ultimo_admin'=>'No puedes desactivar/eliminar al último administrador activo.',
  'upd_ok'=>'Usuario actualizado.',
  'del_ok'=>'Usuario eliminado.',
  'no_delete_self'=>'No puedes eliminar tu propia cuenta.'
];
?>
<h2>Usuarios</h2>

<?php if(isset($MAP[$msg])): ?>
  <p style="color:#0f766e;margin:8px 0;"><?= htmlspecialchars($MAP[$msg]) ?></p>
<?php endif; ?>

<!-- Filtros -->
<form onsubmit="cargarDirecto('administrador/lista_usuarios.php?'+new URLSearchParams(new FormData(this)).toString());return false;"
      style="margin:10px 0;display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
  <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Buscar por nombre, correo o usuario" style="padding:6px;width:320px;">
  <button class="btn btn-primary" type="submit">Buscar</button>
</form>

<table border="1" cellspacing="0" cellpadding="6" style="width:100%;border-collapse:collapse;">
  <thead>
    <tr>
      <th>Nombre</th>
      <th>Correo</th>
      <th>Usuario</th>
      <th>Rol</th>
      <th>Estado</th>
      <th>Activar/Inactivar</th>
      <th>Acciones</th>
    </tr>
  </thead>
  <tbody>
    <?php if($rs->num_rows===0): ?>
      <tr><td colspan="7" style="text-align:center;color:#666">Sin resultados.</td></tr>
    <?php else: while($u=$rs->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($u['nombre_completo']) ?></td>
        <td><?= htmlspecialchars($u['correo']) ?></td>
        <td><?= htmlspecialchars($u['nickname']) ?></td>
        <td><?= htmlspecialchars($u['rol']) ?></td>
        <td><?= $u['estado']==='activo'?'✅ Activo':'⚠️ Inactivo' ?></td>
        <td>
          <?php if ((int)$_SESSION['id_usuario'] !== (int)$u['id_usuario']): ?>
            <a href="administrador/lista_usuarios.php?toggle_estado=<?= (int)$u['id_usuario'] ?>&page=<?= $page ?>&t=<?= time() ?>"
               onclick="return confirm('¿Confirmas el cambio de estado?')">Cambiar</a>
          <?php else: ?>—<?php endif; ?>
        </td>
        <td>
          <a href="#"
             onclick="cargarDirecto('administrador/editar_usuario.php?id=<?= (int)$u['id_usuario'] ?>&page=<?= $page ?>');return false;" title="Editar">✏️</a>
          &nbsp;
          <?php if ((int)$_SESSION['id_usuario'] !== (int)$u['id_usuario']): ?>
            <a href="#"
               onclick="if(!confirm('¿Eliminar este usuario?'))return false;cargarDirecto('administrador/eliminar_usuario.php?id=<?= (int)$u['id_usuario'] ?>&page=<?= $page ?>');return false;"
               title="Eliminar">🗑️</a>
          <?php else: ?>—<?php endif; ?>
        </td>
      </tr>
    <?php endwhile; endif; ?>
  </tbody>
</table>

<?php if ($totalPages>1): ?>
  <div style="margin:12px 0;display:flex;gap:8px;align-items:center;">
  <?php for($i=1;$i<=$totalPages;$i++): ?>
    <?php if($i===$page): ?><strong><?= $i ?></strong>
    <?php else: ?>
      <a href="#" onclick="cargarDirecto('administrador/lista_usuarios.php?<?= http_build_query(['q'=>$q,'page'=>$i]) ?>');return false;"><?= $i ?></a>
    <?php endif; ?>
  <?php endfor; ?>
  </div>
<?php endif;

$stmt->close();
$conexion->close();
