<?php
require_once __DIR__ . '/config/env.php';

/*INICIO CONEXION DB*/
$host = env('DB_HOST', 'localhost');
$user = env('DB_USER', 'root');
$pass = env('DB_PASS', '');
$db   = env('DB_NAME', 'if0_38581055_sys_inv');


$conexion = new mysqli($host, $user, $pass, $db);

if ($conexion->connect_errno) {
    echo("Error al conectar: " . $conexion->connect_error);
    exit();
}

// Establecer la zona horaria de PHP a Venezuela
date_default_timezone_set('America/Caracas');

// Configurar el conjunto de caracteres a utf8mb4 (Soporta Emojis)
$conexion->set_charset("utf8mb4");

// Establecer la zona horaria de la base de datos a Venezuela (UTC -4:00)
$conexion->query("SET time_zone = '-04:00'");
?>