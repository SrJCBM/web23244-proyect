<?php
include("../includes/conexion.php");

$sql = "SELECT * FROM productos WHERE estado = 'activo'";
$resultado = $conexion->query($sql);

echo "<h2>Catálogo de Productos</h2>";
if ($resultado->num_rows > 0) {
    while ($producto = $resultado->fetch_assoc()) {
        echo "<div style='border:1px solid #ccc; padding:10px; margin:10px 0;'>";
        echo "<strong>" . htmlspecialchars($producto["nombre"]) . "</strong><br>";
        echo "Marca: " . htmlspecialchars($producto["marca"]) . "<br>";
        echo "Modelo: " . htmlspecialchars($producto["modelo"]) . "<br>";
        echo "Precio: $" . $producto["precio_base"] . "<br>";
        echo "<button onclick='agregarProducto(" . $producto["id_producto"] . ")'>Agregar a proforma</button>";
        echo "</div>";
    }
} else {
    echo "No hay productos disponibles.";
}


?>
