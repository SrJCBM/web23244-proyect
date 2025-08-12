<?php
// includes/auditoria_accesos.php
if (session_status() === PHP_SESSION_NONE) session_start();

function _ipReal(): string {
  $candidatos = ['HTTP_CF_CONNECTING_IP','HTTP_X_REAL_IP','HTTP_X_FORWARDED_FOR','REMOTE_ADDR'];
  foreach ($candidatos as $k) {
    if (!empty($_SERVER[$k])) {
      $ip = $_SERVER[$k];
      if ($k === 'HTTP_X_FORWARDED_FOR') $ip = trim(explode(',', $ip)[0]);
      return substr($ip, 0, 45);
    }
  }
  return '0.0.0.0';
}
function _ua(): string { return substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 255); }

/** Login OK */
function registrarAccesoExitoso(mysqli $cx, int $idUsuario): int {
  $sid = session_id();
  $ip  = _ipReal();
  $ua  = _ua();
  $stmt = $cx->prepare(
    "INSERT INTO aud_accesos (id_usuario, username_intentado, ip, user_agent, exito, motivo, sesion_id)
     VALUES (?, NULL, ?, ?, 1, 'login', ?)"
  );
  $stmt->bind_param("isss", $idUsuario, $ip, $ua, $sid);
  $stmt->execute();
  $_SESSION['acceso_row_id'] = $stmt->insert_id;
  return $stmt->insert_id;
}

/** Login fallido */
function registrarAccesoFallido(mysqli $cx, string $username, string $motivo='password_incorrecto'): void {
  if (session_status() === PHP_SESSION_NONE) session_start();
  $sid = session_id();
  $ip  = _ipReal();
  $ua  = _ua();
  $stmt = $cx->prepare(
    "INSERT INTO aud_accesos (id_usuario, username_intentado, ip, user_agent, exito, motivo, sesion_id)
     VALUES (NULL, ?, ?, ?, 0, ?, ?)"
  );
  $stmt->bind_param("sssss", $username, $ip, $ua, $motivo, $sid);
  $stmt->execute();
}

/** Logout / cierre */
function cerrarAcceso(mysqli $cx, string $motivo='logout'): void {
  $sid = session_id();
  $stmt = $cx->prepare(
    "UPDATE aud_accesos
       SET fin = NOW(), motivo = ?
     WHERE sesion_id = ? AND fin IS NULL
     ORDER BY id_acceso DESC
     LIMIT 1"
  );
  $stmt->bind_param("ss", $motivo, $sid);
  $stmt->execute();
}
