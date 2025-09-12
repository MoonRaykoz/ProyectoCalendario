<?php
// register.php
require_once 'config/conexion.php';
require_once 'includes/auth.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf = $_POST['_csrf'] ?? '';

    if (!verify_csrf($csrf)) $errors[] = "Token CSRF inválido.";
    if (!$nombre || !$email || !$password) $errors[] = "Todos los campos son obligatorios.";

    if (empty($errors)) {
        // verificar email
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "El correo ya está registrado.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password_hash) VALUES (?, ?, ?)");
            if ($stmt->execute([$nombre, $email, $hash])) {
                header('Location: login.php?registered=1');
                exit;
            } else {
                $errors[] = "Error al registrar: " . implode(" ", $pdo->errorInfo());
            }
        }
    }
}
$csrf = generate_csrf();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Registro - TaskMaster</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <h3>Crear cuenta</h3>
      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
      <?php endif; ?>
      <form method="post">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
        <div class="mb-3">
          <label class="form-label">Nombre</label>
          <input class="form-control" name="nombre" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input class="form-control" type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Contraseña</label>
          <input class="form-control" type="password" name="password" required>
        </div>
        <button class="btn btn-primary">Registrarme</button>
        <a href="login.php" class="btn btn-link">Iniciar sesión</a>
      </form>
    </div>
  </div>
</div>
</body>
</html>
