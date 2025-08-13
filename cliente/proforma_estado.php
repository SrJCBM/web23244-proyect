<?php
require_once("../includes/verificar_rol.php"); verificarRol([2,1,5]);
require_once("../includes/conexion.php");
header("Content-Type: application/json; charset=utf-8");

// Acepta JSON o x-www-form-urlencoded
$raw = file_get_contents("php://input");
$in  = json_decode($raw, true);
if (!$in) { $in = $_POST; }

$id_usuario = $_SESSION["id_usuario"] ?? 0;
$id         = (int)($in["id"] ?? 0);
$estado     = trim($in["estado"] ?? "");

$permitidos = ["borrador","emitida","enviada","cancelada"];
if ($id_usuario <= 0)         { echo json_encode(["ok"=>false,"msg"=>"Sesión inválida"]); exit; }
if ($id <= 0 || $estado === ""){ echo json_encode(["ok"=>false,"msg"=>"Datos incompletos"]); exit; }
if (!in_array($estado, $permitidos, true)) { echo json_encode(["ok"=>false,"msg"=>"Estado inválido"]); exit; }

// Asegura pertenencia de la proforma al usuario
$stmt = $conexion->prepare("SELECT id_cotizacion, estado FROM cotizaciones WHERE id_cotizacion=? AND id_usuario=? LIMIT 1");
$stmt->bind_param("ii", $id, $id_usuario);
$stmt->execute();
$rs = $stmt->get_result();
if (!$rs || $rs->num_rows === 0) { echo json_encode(["ok"=>false,"msg"=>"No encontrado"]); exit; }
$curr = $rs->fetch_assoc();
$stmt->close();

// Si pasa a 'emitida' y no tenía fecha_emision, la marcamos
if ($estado === "emitida") {
  $stmt = $conexion->prepare("UPDATE cotizaciones SET estado=?, fecha_emision=IFNULL(fecha_emision, NOW()), updated_at=NOW() WHERE id_cotizacion=? AND id_usuario=?");
  $stmt->bind_param("sii", $estado, $id, $id_usuario);
} else {
  $stmt = $conexion->prepare("UPDATE cotizaciones SET estado=?, updated_at=NOW() WHERE id_cotizacion=? AND id_usuario=?");
  $stmt->bind_param("sii", $estado, $id, $id_usuario);
}
$stmt->execute();
$aff = $stmt->affected_rows;
$stmt->close();

echo json_encode(["ok"=> ($aff >= 0), "id"=>$id, "estado"=>$estado]);
