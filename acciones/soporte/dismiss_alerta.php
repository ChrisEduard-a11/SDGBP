<?php
error_reporting(0);
if (session_status() === PHP_SESSION_NONE) session_start();
require_once("../../conexion.php");
header('Content-Type: application/json');

$id_ticket = isset($_POST['id_ticket']) ? mysqli_real_escape_string($conexion, trim($_POST['id_ticket'])) : '';
$notif_id  = isset($_POST['notif_id']) ? (int)$_POST['notif_id'] : 0;

if (!empty($notif_id)) {
    // Dismiss a notification
    if (mysqli_query($conexion, "DELETE FROM soporte_alertas WHERE id = $notif_id")) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => mysqli_error($conexion)]);
    }
    exit;
}
echo json_encode(['success' => false, 'message' => 'Parámetro faltante']);
?>
