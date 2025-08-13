<?php
require_once("../includes/verificar_rol.php");
// Solo admin y supervisor crean
verificarRol([1,5]);
require_once("../includes/conexion.php");
$conexion->set_charset("utf8mb4");

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
          && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  if ($isAjax) header('Content-Type: application/json; charset=utf-8');

  $nombre          = trim($_POST["nombre"] ?? '');
  $ruc             = trim($_POST["ruc"] ?? '');
  $direccion       = trim($_POST["direccion"] ?? '');
  $correo_contacto = trim($_POST["correo_contacto"] ?? '');
  $telefono        = trim($_POST["telefono"] ?? '');
  $id_usuario      = (int)($_SESSION["id_usuario"] ?? 0);

  if (!$id_usuario) { http_response_code(401); echo $isAjax?json_encode(["ok"=>false,"msg"=>"Sesión no válida."]):""; exit; }
  if ($nombre==='') { http_response_code(422); echo $isAjax?json_encode(["ok"=>false,"msg"=>"El nombre es obligatorio."]):""; exit; }
  if ($correo_contacto!=='' && !filter_var($correo_contacto,FILTER_VALIDATE_EMAIL)) {
    http_response_code(422); echo $isAjax?json_encode(["ok"=>false,"msg"=>"Correo de contacto inválido."]):""; exit;
  }

  $sql = "INSERT INTO empresas_proveedoras
            (nombre, ruc, direccion, correo_contacto, telefono, estado, created_at, updated_at, id_usuario)
          VALUES (?,?,?,?,?,'activa',NOW(),NOW(),?)";
  $st = $conexion->prepare($sql);
  $st->bind_param("sssssi",$nombre,$ruc,$direccion,$correo_contacto,$telefono,$id_usuario);

  if ($st->execute()){
    echo $isAjax ? json_encode(["ok"=>true,"id_empresa"=>$st->insert_id])
                 : header("Location: ../index.php?ok=empresa_creada");
  } else {
    http_response_code(500);
    echo $isAjax?json_encode(["ok"=>false,"msg"=>"Error: ".$st->error]):"";
  }
  $st->close(); $conexion->close(); exit;
}
?>

<div class="form-card" style="max-width:640px;margin:24px auto;padding:20px;border:1px solid #e5e7eb;border-radius:10px;font-family:system-ui,-apple-system,Segoe UI,Roboto">
  <h2 style="margin:0 0 16px">Registrar Empresa Proveedora</h2>
  <form id="formEmpresa" method="post" action="administrador/registro_empresa.php">
    <label>Nombre:</label>
    <input type="text" name="nombre" required style="width:100%;height:40px;margin-top:6px;padding:8px 10px;border:1px solid #d1d5db;border-radius:8px;">
    <label style="display:block;margin-top:10px;font-weight:600">RUC:</label>
    <input type="text" name="ruc" style="width:100%;height:40px;margin-top:6px;padding:8px 10px;border:1px solid #d1d5db;border-radius:8px;">
    <label style="display:block;margin-top:10px;font-weight:600">Dirección:</label>
    <input type="text" name="direccion" style="width:100%;height:40px;margin-top:6px;padding:8px 10px;border:1px solid #d1d5db;border-radius:8px;">
    <label style="display:block;margin-top:10px;font-weight:600">Correo de contacto:</label>
    <input type="email" name="correo_contacto" style="width:100%;height:40px;margin-top:6px;padding:8px 10px;border:1px solid #d1d5db;border-radius:8px;">
    <label style="display:block;margin-top:10px;font-weight:600">Teléfono:</label>
    <input type="text" name="telefono" style="width:100%;height:40px;margin-top:6px;padding:8px 10px;border:1px solid #d1d5db;border-radius:8px;">

    <button type="submit" style="margin-top:16px;height:44px;border:0;border-radius:8px;background:#003366;color:#fff;font-weight:700;cursor:pointer;width:100%">Registrar Empresa</button>
  </form>
</div>

<script src="../assets/js/empresa.js"></script>
<script>if (window.initEmpresaForm) window.initEmpresaForm();</script>
