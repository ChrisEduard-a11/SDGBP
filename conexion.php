<?php
/*INICIO CONEXION DB*/
$conexion = mysqli_connect("localhost","root","","if0_38581055_sys_inv");

if (mysqli_connect_errno())
{
    echo("Error al conectar: " . mysqli_connect_error());
    exit();
}

// Establecer la zona horaria de PHP a Venezuela
date_default_timezone_set('America/Caracas');

// Configurar el conjunto de caracteres a utf8
mysqli_set_charset($conexion, "utf8");

// Establecer la zona horaria de la base de datos a Venezuela (UTC -4:00)
mysqli_query($conexion, "SET time_zone = '-04:00'");
?>