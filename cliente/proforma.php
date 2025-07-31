<?php
session_start();

// Inicializar carrito si no existe
if (!isset($_SESSION["carrito"])) {
    $_SESSION["carrito"] = [];
}

// Procesar agregar producto (por GET temporalmente, luego será mejorado)
if (isset($_GET["agregar"])) {
    $id = $_GET["agregar"];

    if (!isset($_SESSION["carrito"][$id])) {
        $_SESSION["carrito"][$id] = 1;
    } else {
        $_SESSION["carrito"][$id]++;
    }
}

// Procesar quitar producto
if (isset($_GET["eliminar"])) {
    $id = $_GET["eliminar"];
    unset($_SESSION["carrito"][$id]);
}

// Procesar modificar cantidad
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["cantidades"])) {
    foreach ($_POST["cantidades"] as $id => $cantidad) {
        $_SESSION["carrito"][$id] = max(1, intval($cantidad));
    }
    // Terminar ejecución silenciosamente si se trata de un fetch
    exit;
}

include("../includes/conexion.php");

echo "<h2>Mi Proforma (Carrito)</h2>";

if (empty($_SESSION["carrito"])) {
    echo "<p>No has agregado productos aún.</p>";
    return;
}

$ids = implode(",", array_keys($_SESSION["carrito"]));
$sql = "SELECT * FROM productos WHERE id_producto IN ($ids)";
$resultado = $conexion->query($sql);

$total = 0;
$shipping_total = 0;
echo "<form id='formCantidad'>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Producto</th><th>Precio</th><th>Cantidad</th><th>Subtotal</th><th>Quitar</th></tr>";

while ($producto = $resultado->fetch_assoc()) {
    $id = $producto["id_producto"];
    $precio = $producto["precio_base"];
    $cantidad = $_SESSION["carrito"][$id];
    $subtotal = $precio * $cantidad;
    $total += $subtotal;

    if ($precio < 100) {
        $shipping_total += 5;
    } elseif ($precio <= 500) {
        $shipping_total += 20;
    } else {
        $shipping_total += 50;
    }

    echo "<tr>";
    echo "<td>" . htmlspecialchars($producto["nombre"]) . "</td>";
    echo "<td>$" . number_format($precio, 2) . "</td>";
    echo "<td><input type='number' name='cantidades[$id]' value='$cantidad' min='1'></td>";
    echo "<td>$" . number_format($subtotal, 2) . "</td>";
    echo "<td><button type='button' onclick='quitarProducto($id)'>❌</button></td>";
    echo "</tr>";
}
echo "</table>";

$iva = $total * 0.15;
$total_final = $total + $iva + $shipping_total;

echo "<p>Subtotal: $" . number_format($total, 2) . "</p>";
echo "<p>IVA (15%): $" . number_format($iva, 2) . "</p>";
echo "<p>Envío estimado: $" . number_format($shipping_total, 2) . "</p>";
echo "<p><strong>Total: $" . number_format($total_final, 2) . "</strong></p>";

echo "<button type='button' onclick='actualizarCantidades()'>Actualizar cantidades</button>";
echo "</form>";

echo "<br><form method='POST' action='generar_proforma.php'>";
echo "<button type='submit' name='confirmar'>Generar Proforma</button>";
echo "</form>";
?>
