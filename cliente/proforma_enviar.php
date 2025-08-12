<?php
require_once("../includes/verificar_rol.php"); verificarRol([2,1,5]);
require_once("../includes/conexion.php");
require_once("../vendor/autoload.php");
use PHPMailer\PHPMailer\PHPMailer;

$id = (int)($_POST['id'] ?? 0);
if(!$id){ http_response_code(400); exit('ID requerido'); }

$row = $conexion->query("
  SELECT p.pdf_path, c.correo
  FROM cotizaciones p JOIN clientes c ON c.id_cliente=p.id_cliente
  WHERE p.id_cotizacion=$id")->fetch_assoc();

$pdf = $row['pdf_path'] ?? null;
$to = trim($_POST['correo'] ?? ($row['correo'] ?? ''));

if(!$pdf || !file_exists($pdf)){ http_response_code(400); exit('Genera el PDF primero.'); }
if(!$to){ http_response_code(400); exit('Correo requerido'); }

$mail = new PHPMailer(true);
/* ==== CONFIGURA TU SMTP AQUÍ ==== */
$mail->isSMTP();
$mail->Host = 'smtp.tu-servidor.com';
$mail->SMTPAuth = true;
$mail->Username = 'usuario@tu-servidor.com';
$mail->Password = 'CONTRASEÑA';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;
/* ================================= */

$mail->setFrom('ventas@tuempresa.com','Ventas');
$mail->addAddress($to);
$mail->Subject = "Proforma PRO-$id";
$mail->Body = "Adjuntamos su proforma PRO-$id. Gracias por su preferencia.";
$mail->addAttachment($pdf);
$mail->send();

$conexion->query("UPDATE cotizaciones SET estado='enviada' WHERE id_cotizacion=$id");
echo "OK";
