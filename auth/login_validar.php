<?php
session_start();

// Mostrar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("../includes/conexion.php");

// Obtener valores del formulario
$usuario = trim($_POST["usuario"]);
$password = $_POST["password"];

// Consulta usando tus columnas reales: correo, nickname, contraseña
$sql = "SELECT * FROM usuarios WHERE correo = ? OR nickname = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ss", $usuario, $usuario);
$stmt->execute();
$result = $stmt->get_result();

// Verificar si encontró usuario
if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();

    // Validar contraseña usando password_verify()
	
    if (password_verify($password, $row["contraseña"])) {
        $_SESSION["id_usuario"] = $row["id_usuario"];
        $_SESSION["nombre"] = $row["nombre_completo"];
        $_SESSION["rol"] = $row["id_rol"];
        header("Location: ../index.php");
        exit();
    } else {
        header("Location: login.php?error=Contraseña incorrecta");
        exit();
    }
} else {
    header("Location: login.php?error=Usuario no encontrado");
    exit();
}
