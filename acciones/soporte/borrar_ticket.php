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

// Opcional: solo permitir borrar si está "Resuelto", o dejar libre a los admins. Por requerimiento: "despues de resueltos"
$q = "DELETE FROM soporte_tickets WHERE id_ticket = '$id_ticket' AND estado = 'Resuelto'";
if(mysqli_query($conexion, $q)){
    if (mysqli_affected_rows($conexion) > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'El ticket no pudo ser eliminado (podría no estar Resuelto)']);
    }
} else {
    echo json_encode(['success' => false, 'message' => mysqli_error($conexion)]);
}
?>
