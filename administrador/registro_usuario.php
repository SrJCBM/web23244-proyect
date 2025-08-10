<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../index.php");
    exit;
}
?>

<style>
.form-wrapper{max-width:720px;margin:24px auto;padding:20px;background:#fff;border:1px solid #e5e7eb;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.06);color:#0f172a}
.form-wrapper h2{margin:0 0 12px 0;font-size:22px}
.form-wrapper label{display:block;margin-top:10px;font-size:14px;font-weight:600}
.form-wrapper input[type="text"],
.form-wrapper input[type="email"],
.form-wrapper input[type="password"],
.form-wrapper input[type="date"],
.form-wrapper select{display:block;width:100%;height:44px;margin-top:6px;padding:10px 12px;border:1px solid #d1d5db;border-radius:8px;background:#fff;font-size:14px;box-sizing:border-box}
.form-wrapper input:focus,
.form-wrapper select:focus{outline:none;border-color:#4482C0;box-shadow:0 0 0 3px rgba(68,130,192,.18)}
.form-wrapper button[type="submit"]{width:100%;margin-top:16px;height:46px;border:0;border-radius:10px;background:linear-gradient(180deg,#003366,#4482C0);color:#fff;font-weight:700;cursor:pointer}
.form-wrapper button[type="submit"]:hover{filter:brightness(1.05)}
</style>

<div class="form-wrapper">
  <form action="registro_guardar.php" method="POST">
    <h2>Registrar nuevo usuario</h2>

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

    <label>Rol:</label>
    <select name="id_rol" required>
        <option value="">Seleccionar rol...</option>
        <option value="2">Vendedor</option>
        <option value="4">Auditor</option>
        <option value="5">Supervisor</option>
        <option value="6">Analista</option>
    </select>

    <button type="submit">Registrar usuario</button>
  </form>
</div>
