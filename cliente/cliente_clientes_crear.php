<?php
require_once("../includes/verificar_rol.php"); verificarRol([2,1,5]);
require_once("../includes/conexion.php");
header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents('php://input'), true) ?? [];
$cedula = trim($data['cedula'] ?? '');
$nombre = trim($data['nombre_comercial'] ?? '');
$correo = trim($data['correo'] ?? '');
$telefono = trim($data['telefono'] ?? '');
$direccion = trim($data['direccion'] ?? '');

if ($cedula==='' || $nombre===''){ echo json_encode(['ok'=>false,'msg'=>'Completa cédula y nombre']); exit; }

$stmt = $conexion->prepare("INSERT INTO clientes (cedula, nombre_comercial, correo, telefono, direccion, estado)
                            VALUES (?,?,?,?,?, 'activo')");
$stmt->bind_param("sssss",$cedula,$nombre,$correo,$telefono,$direccion);

try{
  $stmt->execute();
  echo json_encode(['ok'=>true,'id_cliente'=>$conexion->insert_id]);
}catch(Throwable $e){
  echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]);
}
