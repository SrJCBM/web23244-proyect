<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("../../includes/verificar_rol.php");
verificarRol([2]); // proveedor

require_once("../../includes/conexion.php");

if (!isset($_SESSION["id_empresa"])) {
  echo "<p style='color:red;'>⚠️ No se encontró la empresa asociada al proveedor (id_empresa).</p>";
  exit;
}
$id_empresa = (int)$_SESSION["id_empresa"];


/** Convierte una lista por comas a JSON, o valida JSON crudo. */
function normalizarCaracteristicas($raw) {
  $raw = isset($raw) ? trim($raw) : '';
  if ($raw === '') return '[]';
  if ($raw[0] !== '[' && $raw[0] !== '{') {
    $parts = array_filter(array_map('trim', explode(',', $raw)));
    $arr = [];
    foreach ($parts as $n) {
      $arr[] = array('nombre' => $n, 'precio' => 0);
    }
    return json_encode($arr, JSON_UNESCAPED_UNICODE);
  }
  $tmp = json_decode($raw, true);
  return (json_last_error() === JSON_ERROR_NONE)
         ? json_encode($tmp, JSON_UNESCAPED_UNICODE)
         : '[]';
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $nombre        = $_POST["nombre"]        ?? '';
  $descripcion   = $_POST["descripcion"]   ?? '';
  $marca         = $_POST["marca"]         ?? '';
  $modelo        = $_POST["modelo"]        ?? '';
  $precio        = (float)($_POST["precio"] ?? 0);
  $stock         = (int)($_POST["stock"]    ?? 0);
  $caractsInput  = $_POST["caracteristicas"] ?? '';
  $caracteristicas = normalizarCaracteristicas($caractsInput);
  $fecha         = date("Y-m-d");

  $sql = "INSERT INTO productos
          (nombre, descripcion, caracteristicas, marca, modelo, precio_base, stock, id_empresa, fecha_creacion)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

  $stmt = $conexion->prepare($sql);
  if (!$stmt) {
    echo "<p style='color:red;'>Error en prepare(): {$conexion->error}</p>";
    exit;
  }

  if (!$stmt->bind_param(
      "sssssdiis",
      $nombre,           
      $descripcion,      
      $caracteristicas,  
      $marca,            
      $modelo,           
      $precio,           
      $stock,           
      $id_empresa,       
      $fecha             
  )) {
    echo "<p style='color:red;'>Error en bind_param(): {$stmt->error}</p>";
    exit;
  }

  if ($stmt->execute()) {
      header("Location: ../../index.php?view=lista_proveedor");
      exit;
  } else {
      echo "<p style='color:red;'>Error al guardar el producto: " . htmlspecialchars($stmt->error) . "</p>";
  }


  $stmt->close();
  $conexion->close();
  exit;
}
?>

<h2>Crear Producto</h2>
<form id="formProducto" method="POST" action="Electrodomesticos/proveedor/crear_producto.php">
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
  <small>(JSON o lista por comas. Ej: <i>Control remoto, Funda</i> o <i>[{"nombre":"Control remoto","precio":20}]</i>)</small><br>
  <textarea name="caracteristicas" placeholder='["Control remoto","Funda"]'></textarea><br><br>

  <button type="submit">Guardar Producto</button>
</form>

