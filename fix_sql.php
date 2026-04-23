<?php
require_once("conexion.php");
$sql = "ALTER TABLE soporte_tickets ADD COLUMN calificacion VARCHAR(10) DEFAULT NULL";
if(mysqli_query($conexion, $sql)) echo 'SUCCESS';
else echo 'ERROR: ' . mysqli_error($conexion);
