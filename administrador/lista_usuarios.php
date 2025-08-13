<?php
require_once("../includes/verificar_rol.php");
verificarRol([1]);

require_once("../includes/conexion.php");

// OPCIONAL: mostrar errores en desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/** ---------------------------------------
 * Helper de navegación después de acciones
 * -------------------------------------- */
function go($msg = '') {
  // Si prefieres volver directo a esta lista:
  // $url = "lista_usuarios.php";
  $url = "../index.php";
  if ($msg !== '') $url .= (strpos($url,'?')!==false ? '&' : '?') . 'msg=' . urlencode($msg);
  header("Location: $url");
  exit;
}

/** -----------------------
 * Paginación (4 por página)
 * ---------------------- */
$perPage = 4;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// Total de usuarios
$total = 0;
if ($rc = $conexion->query("SELECT COUNT(*) AS total FROM usuarios")) {
  $row = $rc->fetch_assoc();
  $total = (int)($row['total'] ?? 0);
}
$totalPages = max(1, (int)ceil($total / $perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

/** ---------------------------------------
 * Toggle estado (activo/inactivo) por GET
 * -------------------------------------- */
if (isset($_GET['toggle_estado'])) {
  $id = (int) $_GET['toggle_estado'];

  // No permitir que un usuario cambie su propio estado
  if ($id === (int)$_SESSION['id_usuario']) {
    go('yo_mismo');
  }

  // Obtener rol y estado actual del usuario objetivo
  $u = $conexion->prepare("SELECT id_rol, estado FROM usuarios WHERE id_usuario=? LIMIT 1");
  if (!$u) { error_log('prepare u: '.$conexion->error); go('err'); }
  $u->bind_param("i", $id);
  if (!$u->execute()) { error_log('exec u: '.$u->error); go('err'); }
  $u->bind_result($u_rol, $u_estado);
  $ok = $u->fetch();
  $u->close();

  if (!$ok) { go('notfound'); }

  // Proteger al último admin activo
  if ((int)$u_rol === 1 && $u_estado === 'activo') {
    $c = $conexion->query("SELECT COUNT(*) AS n FROM usuarios WHERE id_rol=1 AND estado='activo'");
    if (!$c) { error_log('count admins: '.$conexion->error); go('err'); }
    $row = $c->fetch_assoc();
    $admins_activos = (int)($row['n'] ?? 0);
    if ($admins_activos <= 1) {
      go('ultimo_admin');
    }
  }

  // Toggle
  $stmtT = $conexion->prepare("
    UPDATE usuarios
       SET estado = IF(estado='activo','inactivo','activo')
     WHERE id_usuario = ?
     LIMIT 1
  ");
  if (!$stmtT) { error_log('prepare upd: '.$conexion->error); go('err'); }
  $stmtT->bind_param("i", $id);
  if (!$stmtT->execute()) { error_log('exec upd: '.$stmtT->error); go('err'); }
  $stmtT->close();

  go('ok');
}

/** ----------------------------
 * Función para pintar paginación
 * --------------------------- */
function renderPaginationUsuarios($page, $totalPages){
  if ($totalPages <= 1) return;

  $prev = max(1, $page - 1);
  $next = min($totalPages, $page + 1);

  echo '<div class="pagination">';

  // Botón Anterior
  echo '<button '.($page==1?'disabled':'').' ';
  echo 'onclick="if(window.cargarDirecto){cargarDirecto(\'administrador/lista_usuarios.php?page='.$prev.'\');return false;}">';
  echo '&laquo; Anterior</button>';

  // Ventana de páginas (actual ±2)
  $start = max(1, $page - 2);
  $end   = min($totalPages, $page + 2);

  if ($start > 1) {
    echo '<a class="page" href="administrador/lista_usuarios.php?page=1" ';
    echo 'onclick="if(window.cargarDirecto){cargarDirecto(\'administrador/lista_usuarios.php?page=1\');return false;}">1</a>';
    if ($start > 2) echo '<span>…</span>';
  }

  for ($i = $start; $i <= $end; $i++) {
    if ($i == $page) {
      echo '<button disabled>'.$i.'</button>';
    } else {
      echo '<a class="page" href="administrador/lista_usuarios.php?page='.$i.'" ';
      echo 'onclick="if(window.cargarDirecto){cargarDirecto(\'administrador/lista_usuarios.php?page='.$i.'\');return false;}">'.$i.'</a>';
    }
  }

  if ($end < $totalPages) {
    if ($end < $totalPages - 1) echo '<span>…</span>';
    echo '<a class="page" href="administrador/lista_usuarios.php?page='.$totalPages.'" ';
    echo 'onclick="if(window.cargarDirecto){cargarDirecto(\'administrador/lista_usuarios.php?page='.$totalPages.'\');return false;}">'.$totalPages.'</a>';
  }

  // Botón Siguiente
  echo '<button '.($page==$totalPages?'disabled':'').' ';
  echo 'onclick="if(window.cargarDirecto){cargarDirecto(\'administrador/lista_usuarios.php?page='.$next.'\');return false;}">Siguiente &raquo;</button>';

  echo '</div>';
}

/** -----------------------------------
 * Consulta paginada de usuarios (4 pp)
 * ---------------------------------- */
$stmt = $conexion->prepare("
  SELECT u.id_usuario, u.nombre_completo, u.correo, u.nickname, u.estado, u.id_rol, r.nombre_rol
  FROM usuarios u
  LEFT JOIN roles r ON u.id_rol = r.id_rol
  ORDER BY u.nombre_completo
  LIMIT ? OFFSET ?
");
if (!$stmt) { echo "<p>Error al preparar consulta: ".htmlspecialchars($conexion->error)."</p>"; }
$stmt->bind_param("ii", $perPage, $offset);
$stmt->execute();
$resultado = $stmt->get_result();

/** -----------------------------------
 * Mensajes (opcional, via ?msg=...)
 * ---------------------------------- */
if (!empty($_GET['msg'])) {
  $msgs = [
    'ok'           => 'Estado actualizado.',
    'yo_mismo'     => 'No puedes cambiar tu propio estado.',
    'notfound'     => 'Usuario no encontrado.',
    'ultimo_admin' => 'No puedes desactivar/eliminar al último administrador activo.',
    'err'          => 'Ocurrió un error. Revisa el log del servidor.',
    'upd_ok'       => 'Usuario actualizado correctamente.',
    'del_ok'       => 'Usuario eliminado correctamente.',
    'no_delete_self' => 'No puedes eliminar tu propia cuenta.',
  ];
  if (isset($msgs[$_GET['msg']])) {
    echo '<p style="color:#0f766e;margin:8px 0;">'.htmlspecialchars($msgs[$_GET['msg']]).'</p>';
  }
}
?>

<h2>👥 Lista de Usuarios</h2>

<?php renderPaginationUsuarios($page, $totalPages); ?>

<table border="1" cellpadding="6" cellspacing="0" style="width:100%; border-collapse:collapse;">
  <tr>
    <th>Nombre</th>
    <th>Correo</th>
    <th>Rol</th>
    <th>Estado</th>
    <th>Activar/Inactivar</th>
    <th>Acciones</th>
  </tr>

  <?php if ($resultado && $resultado->num_rows > 0): ?>
    <?php while ($row = $resultado->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['nombre_completo']) ?></td>
        <td><?= htmlspecialchars($row['correo']) ?></td>
        <td><?= htmlspecialchars($row['nombre_rol'] ?: '—') ?></td>
        <td><?= $row['estado'] === 'activo' ? '✅ Activo' : '⛔ Inactivo' ?></td>
        <td>
          <?php if ((int)$_SESSION['id_usuario'] !== (int)$row['id_usuario']): ?>
            <a href="administrador/lista_usuarios.php?toggle_estado=<?= (int)$row['id_usuario'] ?>&page=<?= $page ?>&t=<?= time() ?>"
               onclick="return confirm('¿Confirmas el cambio de estado?')">
              <?= $row['estado'] === 'activo' ? '❌ Desactivar' : '✅ Activar' ?>
            </a>
          <?php else: ?>
            —
          <?php endif; ?>
        </td>
        <td>
          <a href="#"
             title="Editar"
             onclick="cargarDirecto('administrador/editar_usuario.php?id=<?= (int)$row['id_usuario'] ?>&page=<?= $page ?>'); return false;">✏️</a>
          &nbsp;
          <?php if ((int)$_SESSION['id_usuario'] !== (int)$row['id_usuario']): ?>
            <a href="#"
               title="Eliminar"
               onclick="if(!confirm('¿Eliminar este usuario? Esta acción no se puede deshacer.')) return false;
                        cargarDirecto('administrador/eliminar_usuario.php?id=<?= (int)$row['id_usuario'] ?>&page=<?= $page ?>'); return false;">🗑️</a>
          <?php else: ?>
            —
          <?php endif; ?>
        </td>
      </tr>
    <?php endwhile; ?>
  <?php else: ?>
    <tr><td colspan="6" style="text-align:center;">No hay usuarios en esta página.</td></tr>
  <?php endif; ?>
</table>

<?php renderPaginationUsuarios($page, $totalPages); ?>

<?php
$stmt->close();
$conexion->close();
