<?php
session_start();
require_once("../includes/verificar_rol.php"); verificarRol([1,2,5,6]);
require_once("../includes/conexion.php");
header("Content-Type: application/json; charset=utf-8");

$raw = file_get_contents("php://input");
$in  = json_decode($raw, true);

$cedula  = trim($in['cedula'] ?? '');
$nombre  = trim($in['nombre_comercial'] ?? '');
$correo  = trim($in['correo'] ?? '');
$tel     = trim($in['telefono'] ?? '');
$dir     = trim($in['direccion'] ?? '');

if ($cedula==='' || $nombre==='') {
  echo json_encode(["ok"=>false,"msg"=>"Datos insuficientes"]); exit;
}

// evita duplicados por cédula
$chk = $conexion->prepare("SELECT id_cliente FROM clientes WHERE cedula=? LIMIT 1");
$chk->bind_param("s",$cedula);
$chk->execute();
$rchk = $chk->get_result();
if ($rchk && $rchk->num_rows > 0) {
  $row = $rchk->fetch_assoc();
  echo json_encode(["ok"=>true,"id_cliente"=>$row['id_cliente'],"duplicado"=>true]); exit;
}

$stmt = $conexion->prepare("INSERT INTO clientes
  (cedula, nombre_comercial, correo, telefono, direccion, created_at, updated_at)
  VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
$stmt->bind_param("sssss",$cedula,$nombre,$correo,$tel,$dir);

if ($stmt->execute()) {
  echo json_encode(["ok"=>true,"id_cliente"=>$stmt->insert_id]);
} else {
  echo json_encode(["ok"=>false,"msg"=>"No se pudo crear"]);
}
