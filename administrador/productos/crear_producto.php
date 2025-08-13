<?php
// administrador/productos/crear_producto.php
define('APP_DEBUG', true);
if (APP_DEBUG) { ini_set('display_errors', 1); error_reporting(E_ALL); }

require_once("../../includes/verificar_rol.php");
verificarRol([1,5]); // Admin / Supervisor

require_once("../../includes/conexion.php");
$conexion->set_charset("utf8mb4");

// ---- Util: normalizar características (JSON o lista por comas) ----
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

// ---- POST: insertar producto ----
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $id_empresa   = (int)($_POST["id_empresa"] ?? 0);
  $id_categoria = (int)($_POST["id_categoria"] ?? 0); // opcional -> NULL si 0
  $nombre       = trim($_POST["nombre"] ?? '');
  $descripcion  = trim($_POST["descripcion"] ?? '');
  $marca        = trim($_POST["marca"] ?? '');
  $modelo       = trim($_POST["modelo"] ?? '');
  $precio       = (float)($_POST["precio"] ?? 0);
  $stock        = (int)($_POST["stock"] ?? 0);
  $caracts      = normalizarCaracteristicas($_POST["caracteristicas"] ?? '');
  $fecha        = date("Y-m-d");
  $estado       = 1; // activo por defecto

  if ($id_empresa <= 0 || $nombre === '') {
    echo "<p style='color:red;'>Empresa y nombre son obligatorios.</p>";
  } else {
    // Inserta respetando tu esquema (id_categoria opcional y timestamps)
    $sql = "INSERT INTO productos
              (nombre, descripcion, caracteristicas, marca, modelo,
               precio_base, stock, id_categoria, id_empresa, estado,
               fecha_creacion, created_at, updated_at)
            VALUES
              (?, ?, ?, ?, ?, ?, ?, NULLIF(?,0), ?, ?, ?, NOW(), NOW())";

    $stmt = $conexion->prepare($sql);
    if (!$stmt) { echo "<p style='color:red;'>Error prepare(): {$conexion->error}</p>"; exit; }

    // TIPOS CORRECTOS (11 placeholders): ssss s d i i i i s  => "sssssdiiiis"
    if (!$stmt->bind_param(
      "sssssdiiiis",
      $nombre, $descripcion, $caracts, $marca, $modelo,
      $precio, $stock, $id_categoria, $id_empresa, $estado, $fecha
    )) {
      echo "<p style='color:red;'>Error bind_param(): {$stmt->error}</p>"; exit;
    }

    if ($stmt->execute()) {
      echo "<p style='color:green;'>✅ Producto creado correctamente.</p>";
      echo "<a href=\"#\" onclick=\"cargarDirecto('administrador/productos/lista_productos.php');return false;\">Ver lista</a>";
      $stmt->close(); $conexion->close(); exit;
    } else {
      echo "<p style='color:red;'>Error al crear: ".htmlspecialchars($stmt->error)."</p>";
    }
    $stmt->close();
  }
}

// ---- GET: cargar empresas y categorías para los selects ----
$empresas = $conexion->query("
  SELECT id_empresa, nombre
  FROM empresas_proveedoras
  WHERE estado='activa'
  ORDER BY nombre ASC
");
$categorias = $conexion->query("SELECT id_categoria, nombre FROM categorias ORDER BY nombre ASC");
?>
<h2>➕ Crear producto (Admin)</h2>

<form method="post" onsubmit="
  event.preventDefault();
  const fd = new FormData(this);
  fetch('administrador/productos/crear_producto.php', { method: 'POST', body: fd })
    .then(r => r.text())
    .then(html => { document.getElementById('contenido').innerHTML = html; })
    .catch(err => { console.error(err); alert('Error al crear'); });
  return false;
">
  <label>Empresa proveedora:</label><br>
  <select name="id_empresa" required>
    <option value=''>-- Selecciona --</option>
    <?php while ($e = $empresas->fetch_assoc()): ?>
      <option value="<?= (int)$e['id_empresa'] ?>"><?= htmlspecialchars($e['nombre']) ?></option>
    <?php endwhile; ?>
  </select><br><br>

  <label>Categoría (opcional):</label><br>
  <select name="id_categoria">
    <option value="0">-- Sin categoría --</option>
    <?php while ($c = $categorias->fetch_assoc()): ?>
      <option value="<?= (int)$c['id_categoria'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
    <?php endwhile; ?>
  </select><br><br>

  <label>Nombre:</label><br>
  <input type="text" name="nombre" required><br><br>

  <label>Descripción:</label><br>
  <textarea name="descripcion" required></textarea><br><br>

  <label>Marca:</label><br>
  <input type="text" name="marca" required><br><br>

  <label>Modelo:</label><br>
  <input type="text" name="modelo" required><br><br>

  <label>Precio (USD):</label><br>
  <input type="number" name="precio" step="0.01" required><br><br>

  <label>Stock:</label><br>
  <input type="number" name="stock" required><br><br>

  <label>Características / Accesorios:</label>
  <small>(JSON o lista por comas, ej.: Control remoto, Soporte pared)</small><br>
  <textarea name="caracteristicas" placeholder='[{"nombre":"Control remoto","precio":20}]'></textarea><br><br>

  <button type="submit">Crear</button>
</form>
