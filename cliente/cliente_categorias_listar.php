<?php
require_once("../includes/verificar_rol.php");
verificarRol([1,2,5,6]); // Admin, Vendedor, Supervisor, Analista
require_once("../includes/conexion.php");

header("Content-Type: application/json; charset=utf-8");

// SEGURIDAD: evitar notices en JSON
mysqli_report(MYSQLI_REPORT_OFF);

try {
  // Devuelve solo activas (tu columna se llama 'estado' y usa 'activa')
  $sql = "SELECT id_categoria, nombre
          FROM categorias
          WHERE estado IN ('activa','activo')
          ORDER BY nombre ASC";
  $rs = $conexion->query($sql);
  $out = [];
  if ($rs instanceof mysqli_result) {
    while ($r = $rs->fetch_assoc()) {
      $out[] = [
        "id_categoria" => (int)$r["id_categoria"],
        "nombre"       => (string)$r["nombre"]
      ];
    }
  }
  echo json_encode($out);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(["ok"=>false,"msg"=>"Error listando categorías","error"=>$e->getMessage()]);
}
