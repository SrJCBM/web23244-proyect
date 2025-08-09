<?php
require_once("../../includes/verificar_rol.php");
verificarRol([2]); // proveedor

require_once("../../includes/conexion.php");

if (!isset($_SESSION["id_empresa"])) {
  echo "<p style='color:red;'>⚠️ No se encontró la empresa asociada al proveedor (id_empresa).</p>";
  exit;
}
$id_empresa = (int)$_SESSION["id_empresa"];

$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
if ($id <= 0) { echo "<p style='color:red;'>ID inválido.</p>"; exit; }

// HARD DELETE
$sql = "DELETE FROM productos WHERE id_producto = ? AND id_empresa = ? LIMIT 1";
// SOFT DELETE (opcional):
// $sql = "UPDATE productos SET estado = 'inactivo' WHERE id_producto = ? AND id_empresa = ?";

$stmt = $conexion->prepare($sql);
if (!$stmt) { echo "<p style='color:red;'>Error en prepare(): {$conexion->error}</p>"; exit; }
$stmt->bind_param("ii", $id, $id_empresa);

if ($stmt->execute()) {
  echo "<p style='color:green;'>🗑️ Producto eliminado correctamente.</p>";
} else {
  echo "<p style='color:red;'>Error al eliminar: " . htmlspecialchars($stmt->error) . "</p>";
}
$stmt->close();
$conexion->close();

// devuelve la lista actualizada
require_once __DIR__ . '/lista_productos.php';
