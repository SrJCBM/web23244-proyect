<?php
define('APP_DEBUG', true);
if (APP_DEBUG) { ini_set('display_errors', 1); error_reporting(E_ALL); }

require_once("../../includes/verificar_rol.php");
verificarRol([1,5]);
require_once("../../includes/conexion.php");

$id = (int)($_GET['id'] ?? 0);
if ($id<=0) { echo "ID inválido"; exit; }

$op = $conexion->query("SELECT id_opcion, id_categoria FROM opciones_categoria WHERE id_opcion=".$id)->fetch_assoc();
if(!$op){ echo "No existe"; exit; }

$conexion->query("DELETE FROM opciones_categoria WHERE id_opcion=".$id);
echo "<script>cargarDirecto('administrador/componentes/lista_opciones.php?categoria=".$op['id_categoria']."');</script>";
