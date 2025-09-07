<?php

require_once __DIR__ . '/../includes/auth.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre = trim($_POST['nombre'] ?? '');
  $email  = trim($_POST['email'] ?? '');
  $pass   = $_POST['password'] ?? '';
  $pass2  = $_POST['password2'] ?? '';

  if ($nombre === '' || $email === '' || $pass === '' || $pass2 === '') {
    $errors[] = 'Por favor completa todos los campos.';
  }
  //Filter_validate_email es una constante predeterminado que viene con la funcion php filter_var
  if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Correo inválido.';
  }
  if ($pass !== $pass2) {
    $errors[] = 'Las contraseñas no son iguales. Por favor verifica si hay algun error.';
  }
  //Este find_user_by_email lo voy a llamar de auth.php
  if (!$errors && find_user_by_email($email)) {
    $errors[] = 'Ya existe una cuenta con ese correo.';
  }

  //Si hay errores, entonces ejecuta lo siguiente:
  if (!$errors) {
    global $mysqli;
    //Se usa la funcion de php password_hash y aplica el tipo de hasheo default con la constante PASSWORD_DEFAULT
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare('INSERT INTO usuarios (nombre, email, password_hash) VALUES (?,?,?)');
    $stmt->bind_param('sss', $nombre, $email, $hash);
    if ($stmt->execute()) {
      $u = find_user_by_email($email);
      login_user($u);
      header('Location: lista.php'); 
      exit;
    } else {
      $errors[] = 'Error al registrarse. Intenta de nuevo.';
    }
  }
}
?>

<!doctype html>
<html lang="es" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registro</title>
  <link href="../assets/bootstrap.css" rel="stylesheet">
  <link href="../assets/style.css" rel="stylesheet">
  <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"> --> 
</head>
<body class="bg-body-tertiary">
<div class="container py-5" style="max-width: 480px;">
  <h1 class="mb-4">Registrate ahora en Chrono</h1>

  <?php if ($errors): ?>
    <div class="alert alert-danger">
      <ul class="mb-0"><?php foreach ($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?></ul>
    </div>
  <?php endif; ?>

  <form method="post" autocomplete="off">
    <div class="mb-3">
      <label class="form-label">Nombre</label>
      <input type="text" name="nombre" class="form-control" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Correo</label>
      <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Contraseña</label>
      <input type="password" name="password" class="form-control" required minlength="6">
    </div>
    <div class="mb-4">
      <label class="form-label">Confirmar contraseña</label>
      <input type="password" name="password2" class="form-control" required minlength="6">
    </div>
    <button class="btn btn-primary w-100">Registrarme</button>
  </form>

  <p class="mt-3">¿Ya estás en Chrono? <a href="login.php">Inicia sesión</a></p>
</div>
</body>
</html>