<?php
session_start();
if (isset($_SESSION["id_usuario"])) {
  header("Location: ../index.php");
  exit();
}
?>

<?php include("../includes/header.php"); ?>
<link rel="stylesheet" href="../assets/css/login.css">

<div class="form-wrapper">
  <form action="login_validar.php" method="POST">
    <h2>Iniciar Sesión</h2>

    <?php if (isset($_GET["error"])): ?>
      <p style="color:red;"><?php echo htmlspecialchars($_GET["error"]); ?></p>
    <?php endif; ?>

    <label for="usuario">Correo:</label>
    <input type="text" name="usuario" required>

    <label for="password">Contraseña:</label>
    <input type="password" name="password" required>

    <button type="submit">Ingresar</button>
    <a class="back-button" href="../index.php">← Volver al inicio</a>
  </form>
</div>

<?php include("../includes/footer.php"); ?>
