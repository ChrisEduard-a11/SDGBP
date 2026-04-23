<?php
session_start();
require_once("../../conexion.php");

header('Content-Type: application/json');

$is_guest = !isset($_SESSION['id']);
$id_usuario = $is_guest ? 'NULL' : "'" . $_SESSION['id'] . "'";
$nombre_visitante = $is_guest && isset($_POST['nombre']) ? "'" . mysqli_real_escape_string($conexion, trim($_POST['nombre'])) . "'" : 'NULL';
$correo_visitante = $is_guest && isset($_POST['correo']) ? "'" . mysqli_real_escape_string($conexion, trim($_POST['correo'])) . "'" : 'NULL';

if ($is_guest && (!isset($_POST['nombre']) || !isset($_POST['correo']))) {
    echo json_encode(['success' => false, 'message' => 'Los visitantes deben proporcionar nombre y correo.']);
    exit;
}

$asunto = isset($_POST['asunto']) ? mysqli_real_escape_string($conexion, trim($_POST['asunto'])) : 'Soporte General';
$mensaje_inicial = isset($_POST['mensaje']) ? mysqli_real_escape_string($conexion, trim($_POST['mensaje'])) : '';

if (empty($mensaje_inicial)) {
    echo json_encode(['success' => false, 'message' => 'El mensaje no puede estar vacío']);
    exit;
}

// 1. Verificar si el usuario ya tiene un ticket abierto
$res_check = null;
if (!$is_guest) {
    $query_check = "SELECT id_ticket FROM soporte_tickets WHERE id_usuario = $id_usuario AND estado != 'Resuelto' LIMIT 1";
    $res_check = mysqli_query($conexion, $query_check);
} else if (isset($_SESSION['guest_ticket_id'])) {
    $gt_id = $_SESSION['guest_ticket_id'];
    $query_check = "SELECT id_ticket FROM soporte_tickets WHERE id_ticket = '$gt_id' AND estado != 'Resuelto' LIMIT 1";
    $res_check = mysqli_query($conexion, $query_check);
}

if ($res_check && mysqli_num_rows($res_check) > 0) {
    // Si ya tiene uno abierto, devolvemos el existente
    $row = mysqli_fetch_assoc($res_check);
    echo json_encode(['success' => true, 'id_ticket' => $row['id_ticket'], 'message' => 'Ya tienes un ticket activo.']);
    exit;
}

// 2. Generar ID único (ej: TICK-26A4B9)
$unique_str = strtoupper(substr(uniqid(), -6));
$id_ticket = 'TICK-' . date('y') . $unique_str;

// 3. Crear el ticket
$sql_ticket = "INSERT INTO soporte_tickets (id_ticket, id_usuario, nombre_visitante, correo_visitante, asunto, estado, prioridad) VALUES ('$id_ticket', $id_usuario, $nombre_visitante, $correo_visitante, '$asunto', 'Abierto', 'Normal')";

if (mysqli_query($conexion, $sql_ticket)) {
    // Para el invitado, el id_usuario es NULL pero guardaremos enviado_por="guest" o algo que sepamos que es él. 
    $enviado_por = $is_guest ? 'guest_'.$id_ticket : $_SESSION['id'];
    $sql_msg = "INSERT INTO soporte_mensajes (id_ticket, enviado_por, mensaje) VALUES ('$id_ticket', '$enviado_por', '$mensaje_inicial')";
    mysqli_query($conexion, $sql_msg);
    
    if ($is_guest) {
        $_SESSION['guest_ticket_id'] = $id_ticket;
    }
    
    echo json_encode(['success' => true, 'id_ticket' => $id_ticket]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al crear el ticket: ' . mysqli_error($conexion)]);
}
?>
