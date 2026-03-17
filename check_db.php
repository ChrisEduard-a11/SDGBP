<?php
include('conexion.php');
$res = mysqli_query($conexion, "DESCRIBE usuario");
while($row = mysqli_fetch_assoc($res)) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
?>
