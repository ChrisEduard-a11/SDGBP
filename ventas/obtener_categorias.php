<?php
require_once("../conexion.php");

header('Content-Type: application/json');

// Obtener las categorías desde la base de datos
$sql = "SELECT id, nombre FROM categorias_productos";
$result = $conexion->query($sql);

$categorias = [];
while ($row = $result->fetch_assoc()) {
    $categorias[] = $row;
}

echo json_encode($categorias);
?>