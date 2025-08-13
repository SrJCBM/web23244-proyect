<?php
// cliente/editar_cliente.php
define('APP_DEBUG', true);
if (APP_DEBUG) {
  ini_set('display_errors', 1);
  error_reporting(E_ALL);
  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
}

$ROOT = dirname(__DIR__, 1);
require_once($ROOT . "/includes/verificar_rol.php");
require_once($ROOT . "/includes/conexion.php");
verificarRol([2]); // SOLO VENDEDOR

$conexion->set_charset("utf8mb4");

/* ===========================
   POST: guardar cambios
   =========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // ¿Vino desde la SPA?
  $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

  $id   = (int)($_POST['id'] ?? 0);
  $page = max(1, (int)($_POST['page'] ?? 1));

  if ($id <= 0) {
    if ($isAjax) { http_response_code(400); header('Content-Type: text/plain; charset=utf-8'); echo "ID inválido"; }
    else { echo "ID inválido"; }
    exit;
  }

  $nombre   = trim($_POST['nombre_comercial'] ?? '');
  $cedula   = trim($_POST['cedula'] ?? '');
  $correo   = trim($_POST['correo'] ?? '');
  $telefono = trim($_POST['telefono'] ?? '');
  $dir      = trim($_POST['direccion'] ?? '');
  $estado   = ($_POST['estado'] ?? '') === 'inactivo' ? 'inactivo' : 'activo';

  if ($nombre === '') {
    if ($isAjax) { http_response_code(422); header('Content-Type: text/plain; charset=utf-8'); echo "El nombre comercial es obligatorio."; }
    else { echo "<p style='color:#b91c1c'>El nombre comercial es obligatorio.</p>"; }
    exit;
  }
  if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    if ($isAjax) { http_response_code(422); header('Content-Type: text/plain; charset=utf-8'); echo "Correo no válido."; }
    else { echo "<p style='color:#b91c1c'>Correo no válido.</p>"; }
    exit;
  }

  $sql = "UPDATE clientes
          SET nombre_comercial=?, cedula=?, correo=?, telefono=?, direccion=?, estado=?
          WHERE id_cliente=? LIMIT 1";
  $st = $conexion->prepare($sql);
  $st->bind_param("ssssssi", $nombre, $cedula, $correo, $telefono, $dir, $estado, $id);
  $st->execute();

  // Respuesta
  if ($isAjax) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "OK";
    exit;
  } else {
    // Fallback por si alguien entra directo (no SPA)
    header("Location: lista_clientes.php?page={$page}&t=" . time());
    exit;
  }
}

/* ===========================
   GET: pintar formulario
   =========================== */
$id   = (int)($_GET['id'] ?? 0);
$page = max(1, (int)($_GET['page'] ?? 1));
if ($id <= 0) { echo "ID inválido"; exit; }

$stmt = $conexion->prepare("SELECT id_cliente, nombre_comercial, cedula, correo, telefono, direccion, estado
                            FROM clientes WHERE id_cliente=? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$cli = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$cli) { echo "Cliente no existe"; exit; }
?>
<h3>✏️ Editar cliente #<?= (int)$cli['id_cliente'] ?></h3>

<form
  id="formEditarCliente"
  method="post"
  action="cliente/editar_cliente.php"
  onsubmit="
    event.preventDefault();
    if(!confirm('¿Guardar cambios del cliente?')) return false;
    const fd = new FormData(this);
    const page = fd.get('page') || 1;
    fetch('cliente/editar_cliente.php', {
      method: 'POST',
      body: fd,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }, // marca que es SPA
      credentials: 'same-origin',
      cache: 'no-store'
    })
    .then(r => r.text())
    .then(t => {
      if (t.trim() !== 'OK') { alert('Error al guardar: ' + t); return; }
      cargarDirecto('cliente/lista_clientes.php?page=' + encodeURIComponent(page) + '&t=' + Date.now());
    })
    .catch(err => { console.error(err); alert('Error de red.'); });
    return false;
  "
  style="max-width:720px;display:grid;grid-template-columns:1fr 1fr;gap:12px;"
>
  <!-- Campos ocultos necesarios -->
  <input type="hidden" name="id"   value="<?= (int)$cli['id_cliente'] ?>">
  <input type="hidden" name="page" value="<?= (int)$page ?>">

  <label style="grid-column:1 / -1;">Nombre comercial
    <input type="text" name="nombre_comercial" value="<?= htmlspecialchars($cli['nombre_comercial']) ?>" required
           style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:8px;">
  </label>

  <label>Cédula/RUC
    <input type="text" name="cedula" value="<?= htmlspecialchars($cli['cedula']) ?>"
           style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:8px;">
  </label>

  <label>Correo
    <input type="email" name="correo" value="<?= htmlspecialchars($cli['correo']) ?>"
           style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:8px;">
  </label>

  <label>Teléfono
    <input type="text" name="telefono" value="<?= htmlspecialchars($cli['telefono']) ?>"
           style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:8px;">
  </label>

  <label style="grid-column:1 / -1;">Dirección
    <input type="text" name="direccion" value="<?= htmlspecialchars($cli['direccion']) ?>"
           style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:8px;">
  </label>

  <label>Estado
    <select name="estado" style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:8px;">
      <option value="activo"   <?= $cli['estado']==='activo'?'selected':'' ?>>activo</option>
      <option value="inactivo" <?= $cli['estado']==='inactivo'?'selected':'' ?>>inactivo</option>
    </select>
  </label>

  <div style="grid-column:1 / -1;display:flex;gap:10px;margin-top:6px;">
    <button class="btn btn-primary" type="submit">Guardar</button>
    <button type="button"
            onclick="cargarDirecto('cliente/lista_clientes.php?<?= http_build_query(['page'=>$page]) ?>&t='+Date.now())">
      Cancelar
    </button>
  </div>
</form>
