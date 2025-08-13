<?php
require_once("../includes/verificar_rol.php");
verificarRol([1]); // Solo admin para eliminar

require_once("../includes/conexion.php");

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pageActual = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

if ($id <= 0) { echo "<p style='color:red;'>ID inválido.</p>"; require_once __DIR__ . '/lista_empresas.php'; exit; }

// (Opcional) Verifica dependencias, por ejemplo productos asociados
// $dep = $conexion->prepare("SELECT COUNT(*) FROM productos WHERE id_empresa=?");
// $dep->bind_param("i", $id); $dep->execute(); $dep->bind_result($nDep); $dep->fetch(); $dep->close();
// if ($nDep > 0) { echo "<p style='color:red;'>No se puede eliminar: hay $nDep productos asociados.</p>"; require_once __DIR__ . '/lista_empresas.php'; exit; }

// HARD DELETE
$stmt = $conexion->prepare("DELETE FROM empresas_proveedoras WHERE id_empresa=? LIMIT 1");
if (!$stmt) { echo "<p style='color:red;'>Error prepare(): ".htmlspecialchars($conexion->error)."</p>"; require_once __DIR__ . '/lista_empresas.php'; exit; }
$stmt->bind_param("i", $id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
  echo "<p style='color:green;'>🗑️ Empresa eliminada correctamente (ID $id).</p>";
} else {
  echo "<p style='color:red;'>Error al eliminar: ".htmlspecialchars($stmt->error)."</p>";
}
$stmt->close();

// Mantener misma página al refrescar
$_GET['page'] = $pageActual;
require_once __DIR__ . '/lista_empresas.php';
