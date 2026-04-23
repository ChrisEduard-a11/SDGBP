<?php
session_start();
require_once("../../conexion.php");

header('Content-Type: application/json');

if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$id_ticket = isset($_POST['id_ticket']) ? mysqli_real_escape_string($conexion, $_POST['id_ticket']) : '';
$id_usuario = isset($_POST['id_usuario']) ? mysqli_real_escape_string($conexion, $_POST['id_usuario']) : '';

if (empty($id_ticket) || empty($id_usuario)) {
    echo json_encode(['success' => false, 'message' => 'Datos insuficientes']);
    exit;
}

// Vincular el ticket al usuario real
$sql = "UPDATE soporte_tickets SET id_usuario = '$id_usuario' WHERE id_ticket = '$id_ticket'";

if (mysqli_query($conexion, $sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al vincular: ' . mysqli_error($conexion)]);
}
?>
