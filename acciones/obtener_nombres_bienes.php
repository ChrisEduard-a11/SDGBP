<?php
include('../conexion.php');

$categoria_id = $_GET['categoria_id'];

$sql = "SELECT DISTINCT nombre FROM bienes WHERE categoria_id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $categoria_id);
$stmt->execute();
$result = $stmt->get_result();

$nombres = [];
while ($row = mysqli_fetch_assoc($result)) {
    $nombres[] = $row['nombre'];
}

echo json_encode($nombres);
?>