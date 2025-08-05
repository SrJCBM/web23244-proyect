<?php include("../includes/header.php"); ?>
<link rel="stylesheet" href="../assets/css/registro.css">

<div class="form-wrapper">
  <form action="registro_guardar.php" method="POST">
    <h2>Registro de Usuario</h2>

    <label>Nombre completo:</label>
    <input type="text" name="nombre_completo" required>

    <label>Correo:</label>
    <input type="email" name="correo" required>

    <label>Nickname:</label>
    <input type="text" name="nickname" required>

    <label>Contraseña:</label>
    <input type="password" name="password" required>

    <label>Fecha de nacimiento:</label>
    <input type="date" name="fecha_nacimiento">

    <button type="submit">Registrarse</button>
    <a class="back-button" href="../index.php">← Volver al inicio</a>
  </form>
</div>

<?php include("../includes/footer.php"); ?>
