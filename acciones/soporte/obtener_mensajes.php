<?php
session_start();
require_once("../../conexion.php");

header('Content-Type: application/json');

$is_guest = !isset($_SESSION['id']);

$id_ticket = isset($_GET['id_ticket']) ? mysqli_real_escape_string($conexion, trim($_GET['id_ticket'])) : '';
$last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

if (empty($id_ticket)) {
    echo json_encode(['success' => false, 'message' => 'Ticket ID no proporcionado']);
    exit;
}

// Si la vista es del usuario que no es admin, verificamos que el ticket le pertenece
// Si la vista es del usuario que no es admin, verificamos que el ticket le pertenece
$is_admin = !$is_guest && ($_SESSION['tipo'] === 'admin');
if (!$is_admin) {
    $check = mysqli_query($conexion, "SELECT id_usuario FROM soporte_tickets WHERE id_ticket = '$id_ticket'");
    $row_check = mysqli_fetch_assoc($check);
    if (!$row_check) {
        echo json_encode(['success' => false, 'message' => 'El ticket no existe']);
        exit;
    }
    if ($is_guest) {
        // Invitado solo puede ver si coincide con la variable guess
        if (!isset($_SESSION['guest_ticket_id']) || $_SESSION['guest_ticket_id'] !== $id_ticket) {
            echo json_encode(['success' => false, 'message' => 'Acceso denegado a este ticket']);
            exit;
        }
    } else {
        $my_id = $_SESSION['id'];
        if ($row_check['id_usuario'] != $my_id) {
            echo json_encode(['success' => false, 'message' => 'Acceso denegado a este ticket']);
            exit;
        }
    }
}

// Buscar mensajes nuevos
$sql = "SELECT m.*, u.nombre, u.foto FROM soporte_mensajes m 
        LEFT JOIN usuario u ON (m.enviado_por = u.id_usuario AND m.enviado_por != 'admin')
        WHERE m.id_ticket = '$id_ticket' AND m.id_mensaje > $last_id 
        ORDER BY m.id_mensaje ASC";

$result = mysqli_query($conexion, $sql);
$mensajes = [];

// Chequeo estado de ticket (para deshabilitar input en UI)
$estado_ticket = 'Abierto';
$res_estado = mysqli_query($conexion, "SELECT estado FROM soporte_tickets WHERE id_ticket = '$id_ticket'");
if($row_est = mysqli_fetch_assoc($res_estado)){
    $estado_ticket = $row_est['estado'];
}

while ($row = mysqli_fetch_assoc($result)) {
    // Identificar el emisor
    // Si viene de gest_TICK entonces es invitado
    if (strpos($row['enviado_por'], 'guest_') === 0) {
        $emisor_tipo = "Visitante Externo";
        $foto = '../img/default-user.png';
    } else {
        $emisor_tipo = ($row['enviado_por'] === 'admin') ? 'Soporte Técnico' : ($row['nombre'] ?? 'Usuario');
        $foto = ($row['enviado_por'] === 'admin') ? '../img/Logo-OP2_V4.webp' : ($row['foto'] ?? '../img/default-user.png');
    }
    
    // Determinar si yo envié el mensaje o si lo envió el otro
    $es_mio = false;
    if ($is_admin && $row['enviado_por'] === 'admin') {
        $es_mio = true;
    } elseif (!$is_admin && $row['enviado_por'] !== 'admin') {
        // Si no es admin y el mensaje no es del admin, asumimos que es mio, esto sirve también para el guest.
        $es_mio = true; 
    }

    $mensajes[] = [
        'id_mensaje' => (int)$row['id_mensaje'],
        'mensaje' => htmlspecialchars($row['mensaje']),
        'fecha' => date('h:i A - d/m/y', strtotime($row['fecha_envio'])),
        'es_mio' => $es_mio,
        'emisor_nombre' => $emisor_tipo,
        'foto' => $foto
    ];
}

echo json_encode([
    'success' => true, 
    'mensajes' => $mensajes, 
    'estado' => $estado_ticket
]);
?>
