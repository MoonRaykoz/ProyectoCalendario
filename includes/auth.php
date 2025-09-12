<?php
session_start();
require_once __DIR__ . '/../config/conexion.php';

function isLogged() {
    return isset($_SESSION['user']);
}

function requireLogin() {
    if (!isLogged()) {
        header('Location: login.php');
        exit;
    }
}

function currentUser() {
    return $_SESSION['user'] ?? null;
}

function loadUserToSession($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id, nombre, email, tema FROM usuarios WHERE id = ?");
    $stmt->execute([$userId]);
    $u = $stmt->fetch();
    if ($u) {
        $_SESSION['user'] = $u;
        return true;
    }
    return false;
}

function generate_csrf() {
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['_csrf'];
}

function verify_csrf($token) {
    return isset($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $token);
}
?>