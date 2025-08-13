<?php
require_once("../includes/verificar_rol.php");
verificarRol([1]);
require_once("../includes/conexion.php");

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pageActual = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

if ($id <= 0) { echo "<p style='color:red;'>ID inválido.</p>"; require_once __DIR__ . '/lista_usuarios.php'; exit; }

// No permitir eliminarse a sí mismo
if ($id === (int)$_SESSION['id_usuario']) {
  echo "<p style='color:red;'>No puedes eliminar tu propia cuenta.</p>";
  require_once __DIR__ . '/lista_usuarios.php';
  exit;
}

// Obtener rol/estado del usuario a eliminar
$u = $conexion->prepare("SELECT id_rol, estado FROM usuarios WHERE id_usuario=? LIMIT 1");
$u->bind_param("i", $id);
$u->execute();
$u->bind_result($u_rol, $u_estado);
$ok = $u->fetch();
$u->close();

if (!$ok) {
  echo "<p style='color:#b36b00;'>Usuario no encontrado.</p>";
  require_once __DIR__ . '/lista_usuarios.php';
  exit;
}

// Proteger al último admin activo
if ((int)$u_rol === 1 && $u_estado === 'activo') {
  $c = $conexion->query("SELECT COUNT(*) AS n FROM usuarios WHERE id_rol=1 AND estado='activo'");
  $row = $c->fetch_assoc(); $admins_activos = (int)($row['n'] ?? 0);
  if ($admins_activos <= 1) {
    echo "<p style='color:red;'>No puedes eliminar al último administrador activo.</p>";
    require_once __DIR__ . '/lista_usuarios.php';
    exit;
  }
}

// Eliminar (HARD DELETE)
$stmt = $conexion->prepare("DELETE FROM usuarios WHERE id_usuario=? LIMIT 1");
if (!$stmt) { echo "<p style='color:red;'>Error prepare(): ".htmlspecialchars($conexion->error)."</p>"; require_once __DIR__ . '/lista_usuarios.php'; exit; }
$stmt->bind_param("i", $id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
  echo "<p style='color:green;'>🗑️ Usuario eliminado correctamente (ID $id).</p>";
} else {
  echo "<p style='color:red;'>Error al eliminar: ".htmlspecialchars($stmt->error)."</p>";
}
$stmt->close();

// Mantener la misma página en la recarga
$_GET['page'] = $pageActual;
require_once __DIR__ . '/lista_usuarios.php';
