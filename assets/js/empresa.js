document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("formEmpresa");

  form.addEventListener("submit", function (e) {
    e.preventDefault();

    const formData = new FormData(form);

    fetch("registro_empresa.php", {
      method: "POST",
      body: formData
    })
      .then(res => res.text())
      .then(respuesta => {
        alert("✅ Empresa registrada correctamente.");
        form.reset(); // Limpiar formulario
        cargarDirecto("Electrodomesticos/proveedor/perfil_empresa.php"); // Redirige al perfil
      })
      .catch(err => {
        console.error("Error:", err);
        alert("❌ Ocurrió un error al registrar la empresa.");
      });
  });
});


function cambiarEstadoEmpresa(id, nuevoEstado) {
  if (!confirm(`¿Seguro que deseas cambiar el estado a ${nuevoEstado}?`)) return;

  fetch(`admin/actualizar_estado_empresa.php?id=${id}&estado=${nuevoEstado}`)
    .then(res => res.text())
    .then(() => location.reload())
    .catch(err => {
      alert("Error al actualizar estado.");
      console.error(err);
    });
}