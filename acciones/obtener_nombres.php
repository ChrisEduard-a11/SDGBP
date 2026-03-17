<?php
require_once("../conexion.php");

if (isset($_GET['categoria_id'])) {
    $categoria_id = intval($_GET['categoria_id']);
    $query = "SELECT id, nombre FROM bienes WHERE categoria_id = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $categoria_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $bienes = [];
    while ($row = $result->fetch_assoc()) {
        $bienes[] = $row;
    }

    echo json_encode($bienes);
} else {
    echo json_encode([]);
}
?>