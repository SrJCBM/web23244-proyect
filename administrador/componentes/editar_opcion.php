<?php
define('APP_DEBUG', true);
if (APP_DEBUG) { ini_set('display_errors', 1); error_reporting(E_ALL); }

require_once("../../includes/verificar_rol.php");
session_start();
verificarRol([1,5]);
require_once("../../includes/conexion.php");

$id = (int)($_GET['id'] ?? 0);
if ($id<=0) { echo "ID inválido"; exit; }

$cats = $conexion->query("SELECT id_categoria,nombre FROM categorias ORDER BY nombre")->fetch_all(MYSQLI_ASSOC);
$op   = $conexion->query("SELECT * FROM opciones_categoria WHERE id_opcion=".$id)->fetch_assoc();
if (!$op) { echo "No existe"; exit; }

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $id_categoria = (int)($_POST['id_categoria'] ?? 0);
  $nombre       = trim($_POST['nombre'] ?? '');
  $tipo         = $_POST['tipo'] === 'accesorio' ? 'accesorio' : 'componente';
  $modo_precio  = $_POST['modo_precio'] === 'porcentaje' ? 'porcentaje' : 'fijo';
  $valor        = (float)($_POST['valor_precio'] ?? 0);
  $obligatorio  = isset($_POST['obligatorio']) ? 1 : 0;
  $visible      = isset($_POST['visible']) ? 1 : 0;

  $stmt = $conexion->prepare("
    UPDATE opciones_categoria
    SET id_categoria=?, nombre=?, tipo=?, modo_precio=?, valor_precio=?, obligatorio=?, visible=?, updated_at=NOW()
    WHERE id_opcion=?
  ");
  $stmt->bind_param("issdiiii", $id_categoria, $nombre, $tipo, $modo_precio, $valor, $obligatorio, $visible, $id);
  $stmt->execute();
  echo "<script>cargarDirecto('administrador/componentes/lista_opciones.php?categoria=".$id_categoria."');</script>";
  exit;
}
?>
<h3>✏️ Editar opción #<?= (int)$op['id_opcion'] ?></h3>
<form method="post" onsubmit="return confirm('¿Guardar cambios?');">
  <label>Categoría<br>
    <select name="id_categoria" required>
      <?php foreach($cats as $c): ?>
        <option value="<?= (int)$c['id_categoria'] ?>" <?= $op['id_categoria']==$c['id_categoria']?'selected':'' ?>>
          <?= htmlspecialchars($c['nombre']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </label><br><br>
  <label>Nombre<br><input name="nombre" value="<?= htmlspecialchars($op['nombre']) ?>" required></label><br><br>
  <label>Tipo<br>
    <select name="tipo">
      <option value="componente" <?= $op['tipo']==='componente'?'selected':'' ?>>componente</option>
      <option value="accesorio"  <?= $op['tipo']==='accesorio'?'selected':'' ?>>accesorio</option>
    </select>
  </label><br><br>
  <label>Modo de precio<br>
    <select name="modo_precio">
      <option value="fijo"        <?= $op['modo_precio']==='fijo'?'selected':'' ?>>fijo</option>
      <option value="porcentaje"  <?= $op['modo_precio']==='porcentaje'?'selected':'' ?>>porcentaje</option>
    </select>
  </label><br><br>
  <label>Valor<br><input name="valor_precio" type="number" step="0.01" value="<?= (float)$op['valor_precio'] ?>"></label><br><br>
  <label><input type="checkbox" name="obligatorio" <?= $op['obligatorio']?'checked':'' ?>> Obligatorio</label><br>
  <label><input type="checkbox" name="visible" <?= $op['visible']?'checked':'' ?>> Visible</label><br><br>
  <button class="btn btn-primary" type="submit">Guardar</button>
  <button type="button" onclick="cargarDirecto('administrador/componentes/lista_opciones.php?categoria=<?= (int)$op['id_categoria'] ?>')">Cancelar</button>
</form>
