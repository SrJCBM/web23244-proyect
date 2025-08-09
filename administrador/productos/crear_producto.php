<?php
require_once("../../includes/verificar_rol.php");
verificarRol([1]); // Solo administrador

require_once("../../includes/conexion.php");

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
  $nombre       = $_POST["nombre"] ?? '';
  $descripcion  = $_POST["descripcion"] ?? '';
  $marca        = $_POST["marca"] ?? '';
  $modelo       = $_POST["modelo"] ?? '';
  $precio       = (float)($_POST["precio"] ?? 0);
  $stock        = (int)($_POST["stock"] ?? 0);
  $caracts      = normalizarCaracteristicas($_POST["caracteristicas"] ?? '');
  $fecha        = date("Y-m-d");

  if ($id_empresa <= 0) {
    echo "<p style='color:red;'>Debes seleccionar una empresa proveedora.</p>";
  } else {
    // Mantenemos el mismo orden de columnas que usa el módulo de proveedor
    $sql = "INSERT INTO productos
            (nombre, descripcion, caracteristicas, marca, modelo, precio_base, stock, id_empresa, fecha_creacion)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conexion->prepare($sql);
    if (!$stmt) { echo "<p style='color:red;'>Error prepare(): {$conexion->error}</p>"; exit; }

    // Tipos: s s s s s d i i s   → "sssssdiis"
    if (!$stmt->bind_param(
      "sssssdiis",
      $nombre, $descripcion, $caracts, $marca, $modelo, $precio, $stock, $id_empresa, $fecha
    )) { echo "<p style='color:red;'>Error bind_param(): {$stmt->error}</p>"; exit; }

    if ($stmt->execute()) {
      echo "<p style='color:green;'>✅ Producto creado correctamente.</p>";
      echo "<a href=\"#\" onclick=\"cargarDirecto('administrador/productos/lista_productos.php')\">Ver lista</a>";
      $stmt->close(); $conexion->close(); exit;
    } else {
      echo "<p style='color:red;'>Error al crear: ".htmlspecialchars($stmt->error)."</p>";
    }
    $stmt->close();
  }
}

// ---- GET: cargar empresas proveedoras para el dropdown ----
// Usamos la tabla que ya maneja Admin: empresas_proveedoras
$empresas = $conexion->query("
  SELECT id_empresa, nombre
  FROM empresas_proveedoras
  WHERE estado = 'activa'
  ORDER BY nombre ASC
");
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
