function cargar(seccion) {
  fetch(`cliente/${seccion}.php`)
    .then(res => res.text())
    .then(html => {
      document.getElementById("contenido").innerHTML = html;
    })
    .catch(err => {
      document.getElementById("contenido").innerHTML = "<p>Error al cargar contenido.</p>";
      console.error(err);
    });
}

function cargarDetalle(id) {
  fetch(`cliente/detalle_producto.php?id=${id}`)
    .then(res => res.text())
    .then(html => {
      document.getElementById("contenido").innerHTML = html;
    })
    .catch(err => {
      document.getElementById("contenido").innerHTML = "<p>Error al cargar detalle del producto.</p>";
      console.error(err);
    });
}

function agregarProducto(id) {
  fetch(`cliente/proforma.php?agregar=${id}`)
    .then(res => res.text())
    .then(() => {
      alert("Producto agregado a la proforma.");
      cargar('proforma'); // Recargar la proforma para mostrar el nuevo producto
    })
    .catch(err => {
      console.error("Error al agregar producto:", err);
    });
}


function quitarProducto(id) {
  if (confirm("¿Deseas quitar este producto de la proforma?")) {
    fetch(`cliente/proforma.php?eliminar=${id}`)
      .then(() => {
        cargar("proforma"); // Recargar la proforma para reflejar el cambio
      })
      .catch(err => {
        console.error("Error al quitar producto:", err);
      });
  }
}

function actualizarCantidades() {
  const form = document.getElementById("formCantidad");
  const formData = new FormData(form);

  fetch("cliente/proforma.php", {
    method: "POST",
    body: formData
  })
    .then(() => {
      cargar("proforma"); // Recargar la proforma para mostrar las cantidades actualizadas
    })
    .catch(err => {
      console.error("Error al actualizar cantidades:", err);
    });
}

function cargarDirecto(ruta) {
  fetch(ruta)
    .then(res => {
      if (!res.ok) throw new Error(`No se pudo cargar ${ruta}`);
      return res.text();
    })
    .then(html => {
      const cont = document.getElementById("contenido");

      // --- RESET si vamos a cargar el wizard ---
      const isWizardRoute = /proforma_wizard\.php$/i.test(ruta);
      if (isWizardRoute) {
        // resetea el estado global del wizard
        window.__PF_WIZARD_INITED = false;
        window.proforma = { id_cliente: null, id_categoria: null, productos: [] };
      }

      cont.innerHTML = html;

      // Detecta wizard por DOM también (por si no vino con esa ruta)
      const isWizardDom = !!(cont.querySelector('.wiz') || cont.querySelector('#selCategoria'));
      if ((isWizardRoute || isWizardDom) && typeof window.initProformaWizard === 'function') {
        // protege: si otro reset era necesario
        window.__PF_WIZARD_INITED = false;
        setTimeout(() => window.initProformaWizard(), 0);
      }
    })
    .catch(err => {
      console.error("Error al cargar:", err);
      document.getElementById("contenido").innerHTML = "<p>Error al cargar contenido.</p>";
    });
}


async function getJSON(url) {
  const res = await fetch(url, { credentials: 'same-origin' });
  if (!res.ok) throw new Error(`GET ${url} -> ${res.status}`);
  return await res.json();
}

async function postJSON(url, data) {
  const res = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'same-origin',
    body: JSON.stringify(data)
  });
  if (!res.ok) throw new Error(`POST ${url} -> ${res.status}`);
  return await res.json();
}
