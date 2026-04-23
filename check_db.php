<?php
require_once("conexion.php");
$res = mysqli_query($conexion, "SHOW COLUMNS FROM soporte_tickets");
print_r(mysqli_fetch_all($res, MYSQLI_ASSOC));
