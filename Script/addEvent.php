<?php
header('Content-Type: application/json');
$data = json_decode(file_get_contents("php://calendario"), true);

// Aquí guardarás en la base de datos
// $data['title'], $data['start'], $data['end']

echo json_encode(["status" => "ok", "message" => "Evento agregado"]);
