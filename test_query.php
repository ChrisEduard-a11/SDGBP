<?php
require_once("conexion.php");
$query = "SELECT m.*, u.nombre, u.foto, u.tipos FROM soporte_mensajes m 
        LEFT JOIN usuario u ON (m.enviado_por = CAST(u.id_usuario AS CHAR))
        WHERE m.id_ticket = 'TICK-2674FD95'";
$res = mysqli_query($conexion, $query);
if(!$res) echo "ERROR: " . mysqli_error($conexion);
else print_r(mysqli_fetch_all($res, MYSQLI_ASSOC));
