/* ===========================
   Proforma Wizard (SPA)
   Archivo: assets/js/proforma_wizard.js
   =========================== */

/* ---------- Helpers (si el SPA no los definió aún) ---------- */
window.getJSON = window.getJSON || (async function (url) {
  const res = await fetch(url, { credentials: "same-origin" });
  if (!res.ok) throw new Error(`GET ${url} -> ${res.status}`);
  return await res.json();
});

window.postJSON = window.postJSON || (async function (url, data) {
  const res = await fetch(url, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    credentials: "same-origin",
    body: JSON.stringify(data),
  });
  if (!res.ok) throw new Error(`POST ${url} -> ${res.status}`);
  return await res.json();
});

/* ---------- Estado global del wizard ---------- */
window.proforma = window.proforma || {
  id_cliente: null,
  id_categoria: null,
  productos: [], // [{id_producto, marca, modelo, precio_base, cantidad, items:[]}]
};

/* ---------- Navegación de pasos ---------- */
window.nextPaso = function (n) {
  const root = document.querySelector(".wiz");
  if (!root) return;
  root.querySelectorAll(".step").forEach((s) => s.classList.remove("active"));
  const step = document.getElementById("paso" + n);
  if (step) step.classList.add("active");
};
window.prevPaso = function (n) {
  window.nextPaso(n);
};

/* ============================================================
   PASO 1: CLIENTE
   ============================================================ */
window.buscarClienteUI = async function () {
  const cedInput = document.getElementById("cliCedula");
  if (!cedInput) return;
  const ced = (cedInput.value || "").trim();
  if (!ced) {
    alert("Ingresa cédula");
    return;
  }

  const r = await window.getJSON(
    "cliente/cliente_clientes_buscar.php?cedula=" + encodeURIComponent(ced)
  );
  const box = document.getElementById("cliResultado");
  const nuevo = document.getElementById("cliNuevo");

  if (r && r.ok && r.found) {
    const c = r.cliente;
    window.proforma.id_cliente = c.id_cliente;
    if (box)
      box.innerHTML = `<b>Cliente:</b> ${c.nombre_comercial} — ${c.correo || ""} — ${c.telefono || ""}`;
    if (nuevo) nuevo.style.display = "none";
    const btn = document.getElementById("continuar1");
    if (btn) btn.disabled = false;
  } else {
    if (box)
      box.innerHTML =
        '<span style="color:#c00">No existe. Regístralo a la derecha.</span>';
    if (nuevo) nuevo.style.display = "block";
  }
};

window.registrarClienteUI = async function () {
  const payload = {
    cedula: (document.getElementById("cliCedula")?.value || "").trim(),
    nombre_comercial: (document.getElementById("cliNombre")?.value || "").trim(),
    correo: (document.getElementById("cliCorreo")?.value || "").trim(),
    telefono: (document.getElementById("cliTelefono")?.value || "").trim(),
    direccion: (document.getElementById("cliDireccion")?.value || "").trim(),
  };
  const r = await window.postJSON(
    "cliente/cliente_clientes_crear.php",
    payload
  );
  if (r && r.ok) {
    window.proforma.id_cliente = r.id_cliente;
    const box = document.getElementById("cliResultado");
    if (box) box.innerHTML = "<b>Cliente registrado.</b>";
    const nuevo = document.getElementById("cliNuevo");
    if (nuevo) nuevo.style.display = "none";
    const btn = document.getElementById("continuar1");
    if (btn) btn.disabled = false;
  } else {
    alert((r && r.msg) || "Error al registrar");
  }
};

/* ============================================================
   PASO 2: PRODUCTOS (MISMA CATEGORÍA)
   ============================================================ */
window.listarProductosUI = async function () {
  const sel = document.getElementById("selCategoria");
  const cont = document.getElementById("listaProductos");
  const qInp = document.getElementById("prodQuery");
  if (!sel || !cont) return;

  const idc = +sel.value;
  const q = qInp ? qInp.value || "" : "";
  if (!idc) {


    cont.innerHTML = "";
    return;
  }

  // Actualiza categoría en el estado global
  window.proforma.id_categoria = idc;

  try {
    const url =
      `cliente/cliente_productos_listar.php?id_categoria=${idc}` +
      `&q=${encodeURIComponent(q)}`;
    const data = await window.getJSON(url);

    if (!data || data.length === 0) {
      cont.innerHTML =
        '<div class="small" style="color:#666">No hay productos para esta categoría.</div>';
      return;
    }

    // Tarjetas en grilla (mejor legibilidad)
    cont.innerHTML = `<div class="cards-grid">${
      data
        .map((p) => {
          const checked = window.proforma.productos.find(
            (x) => x.id_producto == p.id_producto
          )
            ? "checked"
            : "";
          return `<label class="card">
            <input type="checkbox" ${checked} onchange="toggleProducto(${p.id_producto}, this.checked)">
            <div>
              <div><b>${p.marca || ""} ${p.modelo || ""}</b> — ${p.nombre || ""}</div>
              <div class="badge">$${(+p.precio_base).toFixed(2)}</div>
            </div>
          </label>`;
        })
        .join("")
    }</div>`;
  } catch (e) {
    console.error("Error listando productos:", e);
    cont.innerHTML =
      '<div class="small" style="color:#c00">Error cargando productos.</div>';
  }
};

window.toggleProducto = function (id, checked) {
  if (checked) {
    window.getJSON("cliente/cliente_producto_get.php?id=" + id).then((p) => {
      if (
        window.proforma.id_categoria &&
        p.id_categoria != window.proforma.id_categoria
      ) {
        alert("Solo productos de la misma categoría.");
        window.listarProductosUI();
        return;
      }
      if (!window.proforma.productos.find((x) => x.id_producto == id)) {
        window.proforma.productos.push({
          ...p,
          cantidad: 1,
          items: [],
        });
      }
    });
  } else {
    window.proforma.productos = window.proforma.productos.filter(
      (p) => p.id_producto != id
    );
  }
};

window.validarProductos = function () {
  const n = window.proforma.productos.length;
  if (n < 2 || n > 4) {
    alert("Selecciona entre 2 y 4 productos.");
    return;
  }
  window.cargarOpciones();
  window.nextPaso(3);
};

/* ============================================================
   PASO 3: COMPONENTES / ACCESORIOS
   ============================================================ */
window.cargarOpciones = async function () {
  const cont = document.getElementById("opcionsWrap");
  if (!cont) return;

  const opts = await window.getJSON(
    "cliente/cliente_opciones_listar.php?id_categoria=" +
      window.proforma.id_categoria
  );

  cont.innerHTML = window.proforma.productos
    .map((p, i) => {
      const lista = (opts || [])
        .map((o) => {
          const checked = o.obligatorio ? "checked disabled" : "";
          return `<label>
            <input type="checkbox" data-idx="${i}" data-op="${o.id_opcion}" ${checked} onchange="toggleItem(event)">
            ${o.tipo === "componente" ? "🧩" : "🔌"} ${o.nombre}
            <span class="badge">${
              o.modo_precio === "fijo"
                ? "$" + (+o.valor_precio).toFixed(2)
                : o.valor_precio + "%"
            }</span>
          </label>`;
        })
        .join("<br>");
      return `<div class="card">
        <h4>${p.marca} ${p.modelo} — $${(+p.precio_base).toFixed(2)}</h4>
        ${lista}
      </div>`;
    })
    .join("");

  // Aplica obligatorios al estado
  (opts || []).forEach((o) => {
    if (o.obligatorio) {
      window.proforma.productos.forEach((p) => {
        if (!p.items.find((it) => it.id_opcion == o.id_opcion)) {
          p.items.push({
            id_opcion: o.id_opcion,
            nombre: o.nombre,
            tipo: o.tipo,
            modo_precio: o.modo_precio,
            valor_precio: +o.valor_precio,
            cantidad: 1,
          });
        }
      });
    }
  });
};

window.toggleItem = function (ev) {
  const idx = +ev.target.dataset.idx;
  const idop = +ev.target.dataset.op;
  const checked = ev.target.checked;
  const p = window.proforma.productos[idx];

  window
    .getJSON(
      "cliente/cliente_opciones_listar.php?id_categoria=" +
        window.proforma.id_categoria
    )
    .then((opts) => {
      const o = (opts || []).find((x) => x.id_opcion == idop);
      if (!o) return;
      if (checked) {
        if (!p.items.find((it) => it.id_opcion == idop)) {
          p.items.push({
            id_opcion: o.id_opcion,
            nombre: o.nombre,
            tipo: o.tipo,
            modo_precio: o.modo_precio,
            valor_precio: +o.valor_precio,
            cantidad: 1,
          });
        }
      } else {
        p.items = p.items.filter((it) => it.id_opcion != idop);
      }
    });
};

/* ============================================================
   CÁLCULO Y RESUMEN
   ============================================================ */
window.calcOpcion = function (p) {
  const base = (+p.precio_base) * (p.cantidad || 1);
  let extrasF = 0,
    extrasPct = 0;
  (p.items || []).forEach((it) => {
    if (it.modo_precio === "fijo")
      extrasF += (+it.valor_precio) * (+it.cantidad || 1);
    else extrasPct += +it.valor_precio;
  });
  const extras = extrasF + base * (extrasPct / 100);
  return { base, extras, subtotal: base + extras };
};

window.armarResumen = function () {
  const cols = window.proforma.productos.map((p) => ({
    ...p,
    _calc: window.calcOpcion(p),
  }));

  let html = `<table class="table">
    <tr><th>Característica</th>${cols
      .map((p) => `<th>${p.marca} ${p.modelo}</th>`)
      .join("")}</tr>
    <tr><td><b>Precio base</b></td>${cols
      .map((p) => `<td>$${(+p.precio_base).toFixed(2)}</td>`)
      .join("")}</tr>
    <tr><td><b>Componentes/Accesorios</b></td>${
      cols
        .map(
          (p) => `<td>${
            (p.items || [])
              .map(
                (it) =>
                  `${it.nombre} <span class="badge">${
                    it.modo_precio === "fijo"
                      ? "$" + (+it.valor_precio).toFixed(2)
                      : it.valor_precio + "%"
                  }</span>`
              )
              .join("<br>")
          }</td>`
        )
        .join("")
    }</tr>
    <tr><td><b>Extras</b></td>${cols
      .map((p) => `<td>$${p._calc.extras.toFixed(2)}</td>`)
      .join("")}</tr>
    <tr><td><b>Subtotal</b></td>${cols
      .map((p) => `<td>$${p._calc.subtotal.toFixed(2)}</td>`)
      .join("")}</tr>
  </table>`;

  const res = document.getElementById("resumenTabla");
  if (res) res.innerHTML = `<div class="table-wrap">${html}</div>`;
  window.nextPaso(4);
};

/* ============================================================
   GUARDAR / PDF / ENVIAR
   ============================================================ */
window.guardarProformaUI = async function (estado, enviar = false) {
  // Validaciones mínimas claras
  if (!window.proforma.id_cliente) {
    alert("Selecciona o registra un cliente en el Paso 1.");
    return;
  }
  if (!window.proforma.id_categoria) {
    alert("Elige una categoría.");
    return;
  }
  if (
    !Array.isArray(window.proforma.productos) ||
    window.proforma.productos.length < 2
  ) {
    alert("Selecciona entre 2 y 4 productos.");
    return;
  }

  const payload = {
    id_cliente: window.proforma.id_cliente,
    id_categoria: window.proforma.id_categoria,
    estado,
    moneda: (document.getElementById("moneda")?.value || "USD").substr(0, 3),
    notas: document.getElementById("notas")?.value || "",
    impuesto_pct: +document.getElementById("impuestoPct")?.value || 0,
    opciones: window.proforma.productos.map((p) => ({
      id_producto: p.id_producto,
      cantidad: p.cantidad || 1,
      precio_unitario: +p.precio_base, // el backend validará con precio real
      items: (p.items || []).map((it) => ({
        id_opcion: it.id_opcion,
        nombre: it.nombre,
        tipo: it.tipo,
        modo_precio: it.modo_precio,
        valor_precio: +it.valor_precio,
        cantidad: it.cantidad || 1,
      })),
    })),
  };

  try {
    const r = await postJSON('cliente/proforma_guardar.php', payload);
if (!r || !r.ok) {
  alert((r && (r.msg + (r.error ? ('\n' + r.error) : ''))) || 'Error al guardar');
  return;
}

    if (estado === "emitida" || enviar) {
      await fetch("cliente/proforma_pdf.php?id=" + r.id_cotizacion, {
        credentials: "same-origin",
      });
    }
    if (enviar) {
      const fd = new FormData();
      fd.append("id", r.id_cotizacion);
      await fetch("cliente/proforma_enviar.php", {
        method: "POST",
        body: fd,
        credentials: "same-origin",
      });
      alert("Proforma enviada.");
    } else {
      alert("Proforma guardada.");
    }
    if (typeof cargarDirecto === "function")
      cargarDirecto("cliente/historial_proformas.php");
  } catch (e) {
    console.error(e);
    alert("No se pudo guardar la proforma.");
  }
};

/* ============================================================
   INICIALIZACIÓN DEL WIZARD
   - Guarda anti-doble init
   - Carga categorías una sola vez
   - Fallback con MutationObserver que se desconecta
   ============================================================ */
window.__PF_WIZARD_INITED = false;

window.initProformaWizard = async function () {
	window.proforma = window.proforma || { id_cliente:null, id_categoria:null, productos:[] };
  if (window.__PF_WIZARD_INITED) return; // evita múltiples inits
  window.__PF_WIZARD_INITED = true;

  const sel = document.getElementById("selCategoria");
  if (!sel) return;

  try {
    const cats = await window.getJSON("cliente/cliente_categorias_listar.php");

    sel.innerHTML =
      '<option value="">-- Selecciona una categoría --</option>' +
      (cats || [])
        .map(
          (c) => `<option value="${c.id_categoria}">${c.nombre}</option>`
        )
        .join("");

    // Change (una sola vez)
    sel.onchange = () => {
      if (typeof window.listarProductosUI === "function")
        window.listarProductosUI();
    };

    // Primera carga
    if (cats && cats.length > 0) {
      sel.value = cats[0].id_categoria;
      if (typeof window.listarProductosUI === "function")
        window.listarProductosUI();
    }
  } catch (e) {
    console.error("Error cargando categorías:", e);
    sel.innerHTML = '<option value="">(Error cargando categorías)</option>';
  }
};

// Fallback: intenta iniciar UNA SOLA VEZ cuando el wizard aparezca
(function () {
  function tryInit() {
    if (
      !window.__PF_WIZARD_INITED &&
      document.getElementById("selCategoria") &&
      typeof window.initProformaWizard === "function"
    ) {
      window.initProformaWizard();
      if (obs) obs.disconnect(); // clave: corta bucles
    }
  }
  document.addEventListener("DOMContentLoaded", tryInit);
  const target = document.getElementById("contenido") || document.body;
  const obs = new MutationObserver(() => tryInit());
  obs.observe(target, { childList: true, subtree: true });
})();