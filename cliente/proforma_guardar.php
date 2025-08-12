<?php
session_start();
require_once("../includes/verificar_rol.php"); verificarRol([2,1,5]);
require_once("../includes/conexion.php");
header('Content-Type: application/json; charset=utf-8');

$payload = json_decode(file_get_contents('php://input'), true);
$idUsuario = $_SESSION['id_usuario'] ?? null;
if(!$idUsuario){ echo json_encode(['ok'=>false,'msg'=>'Sesión expirada']); exit; }

$id_cliente   = (int)($payload['id_cliente'] ?? 0);
$id_categoria = (int)($payload['id_categoria'] ?? 0);
$estado       = in_array(($payload['estado'] ?? ''), ['borrador','emitida','enviada']) ? $payload['estado'] : 'borrador';
$moneda       = substr($payload['moneda'] ?? 'USD',0,3);
$notas        = $payload['notas'] ?? '';
$impuesto_pct = (float)($payload['impuesto_pct'] ?? 0);
$opciones     = $payload['opciones'] ?? [];

if(!$id_cliente || !$id_categoria || empty($opciones)){ echo json_encode(['ok'=>false,'msg'=>'Datos incompletos']); exit; }

$conexion->begin_transaction();
try{
  // Cabecera
  $stmt = $conexion->prepare("INSERT INTO cotizaciones (id_usuario,id_cliente,id_categoria,fecha_emision,moneda,notas,total,subtotal,impuestos,estado)
                              VALUES (?,?,?,NOW(),?,?,0,0,0,?)");
  $stmt->bind_param("iiisss",$idUsuario,$id_cliente,$id_categoria,$moneda,$notas,$estado);
  $stmt->execute();
  $id_cot = $stmt->insert_id;
  $stmt->close();

  $subtotalCab = 0.0;

  foreach($opciones as $op){
    $id_prod = (int)$op['id_producto'];
    $cant = 1;
    $precio = (float)$op['precio_unitario'];
    $base = $precio * $cant;

    $extras_json = $op['items'] ?? [];
    $extras_fijo=0.0; $extras_pct=0.0;
    foreach($extras_json as $it){
      if(($it['modo_precio'] ?? 'fijo')==='fijo'){
        $extras_fijo += (float)$it['valor_precio'] * (int)($it['cantidad'] ?? 1);
      } else {
        $extras_pct += (float)$it['valor_precio'];
      }
    }
    $extras_total = $extras_fijo + ($base * $extras_pct/100.0);
    $subtotal = $base + $extras_total;

    $j = $conexion->real_escape_string(json_encode($extras_json, JSON_UNESCAPED_UNICODE));
    $sql = "INSERT INTO detalle_cotizacion (id_cotizacion,id_producto,cantidad,precio_unitario,extras_json,extras_total,subtotal)
            VALUES ($id_cot,$id_prod,$cant,$precio,'$j',$extras_total,$subtotal)";
    if(!$conexion->query($sql)) throw new Exception($conexion->error);

    $subtotalCab += $subtotal;
  }

  $impuestos = $subtotalCab * ($impuesto_pct/100.0);
  $total = $subtotalCab + $impuestos;

  $stmtU = $conexion->prepare("UPDATE cotizaciones SET subtotal=?, impuestos=?, total=?, estado=?, moneda=?, notas=? WHERE id_cotizacion=?");
  $stmtU->bind_param("dddsssi",$subtotalCab,$impuestos,$total,$estado,$moneda,$notas,$id_cot);
  $stmtU->execute();

  $conexion->commit();
  echo json_encode(['ok'=>true,'id_cotizacion'=>$id_cot,'total'=>$total]);
} catch(Throwable $e){
  $conexion->rollback();
  echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]);
}
