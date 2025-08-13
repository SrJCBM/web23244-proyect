<?php
require_once("../includes/verificar_rol.php");
verificarRol([2,1,5]);
require_once("../includes/conexion.php");
?>
<style>
.wiz{max-width:1120px;margin:auto}
.wiz .step{display:none}
.wiz .step.active{display:block}
.badge{padding:2px 6px;border-radius:6px;background:#eee;margin-left:6px;font-size:12px}
.card{border:1px solid #ddd;border-radius:8px;padding:12px;margin:8px 0}
.grid{display:grid;gap:12px}
.grid.cols-2{grid-template-columns:1fr 1fr}
.grid.cols-3{grid-template-columns:repeat(3,1fr)}
.table{width:100%;border-collapse:collapse}
.table th,.table td{border:1px solid #ddd;padding:6px;text-align:left;vertical-align:top}
.btn{padding:8px 14px;border:none;border-radius:6px;cursor:pointer}
.btn-primary{background:#003366;color:#fff}
.btn-light{background:#f1f1f1}
.btn-danger{background:#dc3545;color:#fff}
.small{font-size:12px;color:#666}
	
/* ---- LISTA DE PRODUCTOS (tarjetas) ---- */
.cards-grid{
  display:grid;
  gap:12px;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  align-items: stretch;
}

.cards-grid .card{
  display:flex;
  align-items:flex-start;
  gap:10px;
  margin:0;                 /* la grid maneja el espaciado */
  padding:10px 12px;
  border:1px solid #e5e7eb;
  border-radius:10px;
  background:#fff;
  box-shadow:0 1px 2px rgba(0,0,0,.04);
}

.cards-grid .card input[type="checkbox"]{
  margin-top:4px;
}

.cards-grid .card b{
  display:inline-block;
  margin-right:6px;
}

/* ---- TABLA RESUMEN ---- */
.table-wrap{ overflow:auto; }
.table{ width:100%; border-collapse:collapse; min-width:640px; }
.table th, .table td{
  border:1px solid #e5e7eb; padding:10px; text-align:left; vertical-align:top;
}
.table th{ background:#f8fafc; }

/* Botones en la fila inferior */
.wiz .btn{ padding:10px 14px; border-radius:8px; }

/* Inputs */
.wiz input, .wiz select{ height:36px; padding:6px 10px; border:1px solid #cbd5e1; border-radius:8px; }

</style>

<div class="wiz">
  <h2>Generar proforma</h2>
  <div class="small" style="margin-bottom:8px">1) Cliente → 2) Productos → 3) Componentes → 4) Resumen</div>

  <!-- Paso 1 -->
  <div class="step active" id="paso1">
    <div class="card">
      <h3>1) Cliente</h3>
      <div class="grid cols-2">
        <div>
          <label>Cédula<br><input id="cliCedula" maxlength="20"></label>
          <button class="btn btn-primary" type="button" onclick="buscarClienteUI()" style="margin-left:8px">Buscar</button>
          <div id="cliResultado" style="margin-top:10px"></div>
        </div>
        <div id="cliNuevo" style="display:none">
          <h4>Registrar nuevo</h4>
          <label>Nombre<br><input id="cliNombre"></label><br>
          <label>Correo<br><input id="cliCorreo"></label><br>
          <label>Teléfono<br><input id="cliTelefono"></label><br>
          <label>Dirección<br><input id="cliDireccion"></label><br><br>
          <button class="btn btn-primary" type="button" onclick="registrarClienteUI()">Guardar y continuar</button>

        </div>
      </div>
      <div style="margin-top:10px">
        <button id="continuar1" class="btn btn-light" type="button" onclick="nextPaso(2)" disabled>Continuar</button>

      </div>
    </div>
  </div>

  <!-- Paso 2 -->
  <div class="step" id="paso2">
    <div class="card">
      <h3>2) Productos (misma categoría)</h3>
      <div>
        <label>Categoría
          <select id="selCategoria" onchange="listarProductosUI()"></select>
        </label>
        <label style="margin-left:10px">Buscar
          <input id="prodQuery" oninput="listarProductosUI()">
        </label>
      </div>
      <div id="listaProductos" style="max-height:320px;overflow:auto;margin-top:10px"></div>
      <div class="small">Selecciona de 2 a 4 productos de la MISMA categoría y de marcas distintas.</div>
      <div style="margin-top:10px">
        <button class="btn btn-light" type="button" onclick="prevPaso(1)">Atrás</button>
		<button class="btn btn-primary" type="button" onclick="validarProductos()">Continuar</button>
      </div>
    </div>
  </div>

  <!-- Paso 3 -->
  <div class="step" id="paso3">
    <div class="card">
      <h3>3) Componentes y accesorios</h3>
      <div id="opcionsWrap"></div>
      <div style="margin-top:10px">
        <button class="btn btn-light" type="button" onclick="prevPaso(2)">Atrás</button>
		<button class="btn btn-primary" type="button" onclick="armarResumen()">Continuar</button>
      </div>
    </div>
  </div>

  <!-- Paso 4 -->
  <div class="step" id="paso4">
    <div class="card">
      <h3>4) Resumen</h3>
      <div id="resumenTabla"></div>
      <div class="grid cols-3" style="margin-top:10px">
        <label>Impuesto %<br><input id="impuestoPct" type="number" value="15" min="0" max="50"></label>
        <label>Moneda<br><input id="moneda" value="USD" maxlength="3"></label>
        <label>Notas<br><input id="notas"></label>
      </div>
      <div style="margin-top:10px">
        <button class="btn btn-light" type="button" onclick="prevPaso(3)">Atrás</button>
		<button class="btn btn-primary" type="button" onclick="guardarProformaUI('emitida')">Guardar</button>
      </div>
    </div>
  </div>
</div>