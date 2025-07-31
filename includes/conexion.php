<?php
// Conexión orientada a objetos (MySQLi)
$servername = "localhost";
$username = "admin";
$password = "admin";
$base = "sistema_cotizaciones"; 

$conexion = new mysqli($servername, $username, $password, $base);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$conexion->set_charset("utf8mb4");

?>
