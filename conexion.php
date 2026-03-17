<?php
/*INICIO CONEXION DB*/
$conexion = mysqli_connect("localhost","root","","if0_38581055_sys_inv");

if (mysqli_connect_errno())
{
    echo("Error al conectar: " . mysqli_connect_error());
    exit();
}

// Configurar el conjunto de caracteres a utf8
mysqli_set_charset($conexion, "utf8");
?>