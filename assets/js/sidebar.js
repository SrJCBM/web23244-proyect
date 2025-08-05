function toggleSidebar() {
  const sidebar = document.getElementById("sidebar");
  const wrapper = document.getElementById("contenido-wrapper");
  const toggleBtn = document.getElementById("sidebarToggle");

  sidebar.classList.toggle("active");
  wrapper.classList.toggle("sidebar-open");
  toggleBtn.classList.toggle("sidebar-open"); 
}

function cargarDirecto(url) {
  fetch(url)
    .then(res => res.text())
    .then(html => document.getElementById("contenido").innerHTML = html)
    .catch(err => console.error("Error cargando:", err));
}

function cargar(vista) {
  fetch(`cliente/${vista}.php`)
    .then(res => res.text())
    .then(html => document.getElementById("contenido").innerHTML = html)
    .catch(err => console.error("Error cargando:", err));
}
