<?php
ob_start();
require_once("../conexion.php");
error_reporting(0);
ob_clean();

header('Content-Type: application/json; charset=utf-8');

$sql = "SELECT id, nombre FROM categorias_productos";
$result = $conexion->query($sql);

$categorias = [];
while ($row = $result->fetch_assoc()) {
    foreach($row as $key => $value) {
        if(is_string($value)) $row[$key] = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
    }
    $categorias[] = $row;
}

echo json_encode($categorias);
exit();