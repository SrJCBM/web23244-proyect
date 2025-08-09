<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("../includes/conexion.php");

$usuario = trim($_POST["usuario"]);
$password = $_POST["password"];

$sql = "SELECT * FROM usuarios WHERE correo = ? OR nickname = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ss", $usuario, $usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();

    if (password_verify($password, $row["contraseña"])) {
        $_SESSION["id_usuario"] = $row["id_usuario"];
        $_SESSION["nombre"] = $row["nombre_completo"];
        $_SESSION["id_rol"] = $row["id_rol"];
        if ($row["id_rol"] == 2) {
            $sqlEmpresa = "SELECT id_empresa FROM empresas_proveedoras WHERE id_usuario = ?";
            $stmtEmpresa = $conexion->prepare($sqlEmpresa);

            if ($stmtEmpresa) {
                $stmtEmpresa->bind_param("i", $row["id_usuario"]);
                $stmtEmpresa->execute();
                $resEmpresa = $stmtEmpresa->get_result();

                if ($empresa = $resEmpresa->fetch_assoc()) {
                    $_SESSION["id_empresa"] = $empresa["id_empresa"];
                } else {
                    header("Location: login.php?error=No se encontró empresa asociada al proveedor.");
                    exit();
                }

                $stmtEmpresa->close();
            } else {
                die("Error al preparar consulta de empresa: " . $conexion->error);
            }
        }

        $stmt->close();
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
