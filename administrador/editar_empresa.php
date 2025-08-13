<?php
require_once("../includes/verificar_rol.php");
verificarRol([1]); // Solo admin para editar

require_once("../includes/conexion.php");

$id  = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pageActual = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
if ($id <= 0) { echo "<p style='color:red;'>ID inválido.</p>"; exit; }

// Traer empresa
$e = $conexion->prepare("SELECT id_empresa, nombre, ruc, direccion, correo_contacto, telefono, estado FROM empresas_proveedoras WHERE id_empresa=? LIMIT 1");
$e->bind_param("i", $id);
$e->execute();
$res = $e->get_result();
if ($res->num_rows === 0) { echo "<p style='color:red;'>Empresa no encontrada.</p>"; exit; }
$empresa = $res->fetch_assoc();
$e->close();

// POST: actualizar
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $nombre  = $_POST['nombre'] ?? '';
  $ruc     = $_POST['ruc'] ?? '';
  $dir     = $_POST['direccion'] ?? '';
  $correo  = $_POST['correo_contacto'] ?? '';
  $tel     = $_POST['telefono'] ?? '';
  $estado  = $_POST['estado'] ?? 'activa';

  $stmt = $conexion->prepare("
    UPDATE empresas_proveedoras
       SET nombre=?, ruc=?, direccion=?, correo_contacto=?, telefono=?, estado=?
     WHERE id_empresa=? LIMIT 1
  ");
  if (!$stmt) { echo "<p style='color:red;'>Error prepare(): ".htmlspecialchars($conexion->error)."</p>"; exit; }
  $stmt->bind_param("ssssssi", $nombre, $ruc, $dir, $correo, $tel, $estado, $id);

  if ($stmt->execute()) {
    echo "<p style='color:green;'>✅ Empresa actualizada correctamente.</p>";
    echo "<a href=\"#\" onclick=\"cargarDirecto('administrador/lista_empresas.php?page={$pageActual}')\">🔙 Volver a la lista</a>";
    $stmt->close(); $conexion->close(); exit;
  } else {
    echo "<p style='color:red;'>Error al actualizar: ".htmlspecialchars($stmt->error)."</p>";
  }
  $stmt->close();

  // Actualizar datos locales si hubo error
  $empresa = [
    'id_empresa'       => $id,
    'nombre'           => $nombre,
    'ruc'              => $ruc,
    'direccion'        => $dir,
    'correo_contacto'  => $correo,
    'telefono'         => $tel,
    'estado'           => $estado,
  ];
}

$conexion->close();
?>
<h2>✏️ Editar empresa #<?= (int)$empresa['id_empresa'] ?></h2>

<form method="post" onsubmit="
  event.preventDefault();
  const fd = new FormData(this);
  fetch('administrador/editar_empresa.php?id=<?= (int)$empresa['id_empresa'] ?>&page=<?= $pageActual ?>', { method:'POST', body:fd })
    .then(r => r.text())
    .then(html => { document.getElementById('contenido').innerHTML = html; })
    .catch(err => { console.error(err); alert('Error al guardar'); });
  return false;">
  <div class="form-row cols-2">
    <div>
      <label>Nombre</label>
      <input type="text" name="nombre" value="<?= htmlspecialchars($empresa['nombre']) ?>" required>
    </div>
    <div>
      <label>RUC</label>
      <input type="text" name="ruc" value="<?= htmlspecialchars($empresa['ruc']) ?>" required>
    </div>
    <div>
      <label>Dirección</label>
      <input type="text" name="direccion" value="<?= htmlspecialchars($empresa['direccion']) ?>">
    </div>
    <div>
      <label>Correo</label>
      <input type="email" name="correo_contacto" value="<?= htmlspecialchars($empresa['correo_contacto']) ?>">
    </div>
    <div>
      <label>Teléfono</label>
      <input type="text" name="telefono" value="<?= htmlspecialchars($empresa['telefono']) ?>">
    </div>
    <div>
      <label>Estado</label>
      <select name="estado" required>
        <option value="activa"   <?= $empresa['estado']==='activa' ? 'selected':'' ?>>Activa</option>
        <option value="inactiva" <?= $empresa['estado']==='inactiva' ? 'selected':'' ?>>Inactiva</option>
      </select>
    </div>
  </div>

  <div class="mt-2">
    <button class="btn-primary" type="submit">Guardar cambios</button>
    <button class="btn-ghost" type="button"
            onclick="cargarDirecto('administrador/lista_empresas.php?page=<?= $pageActual ?>')">Cancelar</button>
  </div>
</form>
