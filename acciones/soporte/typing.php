<?php
session_start();
require_once("../../conexion.php");

header('Content-Type: application/json');

$id_ticket = isset($_POST['id_ticket']) ? mysqli_real_escape_string($conexion, trim($_POST['id_ticket'])) : '';
$rol       = isset($_POST['rol']) ? $_POST['rol'] : 'guest'; // 'guest' o 'admin'

if (empty($id_ticket)) {
    echo json_encode(['success' => false]);
    exit;
}

$col = ($rol === 'admin') ? 'typing_admin' : 'typing_guest';
$now = date('Y-m-d H:i:s');

mysqli_query($conexion, "UPDATE soporte_tickets SET $col = '$now' WHERE id_ticket = '$id_ticket'");
echo json_encode(['success' => true]);
?>
