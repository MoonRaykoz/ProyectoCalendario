<?php
require_once __DIR__ . '/../includes/auth.php';

// Ejecuta el logout (esto destruye la sesión y redirige por defecto)
session_destroy();
?>
<!doctype html>
<html lang="es" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Cierre de sesión</title>
  <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/litera/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/style.css" rel="stylesheet">
</head>
<body class="bg-body-tertiary">
<div class="container py-5" style="max-width: 480px; text-align:center;">
  <h1 class="mb-4">Has cerrado sesión</h1>
  <p class="mb-4">Tu sesión en <strong>Chrono</strong> se cerró correctamente.</p>
  <a href="login.php" class="btn btn-primary">Iniciar sesión de nuevo</a>
</div>
</body>
</html>
