<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../index.php");
    exit;
}

require_once("../includes/conexion.php");

$nombre = $_POST["nombre_completo"];
$correo = $_POST["correo"];
$nickname = $_POST["nickname"];
$password = password_hash($_POST["password"], PASSWORD_DEFAULT);
$fecha = $_POST["fecha_nacimiento"];
$rol = $_POST["id_rol"];
$estado = "activo";

// Validar que correo o nickname no existan
$verificar = $conexion->prepare("SELECT * FROM usuarios WHERE correo = ? OR nickname = ?");
$verificar->bind_param("ss", $correo, $nickname);
$verificar->execute();
$resultado = $verificar->get_result();

if ($resultado->num_rows > 0) {
    echo "Error: correo o nickname ya registrados.";
    exit;
}

// Insertar usuario
$stmt = $conexion->prepare("INSERT INTO usuarios (nombre_completo, correo, contraseña, nickname, fecha_nacimiento, id_rol, estado) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssis", $nombre, $correo, $password, $nickname, $fecha, $rol, $estado);

if ($stmt->execute()) {
    header("Location: lista_usuarios.php?registro=ok");
    exit;
} else {
    echo "Error al registrar usuario.";
}
?>
