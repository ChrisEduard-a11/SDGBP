<?php
session_start();
require_once("../../conexion.php");

header('Content-Type: application/json');

if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$id_ticket = isset($_POST['id_ticket']) ? mysqli_real_escape_string($conexion, trim($_POST['id_ticket'])) : '';

if (empty($id_ticket)) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos']);
    exit;
}

$q = "UPDATE soporte_tickets SET estado = 'Resuelto' WHERE id_ticket = '$id_ticket'";
if(mysqli_query($conexion, $q)){
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => mysqli_error($conexion)]);
}
?>
