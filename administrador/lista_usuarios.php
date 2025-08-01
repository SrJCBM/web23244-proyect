<?php
require_once("../includes/verificar_rol.php");
verificarRol([1]);

include("../includes/conexion.php");

if (isset($_GET["toggle_estado"])) {
  $id = intval($_GET["toggle_estado"]);
  $sql = "UPDATE usuarios SET estado = IF(estado = 'activo', 'inactivo', 'activo') WHERE id_usuario = ?";
  $stmt = $conexion->prepare($sql);
  $stmt->bind_param("i", $id);
  $stmt->execute();
  header("Location: admin/lista_usuarios.php");
  exit;
}

// Procesar cambio de rol
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["cambiar_rol"])) {
  $id = intval($_POST["id_usuario"]);
  $nuevo_rol = intval($_POST["nuevo_rol"]);
  $sql = "UPDATE usuarios SET id_rol = ? WHERE id_usuario = ?";
  $stmt = $conexion->prepare($sql);
  $stmt->bind_param("ii", $nuevo_rol, $id);
  $stmt->execute();
  header("Location: admin/lista_usuarios.php");
  exit;
}

// Obtener usuarios
$sql = "
SELECT u.id_usuario, u.nombre_completo, u.correo, u.nickname, u.estado, u.id_rol, r.nombre_rol
FROM usuarios u
LEFT JOIN roles r ON u.id_rol = r.id_rol
ORDER BY u.nombre_completo
";
$resultado = $conexion->query($sql);
?>

<h2>👥 Lista de Usuarios</h2>

<table border="1" cellpadding="6">
  <tr>
    <th>Nombre</th>
    <th>Correo</th>
    <th>Rol</th>
    <th>Estado</th>
    <th>Cambiar Rol</th>
    <th>Activar/Inactivar</th>
  </tr>

  <?php while ($row = $resultado->fetch_assoc()): ?>
    <tr>
      <td><?= htmlspecialchars($row["nombre_completo"]) ?></td>
      <td><?= htmlspecialchars($row["correo"]) ?></td>
      <td><?= htmlspecialchars($row["nombre_rol"]) ?></td>
      <td><?= $row["estado"] === "activo" ? "✅ Activo" : "⛔ Inactivo" ?></td>
      <td>
        <?php if ($_SESSION["id_usuario"] != $row["id_usuario"]): ?>
          <form method="POST" style="display:inline;">
            <input type="hidden" name="id_usuario" value="<?= $row["id_usuario"] ?>">
            <select name="nuevo_rol">
              <option value="1" <?= $row["id_rol"] == 1 ? "selected" : "" ?>>Administrador</option>
              <option value="2" <?= $row["id_rol"] == 2 ? "selected" : "" ?>>Proveedor</option>
              <option value="3" <?= $row["id_rol"] == 3 ? "selected" : "" ?>>Cliente</option>
            </select>
            <button type="submit" name="cambiar_rol">Cambiar</button>
          </form>
        <?php else: ?>
          <em>(Tú)</em>
        <?php endif; ?>
      </td>
      <td>
        <?php if ($_SESSION["id_usuario"] != $row["id_usuario"]): ?>
          <a href="?toggle_estado=<?= $row["id_usuario"] ?>" onclick="return confirm('¿Seguro?')">
            <?= $row["estado"] === "activo" ? "❌ Desactivar" : "✅ Activar" ?>
          </a>
        <?php else: ?>
          —
        <?php endif; ?>
      </td>
    </tr>
  <?php endwhile; ?>
</table>
