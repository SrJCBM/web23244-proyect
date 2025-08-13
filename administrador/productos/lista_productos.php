<?php
// administrador/productos/lista_productos.php
define('APP_DEBUG', true);
if (APP_DEBUG) { ini_set('display_errors', 1); error_reporting(E_ALL); }

require_once("../../includes/verificar_rol.php");

// PERMITIMOS VER a admin(1), vendedor(2), supervisor(5), analista(6)
verificarRol([1,2,5,6]);
require_once("../../includes/conexion.php");

// Rol del usuario para ocultar/mostrar acciones
$ROL = (int)($_SESSION['id_rol'] ?? 0);
$ES_ADMIN = in_array($ROL, [1,5]);      // admin o supervisor -> CRUD
$SOLO_LECTURA = !$ES_ADMIN;             // vendedor(2), analista(6)

// --------- Filtros y paginación -----------
$q        = trim($_GET['q'] ?? '');
$id_cat   = (int)($_GET['categoria'] ?? 0);
$id_emp   = (int)($_GET['empresa'] ?? 0);

$perPage  = 10;
$page     = max(1, (int)($_GET['page'] ?? 1));
$offset   = ($page - 1) * $perPage;

$where = ["1=1"];
$args  = [];
$types = "";

// Filtro de texto
if ($q !== '') {
  $where[] = "(p.nombre LIKE CONCAT('%',?,'%')
               OR p.marca LIKE CONCAT('%',?,'%')
               OR p.modelo LIKE CONCAT('%',?,'%'))";
  array_push($args, $q, $q, $q);
  $types .= "sss";
}
// Filtro categoría
if ($id_cat > 0) {
  $where[] = "p.id_categoria = ?";
  $args[]  = $id_cat;  $types .= "i";
}
// Filtro empresa
if ($id_emp > 0) {
  $where[] = "p.id_empresa = ?";
  $args[]  = $id_emp;  $types .= "i";
}

$whereSql = implode(" AND ", $where);

// --------- Listas para selects -----------
$cats = $conexion->query("SELECT id_categoria, nombre FROM categorias ORDER BY nombre ASC")->fetch_all(MYSQLI_ASSOC);
$emps = $conexion->query("SELECT id_empresa, nombre FROM empresas_proveedoras ORDER BY nombre ASC")->fetch_all(MYSQLI_ASSOC);

// --------- Total -----------
$sqlCount = "SELECT COUNT(*) n FROM productos p WHERE $whereSql";
$stmt = $conexion->prepare($sqlCount);
if ($types) $stmt->bind_param($types, ...$args);
$stmt->execute();
$total = (int)$stmt->get_result()->fetch_assoc()['n'];
$stmt->close();

$totalPages = max(1, (int)ceil($total / $perPage));

// --------- Página -----------
$sql = "
  SELECT p.id_producto, p.nombre, p.marca, p.modelo, p.precio_base, p.stock,
         p.id_categoria, p.id_empresa,
         c.nombre AS categoria, e.nombre AS empresa
  FROM productos p
  LEFT JOIN categorias c ON c.id_categoria=p.id_categoria
  LEFT JOIN empresas_proveedoras e ON e.id_empresa=p.id_empresa
  WHERE $whereSql
  ORDER BY p.id_producto DESC
  LIMIT ? OFFSET ?
";
$args2  = $args;  $types2 = $types . "ii";
array_push($args2, $perPage, $offset);

$stmt = $conexion->prepare($sql);
$stmt->bind_param($types2, ...$args2);
$stmt->execute();
$rs = $stmt->get_result();
?>
<h2>Productos</h2>

<!-- Filtros -->
<form onsubmit="cargarDirecto('administrador/productos/lista_productos.php?'+new URLSearchParams(new FormData(this)).toString()); return false;"
      style="margin:10px 0; display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
  <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Buscar por nombre, marca o modelo" style="padding:6px; width:260px;">
  <select name="categoria" style="padding:6px;">
    <option value="0">-- Todas las categorías --</option>
    <?php foreach ($cats as $c): ?>
      <option value="<?= (int)$c['id_categoria'] ?>" <?= $id_cat===$c['id_categoria']?'selected':'' ?>>
        <?= htmlspecialchars($c['nombre']) ?>
      </option>
    <?php endforeach; ?>
  </select>
  <select name="empresa" style="padding:6px;">
    <option value="0">-- Todas las empresas --</option>
    <?php foreach ($emps as $e): ?>
      <option value="<?= (int)$e['id_empresa'] ?>" <?= $id_emp===$e['id_empresa']?'selected':'' ?>>
        <?= htmlspecialchars($e['nombre']) ?>
      </option>
    <?php endforeach; ?>
  </select>
  <button class="btn btn-primary" type="submit">Filtrar</button>
</form>

<?php if ($ES_ADMIN): ?>
  <div style="margin-bottom:12px;">
    <a href="#" onclick="cargarDirecto('administrador/productos/crear_producto.php')">➕ Crear producto</a>
  </div>
<?php endif; ?>

<table border="1" cellspacing="0" cellpadding="6" style="width:100%; border-collapse:collapse;">
  <thead>
    <tr>
      <th>ID</th>
      <th>Empresa</th>
      <th>Categoría</th>
      <th>Nombre</th>
      <th>Marca</th>
      <th>Modelo</th>
      <th>Precio</th>
      <th>Stock</th>
      <?php if ($ES_ADMIN): ?><th>Acciones</th><?php endif; ?>
    </tr>
  </thead>
  <tbody>
    <?php if ($rs->num_rows === 0): ?>
      <tr><td colspan="<?= $ES_ADMIN?9:8 ?>" style="text-align:center;color:#666">Sin resultados.</td></tr>
    <?php else: ?>
      <?php while($p=$rs->fetch_assoc()): ?>
      <tr>
        <td><?= (int)$p['id_producto'] ?></td>
        <td><?= htmlspecialchars($p['empresa'] ?? '–') ?></td>
        <td><?= htmlspecialchars($p['categoria'] ?? '–') ?></td>
        <td><?= htmlspecialchars($p['nombre']) ?></td>
        <td><?= htmlspecialchars($p['marca']) ?></td>
        <td><?= htmlspecialchars($p['modelo']) ?></td>
        <td>$<?= number_format((float)$p['precio_base'],2) ?></td>
        <td><?= (int)$p['stock'] ?></td>
        <?php if ($ES_ADMIN): ?>
        <td>
          <a href="#" title="Editar"
             onclick="cargarDirecto('administrador/productos/editar_producto.php?id=<?= (int)$p['id_producto'] ?>&page=<?= $page ?>');return false;">✏️</a>
          &nbsp;
          <a href="#" title="Eliminar"
             onclick="if(!confirm('¿Eliminar este producto?'))return false;
                      cargarDirecto('administrador/productos/eliminar_producto.php?id=<?= (int)$p['id_producto'] ?>&page=<?= $page ?>');return false;">🗑️</a>
        </td>
        <?php endif; ?>
      </tr>
      <?php endwhile; ?>
    <?php endif; ?>
  </tbody>
</table>

<!-- Paginación -->
<?php if ($totalPages > 1): ?>
  <div style="margin:12px 0; display:flex; gap:8px; align-items:center;">
    <?php for($i=1;$i<=$totalPages;$i++): ?>
      <?php if ($i === $page): ?>
        <strong><?= $i ?></strong>
      <?php else: ?>
        <a href="#"
           onclick="cargarDirecto('administrador/productos/lista_productos.php?<?= http_build_query(['q'=>$q,'categoria'=>$id_cat,'empresa'=>$id_emp,'page'=>$i]) ?>');return false;"><?= $i ?></a>
      <?php endif; ?>
    <?php endfor; ?>
  </div>
<?php endif;

$stmt->close();
$conexion->close();
