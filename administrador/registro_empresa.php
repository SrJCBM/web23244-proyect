<?php
require_once("../includes/verificar_rol.php");
verificarRol([1,5]); 
require_once("../includes/conexion.php");

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
          && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
    }

    $nombre          = trim($_POST["nombre"] ?? '');
    $ruc             = trim($_POST["ruc"] ?? '');
    $direccion       = trim($_POST["direccion"] ?? '');
    $correo_contacto = trim($_POST["correo_contacto"] ?? '');
    $telefono        = trim($_POST["telefono"] ?? '');
    $id_usuario      = $_SESSION["id_usuario"] ?? null;

    if (!$id_usuario) {
        if ($isAjax) {
            http_response_code(401);
            echo json_encode(["ok" => false, "msg" => "Sesión no válida."]);
        } else {
            header("Location: ../index.php?err=sesion");
        }
        exit;
    }

    if ($nombre === '') {
        if ($isAjax) {
            http_response_code(422);
            echo json_encode(["ok" => false, "msg" => "El nombre es obligatorio."]);
        } else {
            header("Location: registro_empresa.php?msg=nombre_obligatorio");
        }
        exit;
    }

    if ($correo_contacto !== '' && !filter_var($correo_contacto, FILTER_VALIDATE_EMAIL)) {
        if ($isAjax) {
            http_response_code(422);
            echo json_encode(["ok" => false, "msg" => "El correo de contacto no es válido."]);
        } else {
            header("Location: registro_empresa.php?msg=correo_invalido");
        }
        exit;
    }

    $sql = "INSERT INTO empresas_proveedoras
            (nombre, ruc, direccion, correo_contacto, telefono, id_usuario)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sssssi", $nombre, $ruc, $direccion, $correo_contacto, $telefono, $id_usuario);

    if ($stmt->execute()) {
        if ($isAjax) {
            echo json_encode(["ok" => true, "id_empresa" => $stmt->insert_id]);
        } else {
            // Fallback sin JS: redirige a donde prefieras
            header("Location: ../index.php?ok=empresa_creada");
        }
    } else {
        if ($isAjax) {
            http_response_code(500);
            echo json_encode(["ok" => false, "msg" => "Error al registrar: " . $stmt->error]);
        } else {
            header("Location: registro_empresa.php?msg=error");
        }
    }

    $stmt->close();
    $conexion->close();
    exit;
}
?>

<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Registrar Empresa Proveedora</title>
  <style>
    .form-card{max-width:640px;margin:24px auto;padding:20px;border:1px solid #e5e7eb;border-radius:10px;font-family:system-ui,-apple-system,Segoe UI,Roboto}
    .form-card h2{margin:0 0 16px}
    .form-card label{display:block;margin-top:10px;font-weight:600}
    .form-card input[type="text"],.form-card input[type="email"]{width:100%;height:40px;margin-top:6px;padding:8px 10px;border:1px solid #d1d5db;border-radius:8px;box-sizing:border-box}
    .form-card button{margin-top:16px;height:44px;border:0;border-radius:8px;background:#003366;color:#fff;font-weight:700;cursor:pointer;width:100%}
    .form-card button:hover{filter:brightness(1.05)}
  </style>
</head>
<body>
  <div class="form-card">
    <h2>Registrar Empresa Proveedora</h2>

    <form id="formEmpresa" method="post" action="administrador/registro_empresa.php">
      <label>Nombre:</label>
      <input type="text" name="nombre" required>

      <label>RUC:</label>
      <input type="text" name="ruc">

      <label>Dirección:</label>
      <input type="text" name="direccion">

      <label>Correo de contacto:</label>
      <input type="email" name="correo_contacto">

      <label>Teléfono:</label>
      <input type="text" name="telefono">

      <button type="submit">Registrar Empresa</button>
    </form>
  </div>

  <script src="../assets/js/empresa.js"></script>
  <script>
    if (window.initEmpresaForm) window.initEmpresaForm();
  </script>
</body>
</html>
