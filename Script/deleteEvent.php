<?php
header('Content-Type: application/json');
$data = json_decode(file_get_contents("php://calendario"), true);

// Aquí borrarás de la base de datos según $data['id']

echo json_encode(["status" => "ok", "message" => "Evento eliminado"]);
