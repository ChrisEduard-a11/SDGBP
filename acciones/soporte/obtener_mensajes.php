<?php
error_reporting(0);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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
$sql = "SELECT m.*, u.nombre, u.foto, u.tipos FROM soporte_mensajes m 
        LEFT JOIN usuario u ON (m.enviado_por = CAST(u.id_usuario AS CHAR))
        WHERE m.id_ticket = '$id_ticket' AND m.id_mensaje > $last_id 
        ORDER BY m.id_mensaje ASC";

$result = mysqli_query($conexion, $sql);
$mensajes = [];

// Chequeo estado de ticket
$estado_ticket = 'Abierto';
$typing_otro = false;
$calificacion_ticket = null;
$res_estado = mysqli_query($conexion, "SELECT estado, typing_guest, typing_admin, calificacion FROM soporte_tickets WHERE id_ticket = '$id_ticket'");
if($row_est = mysqli_fetch_assoc($res_estado)){
    $estado_ticket = $row_est['estado'];
    $calificacion_ticket = $row_est['calificacion'];
    // El admin ve si el invitado/usuario está escribiendo; el invitado ve si el admin está escribiendo
    $col_typing = $is_admin ? 'typing_guest' : 'typing_admin';
    if (!empty($row_est[$col_typing])) {
        $diff = time() - strtotime($row_est[$col_typing]);
        $typing_otro = $diff <= 5; // Si actualizó en los últimos 5 segundos, está escribiendo
    }
}

while ($row = mysqli_fetch_assoc($result)) {
    // Identificar el emisor
    // Si viene de gest_TICK entonces es invitado
    if (strpos($row['enviado_por'], 'guest_') === 0) {
        $emisor_tipo = "Visitante Externo";
        $foto = '../img/default_profile.png';
    } else if ($row['enviado_por'] === 'admin') {
        $emisor_tipo = 'Soporte Técnico'; // Mensajes antiguos o del bot
        $foto = '../img/Logo-OP2_V4.webp';
    } else {
        $is_sender_admin = (isset($row['tipos']) && $row['tipos'] === 'admin');
        if ($is_sender_admin) {
            $emisor_tipo = 'Soporte Técnico (' . ($row['nombre'] ?? 'Admin') . ')';
        } else {
            $emisor_tipo = $row['nombre'] ?? 'Usuario';
        }
        $foto = $row['foto'] ?? '../img/default_profile.png';
    }
    
    // Determinar si yo envié el mensaje o si lo envió el otro
    $es_mio = false;
    if ($is_admin) {
        // Para administradores, todo mensaje enviado por un admin se muestra como "suyo" (del lado derecho)
        if ($row['enviado_por'] === 'admin' || (isset($row['tipos']) && $row['tipos'] === 'admin')) {
            $es_mio = true;
        }
    } else {
        // Para visitantes/usuarios normales
        if ($is_guest) {
            if (isset($_SESSION['guest_ticket_id']) && $row['enviado_por'] === 'guest_' . $_SESSION['guest_ticket_id']) {
                $es_mio = true;
            }
        } else {
            if ($row['enviado_por'] == $_SESSION['id']) {
                $es_mio = true;
            }
        }
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

$response = [
    'success' => true, 
    'mensajes' => $mensajes, 
    'estado' => $estado_ticket,
    'typing' => $typing_otro,
    'calificacion' => $calificacion_ticket
];

$json = json_encode($response, JSON_INVALID_UTF8_SUBSTITUTE);
if ($json === false) {
    echo json_encode(['success' => false, 'message' => 'Error de codificación JSON: ' . json_last_error_msg()]);
} else {
    echo $json;
}
?>
