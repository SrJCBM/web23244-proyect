<?php
session_start();
require_once("../includes/conexion.php");

// Helper: id_cliente
function clienteId($usuarioId, $conexion) {
    $sql = "SELECT id_cliente FROM clientes WHERE id_usuario = ? LIMIT 1";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $usuarioId);
    $stmt->execute();
    $id_cliente = null;
    $stmt->bind_result($id_cliente);
    $stmt->fetch();
    $stmt->close();
    return $id_cliente ?: null;
}

$id_usuario = $_SESSION["id_usuario"] ?? null;
if (!$id_usuario) { echo "Sesión expirada."; exit; }

$id_cliente = clienteId($id_usuario, $conexion);

if (empty($_SESSION["carrito"]) || !is_array($_SESSION["carrito"])) {
    echo "Tu carrito está vacío.";
    exit;
}
$carrito = $_SESSION["carrito"]; // [id_producto => cantidad]

$ids = array_map('intval', array_keys($carrito));
$placeholders = implode(',', array_fill(0, count($ids), '?'));

$types = str_repeat('i', count($ids));
$sql = "SELECT id_producto, precio_base FROM productos WHERE id_producto IN ($placeholders)";
$stmt = $conexion->prepare($sql);
$stmt->bind_param($types, ...$ids);
$stmt->execute();
$res = $stmt->get_result();

$subtotal = 0.0;
$shipping_total = 0.0;
$lineas = []; 

while ($row = $res->fetch_assoc()) {
    $id = (int)$row['id_producto'];
    $precio = (float)$row['precio_base'];
    $cantidad = (int)$carrito[$id];

    $line_subtotal = $precio * $cantidad; 
    $subtotal += $line_subtotal;

    if ($precio < 100)       $shipping_total += 5;
    elseif ($precio <= 500)  $shipping_total += 20;
    else                     $shipping_total += 50;

    $lineas[] = [
        'id_producto'    => $id,
        'cantidad'       => $cantidad,
        'precio_unit'    => $precio,
        'extras_json'    => null,   // si no manejas extras aún, deja NULL o '[]'
        'extras_total'   => 0.00,   // 0 por ahora
        'subtotal'       => $line_subtotal
    ];
}
$stmt->close();

if (!$lineas) { echo "No se encontraron productos válidos."; exit; }

$iva = $subtotal * 0.15;
$total_final = $subtotal + $iva + $shipping_total;

$conexion->begin_transaction();

try {
    $stmt = $conexion->prepare(
        "INSERT INTO cotizaciones (id_usuario, id_cliente, total) VALUES (?, ?, ?)"
    );
    $stmt->bind_param("iid", $id_usuario, $id_cliente, $total_final);
    $stmt->execute();
    $id_cotizacion = $stmt->insert_id;
    $stmt->close();

    $stmt = $conexion->prepare(
        "INSERT INTO detalle_cotizacion
         (id_cotizacion, id_producto, cantidad, precio_unitario, extras_json, extras_total, subtotal)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    foreach ($lineas as $l) {
        $stmt->bind_param(
            "iiidsdd",
            $id_cotizacion,
            $l['id_producto'],
            $l['cantidad'],
            $l['precio_unit'],
            $l['extras_json'],   
            $l['extras_total'],
            $l['subtotal']
        );
        $stmt->execute();
    }
    $stmt->close();

    $conexion->commit();

    unset($_SESSION["carrito"]);
    echo "<p>✅ Proforma #{$id_cotizacion} generada exitosamente.</p>";
    echo "<p>Total productos: " . number_format($subtotal,2) . " | IVA: " . number_format($iva,2) . " | Envío: " . number_format($shipping_total,2) . "</p>";
    echo "<p>Total final: <strong>" . number_format($total_final,2) . "</strong></p>";
    echo "<p><a href='../index.php'>Volver al inicio</a></p>";

} catch (Throwable $e) {
    $conexion->rollback();
    echo "<p style='color:red'>Error al generar la proforma: ".htmlspecialchars($e->getMessage())."</p>";
}
