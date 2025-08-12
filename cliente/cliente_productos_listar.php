<?php
require_once("../includes/verificar_rol.php");
verificarRol([1,2,5,6]);
require_once("../includes/conexion.php");
header("Content-Type: application/json; charset=utf-8");

$idc = isset($_GET["id_categoria"]) ? (int)$_GET["id_categoria"] : 0;
$q   = isset($_GET["q"]) ? trim($_GET["q"]) : "";

if ($idc <= 0) { echo json_encode([]); exit; }

$sql = "SELECT id_producto, id_categoria, nombre, marca, modelo, precio_base
        FROM productos
        WHERE id_categoria = ?
          AND (estado='activo' OR estado='activa' OR estado IS NULL)";
$params = [$idc];
$types  = "i";

if ($q !== "") {
  $sql .= " AND (nombre LIKE CONCAT('%',?,'%') OR marca LIKE CONCAT('%',?,'%') OR modelo LIKE CONCAT('%',?,'%'))";
  $params[] = $q; $params[] = $q; $params[] = $q;
  $types .= "sss";
}
$sql .= " ORDER BY marca ASC, modelo ASC";

$stmt = $conexion->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$rs = $stmt->get_result();

$out = [];
while ($r = $rs->fetch_assoc()) {
  $out[] = [
    "id_producto"  => (int)$r["id_producto"],
    "id_categoria" => (int)$r["id_categoria"],
    "nombre"       => $r["nombre"],
    "marca"        => $r["marca"],
    "modelo"       => $r["modelo"],
    "precio_base"  => (float)$r["precio_base"],
  ];
}
echo json_encode($out);
