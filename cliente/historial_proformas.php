<?php
session_start();
require_once("../includes/verificar_rol.php"); verificarRol([2,1,5]);
require_once("../includes/conexion.php");

$idUsuario = $_SESSION['id_usuario'];

$stmt = $conexion->prepare("
  SELECT p.id_cotizacion, p.fecha_emision, p.total, p.estado,
         c.nombre_comercial AS cliente, cat.nombre AS categoria, p.pdf_path
  FROM cotizaciones p
  JOIN clientes c ON c.id_cliente=p.id_cliente
  LEFT JOIN categorias cat ON cat.id_categoria=p.id_categoria
  WHERE p.id_usuario=? ORDER BY p.fecha_emision DESC");
$stmt->bind_param("i",$idUsuario);
$stmt->execute();
$rs = $stmt->get_result();
?>
<h2>Mis proformas</h2>
<table border="1" cellpadding="6" cellspacing="0" style="width:100%;border-collapse:collapse">
  <tr><th>PRO-#</th><th>Fecha</th><th>Cliente</th><th>Categoría</th><th>Total</th><th>Estado</th><th>Acciones</th></tr>
  <?php while($r=$rs->fetch_assoc()): ?>
  <tr>
    <td>PRO-<?= (int)$r['id_cotizacion'] ?></td>
    <td><?= htmlspecialchars($r['fecha_emision']) ?></td>
    <td><?= htmlspecialchars($r['cliente']) ?></td>
    <td><?= htmlspecialchars($r['categoria'] ?? '') ?></td>
    <td>$<?= number_format($r['total'],2) ?></td>
    <td><?= htmlspecialchars($r['estado']) ?></td>
    <td>
      <a href="#" onclick="cargarDirecto('cliente/proforma_pdf.php?id=<?= (int)$r['id_cotizacion'] ?>'); return false;">PDF</a> |
      <a href="#" onclick="enviarProforma(<?= (int)$r['id_cotizacion'] ?>); return false;">Enviar</a>
    </td>
  </tr>
  <?php endwhile; ?>
</table>
<script>
async function enviarProforma(id){
  const correo = prompt('Correo destino (dejar vacío para usar el del cliente)');
  const fd = new FormData(); fd.append('id', id); if(correo) fd.append('correo', correo);
  const r = await fetch('cliente/proforma_enviar.php', {method:'POST', body:fd, credentials:'same-origin'});
  if(r.ok) alert('Enviado'); else alert('Error al enviar');
}
</script>
