<?php
//Cookies
if (session_status() === PHP_SESSION_NONE) {
  session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
  ]);
  session_start();
}

// Cierre de sesion con limpieza de datos y cookies
$_SESSION = [];
if (ini_get('session.use_cookies')) {
  $params = session_get_cookie_params();
  setcookie(
    session_name(), '',
    time() - 42000,
    $params['path'] ?? '/',
    $params['domain'] ?? '',
    (bool)($params['secure'] ?? false),
    (bool)($params['httponly'] ?? true)
  );
}
session_destroy();
?>
<!doctype html>
<html lang="es" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sesión cerrada — Chronos</title>
  <link href="assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
  <link rel="icon" type="image/png" sizes="32x32" href="assets/icons/icon.png">
</head>
<body class="bg-body-tertiary">
  <main class="d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow mx-3" style="max-width:560px;">
      <div class="card-body p-4 text-center">
        <img src="assets/img/Cro.png" alt="Chronos" height="42" class="mb-3">
        <h1 class="h4 fw-bold mb-2">Has cerrado sesión</h1>
        <p class="text-secondary mb-4">Gracias por usar Chronos. Cuando quieras, puedes volver a entrar.</p>
        <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
          <a class="btn btn-info text-white" href="login.php">Iniciar sesión de nuevo</a>
          <a class="btn btn-outline-secondary" href="index.php">Volver al inicio</a>
        </div>
      </div>
    </div>
  </main>
</body>
</html>
