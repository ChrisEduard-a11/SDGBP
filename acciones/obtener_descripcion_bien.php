<?php
require_once("../conexion.php");

$nombre = $_GET['nombre'];

$sql = "SELECT descripcion FROM bienes WHERE nombre = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $nombre);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo $row['descripcion'];
} else {
    echo '';
}

$stmt->close();
?>