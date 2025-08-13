<?php
// administrador/componentes/crear_opcion.php
define('APP_DEBUG', true);
if (APP_DEBUG) {
  ini_set('display_errors', 1);
  error_reporting(E_ALL);
  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
}

require_once("../../includes/verificar_rol.php");
verificarRol([1,5]); // sólo admin/supervisor
require_once("../../includes/conexion.php");
$conexion->set_charset("utf8mb4");

$id_cat = (int)($_GET['categoria'] ?? 0);
$cats = $conexion->query("SELECT id_categoria,nombre FROM categorias ORDER BY nombre")->fetch_all(MYSQLI_ASSOC);

/* POST (SPA) */
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])==='xmlhttprequest';

  $id_categoria = (int)($_POST['id_categoria'] ?? 0);
  $nombre       = trim($_POST['nombre'] ?? '');
  $tipo         = ($_POST['tipo'] ?? '') === 'accesorio' ? 'accesorio' : 'componente';
  $modo_precio  = ($_POST['modo_precio'] ?? '') === 'porcentaje' ? 'porcentaje' : 'fijo';
  $valor        = (float)($_POST['valor_precio'] ?? 0);
  $obligatorio  = isset($_POST['obligatorio']) ? 1 : 0;
  $visible      = isset($_POST['visible']) ? 1 : 0;

  if ($id_categoria<=0 || $nombre==='') {
    if ($isAjax) { http_response_code(422); header('Content-Type:text/plain;charset=utf-8'); echo "Datos incompletos."; }
    else { echo "Datos incompletos."; }
    exit;
  }

  $stmt = $conexion->prepare("
    INSERT INTO opciones_categoria
      (id_categoria, nombre, tipo, modo_precio, valor_precio, obligatorio, visible, created_at, updated_at)
    VALUES (?,?,?,?,?,?,?, NOW(), NOW())
  ");
  // Tipos correctos: i s s s d i i
  $stmt->bind_param("isssdii", $id_categoria, $nombre, $tipo, $modo_precio, $valor, $obligatorio, $visible);
  $stmt->execute();
  $stmt->close();

  if ($isAjax) {
    header('Content-Type:text/plain;charset=utf-8'); echo "OK"; exit;
  } else {
    header("Location: lista_opciones.php?categoria={$id_categoria}&t=".time()); exit;
  }
}
?>
<h3>➕ Crear opción</h3>

<form method="post"
      action="administrador/componentes/crear_opcion.php"
      onsubmit="
        event.preventDefault();
        if(!confirm('¿Guardar opción?')) return false;
        const fd = new FormData(this);
        const cat = fd.get('id_categoria') || '0';
        fetch('administrador/componentes/crear_opcion.php', {
          method: 'POST',
          body: fd,
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          credentials: 'same-origin',
          cache: 'no-store'
        })
        .then(r => r.text())
        .then(t => {
          if(t.trim() !== 'OK'){ alert('Error al guardar: '+t); return; }
          cargarDirecto('administrador/componentes/lista_opciones.php?categoria='+encodeURIComponent(cat)+'&t='+Date.now());
        })
        .catch(e => { console.error(e); alert('Error de red'); });
        return false;
      ">
  <label>Categoría<br>
    <select name="id_categoria" required>
      <?php foreach($cats as $c): ?>
        <option value="<?= (int)$c['id_categoria'] ?>" <?= $id_cat===$c['id_categoria']?'selected':'' ?>>
          <?= htmlspecialchars($c['nombre']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </label><br><br>

  <label>Nombre<br>
    <input name="nombre" required>
  </label><br><br>

  <label>Tipo<br>
    <select name="tipo">
      <option value="componente">componente</option>
      <option value="accesorio">accesorio</option>
    </select>
  </label><br><br>

  <label>Modo de precio<br>
    <select name="modo_precio">
      <option value="fijo">fijo</option>
      <option value="porcentaje">porcentaje</option>
    </select>
  </label><br><br>

  <label>Valor<br>
    <input name="valor_precio" type="number" step="0.01" value="0">
  </label><br><br>

  <label><input type="checkbox" name="obligatorio"> Obligatorio</label><br>
  <label><input type="checkbox" name="visible" checked> Visible</label><br><br>

  <button class="btn btn-primary" type="submit">Guardar</button>
  <button type="button"
          onclick="cargarDirecto('administrador/componentes/lista_opciones.php<?= $id_cat?('?categoria='.$id_cat):'' ?>')">
    Cancelar
  </button>
</form>
