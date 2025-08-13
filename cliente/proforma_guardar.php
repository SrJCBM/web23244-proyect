<?php
require_once("../includes/verificar_rol.php");
verificarRol([1,2,5,6]);
require_once("../includes/conexion.php");

header("Content-Type: application/json; charset=utf-8");

// === Utilidad para explotar en errores de mysqli ===
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
  // ---- 1) Entrada ----
  $raw = file_get_contents("php://input");
  $in = json_decode($raw, true);
  if (!$in) throw new Exception("JSON inválido");

  $id_usuario   = (int)($_SESSION["id_usuario"] ?? 0);
  $id_cliente   = (int)($in["id_cliente"] ?? 0);
  $id_categoria = (int)($in["id_categoria"] ?? 0);
  $estado       = substr(trim($in["estado"] ?? "borrador"),0,20);
  $moneda       = substr(trim($in["moneda"] ?? "USD"),0,3);
  $notas        = trim($in["notas"] ?? "");
  $impuesto_pct = (float)($in["impuesto_pct"] ?? 0);
  $opcs         = $in["opciones"] ?? [];

  if ($id_usuario <= 0)                  throw new Exception("Sesión inválida");
  if ($id_cliente <= 0 || $id_categoria <= 0) throw new Exception("Datos incompletos: cliente/categoría");
  if (!is_array($opcs) || count($opcs)==0)    throw new Exception("Datos incompletos: productos");
  if (count($opcs) < 2 || count($opcs) > 4)   throw new Exception("Debes comparar entre 2 y 4 productos");

  // ---- 2) Transacción ----
  $conexion->begin_transaction();

  // Preparar consulta de producto (precio)
  $stmtP = $conexion->prepare("
    SELECT id_producto, precio_base
    FROM productos
    WHERE id_producto=? AND id_categoria=?
    LIMIT 1
  ");

  $subtotal = 0.0;
  $lineas   = [];

  foreach ($opcs as $p) {
    $id_producto = (int)($p["id_producto"] ?? 0);
    $cantidad    = max(1, (int)($p["cantidad"] ?? 1));
    $items       = is_array($p["items"] ?? null) ? $p["items"] : [];
    if ($id_producto <= 0) throw new Exception("Producto inválido");

    // Precio real desde BD (no confíes en el UI)
    $stmtP->bind_param("ii", $id_producto, $id_categoria);
    $stmtP->execute();
    $resP = $stmtP->get_result();
    if ($resP->num_rows === 0) throw new Exception("El producto $id_producto no pertenece a la categoría seleccionada");
    $rowP = $resP->fetch_assoc();
    $precio_unit = (float)$rowP["precio_base"];

    // Calcula extras
    $extras_fijo = 0.0;
    $extras_pct  = 0.0;
    foreach ($items as $it) {
      $modo   = ($it["modo_precio"] ?? "fijo") === "porcentaje" ? "porcentaje" : "fijo";
      $valor  = (float)($it["valor_precio"] ?? 0);
      $cantIt = max(1, (int)($it["cantidad"] ?? 1));
      if ($modo === "fijo") $extras_fijo += $valor * $cantIt;
      else                  $extras_pct  += $valor; // % acumulado
    }

    $base      = $precio_unit * $cantidad;
    $extras    = $extras_fijo + ($base * ($extras_pct/100.0));
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

  $impuestos = $subtotal * ($impuesto_pct/100.0);
  $total     = $subtotal + $impuestos;

  // ---- 3) Inserta cabecera (ahora con todas las columnas) ----
  $stmt = $conexion->prepare("
  INSERT INTO cotizaciones
    (id_usuario, id_cliente, id_categoria, total, subtotal, impuestos, estado, fecha_emision, moneda, notas, created_at, updated_at)
  VALUES
    (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, NOW(), NOW())
");

# Tipos: i i i d d d s s s  →  "iiidddsss"
$stmt->bind_param(
  "iiidddsss",
  $id_usuario,
  $id_cliente,
  $id_categoria,
  $total,
  $subtotal,
  $impuestos,
  $estado,
  $moneda,
  $notas
);

  // ojo: espacios en ii d d d s s s son para legibilidad, PHP los ignora.
  $stmt->execute();
  $id_cotizacion = $stmt->insert_id;
  $stmt->close();

  // ---- 4) Inserta detalle con verificación de errores ----
  $stmtD = $conexion->prepare("
    INSERT INTO detalle_cotizacion
      (id_cotizacion, id_producto, cantidad, precio_unitario, extras_json, extras_total, subtotal)
    VALUES
      (?, ?, ?, ?, ?, ?, ?)
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
    "ok"           => true,
    "id_cotizacion"=> $id_cotizacion,
    "subtotal"     => round($subtotal,2),
    "impuestos"    => round($impuestos,2),
    "total"        => round($total,2)
  ]);
} catch (Throwable $e) {
  if ($conexion && $conexion->errno === 0) {
    // si no hay transacción abierta, no hay rollback que hacer
    try { $conexion->rollback(); } catch (Throwable $e2) {}
  }
  http_response_code(500);
  echo json_encode([
    "ok"   => false,
    "msg"  => "Error guardando proforma",
    "error"=> $e->getMessage()
  ]);
}
