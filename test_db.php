<?php
require_once __DIR__ . '/includes/conexion.php';

// Probar la conexión
echo "<h2> Conexión exitosa a la base de datos: proyecto_calendario</h2>";

// Probar si existen las tablas
$res = $mysqli->query("SHOW TABLES");
echo "<h3>Tablas encontradas:</h3><ul>";
while ($row = $res->fetch_array()) {
    echo "<li>" . htmlspecialchars($row[0]) . "</li>";
}
echo "</ul>";
