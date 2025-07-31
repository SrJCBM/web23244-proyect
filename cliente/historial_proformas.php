<?php
session_start();
include("../includes/conexion.php");

// Verificar sesión activa
if (!isset($_SESSION["id_usuario"])) {
    echo "Debes iniciar sesión para ver tu historial.";
    exit;
}

$id_usuario = $_SESSION["id_usuario"];

$sql = "SELECT * FROM cotizaciones WHERE id_usuario = ? ORDER BY fecha_emision DESC";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();

echo "<h2>Historial de Proformas</h2>";

if ($resultado->num_rows === 0) {
    echo "<p>No has generado proformas aún.</p>";
} else {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Fecha</th><th>Total</th><th>Estado</th></tr>";

    while ($fila = $resultado->fetch_assoc()) {
        echo "<tr>";
        echo "<td>PRO-" . $fila["id_cotizacion"] . "</td>";
        echo "<td>" . $fila["fecha_emision"] . "</td>";
        echo "<td>$" . number_format($fila["total"], 2) . "</td>";
        echo "<td>" . $fila["estado"] . "</td>";
        echo "</tr>";
    }

    echo "</table>";
}
?>
