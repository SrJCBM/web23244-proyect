<?php
require_once("../includes/verificar_rol.php");
verificarRol([1]);

require_once("../includes/conexion.php");

// OPCIONAL: mostrar errores en desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function go($msg = '') {
  $url = "../index.php"; // Ruta al index
  if ($msg !== '') $url .= '?msg=' . urlencode($msg);
  header("Location: $url");
  exit;
}


if (isset($_GET['toggle_estado'])) {
  $id = (int) $_GET['toggle_estado'];

  if ($id === (int)$_SESSION['id_usuario']) {
    go('yo_mismo');
  }

  $u = $conexion->prepare("SELECT id_rol, estado FROM usuarios WHERE id_usuario=? LIMIT 1");
  if (!$u) { error_log('prepare u: '.$conexion->error); go('err'); }
  $u->bind_param("i", $id);
  if (!$u->execute()) { error_log('exec u: '.$u->error); go('err'); }
  $u->bind_result($u_rol, $u_estado);
  $ok = $u->fetch();
  $u->close();

  if (!$ok) {
    go('notfound');
  }

  if ((int)$u_rol === 1 && $u_estado === 'activo') {
    $c = $conexion->query("SELECT COUNT(*) AS n FROM usuarios WHERE id_rol=1 AND estado='activo'");
    if (!$c) { error_log('count admins: '.$conexion->error); go('err'); }
    $row = $c->fetch_assoc();
    $admins_activos = (int)($row['n'] ?? 0);
    if ($admins_activos <= 1) {
      go('ultimo_admin');
    }
  }

  $stmt = $conexion->prepare("
    UPDATE usuarios
       SET estado = IF(estado='activo','inactivo','activo')
     WHERE id_usuario = ?
     LIMIT 1
  ");
  if (!$stmt) { error_log('prepare upd: '.$conexion->error); go('err'); }
  $stmt->bind_param("i", $id);
  if (!$stmt->execute()) { error_log('exec upd: '.$stmt->error); go('err'); }
  $stmt->close();

  go('ok');
}

$sql = "
SELECT u.id_usuario, u.nombre_completo, u.correo, u.nickname, u.estado, u.id_rol, r.nombre_rol
FROM usuarios u
LEFT JOIN roles r ON u.id_rol = r.id_rol
ORDER BY u.nombre_completo";
$resultado = $conexion->query($sql);
if (!$resultado) { echo "<p>Error al listar: ".htmlspecialchars($conexion->error)."</p>"; }
?>

<?php
if (!empty($_GET['msg'])) {
  $msgs = [
    'ok'           => 'Estado actualizado.',
    'yo_mismo'     => 'No puedes cambiar tu propio estado.',
    'notfound'     => 'Usuario no encontrado.',
    'ultimo_admin' => 'No puedes desactivar al último administrador activo.',
    'err'          => 'Ocurrió un error. Revisa el log del servidor.',
  ];
  if (isset($msgs[$_GET['msg']])) {
    echo '<p style="color:#0f766e;margin:8px 0;">'.htmlspecialchars($msgs[$_GET['msg']]).'</p>';
  }
}
?>

<h2>👥 Lista de Usuarios</h2>

<table border="1" cellpadding="6">
  <tr>
    <th>Nombre</th>
    <th>Correo</th>
    <th>Rol</th>
    <th>Estado</th>
    <th>Activar/Inactivar</th>
  </tr>
  <?php if ($resultado) while ($row = $resultado->fetch_assoc()): ?>
    <tr>
      <td><?= htmlspecialchars($row['nombre_completo']) ?></td>
      <td><?= htmlspecialchars($row['correo']) ?></td>
      <td><?= htmlspecialchars($row['nombre_rol'] ?: '—') ?></td>
      <td><?= $row['estado'] === 'activo' ? '✅ Activo' : '⛔ Inactivo' ?></td>
      <td>
        <?php if ((int)$_SESSION['id_usuario'] !== (int)$row['id_usuario']): ?>
      <a href="administrador/lista_usuarios.php?toggle_estado=<?= (int)$row['id_usuario'] ?>&t=<?= time() ?>"
        onclick="return confirm('¿Confirmas el cambio de estado?')">
        <?= $row['estado'] === 'activo' ? '❌ Desactivar' : '✅ Activar' ?>
      </a>
        <?php else: ?>
          —
        <?php endif; ?>
      </td>
    </tr>
  <?php endwhile; ?>
</table>
