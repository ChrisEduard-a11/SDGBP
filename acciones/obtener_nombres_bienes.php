<?php
include('../conexion.php');

$categoria_id = $_GET['categoria_id'];

$sql = "SELECT DISTINCT nombre FROM bienes WHERE categoria_id = '$categoria_id'";
$result = mysqli_query($conexion, $sql);

$nombres = [];
while ($row = mysqli_fetch_assoc($result)) {
    $nombres[] = $row['nombre'];
}

echo json_encode($nombres);
?>