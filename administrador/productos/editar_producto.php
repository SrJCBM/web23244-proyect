<?php
require_once("../../includes/verificar_rol.php");
verificarRol([1]); // Admin

require_once("../../includes/conexion.php");

if (!isset($_GET["id"])) { echo "<p style='color:red;'>ID no especificado.</p>"; exit; }
$id_producto = (int)$_GET["id"];

// ---- util: normalizar características (JSON o lista por comas)
function normalizarCaracteristicas($raw) {
  $raw = isset($raw) ? trim($raw) : '';
  if ($raw === '') return '[]';
  if ($raw[0] !== '[' && $raw[0] !== '{') {
    $parts = array_filter(array_map('trim', explode(',', $raw)));
    $arr = [];
    foreach ($parts as $n) { $arr[] = ['nombre' => $n, 'precio' => 0]; }
    return json_encode($arr, JSON_UNESCAPED_UNICODE);
  }
  $tmp = json_decode($raw, true);
  return (json_last_error() === JSON_ERROR_NONE)
         ? json_encode($tmp, JSON_UNESCAPED_UNICODE)
         : '[]';
}

// ---- POST: UPDATE
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $id_empresa   = (int)($_POST["id_empresa"] ?? 0);
  $nombre       = $_POST["nombre"] ?? '';
  $descripcion  = $_POST["descripcion"] ?? '';
  $marca        = $_POST["marca"] ?? '';
  $modelo       = $_POST["modelo"] ?? '';
  $precio       = (float)($_POST["precio"] ?? 0);
  $stock        = (int)($_POST["stock"] ?? 0);
  $caracts      = normalizarCaracteristicas($_POST["caracteristicas"] ?? '');

  if ($id_empresa <= 0) {
    echo "<p style='color:red;'>Debes seleccionar una empresa proveedora.</p>";
  } else {
    $sql = "UPDATE productos
            SET id_empresa=?, nombre=?, descripcion=?, caracteristicas=?, marca=?, modelo=?, precio_base=?, stock=?
            WHERE id_producto=?";
    $stmt = $conexion->prepare($sql);
    if (!$stmt) { echo "<p style='color:red;'>Error prepare(): {$conexion->error}</p>"; exit; }

    // i s s s s s d i i  → "isssssdii"
    if (!$stmt->bind_param(
      "isssssdii",
      $id_empresa, $nombre, $descripcion, $caracts, $marca, $modelo, $precio, $stock, $id_producto
    )) { echo "<p style='color:red;'>Error bind_param(): {$stmt->error}</p>"; exit; }

    if ($stmt->execute()) {
      echo "<p style='color:green;'>✅ Producto actualizado correctamente.</p>";
      echo "<a href=\"#\" onclick=\"cargarDirecto('administrador/productos/lista_productos.php')\">🔙 Volver a la lista</a>";
      $stmt->close(); $conexion->close(); exit;
    } else {
      echo "<p style='color:red;'>Error al actualizar: ".htmlspecialchars($stmt->error)."</p>";
    }
    $stmt->close();
  }
}

// ---- GET: cargar producto
$sql = "SELECT * FROM productos WHERE id_producto = ? LIMIT 1";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_producto);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) { echo "<p style='color:red;'>Producto no encontrado.</p>"; exit; }
$producto = $res->fetch_assoc();
$stmt->close();

// ---- GET: empresas para dropdown (empresas_proveedoras o fallback a empresas)
$empresas = [];
$q = $conexion->query("SELECT id_empresa, nombre FROM empresas_proveedoras ORDER BY nombre ASC");
if ($q instanceof mysqli_result) {
  while ($row = $q->fetch_assoc()) { $empresas[] = $row; }
} else {
  $q2 = $conexion->query("SELECT id_empresa, COALESCE(nombre, nombre_empresa, razon_social) AS nombre FROM empresas ORDER BY nombre ASC");
  while ($row = $q2->fetch_assoc()) { $empresas[] = $row; }
}

$conexion->close();
?>
<h2>✏️ Editar producto (Admin) #<?= (int)$producto['id_producto'] ?></h2>

<form method="post" onsubmit="
  event.preventDefault();
  const fd = new FormData(this);
  fetch('administrador/productos/editar_producto.php?id=<?= (int)$producto['id_producto'] ?>', { method: 'POST', body: fd })
    .then(r => r.text())
    .then(html => { document.getElementById('contenido').innerHTML = html; })
    .catch(err => { console.error(err); alert('Error al guardar'); });
  return false;
">
  <label>Empresa proveedora:</label><br>
  <select name="id_empresa" required>
    <option value=''>-- Selecciona --</option>
    <?php foreach($empresas as $e): ?>
      <option value="<?= (int)$e['id_empresa'] ?>"
        <?= ((int)$e['id_empresa'] === (int)$producto['id_empresa']) ? 'selected' : '' ?>>
        <?= htmlspecialchars($e['nombre']) ?>
      </option>
    <?php endforeach; ?>
  </select><br><br>

  <label>Nombre:</label><br>
  <input type="text" name="nombre" value="<?= htmlspecialchars($producto['nombre']) ?>" required><br><br>

  <label>Descripción:</label><br>
  <textarea name="descripcion" required><?= htmlspecialchars($producto['descripcion']) ?></textarea><br><br>

  <label>Marca:</label><br>
  <input type="text" name="marca" value="<?= htmlspecialchars($producto['marca']) ?>" required><br><br>

  <label>Modelo:</label><br>
  <input type="text" name="modelo" value="<?= htmlspecialchars($producto['modelo']) ?>" required><br><br>

  <label>Precio (USD):</label><br>
  <input type="number" name="precio" value="<?= number_format((float)$producto['precio_base'], 2, '.', '') ?>" step="0.01" required><br><br>

  <label>Stock:</label><br>
  <input type="number" name="stock" value="<?= (int)$producto['stock'] ?>" required><br><br>

  <label>Características / Accesorios:</label>
  <small>(JSON o lista por comas)</small><br>
  <textarea name="caracteristicas" placeholder='[{"nombre":"Control remoto","precio":20}]'><?= htmlspecialchars($producto['caracteristicas'] ?? '') ?></textarea><br><br>

  <button type="submit">Guardar cambios</button>
</form>
