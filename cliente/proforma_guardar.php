<?php
require_once("../includes/verificar_rol.php");
verificarRol([1,2,5,6]);
require_once("../includes/conexion.php");
header("Content-Type: application/json; charset=utf-8");

// Lee JSON
$raw = file_get_contents("php://input");
$in = json_decode($raw, true);

if (!$in) { echo json_encode(["ok"=>false,"msg"=>"JSON inválido"]); exit; }

$id_usuario   = $_SESSION["id_usuario"] ?? 0;
$id_cliente   = (int)($in["id_cliente"] ?? 0);
$id_categoria = (int)($in["id_categoria"] ?? 0);
$estado       = trim($in["estado"] ?? "borrador");
$moneda       = substr(trim($in["moneda"] ?? "USD"),0,3);
$notas        = trim($in["notas"] ?? "");
$impuesto_pct = (float)($in["impuesto_pct"] ?? 0);
$opcs         = $in["opciones"] ?? [];

if ($id_usuario<=0) { echo json_encode(["ok"=>false,"msg"=>"Sesión inválida"]); exit; }
if ($id_cliente<=0 || $id_categoria<=0) { echo json_encode(["ok"=>false,"msg"=>"Datos incompletos: cliente/categoría"]); exit; }
if (!is_array($opcs) || count($opcs)==0) { echo json_encode(["ok"=>false,"msg"=>"Datos incompletos: productos"]); exit; }
if (count($opcs) < 2 || count($opcs) > 4) { echo json_encode(["ok"=>false,"msg"=>"Debes comparar entre 2 y 4 productos"]); exit; }

try {
  $conexion->begin_transaction();

  $subtotal = 0.0;
  $lineas = [];

  // Trae info de cada producto y recalcula precios con items
  $stmtP = $conexion->prepare("SELECT id_producto, precio_base FROM productos WHERE id_producto=? AND id_categoria=? LIMIT 1");

  foreach ($opcs as $p) {
    $id_producto    = (int)($p["id_producto"] ?? 0);
    $cantidad       = max(1, (int)($p["cantidad"] ?? 1));
    $precio_unit_ui = (float)($p["precio_unitario"] ?? 0.0);
    $items          = is_array($p["items"] ?? null) ? $p["items"] : [];

    if ($id_producto<=0) { throw new Exception("Producto inválido"); }

    // Precio base real desde BD (por seguridad)
    $stmtP->bind_param("ii", $id_producto, $id_categoria);
    $stmtP->execute();
    $resP = $stmtP->get_result();
    if (!$resP || $resP->num_rows==0) { throw new Exception("Producto no pertenece a la categoría"); }
    $rowP = $resP->fetch_assoc();
    $precio_unit = (float)$rowP["precio_base"];

    // Calcula extras
    $extras_fijo = 0.0;
    $extras_pct  = 0.0;
    foreach ($items as $it) {
      $modo   = $it["modo_precio"] ?? "fijo";
      $valor  = (float)($it["valor_precio"] ?? 0);
      $cantIt = max(1, (int)($it["cantidad"] ?? 1));
      if ($modo === "fijo") $extras_fijo += $valor * $cantIt;
      else                  $extras_pct  += $valor; // % acumulado
    }
    $base      = $precio_unit * $cantidad;
    $extras    = $extras_fijo + ($base * ($extras_pct/100));
    $line_total= $base + $extras;
    $subtotal += $line_total;

    $lineas[] = [
      "id_producto"   => $id_producto,
      "cantidad"      => $cantidad,
      "precio_unit"   => $precio_unit,
      "extras_json"   => json_encode($items, JSON_UNESCAPED_UNICODE),
      "extras_total"  => $extras,
      "subtotal"      => $line_total
    ];
  }
  $stmtP->close();

  $impuesto = $subtotal * ($impuesto_pct/100.0);
  $total    = $subtotal + $impuesto;

  // Inserta cabecera
  $stmt = $conexion->prepare("
    INSERT INTO cotizaciones (id_usuario, id_cliente, total, estado, fecha_emision, created_at, updated_at)
    VALUES (?, ?, ?, ?, NOW(), NOW(), NOW())
  ");
  $stmt->bind_param("iids", $id_usuario, $id_cliente, $total, $estado);
  $stmt->execute();
  $id_cotizacion = $stmt->insert_id;
  $stmt->close();

  // Inserta detalle
  $stmtD = $conexion->prepare("
    INSERT INTO detalle_cotizacion
    (id_cotizacion, id_producto, cantidad, precio_unitario, extras_json, extras_total, subtotal)
    VALUES (?, ?, ?, ?, ?, ?, ?)
  ");
  foreach ($lineas as $l) {
    $stmtD->bind_param(
      "iiidsdd",
      $id_cotizacion,
      $l["id_producto"],
      $l["cantidad"],
      $l["precio_unit"],
      $l["extras_json"],
      $l["extras_total"],
      $l["subtotal"]
    );
    $stmtD->execute();
  }
  $stmtD->close();

  $conexion->commit();

  echo json_encode([
    "ok"=>true,
    "id_cotizacion"=>$id_cotizacion,
    "subtotal"=>$subtotal,
    "impuesto"=>$impuesto,
    "total"=>$total
  ]);
} catch (Throwable $e) {
  $conexion->rollback();
  http_response_code(500);
  echo json_encode(["ok"=>false,"msg"=>"Error guardando proforma","error"=>$e->getMessage()]);
}
