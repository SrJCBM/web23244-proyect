<?php
require_once("../includes/verificar_rol.php");
verificarRol([1]);
require_once("../includes/conexion.php");

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pageActual = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
if ($id <= 0) { echo "<p style='color:red;'>ID inválido.</p>"; exit; }

// Obtener usuario
$u = $conexion->prepare("SELECT id_usuario, nombre_completo, correo, nickname, id_rol, estado FROM usuarios WHERE id_usuario=? LIMIT 1");
$u->bind_param("i", $id);
$u->execute();
$resU = $u->get_result();
if ($resU->num_rows === 0) { echo "<p style='color:red;'>Usuario no encontrado.</p>"; exit; }
$usuario = $resU->fetch_assoc();
$u->close();

// Obtener roles para el dropdown
$roles = $conexion->query("SELECT id_rol, nombre_rol FROM roles ORDER BY nombre_rol ASC");

// POST: actualizar
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $nombre   = $_POST["nombre_completo"] ?? '';
  $correo   = $_POST["correo"] ?? '';
  $nick     = $_POST["nickname"] ?? '';
  $id_rol   = (int)($_POST["id_rol"] ?? 0);
  $estado   = $_POST["estado"] ?? 'activo';

  // Protecciones: no auto-desactivarse
  if ($id === (int)$_SESSION['id_usuario'] && $estado !== 'activo') {
    echo "<p style='color:red;'>No puedes desactivar tu propia cuenta.</p>";
  } else {
    // Si el usuario actual es admin activo y lo convertimos en no-admin o inactivo → verificar último admin
    $esAdminAntes = ((int)$usuario['id_rol'] === 1 && $usuario['estado'] === 'activo');
    $seraAdminDespues = ($id_rol === 1 && $estado === 'activo');

    if ($esAdminAntes && !$seraAdminDespues) {
      // Contar admins activos
      $c = $conexion->query("SELECT COUNT(*) AS n FROM usuarios WHERE id_rol=1 AND estado='activo'");
      $rowC = $c->fetch_assoc(); $admins_activos = (int)($rowC['n'] ?? 0);
      if ($admins_activos <= 1) {
        echo "<p style='color:red;'>No puedes quitar el único administrador activo.</p>";
        // no continuar
      } else {
        $stmt = $conexion->prepare("
          UPDATE usuarios
             SET nombre_completo=?, correo=?, nickname=?, id_rol=?, estado=?
           WHERE id_usuario=? LIMIT 1
        ");
        $stmt->bind_param("sssisi", $nombre, $correo, $nick, $id_rol, $estado, $id);
        if ($stmt->execute()) {
          echo "<p style='color:green;'>✅ Usuario actualizado correctamente.</p>";
          echo "<a href=\"#\" onclick=\"cargarDirecto('administrador/lista_usuarios.php?page={$pageActual}')\">🔙 Volver a la lista</a>";
          $stmt->close(); $conexion->close(); exit;
        } else {
          echo "<p style='color:red;'>Error al actualizar: ".htmlspecialchars($stmt->error)."</p>";
        }
        $stmt->close();
      }
    } else {
      $stmt = $conexion->prepare("
        UPDATE usuarios
           SET nombre_completo=?, correo=?, nickname=?, id_rol=?, estado=?
         WHERE id_usuario=? LIMIT 1
      ");
      $stmt->bind_param("sssisi", $nombre, $correo, $nick, $id_rol, $estado, $id);
      if ($stmt->execute()) {
        echo "<p style='color:green;'>✅ Usuario actualizado correctamente.</p>";
        echo "<a href=\"#\" onclick=\"cargarDirecto('administrador/lista_usuarios.php?page={$pageActual}')\">🔙 Volver a la lista</a>";
        $stmt->close(); $conexion->close(); exit;
      } else {
        echo "<p style='color:red;'>Error al actualizar: ".htmlspecialchars($stmt->error)."</p>";
      }
      $stmt->close();
    }
  }
  // refrescar datos actuales en caso de error
  $usuario = [
    'id_usuario'      => $id,
    'nombre_completo' => $nombre,
    'correo'          => $correo,
    'nickname'        => $nick,
    'id_rol'          => $id_rol,
    'estado'          => $estado,
  ];
}

?>
<h2>✏️ Editar usuario #<?= (int)$usuario['id_usuario'] ?></h2>

<form method="post" onsubmit="
  event.preventDefault();
  const fd = new FormData(this);
  fetch('administrador/editar_usuario.php?id=<?= (int)$usuario['id_usuario'] ?>&page=<?= $pageActual ?>', { method:'POST', body:fd })
    .then(r => r.text())
    .then(html => { document.getElementById('contenido').innerHTML = html; })
    .catch(err => { console.error(err); alert('Error al guardar'); });
  return false;">
  <div class="form-row cols-2">
    <div>
      <label>Nombre completo</label>
      <input type="text" name="nombre_completo" value="<?= htmlspecialchars($usuario['nombre_completo']) ?>" required>
    </div>
    <div>
      <label>Correo</label>
      <input type="email" name="correo" value="<?= htmlspecialchars($usuario['correo']) ?>" required>
    </div>
    <div>
      <label>Nickname</label>
      <input type="text" name="nickname" value="<?= htmlspecialchars($usuario['nickname']) ?>" required>
    </div>
    <div>
      <label>Rol</label>
      <select name="id_rol" required>
        <?php while($r = $roles->fetch_assoc()): ?>
          <option value="<?= (int)$r['id_rol'] ?>" <?= ((int)$r['id_rol'] === (int)$usuario['id_rol']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($r['nombre_rol']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>
    <div>
      <label>Estado</label>
      <select name="estado" required>
        <option value="activo"   <?= $usuario['estado']==='activo' ? 'selected':'' ?>>Activo</option>
        <option value="inactivo" <?= $usuario['estado']==='inactivo' ? 'selected':'' ?>>Inactivo</option>
      </select>
    </div>
  </div>

  <div class="mt-2">
    <button class="btn-primary" type="submit">Guardar cambios</button>
    <button class="btn-ghost" type="button"
            onclick="cargarDirecto('administrador/lista_usuarios.php?page=<?= $pageActual ?>')">Cancelar</button>
  </div>
</form>
