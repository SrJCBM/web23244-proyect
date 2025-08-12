<?php
require_once("../includes/verificar_rol.php"); verificarRol([2,1,5]);
require_once("../includes/conexion.php");
require_once("../vendor/autoload.php");
use Dompdf\Dompdf;

$id = (int)($_GET['id'] ?? 0);
if(!$id) { http_response_code(400); exit('ID requerido'); }

// Cabecera
$stmt = $conexion->prepare("SELECT p.*, c.nombre_comercial, c.correo, c.telefono, cat.nombre AS categoria
  FROM cotizaciones p
  JOIN clientes c ON c.id_cliente = p.id_cliente
  LEFT JOIN categorias cat ON cat.id_categoria = p.id_categoria
  WHERE p.id_cotizacion=?");
$stmt->bind_param("i",$id); $stmt->execute();
$pro = $stmt->get_result()->fetch_assoc();
if(!$pro){ exit('No existe proforma'); }

// Detalle
$det = $conexion->query("SELECT d.*, pr.nombre, pr.marca, pr.modelo FROM detalle_cotizacion d
  JOIN productos pr ON pr.id_producto=d.id_producto
  WHERE d.id_cotizacion=".$id." ORDER BY pr.marca, pr.modelo")->fetch_all(MYSQLI_ASSOC);

// HTML
ob_start(); ?>
<html><head><meta charset="utf-8"><style>
body{font-family:Arial;font-size:12px}
h2{margin:0 0 8px}
.table{width:100%;border-collapse:collapse}
.table th,.table td{border:1px solid #ccc;padding:6px;text-align:left;vertical-align:top}
.badge{background:#eee;padding:2px 6px;border-radius:6px}
</style></head><body>
<h2>PROFORMA PRO-<?= (int)$id ?></h2>
<p><b>Cliente:</b> <?= htmlspecialchars($pro['nombre_comercial']) ?> — <?= htmlspecialchars($pro['correo']) ?> — <?= htmlspecialchars($pro['telefono']) ?></p>
<p><b>Categoría:</b> <?= htmlspecialchars($pro['categoria'] ?? '') ?> &nbsp; <b>Fecha:</b> <?= htmlspecialchars($pro['fecha_emision']) ?></p>

<table class="table">
  <tr>
    <th>Característica</th>
    <?php foreach($det as $d): ?>
      <th><?= htmlspecialchars($d['marca'].' '.$d['modelo']) ?></th>
    <?php endforeach; ?>
  </tr>
  <tr>
    <td><b>Precio base</b></td>
    <?php foreach($det as $d): ?>
      <td>$<?= number_format($d['precio_unitario'],2) ?></td>
    <?php endforeach; ?>
  </tr>
  <tr>
    <td><b>Componentes/Accesorios</b></td>
    <?php foreach($det as $d): $items=json_decode($d['extras_json']??'[]',true) ?: []; ?>
      <td>
        <?php foreach($items as $it): ?>
          <?= htmlspecialchars($it['nombre']) ?>
          <span class="badge">
            <?= $it['modo_precio']==='fijo' ? ('$'.number_format($it['valor_precio'],2)) : ($it['valor_precio'].'%') ?>
          </span><br>
        <?php endforeach; ?>
      </td>
    <?php endforeach; ?>
  </tr>
  <tr>
    <td><b>Extras</b></td>
    <?php foreach($det as $d): ?>
      <td>$<?= number_format($d['extras_total'],2) ?></td>
    <?php endforeach; ?>
  </tr>
  <tr>
    <td><b>Subtotal</b></td>
    <?php foreach($det as $d): ?>
      <td>$<?= number_format($d['subtotal'],2) ?></td>
    <?php endforeach; ?>
  </tr>
</table>

<p style="text-align:right">
  <b>Subtotal:</b> $<?= number_format($pro['subtotal'],2) ?><br>
  <b>Impuestos:</b> $<?= number_format($pro['impuestos'],2) ?><br>
  <b>Total:</b> $<?= number_format($pro['total'],2) ?>
</p>
</body></html>
<?php
$html = ob_get_clean();

$dompdf = new Dompdf();
$dompdf->loadHtml($html,'UTF-8');
$dompdf->setPaper('A4','portrait');
$dompdf->render();

$path = __DIR__."/../storage/proformas";
if(!is_dir($path)) mkdir($path,0777,true);
$file = $path."/PRO-".$id.".pdf";
file_put_contents($file, $dompdf->output());

// guarda ruta
$st = $conexion->prepare("UPDATE cotizaciones SET pdf_path=? WHERE id_cotizacion=?");
$st->bind_param("si",$file,$id); $st->execute();

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="PRO-'.$id.'.pdf"');
echo $dompdf->output();
