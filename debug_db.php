<?php
session_start();
include('conexion.php');
$f = fopen('db_debug.txt', 'w');
fwrite($f, "SessID: " . ($_SESSION['id'] ?? 'NULL') . "\n");

$res = mysqli_query($conexion, "DESCRIBE usuario");
if (!$res) {
    fwrite($f, "Error DESCRIBE: " . mysqli_error($conexion) . "\n");
} else {
    while($row = mysqli_fetch_assoc($res)) {
        fwrite($f, "Col: " . $row['Field'] . " Type: " . $row['Type'] . "\n");
    }
}

$uid = $_SESSION['id'] ?? 0;
$res2 = mysqli_query($conexion, "SELECT id_usuario, usuario, fecha_cambio_clave FROM usuario WHERE id_usuario = '$uid'");
if (!$res2) {
    fwrite($f, "Error SELECT: " . mysqli_error($conexion) . "\n");
} else {
    $row2 = mysqli_fetch_assoc($res2);
    fwrite($f, "User: " . ($row2['usuario'] ?? 'N/A') . " Fecha: " . ($row2['fecha_cambio_clave'] ?? 'NULL/NOTSET') . "\n");
}
fclose($f);
echo "Debug done";
?>
