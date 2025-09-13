<?php
require_once __DIR__ . '/../../includes/auth.php';
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) { session_start(); }

$me = current_user();
if (!$me) { http_response_code(401); echo json_encode(['error'=>'No autorizado']); exit; }

$action = $_POST['action'] ?? '';

if ($action === 'save_theme') {
  if (!isset($_POST['_csrf']) || !hash_equals($_SESSION['_csrf'] ?? '', $_POST['_csrf'])) {
    echo json_encode(['error'=>'CSRF']); exit;
  }
  $theme = ($_POST['theme'] ?? '') === 'dark' ? 'dark' : 'light';
  global $mysqli;
  $stmt = $mysqli->prepare("UPDATE usuarios SET tema=? WHERE id=?");
  $uid  = (int)$me['id'];
  $stmt->bind_param('si', $theme, $uid);
  $stmt->execute();
  $_SESSION['user']['tema'] = $theme;
  echo json_encode(['success'=>true, 'theme'=>$theme]); exit;
}

echo json_encode(['error'=>'Petición inválida']);
