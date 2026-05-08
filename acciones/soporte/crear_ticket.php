<?php
session_start();
require_once("../../conexion.php");

header('Content-Type: application/json');

$is_guest = !isset($_SESSION['id']);
$id_usuario = $is_guest ? null : $_SESSION['id'];
$nombre_visitante = $is_guest && isset($_POST['nombre']) ? trim($_POST['nombre']) : null;
$cedula_visitante = $is_guest && isset($_POST['cedula']) ? trim($_POST['cedula']) : null;
$correo_visitante = $is_guest && isset($_POST['correo']) ? trim($_POST['correo']) : null;

if ($is_guest && (!isset($_POST['nombre']) || !isset($_POST['correo']) || !isset($_POST['cedula']))) {
    echo json_encode(['success' => false, 'message' => 'Los visitantes deben proporcionar nombre, cédula y correo.']);
    exit;
}

$asunto = isset($_POST['asunto']) ? trim($_POST['asunto']) : 'Soporte General';
$mensaje_inicial = isset($_POST['mensaje']) ? trim($_POST['mensaje']) : '';

if (empty($mensaje_inicial)) {
    echo json_encode(['success' => false, 'message' => 'El mensaje no puede estar vacío']);
    exit;
}

// 1. Verificar si el usuario ya tiene un ticket abierto
$tiene_abierto = false;
$ticket_abierto_id = null;

if (!$is_guest) {
    $query_check = "SELECT id_ticket FROM soporte_tickets WHERE id_usuario = ? AND estado != 'Resuelto' LIMIT 1";
    $stmt_check = $conexion->prepare($query_check);
    $stmt_check->bind_param("i", $id_usuario);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();
    if ($res_check->num_rows > 0) {
        $tiene_abierto = true;
        $ticket_abierto_id = $res_check->fetch_assoc()['id_ticket'];
    }
} else if (isset($_SESSION['guest_ticket_id'])) {
    $gt_id = $_SESSION['guest_ticket_id'];
    $query_check = "SELECT id_ticket FROM soporte_tickets WHERE id_ticket = ? AND estado != 'Resuelto' LIMIT 1";
    $stmt_check = $conexion->prepare($query_check);
    $stmt_check->bind_param("s", $gt_id);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();
    if ($res_check->num_rows > 0) {
        $tiene_abierto = true;
        $ticket_abierto_id = $res_check->fetch_assoc()['id_ticket'];
    }
}

if ($tiene_abierto) {
    echo json_encode(['success' => true, 'id_ticket' => $ticket_abierto_id, 'message' => 'Ya tienes un ticket activo.']);
    exit;
}

// 2. Generar ID único (ej: TICK-26A4B9)
$unique_str = strtoupper(substr(uniqid(), -6));
$id_ticket = 'TICK-' . date('y') . $unique_str;

// 3. Crear el ticket
$sql_ticket = "INSERT INTO soporte_tickets (id_ticket, id_usuario, nombre_visitante, cedula_visitante, correo_visitante, asunto, estado, prioridad) VALUES (?, ?, ?, ?, ?, ?, 'Abierto', 'Normal')";
$stmt_ticket = $conexion->prepare($sql_ticket);
$stmt_ticket->bind_param("sissss", $id_ticket, $id_usuario, $nombre_visitante, $cedula_visitante, $correo_visitante, $asunto);

if ($stmt_ticket->execute()) {
    $enviado_por = $is_guest ? 'guest_'.$id_ticket : $_SESSION['id'];
    $sql_msg = "INSERT INTO soporte_mensajes (id_ticket, enviado_por, mensaje) VALUES (?, ?, ?)";
    $stmt_msg = $conexion->prepare($sql_msg);
    $stmt_msg->bind_param("sss", $id_ticket, $enviado_por, $mensaje_inicial);
    $stmt_msg->execute();
    
    $bot_msg = "¡Hola! Bienvenido(a) al centro de soporte de SDGBP. Hemos recibido tu solicitud. Nuestro equipo de soporte técnico te atenderá lo más pronto posible. Por favor, mantente en línea.";
    $admin_enc = 'admin';
    $stmt_bot = $conexion->prepare($sql_msg);
    $stmt_bot->bind_param("sss", $id_ticket, $admin_enc, $bot_msg);
    $stmt_bot->execute();
    
    if ($is_guest) {
        $_SESSION['guest_ticket_id'] = $id_ticket;
    }
    
    echo json_encode(['success' => true, 'id_ticket' => $id_ticket]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al crear el ticket: ' . $conexion->error]);
}
?>
