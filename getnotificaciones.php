<?php
session_start();
require_once 'config/conexion.php'; // Define $pdo

$usuario_id = $_SESSION['usuario_id']; // Asumiendo que guardas el usuario logueado

$now = new DateTime();
$end = (clone $now)->modify("+60 minutes"); // tareas en la próxima hora

$stmt = $pdo->prepare("
    SELECT id, titulo, fecha_vencimiento 
    FROM tareas 
    WHERE usuario_id = :uid 
      AND estado = 'pendiente'
      AND fecha_vencimiento BETWEEN :now AND :end
");
$stmt->execute([
    ':uid' => $usuario_id,
    ':now' => $now->format('Y-m-d H:i:s'),
    ':end' => $end->format('Y-m-d H:i:s')
]);

$tareas = $stmt->fetchAll();

echo json_encode($tareas);
