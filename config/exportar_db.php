<?php
// filepath: c:\xampp\htdocs\SistemaProyectoxampp\acciones\exportar_bd.php
// Configura tus datos de conexión
$host = "localhost";
$user = "root";
$pass = ""; // Cambia por tu contraseña si tienes
$db   = "nombre_de_tu_base_de_datos"; // Cambia por el nombre real de tu base de datos

$fecha = date("Ymd_His");
$nombreArchivo = "respaldo_bd_{$db}_{$fecha}.sql";

// Comando mysqldump
$comando = "mysqldump --user={$user} --password=\"{$pass}\" --host={$host} {$db}";

// Ejecutar y enviar headers para descarga
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"$nombreArchivo\"");
passthru($comando);
exit;
?>