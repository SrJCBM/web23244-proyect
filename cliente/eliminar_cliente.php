<?php
// cliente/eliminar_cliente.php  -> ahora inactiva (soft delete)
define('APP_DEBUG', true);
if (APP_DEBUG) { ini_set('display_errors', 1); error_reporting(E_ALL); }

$ROOT = dirname(__DIR__, 1);
require_once($ROOT . "/includes/verificar_rol.php");
require_once($ROOT . "/includes/conexion.php");
verificarRol([2]); // SOLO VENDEDOR

$conexion->set_charset("utf8mb4");

$id   = (int)($_GET['id'] ?? 0);
$page = max(1, (int)($_GET['page'] ?? 1));
if ($id <= 0) { echo "ID inválido"; exit; }

$st = $conexion->prepare("UPDATE clientes SET estado='inactivo' WHERE id_cliente=? LIMIT 1");
$st->bind_param("i", $id);

if ($st->execute()) {
  echo "<script>cargarDirecto('cliente/lista_clientes.php?".http_build_query(['page'=>$page])."');</script>";
} else {
  echo "<p style='color:#b91c1c'>No se pudo inactivar: ".htmlspecialchars($st->error)."</p>";
  echo "<p><a href='#' onclick=\"cargarDirecto('cliente/lista_clientes.php?".htmlspecialchars(http_build_query(['page'=>$page])). "');return false;\">Volver</a></p>";
}
$st->close();

