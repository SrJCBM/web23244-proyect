<?php
session_start();
include("../includes/conexion.php");

if (!isset($_SESSION["id_usuario"])) {
    echo "<p>No has iniciado sesión.</p>";
    exit;
}

$id_usuario = $_SESSION["id_usuario"];
$msg = "";

// Procesar actualización
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre_completo"]);
    $nickname = trim($_POST["nickname"]);
    $nueva_clave = $_POST["nueva_clave"];

    // Validaciones básicas
    if ($nombre === "" || $nickname === "") {
        $msg = "Nombre y nickname son obligatorios.";
    } else {
        if ($nueva_clave !== "") {
            $clave_hash = password_hash($nueva_clave, PASSWORD_BCRYPT);
            $sql = "UPDATE usuarios SET nombre_completo=?, nickname=?, contraseña=? WHERE id_usuario=?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("sssi", $nombre, $nickname, $clave_hash, $id_usuario);
        } else {
            $sql = "UPDATE usuarios SET nombre_completo=?, nickname=? WHERE id_usuario=?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("ssi", $nombre, $nickname, $id_usuario);
        }

        if ($stmt->execute()) {
            $msg = "Datos actualizados correctamente.";
        } else {
            $msg = "Error al actualizar: " . $stmt->error;
        }
    }
}

// Cargar datos actuales
$sql = "SELECT nombre_completo, correo, nickname, fecha_nacimiento FROM usuarios WHERE id_usuario=?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();
?>

<h2>Mi Perfil</h2>

<?php if ($msg) echo "<p><strong>$msg</strong></p>"; ?>

<form method="POST">
  <label>Nombre completo:</label><br>
  <input type="text" name="nombre_completo" value="<?= htmlspecialchars($usuario["nombre_completo"]) ?>" required><br><br>

  <label>Correo electrónico (no editable):</label><br>
  <input type="email" value="<?= htmlspecialchars($usuario["correo"]) ?>" disabled><br><br>

  <label>Nickname:</label><br>
  <input type="text" name="nickname" value="<?= htmlspecialchars($usuario["nickname"]) ?>" required><br><br>

  <label>Fecha de nacimiento (no editable):</label><br>
  <input type="text" value="<?= htmlspecialchars($usuario["fecha_nacimiento"]) ?>" disabled><br><br>

  <label>Nueva contraseña (opcional):</label><br>
  <input type="password" name="nueva_clave"><br><br>

  <button type="submit">Guardar Cambios</button>
</form>
