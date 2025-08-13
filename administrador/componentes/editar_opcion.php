<?php
// administrador/componentes/editar_opcion.php
define('APP_DEBUG', true);
if (APP_DEBUG) {
  ini_set('display_errors', 1);
  error_reporting(E_ALL);
  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
}

require_once("../../includes/verificar_rol.php");
// Sólo Admin (1) y Supervisor (5)
verificarRol([1,5]);

require_once("../../includes/conexion.php");
$conexion->set_charset("utf8mb4");

/* ===========================
   POST: guardar cambios (SPA)
   =========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // ¿viene desde la SPA?
  $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

  $id            = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
  $id_categoria  = (int)($_POST['id_categoria'] ?? 0);
  $nombre        = trim($_POST['nombre'] ?? '');
  $tipo          = ($_POST['tipo'] ?? '') === 'accesorio' ? 'accesorio' : 'componente';
  $modo_precio   = ($_POST['modo_precio'] ?? '') === 'porcentaje' ? 'porcentaje' : 'fijo';
  $valor         = (float)($_POST['valor_precio'] ?? 0);
  $obligatorio   = isset($_POST['obligatorio']) ? 1 : 0;
  $visible       = isset($_POST['visible']) ? 1 : 0;

  if ($id <= 0) {
    if ($isAjax) { http_response_code(400); header('Content-Type: text/plain; charset=utf-8'); echo "ID inválido"; }
    else { echo "ID inválido"; }
    exit;
  }
  if ($id_categoria <= 0 || $nombre === '') {
    if ($isAjax) { http_response_code(422); header('Content-Type: text/plain; charset=utf-8'); echo "Datos incompletos."; }
    else { echo "Datos incompletos."; }
    exit;
  }

  $stmt = $conexion->prepare("
    UPDATE opciones_categoria
       SET id_categoria = ?,
           nombre       = ?,
           tipo         = ?,
           modo_precio  = ?,
           valor_precio = ?,
           obligatorio  = ?,
           visible      = ?,
           updated_at   = NOW()
     WHERE id_opcion    = ?
     LIMIT 1
  ");
  // i s s s d i i i
  $stmt->bind_param("isssdiii",
    $id_categoria, $nombre, $tipo, $modo_precio, $valor, $obligatorio, $visible, $id
  );
  $stmt->execute();
  $stmt->close();

  if ($isAjax) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "OK";
    exit;
  } else {
    // Fallback fuera de la SPA
    header("Location: lista_opciones.php?categoria={$id_categoria}&t=" . time());
    exit;
  }
}

/* ===========================
   GET: pintar formulario
   =========================== */
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { echo "ID inválido"; exit; }

$cats = $conexion->query("SELECT id_categoria, nombre FROM categorias ORDER BY nombre")->fetch_all(MYSQLI_ASSOC);
$op   = $conexion->query("SELECT * FROM opciones_categoria WHERE id_opcion = {$id} LIMIT 1")->fetch_assoc();
if (!$op) { echo "No existe"; exit; }
?>
<h3>✏️ Editar opción #<?= (int)$op['id_opcion'] ?></h3>

<form
  id="formEditarOpcion"
  method="post"
  action="administrador/componentes/editar_opcion.php"
  onsubmit="
    event.preventDefault();
    if(!confirm('¿Guardar cambios?')) return false;
    const fd = new FormData(this);
    const cat = fd.get('id_categoria') || '0';
    fetch('administrador/componentes/editar_opcion.php', {
      method: 'POST',
      body: fd,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }, // marca SPA
      credentials: 'same-origin',
      cache: 'no-store'
    })
    .then(r => r.text())
    .then(t => {
      if (t.trim() !== 'OK') { alert('Error al guardar: ' + t); return; }
      cargarDirecto('administrador/componentes/lista_opciones.php?categoria=' + encodeURIComponent(cat) + '&t=' + Date.now());
    })
    .catch(err => { console.error(err); alert('Error de red.'); });
    return false;
  "
  style="max-width:740px;display:grid;grid-template-columns:1fr 1fr;gap:12px;"
>
  <!-- Necesarios para el POST -->
  <input type="hidden" name="id" value="<?= (int)$op['id_opcion'] ?>">

  <label style="grid-column:1 / -1;">Categoría<br>
    <select name="id_categoria" required style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:8px;">
      <?php foreach($cats as $c): ?>
        <option value="<?= (int)$c['id_categoria'] ?>" <?= ((int)$op['id_categoria']===(int)$c['id_categoria'])?'selected':'' ?>>
          <?= htmlspecialchars($c['nombre']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </label>

  <label style="grid-column:1 / -1;">Nombre<br>
    <input name="nombre" value="<?= htmlspecialchars($op['nombre']) ?>" required
           style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:8px;">
  </label>

  <label>Tipo<br>
    <select name="tipo" style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:8px;">
      <option value="componente" <?= $op['tipo']==='componente'?'selected':'' ?>>componente</option>
      <option value="accesorio"  <?= $op['tipo']==='accesorio'?'selected':''  ?>>accesorio</option>
    </select>
  </label>

  <label>Modo de precio<br>
    <select name="modo_precio" style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:8px;">
      <option value="fijo"       <?= $op['modo_precio']==='fijo'?'selected':''        ?>>fijo</option>
      <option value="porcentaje" <?= $op['modo_precio']==='porcentaje'?'selected':'' ?>>porcentaje</option>
    </select>
  </label>

  <label style="grid-column:1 / -1;">Valor<br>
    <input name="valor_precio" type="number" step="0.01" value="<?= (float)$op['valor_precio'] ?>"
           style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:8px;">
  </label>

  <label><input type="checkbox" name="obligatorio" <?= $op['obligatorio']?'checked':'' ?>> Obligatorio</label>
  <label><input type="checkbox" name="visible"     <?= $op['visible']?'checked':''     ?>> Visible</label>

  <div style="grid-column:1 / -1;display:flex;gap:10px;margin-top:6px;">
    <button class="btn btn-primary" type="submit">Guardar</button>
    <button type="button"
            onclick="cargarDirecto('administrador/componentes/lista_opciones.php?categoria=<?= (int)$op['id_categoria'] ?>&t='+Date.now())">
      Cancelar
    </button>
  </div>
</form>
