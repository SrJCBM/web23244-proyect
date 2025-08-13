<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function verificarRol(array $rolesPermitidos) {
    if (!isset($_SESSION["id_usuario"]) || !isset($_SESSION["id_rol"])) {
        header("Location: ../auth/login.php?error=Acceso denegado (sesión no iniciada)");
        exit();
    }

    if (!in_array($_SESSION["id_rol"], $rolesPermitidos)) {
        echo "<p style='color:red;'>⚠️ Acceso restringido: No tienes permisos suficientes.</p>";
        exit();
    }
}
