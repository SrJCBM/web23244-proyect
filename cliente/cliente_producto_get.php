<?php
require_once("../includes/verificar_rol.php"); verificarRol([2,1,5]);
require_once("../includes/conexion.php");
header('Content-Type: application/json; charset=utf-8');

$id = (int)($_GET['id'] ?? 0);
$stmt = $conexion->prepare("SELECT id_producto, id_categoria, nombre, marca, modelo, precio_base
                            FROM productos WHERE id_producto=? LIMIT 1");
$stmt->bind_param("i",$id);
$stmt->execute();
echo json_encode($stmt->get_result()->fetch_assoc());
