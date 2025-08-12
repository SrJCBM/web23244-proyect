<?php
require_once("../includes/verificar_rol.php"); verificarRol([2,1,5]);
require_once("../includes/conexion.php");
header('Content-Type: application/json; charset=utf-8');

$idc = (int)($_GET['id_categoria'] ?? 0);
$q = '%'.($_GET['q'] ?? '').'%';

$stmt = $conexion->prepare("SELECT id_producto, id_categoria, nombre, marca, modelo, precio_base
  FROM productos
  WHERE id_categoria=? AND estado='activo'
    AND (nombre LIKE ? OR marca LIKE ? OR modelo LIKE ?)
  ORDER BY marca, modelo");
$stmt->bind_param("isss",$idc,$q,$q,$q);
$stmt->execute();
echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
