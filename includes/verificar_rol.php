<?php
session_start();

function verificarRol(array $rolesPermitidos) {
    // Verificar si hay una sesión activa
    if (!isset($_SESSION["id_usuario"]) || !isset($_SESSION["id_rol"])) {
        header("Location: ../auth/login.php?error=Acceso denegado (sesión no iniciada)");
        exit();
    }

    // Verificar si el rol del usuario está entre los permitidos
    if (!in_array($_SESSION["id_rol"], $rolesPermitidos)) {
        echo "<p style='color:red;'>⚠️ Acceso restringido: No tienes permisos suficientes.</p>";
        exit();
    }
}
