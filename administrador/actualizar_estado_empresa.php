<?php
require_once("../includes/verificar_rol.php");
verificarRol([1]);

include("../includes/conexion.php");

if (!isset($_GET["id"]) || !isset($_GET["estado"])) {
  http_response_code(400);
  echo "Parámetros faltantes.";
  exit;
}

$id = intval($_GET["id"]);
$nuevoEstado = $_GET["estado"] === "activa" ? "activa" : "inactiva";

$sql = "UPDATE empresas_proveedoras SET estado = ? WHERE id_empresa = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("si", $nuevoEstado, $id);

if ($stmt->execute()) {
  echo "Estado actualizado.";
} else {
  http_response_code(500);
  echo "Error al actualizar estado.";
}
?>
