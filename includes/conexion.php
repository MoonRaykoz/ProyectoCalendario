<?php
// Configuración de conexión
$host = "localhost";  //ip local
$port = 3307;         
$user = "root";
$pass = "admin";      
$db   = "proyecto_calendario";

//Crear conexion
$mysqli = new mysqli($host, $user, $pass, $db, $port);

// Verificar conexión
if ($mysqli->connect_errno) {
    die("Error de conexión: " . $mysqli->connect_error);
}

// Definir charset 
$mysqli->set_charset("utf8mb4");
