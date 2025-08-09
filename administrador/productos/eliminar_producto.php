<?php
require_once("../../includes/verificar_rol.php");
verificarRol([1]); // Solo administrador

require_once("../../includes/conexion.php");

// 1) Validar ID
$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
if ($id <= 0) {
  echo "<p style='color:red;'>ID inválido.</p>";
  require_once __DIR__ . '/lista_productos.php';
  exit;
}

// 2) Intentar eliminar (HARD DELETE)
// Si prefieres "soft delete", mira el bloque alternativo más abajo.
$sql = "DELETE FROM productos WHERE id_producto = ? LIMIT 1";
$stmt = $conexion->prepare($sql);
if (!$stmt) {
  echo "<p style='color:red;'>Error en prepare(): " . htmlspecialchars($conexion->error) . "</p>";
  require_once __DIR__ . '/lista_productos.php';
  exit;
}

$stmt->bind_param("i", $id);

if ($stmt->execute()) {
  if ($stmt->affected_rows > 0) {
    echo "<p style='color:green;'>🗑️ Producto eliminado correctamente (ID $id).</p>";
  } else {
    echo "<p style='color:#b36b00;'>No se encontró el producto (ID $id) o ya estaba eliminado.</p>";
  }
} else {
  // Tip: si falla por FK (clave foránea), puede deberse a cotizaciones/detalles relacionados
  echo "<p style='color:red;'>Error al eliminar: " . htmlspecialchars($stmt->error) . "</p>";
}

$stmt->close();
$conexion->close();

// 3) Refrescar la lista
require_once __DIR__ . '/lista_productos.php';

/* ===========================================
   Alternativa SOFT DELETE:
   -------------------------------------------
   $sql = "UPDATE productos SET estado='inactivo' WHERE id_producto=? LIMIT 1";
   // Y en lista_productos.php, filtrar activos:
   // SELECT ... FROM productos WHERE (estado IS NULL OR estado <> 'inactivo') ORDER BY id_producto DESC
   =========================================== */
