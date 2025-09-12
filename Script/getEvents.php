<?php
header('Content-Type: application/json');

// 🔹 Simulación: esto después lo sacaremos de la base de datos
$events = [
    ["id" => "1", "title" => "Entrega de proyecto", "start" => "2025-09-10"],
    ["id" => "2", "title" => "Reunión con equipo", "start" => "2025-09-12", "end" => "2025-09-13"]
];

echo json_encode($events);
