<?php
require_once("../../includes/verificar_rol.php");
verificarRol([2]);

include("../../includes/conexion.php");

$id_empresa = $_SESSION["id_empresa"];

$sql = "SELECT * FROM empresas_proveedoras WHERE id_empresa = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_empresa);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p style='color:red;'>⚠️ Empresa no encontrada.</p>";
    exit;
}

$empresa = $result->fetch_assoc();
?>

<h2>🏢 Perfil de Empresa Proveedora</h2>
<table border="1" cellpadding="8">
  <tr><th>Nombre</th><td><?= htmlspecialchars($empresa["nombre"]) ?></td></tr>
  <tr><th>RUC</th><td><?= htmlspecialchars($empresa["ruc"]) ?></td></tr>
  <tr><th>Dirección</th><td><?= htmlspecialchars($empresa["direccion"]) ?></td></tr>
  <tr><th>Correo de Contacto</th><td><?= htmlspecialchars($empresa["correo_contacto"]) ?></td></tr>
  <tr><th>Teléfono</th><td><?= htmlspecialchars($empresa["telefono"]) ?></td></tr>
  <tr><th>Estado</th><td><?= htmlspecialchars($empresa["estado"]) ?></td></tr>
</table>
