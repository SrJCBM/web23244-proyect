<?php
require_once("../includes/verificar_rol.php"); verificarRol([2,1,5]);
require_once("../includes/conexion.php");
header('Content-Type: application/json; charset=utf-8');

$idc = (int)($_GET['id_categoria'] ?? 0);
$stmt = $conexion->prepare("SELECT id_opcion, nombre, tipo, modo_precio, valor_precio, obligatorio
                            FROM opciones_categoria
                            WHERE id_categoria=? AND visible=1
                            ORDER BY tipo, nombre");
$stmt->bind_param("i",$idc);
$stmt->execute();
echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
