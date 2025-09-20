<?php
//Cookeis para seguridad
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

require_once __DIR__ . '/../includes/auth.php';

// Si ya inicio sesion, redirige a lista de tareas
if (current_user()) { header('Location: lista.php'); exit; }

//CSRF helpers
function ensure_csrf_token(): string {
  if (empty($_SESSION['_csrf'])) { $_SESSION['_csrf'] = bin2hex(random_bytes(32)); }
  return $_SESSION['_csrf'];
}
function check_csrf_token(?string $t): bool {
  return $t && isset($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $t);
}

// Estado de formulario 
$errors = []; // errores globales
$field = ['nombre' => '', 'email' => ''];
$ferr  = ['nombre' => '', 'email' => '', 'password' => '', 'password2' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $field['nombre'] = trim($_POST['nombre'] ?? '');
  $field['email']  = strtolower(trim($_POST['email'] ?? ''));
  $pass            = $_POST['password']  ?? '';
  $pass2           = $_POST['password2'] ?? '';
  $csrf            = $_POST['_csrf'] ?? null;

  // CSRF
  if (!check_csrf_token($csrf)) {
    $errors[] = 'Solicitud inválida. Recarga la página e inténtalo de nuevo.';
  } else {
    // Validaciones
    if ($field['nombre'] === '')            { $ferr['nombre'] = 'Ingresa tu nombre.'; }
    elseif (mb_strlen($field['nombre'])>80) { $ferr['nombre'] = 'Máximo 80 caracteres.'; }

    if ($field['email'] === '') {
      $ferr['email'] = 'Ingresa tu correo.';
    } elseif (!filter_var($field['email'], FILTER_VALIDATE_EMAIL)) {
      $ferr['email'] = 'Correo inválido.';
    }

    if ($pass === '')                       { $ferr['password'] = 'Ingresa una contraseña.'; }
    elseif (strlen($pass) < 6)              { $ferr['password'] = 'Mínimo 6 caracteres.'; }

    if ($pass2 === '')                      { $ferr['password2'] = 'Confirma tu contraseña.'; }
    elseif ($pass !== $pass2)               { $ferr['password2'] = 'Las contraseñas no coinciden.'; }

    // Duplicado por email
    if (!$ferr['email'] && find_user_by_email($field['email'])) {
      $ferr['email'] = 'Ya existe una cuenta con ese correo.';
    }

    // Crear usuario
    if (!array_filter($ferr)) {
      global $mysqli;
      $hash = password_hash($pass, PASSWORD_DEFAULT);
      $stmt = $mysqli->prepare('INSERT INTO usuarios (nombre, email, password_hash) VALUES (?,?,?)');

      if (!$stmt) {
        $errors[] = 'Error al preparar la consulta.';
      } else {
        $stmt->bind_param('sss', $field['nombre'], $field['email'], $hash);
        if ($stmt->execute()) {
          // Login inmediato
          $u = find_user_by_email($field['email']);
          session_regenerate_id(true);      // evitar fijación de sesión
          unset($_SESSION['_csrf']);        // opcional: rotar token
          login_user($u);
          header('Location: lista.php'); exit;
        } else {
          $errors[] = 'Error al registrarse. Intenta de nuevo.';
        }
      }
    }
  }
}
$csrf = ensure_csrf_token();
?>
<!doctype html>
<html lang="es" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registro — Chronos</title>
  <link href="assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="assets/css/style.css" rel="stylesheet">

  <link rel="icon" type="image/png" sizes="32x32" href="assets/icons/icon.png">
</head>
<body class="bg-body-tertiary">
  <main class="auth-wrap d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow auth-card mx-3" style="max-width: 480px;">
      <div class="card-body p-4 p-md-5">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <a class="d-flex align-items-center fw-bold text-decoration-none" href="index.php">
            <img src="assets/img/Cro.png" alt="Chronos" height="40" class="me-2">
          </a>
          <a href="login.php" class="small text-secondary">Iniciar sesión</a>
        </div>

        <h1 class="h4 fw-bold mb-1">Crea tu cuenta</h1>
        <p class="text-secondary mb-4">Empieza a organizar tus tareas y proyectos.</p>

        <?php if ($errors): ?>
          <div class="alert alert-danger">
            <ul class="mb-0"><?php foreach ($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?></ul>
          </div>
        <?php endif; ?>

        <form method="post" autocomplete="off" novalidate>
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

          <div class="mb-3">
            <label class="form-label" for="nombre">Nombre</label>
            <input
              id="nombre"
              type="text"
              name="nombre"
              class="form-control rounded <?= $ferr['nombre'] ? 'is-invalid' : '' ?>"
              required
              maxlength="80"
              value="<?= htmlspecialchars($field['nombre']) ?>">
            <?php if ($ferr['nombre']): ?>
              <div class="invalid-feedback"><?= htmlspecialchars($ferr['nombre']) ?></div>
            <?php endif; ?>
          </div>

          <div class="mb-3">
            <label class="form-label" for="email">Correo</label>
            <input
              id="email"
              type="email"
              name="email"
              class="form-control rounded <?= $ferr['email'] ? 'is-invalid' : '' ?>"
              required
              autocomplete="email"
              value="<?= htmlspecialchars($field['email']) ?>">
            <?php if ($ferr['email']): ?>
              <div class="invalid-feedback"><?= htmlspecialchars($ferr['email']) ?></div>
            <?php endif; ?>
          </div>

          <div class="mb-3">
            <label class="form-label" for="password">Contraseña</label>
            <input
              id="password"
              type="password"
              name="password"
              class="form-control rounded <?= $ferr['password'] ? 'is-invalid' : '' ?>"
              required
              minlength="6"
              autocomplete="new-password">
            <?php if ($ferr['password']): ?>
              <div class="invalid-feedback"><?= htmlspecialchars($ferr['password']) ?></div>
            <?php endif; ?>
          </div>

          <div class="mb-4">
            <label class="form-label" for="password2">Confirmar contraseña</label>
            <input
              id="password2"
              type="password"
              name="password2"
              class="form-control rounded <?= $ferr['password2'] ? 'is-invalid' : '' ?>"
              required
              minlength="6"
              autocomplete="new-password">
            <?php if ($ferr['password2']): ?>
              <div class="invalid-feedback"><?= htmlspecialchars($ferr['password2']) ?></div>
            <?php endif; ?>
          </div>

          <div class="d-grid">
            <button class="btn btn-info text-white btn-lg">Crear cuenta</button>
          </div>

          <p class="small text-secondary mt-3 mb-0">
            ¿Ya tienes cuenta? <a href="login.php" class="text-decoration-none fw-semibold">Inicia sesión</a>
          </p>
        </form>
      </div>
    </div>
  </main>
</body>
</html>
