<?php


// --- Sesión cookies para seguridad
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

if (current_user()) {
  header('Location: lista.php');
  exit;
}

// --- CSRF helpers para seguridad
function ensure_csrf_token(): string
{
  if (empty($_SESSION['_csrf'])) {
    $_SESSION['_csrf'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['_csrf'];
}
function check_csrf_token(?string $t): bool
{
  return $t && isset($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $t);
}

// Permitir 5 intentos cada 10 min por sesión
function throttle_login(): ?string
{
  $now = time();
  $win = 600;
  $max = 5;
  $_SESSION['login_attempts'] = array_filter($_SESSION['login_attempts'] ?? [], fn($ts) => $ts > $now - $win);
  if (count($_SESSION['login_attempts']) >= $max) {
    $next = min($_SESSION['login_attempts']) + $win;
    $sec  = max(0, $next - $now);
    return "Demasiados intentos. Intenta de nuevo en ~{$sec}s.";
  }
  return null;
}
function record_attempt(): void
{
  $_SESSION['login_attempts'][] = time();
}

//Estado de formulario 
$errors = [];
$field = ['email' => '', 'password' => ''];
$ferr  = ['email' => '', 'password' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $field['email']    = trim($_POST['email'] ?? '');
  $field['password'] = $_POST['password'] ?? '';
  $csrf              = $_POST['_csrf'] ?? null;

  if (!check_csrf_token($csrf)) {
    $errors[] = 'Solicitud inválida. Recarga la página e inténtalo de nuevo.';
  } elseif ($msg = throttle_login()) {
    $errors[] = $msg;
  } else {
    if ($field['email'] === '') {
      $ferr['email'] = 'Ingresa tu correo.';
    } elseif (!filter_var($field['email'], FILTER_VALIDATE_EMAIL)) {
      $ferr['email'] = 'Correo inválido.';
    }

    if ($field['password'] === '') {
      $ferr['password'] = 'Ingresa tu contraseña.';
    }

    if (!$ferr['email'] && !$ferr['password']) {
      $u = find_user_by_email(strtolower($field['email']));
      if (!$u || !password_verify($field['password'], $u['password_hash'])) {
        record_attempt();
        $errors[] = 'Correo o contraseña incorrectos.';
      } else {
        // Regenerar ID de sesión para evitar fijación
        session_regenerate_id(true);
        unset($_SESSION['_csrf'], $_SESSION['login_attempts']);
        login_user($u);
        header('Location: lista.php');
        exit;
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
  <title>Iniciar sesión — Chronos</title>

  <link href="assets/css/bootstrap.min.css" rel="stylesheet">

  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="assets/css/style.css" rel="stylesheet">
  <link rel="icon" type="image/png" sizes="32x32" href="assets/icons/icon.png">

</head>

<body class="bg-body-tertiary">
  <main class="auth-wrap d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow auth-card mx-3" style="max-width: 460px;">
      <div class="card-body p-4 p-md-5">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <a class="d-flex align-items-center fw-bold text-decoration-none" href="index.php">
            <img src="assets/img/Cro.png" alt="Chronos" height="40" class="me-2">
          </a>
          <a href="register.php" class="small text-secondary">Crear cuenta</a>
        </div>

        <h1 class="h4 fw-bold mb-1">Inicia sesión</h1>
        <p class="text-secondary mb-4">Organiza tus tareas y proyectos en un solo lugar.</p>

        <?php if ($errors): ?>
          <div class="alert alert-danger">
            <ul class="mb-0">
              <?php foreach ($errors as $e) echo '<li>' . htmlspecialchars($e) . '</li>'; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="post" novalidate>
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

          <div class="mb-3">
            <label for="email" class="form-label">Correo</label>
            <input
              id="email"
              type="email"
              name="email"
              class="form-control rounded <?= $ferr['email'] ? 'is-invalid' : '' ?>"
              value="<?= htmlspecialchars($field['email']) ?>"
              required
              autocomplete="email"
              autofocus>
            <?php if ($ferr['email']): ?>
              <div class="invalid-feedback"><?= htmlspecialchars($ferr['email']) ?></div>
            <?php endif; ?>
          </div>

          <div class="mb-2">
            <label for="password" class="form-label d-flex justify-content-between">
              <span>Contraseña</span>
              <a class="small text-secondary" href="#">¿Olvidaste tu contraseña?</a>
            </label>
            <div class="input-group">
              <input
                id="password"
                type="password"
                name="password"
                class="form-control rounded <?= $ferr['password'] ? 'is-invalid' : '' ?>"
                required
                minlength="6"
                autocomplete="current-password">
              <button class="btn btn-outline-secondary" type="button" id="togglePass" aria-label="Mostrar u ocultar contraseña">
                <i class="bi bi-eye"></i>
              </button>
              <?php if ($ferr['password']): ?>
                <div class="invalid-feedback d-block"><?= htmlspecialchars($ferr['password']) ?></div>
              <?php endif; ?>
            </div>
          </div>

          <div class="d-grid mt-4">
            <button class="btn btn-info text-white btn-lg" type="submit">Entrar</button>
          </div>

          <p class="small text-secondary mt-3 mb-0">
            ¿No tienes cuenta? <a href="register.php" class="text-decoration-none fw-semibold">Regístrate</a>
          </p>
        </form>
      </div>
    </div>
  </main>

  <script>
    // Mostrar/ocultar contraseña
    const btn = document.getElementById('togglePass');
    const pwd = document.getElementById('password');
    btn?.addEventListener('click', () => {
      pwd.type = pwd.type === 'password' ? 'text' : 'password';
      btn.classList.toggle('active');
    });
  </script>
</body>

</html>