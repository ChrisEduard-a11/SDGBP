<?php
error_reporting(0);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once("../../conexion.php");

header('Content-Type: application/json');

$is_guest = !isset($_SESSION['id']);

$id_ticket = isset($_POST['id_ticket']) ? mysqli_real_escape_string($conexion, trim($_POST['id_ticket'])) : '';
$mensaje = isset($_POST['mensaje']) ? mysqli_real_escape_string($conexion, trim($_POST['mensaje'])) : '';
// The remitente can be user id or 'ADMIN' or 'guest_TICK-XX'

$is_admin = !$is_guest && ($_SESSION['tipo'] === 'admin');

if ($is_guest) {
    if (!isset($_SESSION['guest_ticket_id']) || $_SESSION['guest_ticket_id'] !== $id_ticket) {
        echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
        exit;
    }
    $enviado_por = 'guest_' . $id_ticket;
} else {
    $enviado_por = $_SESSION['id'];
}

if (empty($id_ticket) || empty($mensaje)) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos (ticket o mensaje)']);
    exit;
}

// Ensure the ticket isn't closed
$q_ticket = "SELECT estado FROM soporte_tickets WHERE id_ticket = '$id_ticket'";
$res_t = mysqli_query($conexion, $q_ticket);
if ($row_t = mysqli_fetch_assoc($res_t)) {
    if ($row_t['estado'] === 'Resuelto') {
        echo json_encode(['success' => false, 'message' => 'El ticket ya ha sido cerrado.']);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'El ticket no existe.']);
    exit;
}

$sql_msg = "INSERT INTO soporte_mensajes (id_ticket, enviado_por, mensaje) VALUES ('$id_ticket', '$enviado_por', '$mensaje')";
if (mysqli_query($conexion, $sql_msg)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al enviar: ' . mysqli_error($conexion)]);
}
?>
