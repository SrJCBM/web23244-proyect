<?php
session_start();
require_once("../includes/verificar_rol.php"); verificarRol([1,2,5,6]); // los que pueden usar el wizard
require_once("../includes/conexion.php");
header("Content-Type: application/json; charset=utf-8");

$cedula = trim($_GET['cedula'] ?? '');
if ($cedula === '') {
  echo json_encode(["ok"=>false,"found"=>false,"msg"=>"Falta cédula"]); exit;
}

// busca por cédula (ajusta el nombre de la columna según tu tabla)
$stmt = $conexion->prepare("SELECT id_cliente, cedula, nombre_comercial, correo, telefono, direccion 
                            FROM clientes WHERE cedula = ? LIMIT 1");
$stmt->bind_param("s", $cedula);
$stmt->execute();
$res = $stmt->get_result();

if ($res && $row = $res->fetch_assoc()) {
  echo json_encode(["ok"=>true, "found"=>true, "cliente"=>$row]);
} else {
  echo json_encode(["ok"=>true, "found"=>false]);
}
