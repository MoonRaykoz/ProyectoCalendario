<?php
require_once __DIR__ . '/../includes/auth.php';

if (current_user()) { header('Location: lista.php'); exit; }

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// --- Security helpers --------------------------------------------------------
function ensure_csrf_token(): string {
  if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf'];
}
function check_csrf_token(?string $t): bool {
  return $t && isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $t);
}
function throttle_login(): ?string {
  // Simple per-session throttle: max 5 attempts / 10 min
  $now = time();
  $win = 600; // 10 min
  $max = 5;

  $_SESSION['login_attempts'] = array_filter($_SESSION['login_attempts'] ?? [], fn($ts) => $ts > $now - $win);
  if (count($_SESSION['login_attempts']) >= $max) {
    $next = min($_SESSION['login_attempts']) + $win;
    $sec  = max(0, $next - $now);
    return "Demasiados intentos. Intenta de nuevo en ~{$sec}s.";
  }
  return null;
}
function record_attempt(): void {
  $_SESSION['login_attempts'][] = time();
}

// --- State -------------------------------------------------------------------
$errors = [];               // global (top) errors
$field = ['email' => '', 'password' => ''];  // field values
$ferr  = ['email' => '', 'password' => ''];  // field-level errors

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $field['email']    = trim($_POST['email'] ?? '');
  $field['password'] = $_POST['password'] ?? '';
  $csrf              = $_POST['csrf'] ?? null;

  if (!check_csrf_token($csrf)) {
    $errors[] = 'Solicitud inválida. Recarga la página e inténtalo de nuevo.';
  } else {
    if ($msg = throttle_login()) {
      $errors[] = $msg;
    } else {
      if ($field['email'] === '') { $ferr['email'] = 'Ingresa tu correo.'; }
      elseif (!filter_var($field['email'], FILTER_VALIDATE_EMAIL)) { $ferr['email'] = 'Correo inválido.'; }

      if ($field['password'] === '') { $ferr['password'] = 'Ingresa tu contraseña.'; }

      if (!$ferr['email'] && !$ferr['password']) {
        $u = find_user_by_email($field['email']);
        if (!$u || !password_verify($field['password'], $u['password_hash'])) {
          record_attempt();
          // Mensaje genérico:
          $errors[] = 'Correo o contraseña incorrectos.';
        } else {
          // (Opcional) rotar el token CSRF en login
          unset($_SESSION['csrf'], $_SESSION['login_attempts']);
          login_user($u);
          header('Location: lista.php');
          exit;
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
  <title>Iniciar sesión — Chrono</title>
  <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/litera/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/style.css" rel="stylesheet">
  <style>
    .auth-wrap { min-height: 100vh; display: grid; place-items: center; padding: 2rem; }
    .auth-card { max-width: 460px; width: 100%; }
    .brand-badge { font-weight: 700; letter-spacing:.4px; }
    .muted { color: var(--bs-secondary-color); }
  </style>
</head>
<body class="bg-info">
<main class="auth-wrap">
  <div class="card shadow-sm auth-card">
    <div class="card-body p-4 p-md-5">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="brand-badge">Chrono</div>
        <a href="register.php" class="small">Crear cuenta</a>
      </div>
      <h1 class="h4 mb-1">Inicia sesión</h1>
      <p class="muted mb-4">Organiza tus tareas y proyectos en un solo lugar.</p>

      <?php if ($errors): ?>
        <div class="alert alert-danger">
          <ul class="mb-0">
            <?php foreach ($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="post" novalidate>
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

        <div class="mb-3">
          <label for="email" class="form-label">Correo</label>
          <input
            id="email"
            type="email"
            name="email"
            class="form-control <?= $ferr['email'] ? 'is-invalid' : '' ?>"
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
            <a class="small" href="forgot.php">¿Olvidaste tu contraseña?</a>
          </label>
          <div class="input-group">
            <input
              id="password"
              type="password"
              name="password"
              class="form-control <?= $ferr['password'] ? 'is-invalid' : '' ?>"
              required
              minlength="6"
              autocomplete="current-password">
            <button class="btn btn-outline-secondary" type="button" id="togglePass" aria-label="Mostrar u ocultar contraseña">👁</button>
            <?php if ($ferr['password']): ?>
              <div class="invalid-feedback d-block"><?= htmlspecialchars($ferr['password']) ?></div>
            <?php endif; ?>
          </div>
        </div>

        <div class="d-grid mt-4">
          <button class="btn btn-primary btn-lg" type="submit">Entrar</button>
        </div>

        <p class="small muted mt-3 mb-0">
          ¿No tienes cuenta? <a href="register.php">Regístrate</a>
        </p>
      </form>
    </div>
  </div>
</main>

<script>
  // Toggle show/hide password
  const btn = document.getElementById('togglePass');
  const pwd = document.getElementById('password');
  btn?.addEventListener('click', () => {
    pwd.type = pwd.type === 'password' ? 'text' : 'password';
    btn.classList.toggle('active');
  });
</script>
</body>
</html>
