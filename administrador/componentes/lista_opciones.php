<?php
// administrador/componentes/lista_opciones.php
define('APP_DEBUG', true);
if (APP_DEBUG) { ini_set('display_errors', 1); error_reporting(E_ALL); }

require_once("../../includes/verificar_rol.php");
// Pueden ver: 1,2,5,6. CRUD sólo 1 y 5.
verificarRol([1,2,5,6]);
require_once("../../includes/conexion.php");

$ROL = (int)($_SESSION['id_rol'] ?? 0);
$ES_ADMIN = in_array($ROL,[1,5]);

$id_cat = (int)($_GET['categoria'] ?? 0);
$cats = $conexion->query("SELECT id_categoria,nombre FROM categorias ORDER BY nombre")->fetch_all(MYSQLI_ASSOC);

$where = "1=1";
if ($id_cat>0) $where = "oc.id_categoria=".$id_cat;

$sql = "
  SELECT oc.id_opcion, oc.id_categoria, c.nombre AS categoria,
         oc.nombre, oc.tipo, oc.modo_precio, oc.valor_precio, oc.obligatorio, oc.visible
  FROM opciones_categoria oc
  JOIN categorias c ON c.id_categoria=oc.id_categoria
  WHERE $where
  ORDER BY oc.id_categoria, oc.tipo, oc.nombre
";
$rs = $conexion->query($sql);
?>
<h2>Componentes / Accesorios por categoría</h2>

<form onsubmit="cargarDirecto('administrador/componentes/lista_opciones.php?'+new URLSearchParams(new FormData(this)).toString()); return false;"
      style="margin:10px 0; display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
  <select name="categoria" style="padding:6px;">
    <option value="0">-- Todas las categorías --</option>
    <?php foreach($cats as $c): ?>
      <option value="<?= (int)$c['id_categoria'] ?>" <?= $id_cat===$c['id_categoria']?'selected':'' ?>>
        <?= htmlspecialchars($c['nombre']) ?>
      </option>
    <?php endforeach; ?>
  </select>
  <button class="btn btn-primary" type="submit">Filtrar</button>
  <?php if ($ES_ADMIN): ?>
    <a href="#" onclick="cargarDirecto('administrador/componentes/crear_opcion.php<?= $id_cat?('?categoria='.$id_cat):'' ?>');return false;">➕ Crear opción</a>
  <?php endif; ?>
</form>

<table border="1" cellspacing="0" cellpadding="6" style="width:100%; border-collapse:collapse;">
  <thead>
    <tr>
      <th>ID</th>
      <th>Categoría</th>
      <th>Nombre</th>
      <th>Tipo</th>
      <th>Modo precio</th>
      <th>Valor</th>
      <th>Oblig.</th>
      <th>Visible</th>
      <?php if ($ES_ADMIN): ?><th>Acciones</th><?php endif; ?>
    </tr>
  </thead>
  <tbody>
  <?php if ($rs->num_rows===0): ?>
    <tr><td colspan="<?= $ES_ADMIN?9:8 ?>" style="text-align:center;color:#666">Sin resultados.</td></tr>
  <?php else: while($r=$rs->fetch_assoc()): ?>
    <tr>
      <td><?= (int)$r['id_opcion'] ?></td>
      <td><?= htmlspecialchars($r['categoria']) ?></td>
      <td><?= htmlspecialchars($r['nombre']) ?></td>
      <td><?= htmlspecialchars($r['tipo']) ?></td>
      <td><?= htmlspecialchars($r['modo_precio']) ?></td>
      <td><?= ($r['modo_precio']==='fijo'?'$':'').number_format((float)$r['valor_precio'],2).($r['modo_precio']==='porcentaje'?'%':'') ?></td>
      <td><?= $r['obligatorio']? 'Sí':'No' ?></td>
      <td><?= $r['visible']? 'Sí':'No' ?></td>
      <?php if ($ES_ADMIN): ?>
      <td>
        <a href="#" onclick="cargarDirecto('administrador/componentes/editar_opcion.php?id=<?= (int)$r['id_opcion'] ?>');return false;">✏️</a>
        &nbsp;
        <a href="#"
           onclick="if(!confirm('¿Eliminar esta opción?'))return false;
                    cargarDirecto('administrador/componentes/eliminar_opcion.php?id=<?= (int)$r['id_opcion'] ?>');return false;">🗑️</a>
      </td>
      <?php endif; ?>
    </tr>
  <?php endwhile; endif; ?>
  </tbody>
</table>
