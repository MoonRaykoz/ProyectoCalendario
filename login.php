<?php
// login.php
require_once 'config/conexion.php';
require_once 'includes/auth.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf = $_POST['_csrf'] ?? '';
    if (!verify_csrf($csrf)) $errors[] = "Token CSRF inválido.";

    if (!$email || !$password) $errors[] = "Email y contraseña requeridos.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id, password_hash FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        if ($row && password_verify($password, $row['password_hash'])) {
            loadUserToSession($row['id']);
            header('Location: tareas.php');
            exit;
        } else {
            $errors[] = "Credenciales inválidas.";
        }
    }
}
$csrf = generate_csrf();
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Login - TaskMaster</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <?php if (!empty($_GET['registered'])): ?>
        <div class="alert alert-success">Registro completado. Inicia sesión.</div>
      <?php endif; ?>
      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
      <?php endif; ?>
      <form method="post">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
        <div class="mb-3">
          <label>Email</label>
          <input class="form-control" type="email" name="email" required>
        </div>
        <div class="mb-3">
          <label>Contraseña</label>
          <input class="form-control" type="password" name="password" required>
        </div>
        <button class="btn btn-primary">Iniciar sesión</button>
        <a href="register.php" class="btn btn-link">Crear cuenta</a>
      </form>
    </div>
  </div>
</div>
</body>
</html>
