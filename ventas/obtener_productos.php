<?php
ob_start();
require_once("../conexion.php");
error_reporting(0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents('php://input'), true);
$categoriaId = $data['categoria'] ?? '';

$sql = "SELECT p.id, p.nombre, p.descripcion, p.precio, p.stock, p.imagen, c.nombre AS categoria 
        FROM productos p 
        JOIN categorias_productos c ON p.categoria_productos_id = c.id";
if (!empty($categoriaId)) {
    $sql .= " WHERE p.categoria_productos_id = ?";
}

$stmt = $conexion->prepare($sql);
if (!empty($categoriaId)) {
    $stmt->bind_param("i", $categoriaId);
}
$stmt->execute();
$result = $stmt->get_result();

$productos = [];
while ($row = $result->fetch_assoc()) {
    foreach($row as $key => $value) {
        if(is_string($value)) $row[$key] = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
    }
    $productos[] = $row;
}
echo json_encode($productos);
exit();