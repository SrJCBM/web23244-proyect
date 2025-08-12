<?php
session_start();
require_once("../includes/conexion.php");
require_once("../includes/auditoria_accesos.php");

cerrarAcceso($conexion, 'logout');   

session_destroy();
header("Location: ../cliente/landing.php");
exit();
