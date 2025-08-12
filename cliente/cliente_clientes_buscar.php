<?php
require_once("../includes/verificar_rol.php"); verificarRol([2,1,5]);
require_once("../includes/conexion.php");
header('Content-Type: application/json; charset=utf-8');

$cedula = trim($_GET['cedula'] ?? '');
if ($cedula===''){ echo json_encode(['ok'=>false,'msg'=>'Cédula requerida']); exit; }

$stmt = $conexion->prepare("SELECT id_cliente, nombre_comercial, persona_contacto, correo, telefono
                            FROM clientes WHERE cedula=? LIMIT 1");
$stmt->bind_param("s",$cedula);
$stmt->execute();
$res = $stmt->get_result();
echo json_encode(['ok'=>true,'found'=>$res->num_rows>0,'cliente'=>$res->fetch_assoc()]);
