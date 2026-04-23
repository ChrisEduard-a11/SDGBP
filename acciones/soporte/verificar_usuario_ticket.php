<?php
session_start();
require_once("../../conexion.php");

header('Content-Type: application/json');

$is_guest = !isset($_SESSION['id']);

$q = "";
if (!$is_guest) {
    $id_usuario = $_SESSION['id'];
    $q = "SELECT id_ticket, estado FROM soporte_tickets WHERE id_usuario = '$id_usuario' AND estado != 'Resuelto' ORDER BY fecha_apertura DESC LIMIT 1";
} else if (isset($_SESSION['guest_ticket_id'])) {
    $gt_id = $_SESSION['guest_ticket_id'];
    $q = "SELECT id_ticket, estado FROM soporte_tickets WHERE id_ticket = '$gt_id' AND estado != 'Resuelto' ORDER BY fecha_apertura DESC LIMIT 1";
}

if (!empty($q)) {
    $r = mysqli_query($conexion, $q);
    $row = mysqli_fetch_assoc($r);
} else {
    $row = null;
}

if ($row) {
    echo json_encode([
        'success' => true,
        'has_ticket' => true,
        'id_ticket' => $row['id_ticket'],
        'estado' => $row['estado']
    ]);
} else {
    echo json_encode([
        'success' => true,
        'has_ticket' => false
    ]);
}
?>
