<?php
session_start();
if (isset($_SESSION["id_usuario"])) {
  header("Location: ../index.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Iniciar Sesión</title>
  <link rel="stylesheet" href="../assets/css/estilos.css">
</head>
<body>
  <div class="container">
    <h2>Iniciar Sesión</h2>

    <?php if (isset($_GET["error"])): ?>
      <p style="color:red;"><?php echo htmlspecialchars($_GET["error"]); ?></p>
    <?php endif; ?>

    <form action="login_validar.php" method="POST">
      <label for="usuario">Correo:</label><br>
      <input type="text" name="usuario" required><br><br>

      <label for="password">Contraseña:</label><br>
      <input type="password" name="password" required><br><br>

      <button type="submit">Ingresar</button>
    </form>
  </div>
</body>
</html>
