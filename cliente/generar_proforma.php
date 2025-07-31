<?php

function clienteId($usuarioId, $conexion) {
    $sql = "SELECT id_cliente FROM clientes WHERE id_usuario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $usuarioId);
    $stmt->execute();
    $stmt->bind_result($id_cliente);
    $stmt->fetch();
    $stmt->close();

    return $id_cliente ?? null;
}

session_start();
include("../includes/conexion.php");

$id_usuario = $_SESSION["id_usuario"];
$id_cliente = clienteId($id_usuario, $conexion);

// Verificar si hay carrito
if (empty($_SESSION["carrito"])) {
    echo "Tu carrito está vacío.";
    exit;
}

$ids = implode(",", array_keys($_SESSION["carrito"]));
$sql = "SELECT * FROM productos WHERE id_producto IN ($ids)";
$resultado = $conexion->query($sql);

// Calcular totales
$subtotal = 0;
$shipping_total = 0;
$productos = [];

while ($producto = $resultado->fetch_assoc()) {
    $id = $producto["id_producto"];
    $precio = $producto["precio_base"];
    $cantidad = $_SESSION["carrito"][$id];
    $sub = $precio * $cantidad;
    $subtotal += $sub;

    // Cálculo de envío
    if ($precio < 100) {
        $shipping_total += 5;
    } elseif ($precio <= 500) {
        $shipping_total += 20;
    } else {
        $shipping_total += 50;
    }

    $productos[] = [
        "id_producto" => $id,
        "cantidad" => $cantidad,
        "precio_unitario" => $precio,
        "subtotal" => $sub
    ];
}

$iva = $subtotal * 0.15;
$total_final = $subtotal + $iva + $shipping_total;

// Insertar cotización
$stmt = $conexion->prepare("INSERT INTO cotizaciones (id_usuario, id_cliente, total) VALUES (?, ?, ?)");
$stmt->bind_param("iid", $id_usuario, $id_cliente, $total_final);
$stmt->execute();
$id_cotizacion = $stmt->insert_id;
$stmt->close();

// Insertar detalle de cotización
foreach ($productos as $p) {
    $stmt = $conexion->prepare("INSERT INTO detalle_cotizacion (id_cotizacion, id_producto, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "iiidd",
        $id_cotizacion,
        $p["id_producto"],
        $p["cantidad"],
        $p["precio_unitario"],
        $p["subtotal"]
    );
    $stmt->execute();
    $stmt->close();
}

// Vaciar carrito
unset($_SESSION["carrito"]);

echo "<p>✅ Proforma generada exitosamente.</p>";
echo "<p><a href='../index.php'>Volver al inicio</a></p>";
?>
