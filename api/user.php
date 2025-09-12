<?php
// api/user.php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');
if (!isLogged()) { http_response_code(401); echo json_encode(['error' => 'No autorizado']); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'save_theme') {
        if (!verify_csrf($_POST['_csrf'] ?? '')) { echo json_encode(['error'=>'CSRF']); exit; }
        $theme = $_POST['theme'] === 'dark' ? 'dark' : 'light';
        $stmt = $pdo->prepare("UPDATE usuarios SET tema = ? WHERE id = ?");
        $stmt->execute([$theme, $_SESSION['user']['id']]);
        // refrescar en sesión
        $_SESSION['user']['tema'] = $theme;
        echo json_encode(['success'=>true, 'theme'=>$theme]);
        exit;
    }
}
echo json_encode(['error'=>'Petición inválida']);
