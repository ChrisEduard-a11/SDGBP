<?php
include('../conexion.php');

if (!isset($_GET['categoria_id'])) {
    echo json_encode([]);
    exit;
}

$categoria_id = intval($_GET['categoria_id']);

$sql = "SELECT id, nombre, descripcion FROM bienes WHERE categoria_id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $categoria_id);
$stmt->execute();
$result = $stmt->get_result();

$bienes = [];
while ($row = $result->fetch_assoc()) {
    $bienes[] = $row;
}

$stmt->close();
echo json_encode($bienes);
?>
