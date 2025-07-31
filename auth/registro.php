<?php include("../includes/header.php"); ?>
<h2>Registro de Usuario</h2>
<form action="registro_guardar.php" method="POST">
  <label>Nombre completo:</label><br>
  <input type="text" name="nombre_completo" required><br>

  <label>Correo:</label><br>
  <input type="email" name="correo" required><br>

  <label>Nickname:</label><br>
  <input type="text" name="nickname" required><br>

  <label>Contraseña:</label><br>
  <input type="password" name="password" required><br>

  <label>Fecha de nacimiento:</label><br>
  <input type="date" name="fecha_nacimiento"><br><br>

  <button type="submit">Registrarse</button>
</form>
<?php include("../includes/footer.php"); ?>
