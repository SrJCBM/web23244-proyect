<?php
require_once("../includes/verificar_rol.php");
verificarRol([1,5,6]); // Solo administrador

include("../includes/conexion.php");

// Obtener todas las empresas
$sql = "SELECT * FROM empresas_proveedoras ORDER BY created_at DESC";
$resultado = $conexion->query($sql);
?>

<h2>🏭 Empresas Proveedoras</h2>

<?php if ($resultado->num_rows > 0): ?>
  <table border="1" cellpadding="8" cellspacing="0">
    <tr>
      <th>Nombre</th>
      <th>RUC</th>
      <th>Dirección</th>
      <th>Correo</th>
      <th>Teléfono</th>
      <th>Estado</th>
      <th>Acciones</th>
    </tr>
    <?php while ($row = $resultado->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row["nombre"]) ?></td>
        <td><?= htmlspecialchars($row["ruc"]) ?></td>
        <td><?= htmlspecialchars($row["direccion"]) ?></td>
        <td><?= htmlspecialchars($row["correo_contacto"]) ?></td>
        <td><?= htmlspecialchars($row["telefono"]) ?></td>
        <td><?= $row["estado"] === "activa" ? "🟢 Activa" : "🔴 Inactiva" ?></td>
        <td>
          <?php if ($row["estado"] === "activa"): ?>
            <button onclick="cambiarEstadoEmpresa(<?= $row['id_empresa'] ?>, 'inactiva')">Inactivar</button>
          <?php else: ?>
            <button onclick="cambiarEstadoEmpresa(<?= $row['id_empresa'] ?>, 'activa')">Reactivar</button>
          <?php endif; ?>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>
<?php else: ?>
  <p>No hay empresas registradas.</p>
<?php endif; ?>

<script src="assets/js/empresa.js"></script>
