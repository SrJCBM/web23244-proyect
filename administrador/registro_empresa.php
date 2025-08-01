<?php
require_once("../includes/verificar_rol.php");
verificarRol([1]); // Solo administrador

include("../includes/conexion.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = $_POST["nombre"];
    $ruc = $_POST["ruc"];
    $direccion = $_POST["direccion"];
    $telefono = $_POST["telefono"];
    $estado = "activa";

    $stmt = $conexion->prepare("INSERT INTO empresas_proveedoras (nombre, ruc, direccion, telefono, estado) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $nombre, $ruc, $direccion, $telefono, $estado);

    if ($stmt->execute()) {
        echo "<p style='color:green;'>Empresa registrada correctamente.</p>";
    } else {
        echo "<p style='color:red;'>Error al registrar: " . $stmt->error . "</p>";
    }

    $stmt->close();
    $conexion->close();
    exit;
}
?>

<h2>Registrar Empresa Proveedora</h2>
<form id="formEmpresa">
  <label>Nombre:</label><br>
  <input type="text" name="nombre" required><br><br>

  <label>RUC:</label><br>
  <input type="text" name="ruc" required><br><br>

  <label>Dirección:</label><br>
  <input type="text" name="direccion" required><br><br>

  <label>Teléfono:</label><br>
  <input type="text" name="telefono" required><br><br>

  <button type="submit">Registrar Empresa</button>
</form>

<script src="../assets/js/empresa.js"></script>
