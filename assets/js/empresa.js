// assets/js/empresa.js
window.initEmpresaForm = function initEmpresaForm() {
  const form = document.getElementById("formEmpresa");
  if (!form) return;

  // Evitar doble binding si recargas la vista
  if (form.dataset.bound === "1") return;
  form.dataset.bound = "1";

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const formData = new FormData(form);

    try {
      const res = await fetch(form.getAttribute('action'), {
        method: 'POST',
        body: new FormData(form),
        headers: { "Accept": "application/json", "X-Requested-With": "XMLHttpRequest" }
      });

      const data = await res.json();

      if (!data.ok) {
        alert("❌ " + (data.msg || "No se pudo registrar la empresa."));
        return;
      }

      alert("✅ Empresa registrada correctamente.");
      form.reset();

      if (typeof cargarDirecto === "function") {
        cargarDirecto("Electrodomesticos/proveedor/perfil_empresa.php");
      } else {
        window.location.href = "index.php";
      }
    } catch (err) {
      console.error(err);
      alert("❌ Ocurrió un error al registrar la empresa.");
    }
  });
};

document.addEventListener("DOMContentLoaded", () => {
  if (typeof window.initEmpresaForm === "function") window.initEmpresaForm();
});

window.cambiarEstadoEmpresa = async function (id, nuevoEstado) {
  if (!confirm(`¿Seguro que deseas cambiar el estado a ${nuevoEstado}?`)) return;
  try {
    await fetch(
      `administrador/actualizar_estado_empresa.php?id=${encodeURIComponent(id)}&estado=${encodeURIComponent(nuevoEstado)}`
    );
    location.reload();
  } catch (err) {
    console.error(err);
    alert("Error al actualizar estado.");
  }
};
