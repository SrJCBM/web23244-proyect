<?php
session_start();
require_once("../includes/verificar_rol.php"); verificarRol([2,1,5]);
require_once("../includes/conexion.php");

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { echo "<p>ID inválido.</p>"; exit; }

// Cabecera
$sqlCab = "SELECT p.*, cli.nombre_comercial, cli.correo, cli.telefono,
                  COALESCE(cat.nombre,'') AS categoria
           FROM cotizaciones p
           JOIN clientes cli ON cli.id_cliente=p.id_cliente
           LEFT JOIN categorias cat ON cat.id_categoria=p.id_categoria
           WHERE p.id_cotizacion=?";
$stmt = $conexion->prepare($sqlCab);
$stmt->bind_param("i", $id);
$stmt->execute();
$cab = $stmt->get_result()->fetch_assoc();
$stmt->close();
if(!$cab){ echo "<p>No existe la proforma.</p>"; exit; }

// Detalle
$sqlDet = "SELECT d.*, pr.nombre, pr.marca, pr.modelo
           FROM detalle_cotizacion d
           JOIN productos pr ON pr.id_producto=d.id_producto
           WHERE d.id_cotizacion=?
           ORDER BY pr.marca, pr.modelo";
$stmt = $conexion->prepare($sqlDet);
$stmt->bind_param("i", $id);
$stmt->execute();
$det = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<h2>Proforma PRO-<?= (int)$cab['id_cotizacion'] ?></h2>
<p>
  <b>Cliente:</b> <?= htmlspecialchars($cab['nombre_comercial']) ?> —
  <?= htmlspecialchars($cab['correo']) ?> —
  <?= htmlspecialchars($cab['telefono']) ?><br>
  <b>Categoría:</b> <?= htmlspecialchars($cab['categoria']) ?> &nbsp;
  <b>Fecha:</b> <?= htmlspecialchars($cab['fecha_emision']) ?> &nbsp;
  <b>Estado:</b> <?= htmlspecialchars($cab['estado']) ?>
</p>

<table border="1" cellpadding="6" cellspacing="0" style="width:100%;border-collapse:collapse">
  <tr>
    <th>Producto</th>
    <th>Cantidad</th>
    <th>Precio unitario</th>
    <th>Extras</th>
    <th>Subtotal</th>
  </tr>
  <?php foreach($det as $d): ?>
    <tr>
      <td><?= htmlspecialchars($d['marca'].' '.$d['modelo']) ?><br><small><?= htmlspecialchars($d['nombre']) ?></small></td>
      <td><?= (int)$d['cantidad'] ?></td>
      <td>$<?= number_format($d['precio_unitario'],2) ?></td>
      <td>
        <?php
          $items = json_decode($d['extras_json'] ?? '[]', true) ?: [];
          if(!$items){ echo '$0.00'; }
          else {
            foreach($items as $it){
              $et = ($it['modo_precio']==='fijo'
                ? '$'.number_format((float)$it['valor_precio'],2)
                : ((float)$it['valor_precio']).'%');
              echo htmlspecialchars($it['nombre'])." <span style='background:#eee;padding:2px 6px;border-radius:6px'>{$et}</span><br>";
            }
          }
        ?>
      </td>
      <td>$<?= number_format($d['subtotal'],2) ?></td>
    </tr>
  <?php endforeach; ?>
</table>

<p style="text-align:right">
  <b>Subtotal:</b> $<?= number_format((float)$cab['subtotal'],2) ?><br>
  <b>Impuestos:</b> $<?= number_format((float)$cab['impuestos'],2) ?><br>
  <b>Total:</b> <span style="font-size:1.1em">$<?= number_format((float)$cab['total'],2) ?></span>
</p>

<div style="margin-top:10px; display:flex; gap:10px;">
  <button class="btn btn-light" onclick="cargarDirecto('cliente/historial_proformas.php')">Volver</button>
  <a class="btn btn-primary" href="cliente/proforma_pdf.php?id=<?= (int)$cab['id_cotizacion'] ?>" target="_blank" rel="noopener">Abrir PDF</a>
</div>
