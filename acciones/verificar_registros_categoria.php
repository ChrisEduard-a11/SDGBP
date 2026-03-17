<?php
require_once("../conexion.php");

$categoria_id = $_GET['categoria_id'];

// Verificar si la categoría tiene registros anclados
$sql = "SELECT COUNT(*) as total FROM bienes WHERE categoria_id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $categoria_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$response = array('tieneRegistros' => $row['total'] > 0);
echo json_encode($response);

$stmt->close();
?>