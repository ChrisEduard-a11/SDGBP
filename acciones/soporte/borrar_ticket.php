<?php
error_reporting(0);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once("../../conexion.php");

header('Content-Type: application/json');

$id_usuario = $_SESSION['id'] ?? null;
$tipo_usuario = $_SESSION['tipo'] ?? '';
$id_ticket = isset($_POST['id_ticket']) ? mysqli_real_escape_string($conexion, trim($_POST['id_ticket'])) : '';

if (!$id_usuario || empty($id_ticket)) {
    echo json_encode(['success' => false, 'message' => 'Parámetros inválidos']);
    exit;
}

// Verificar pertenencia o permisos
if ($tipo_usuario !== 'admin') {
    $check = mysqli_query($conexion, "SELECT id_usuario FROM soporte_tickets WHERE id_ticket = '$id_ticket'");
    $row = mysqli_fetch_assoc($check);
    if (!$row || $row['id_usuario'] != $id_usuario) {
        echo json_encode(['success' => false, 'message' => 'No tienes permiso para eliminar este ticket']);
        exit;
    }
}

// Eliminar mensajes asociados primero
mysqli_query($conexion, "DELETE FROM soporte_mensajes WHERE id_ticket = '$id_ticket'");

// Eliminar el ticket
if (mysqli_query($conexion, "DELETE FROM soporte_tickets WHERE id_ticket = '$id_ticket'")) {
    echo json_encode(['success' => true, 'message' => 'Ticket eliminado con éxito']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar el ticket: ' . mysqli_error($conexion)]);
}
?>
