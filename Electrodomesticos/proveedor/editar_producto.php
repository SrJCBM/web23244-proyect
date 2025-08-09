<?php
require_once("../../includes/verificar_rol.php");
verificarRol([2]); // proveedor

require_once("../../includes/conexion.php");

// Debe existir la empresa del proveedor en sesión
if (!isset($_SESSION["id_empresa"])) {
  echo "<p style='color:red;'>⚠️ No se encontró la empresa asociada al proveedor (id_empresa).</p>";
  exit;
}
$id_empresa = (int)$_SESSION["id_empresa"];

// Validar ID del producto
if (!isset($_GET["id"])) { echo "ID de producto no especificado."; exit; }
$id_producto = (int)$_GET["id"];

// Acepta JSON o lista por comas y devuelve JSON
function normalizarCaracteristicas($raw) {
  $raw = isset($raw) ? trim($raw) : '';
  if ($raw === '') return '[]';
  // si viene "Control remoto, Soporte pared"
  if ($raw[0] !== '[' && $raw[0] !== '{') {
    $parts = array_filter(array_map('trim', explode(',', $raw)));
    $arr = [];
    foreach ($parts as $n) {
      $arr[] = array('nombre' => $n, 'precio' => 0);
    }
    return json_encode($arr, JSON_UNESCAPED_UNICODE);
  }
  // si ya es JSON, lo validamos
  $tmp = json_decode($raw, true);
  return (json_last_error() === JSON_ERROR_NONE)
         ? json_encode($tmp, JSON_UNESCAPED_UNICODE)
         : '[]';
}

// ---- Guardar cambios (UPDATE) ----
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $nombre  = $_POST["nombre"] ?? '';
  $descripcion = $_POST["descripcion"] ?? '';
  $marca   = $_POST["marca"] ?? '';
  $modelo  = $_POST["modelo"] ?? '';
  $precio  = (float)($_POST["precio"] ?? 0);
  $stock   = (int)($_POST["stock"] ?? 0);
  $caracteristicas = normalizarCaracteristicas($_POST["caracteristicas"] ?? '');

  $sql = "UPDATE productos
          SET nombre=?, descripcion=?, caracteristicas=?, marca=?, modelo=?, precio_base=?, stock=?
          WHERE id_producto=? AND id_empresa=?";

  $stmt = $conexion->prepare($sql);

  // 9 placeholders → tipos: s s s s s d i i i
  $stmt->bind_param(
    "sssssdiii",
    $nombre,
    $descripcion,
    $caracteristicas,
    $marca,
    $modelo,
    $precio,
    $stock,
    $id_producto,
    $id_empresa
  );

  if ($stmt->execute()) {
    echo "<p style='color:green;'>✅ Producto actualizado correctamente.</p>";
    echo "<a href=\"#\" onclick=\"cargarDirecto('Electrodomesticos/proveedor/lista_productos.php')\">🔙 Volver a la lista</a>";
  } else {
    echo "<p style='color:red;'>Error al actualizar: " . htmlspecialchars($stmt->error) . "</p>";
  }
  exit;
}


// ---- Cargar datos del producto a editar ----
$sql = "SELECT * FROM productos WHERE id_producto = ? AND id_empresa = ? LIMIT 1";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ii", $id_producto, $id_empresa);
$stmt->execute();
$resultado = $stmt->get_result();
if ($resultado->num_rows === 0) {
  echo "<p style='color:red;'>Producto no encontrado o no pertenece a tu empresa.</p>";
  exit;
}
$producto = $resultado->fetch_assoc();
$stmt->close();
$conexion->close();
?>

<h2>✏️ Editar producto #<?= (int)$producto['id_producto'] ?></h2>

<form id="formEditar"
      method="post"
      onsubmit="
        event.preventDefault();
        const fd = new FormData(this);
        fetch('Electrodomesticos/proveedor/editar_producto.php?id=<?= (int)$producto['id_producto'] ?>', {
          method: 'POST',
          body: fd
        })
        .then(r => r.text())
        .then(html => { document.getElementById('contenido').innerHTML = html; })
        .catch(err => {
          console.error('Error al editar:', err);
          document.getElementById('contenido').innerHTML = '<p>Error al guardar cambios.</p>';
        });
        return false;
      ">
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

  <button type="submit">Guardar Cambios</button>
</form>

