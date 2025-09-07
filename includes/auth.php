<?php

//Si la sesion no esta iniciada, la inicia 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
// Incluye la conexión a la base de datos
require_once __DIR__ . '/conexion.php';

// Buscar usuario por email, y si lo encuentra devuelve el array con todo los datos
function find_user_by_email(string $email): ?array {
  global $mysqli;
  $stmt = $mysqli->prepare('SELECT id, nombre, email, password_hash, tema FROM usuarios WHERE email=?');
  $stmt->bind_param('s', $email);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  return $row ?: null;
}

// Funcion para guardar los datos del usuario en sesión actual
function login_user(array $row): void {
  $_SESSION['user'] = [
    'id' => (int)$row['id'],
    'nombre' => $row['nombre'],
    'email' => $row['email'],
    'tema' => $row['tema'] ?? 'light'
  ];
}

// Funcion para obtener el usuario actual
function current_user(): ?array {
  return $_SESSION['user'] ?? null;
}

// Funcion para proteger páginas si todavia no ha hecho login
function require_login(): void {
  if (!current_user()) { header('Location: login.php'); exit; }
}

// Fucnion para cerrar sesión
function logout_user(): void {
  session_destroy();
  header('Location: login.php'); exit;
}
