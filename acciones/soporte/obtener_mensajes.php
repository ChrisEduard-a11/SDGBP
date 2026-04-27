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
// MARCAR COMO LEÍDOS los mensajes que NO fueron enviados por el usuario actual
$condicion_no_mio = "";
if ($is_admin) {
    // Si soy admin, marco como leídos los mensajes que NO vienen de admins
    $condicion_no_mio = "enviado_por NOT IN (SELECT CAST(id_usuario AS CHAR) FROM usuario WHERE tipos = 'admin') AND enviado_por != 'admin'";
} else {
    // Si soy usuario/invitado, marco como leídos los mensajes que vienen de admins
    $condicion_no_mio = "(enviado_por IN (SELECT CAST(id_usuario AS CHAR) FROM usuario WHERE tipos = 'admin') OR enviado_por = 'admin')";
}

$update_leido = "UPDATE soporte_mensajes SET leido = 1, leido_at = NOW() 
                 WHERE id_ticket = '$id_ticket' AND leido = 0 AND $condicion_no_mio";
mysqli_query($conexion, $update_leido);

// Buscar mensajes nuevos
$sql = "SELECT m.*, u.nombre, u.foto, u.tipos FROM soporte_mensajes m 
        LEFT JOIN usuario u ON (m.enviado_por = CAST(u.id_usuario AS CHAR))
        WHERE m.id_ticket = '$id_ticket' AND m.id_mensaje > $last_id 
        ORDER BY m.id_mensaje ASC";

$result = mysqli_query($conexion, $sql);
$mensajes = [];

// Chequeo estado de ticket y tiempo restante
$estado_ticket = 'Abierto';
$typing_otro = false;
$calificacion_ticket = null;
$tiempo_restante = 1800; // Default 30 min (1800s)

$res_estado = mysqli_query($conexion, "SELECT estado, typing_guest, typing_admin, calificacion, fecha_apertura FROM soporte_tickets WHERE id_ticket = '$id_ticket'");
if($row_est = mysqli_fetch_assoc($res_estado)){
    $estado_ticket = $row_est['estado'];
    $calificacion_ticket = $row_est['calificacion'];
    
    // Cálculo de tiempo restante (Vida útil de 30 min = 1800 segundos)
    $apertura_time = strtotime($row_est['fecha_apertura']);
    $ahora = time();
    $transcurrido = $ahora - $apertura_time;
    $tiempo_restante = 1800 - $transcurrido;
    
    // Auto-resolución si el tiempo expiró
    if ($tiempo_restante <= 0 && $estado_ticket !== 'Resuelto') {
        // Eliminar imágenes antes de resolver
        $res_imgs = mysqli_query($conexion, "SELECT archivo_adjunto FROM soporte_mensajes WHERE id_ticket = '$id_ticket' AND archivo_adjunto IS NOT NULL");
        while ($ri = mysqli_fetch_assoc($res_imgs)) {
            $fpath = "../../" . $ri['archivo_adjunto'];
            if (file_exists($fpath)) unlink($fpath);
        }
        
        mysqli_query($conexion, "UPDATE soporte_tickets SET estado = 'Resuelto', ultima_actualizacion = NOW() WHERE id_ticket = '$id_ticket'");
        $estado_ticket = 'Resuelto';
        $tiempo_restante = 0;
    }

    if ($tiempo_restante < 0) $tiempo_restante = 0;

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
        $emisor_tipo = 'Soporte técnico BOT SDGBP'; // Mensajes del bot de sugerencias
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

    $mensaje_final = htmlspecialchars($row['mensaje']);
    // Soporte para Markdown (**negrita**)
    $mensaje_final = preg_replace('/\*\*(.*?)\*\*/', '<b>$1</b>', $mensaje_final);
    // Permitir ciertos tags decorativos (br, b, strong) para mensajes del bot/formateados
    $buscar = ['&lt;br&gt;', '&lt;b&gt;', '&lt;/b&gt;', '&lt;strong&gt;', '&lt;/strong&gt;'];
    $reemplazar = ['<br>', '<b>', '</b>', '<strong>', '</strong>'];
    $mensaje_final = str_replace($buscar, $reemplazar, $mensaje_final);

    $mensajes[] = [
        'id_mensaje' => (int)$row['id_mensaje'],
        'mensaje' => $mensaje_final,
        'archivo_adjunto' => $row['archivo_adjunto'],
        'fecha' => date('h:i A - d/m/y', strtotime($row['fecha_envio'])),
        'es_mio' => $es_mio,
        'emisor_nombre' => $emisor_tipo,
        'foto' => $foto,
        'leido' => (int)($row['leido'] ?? 0)
    ];
}

// Obtener IDs de todos los mensajes leídos de este ticket para sincronizar ticks en tiempo real
$id_leidos = [];
$res_leidos = mysqli_query($conexion, "SELECT id_mensaje FROM soporte_mensajes WHERE id_ticket = '$id_ticket' AND leido = 1");
while($rl = mysqli_fetch_assoc($res_leidos)){
    $id_leidos[] = (int)$rl['id_mensaje'];
}

$response = [
    'success' => true, 
    'mensajes' => $mensajes, 
    'id_leidos' => $id_leidos,
    'estado' => $estado_ticket,
    'typing' => $typing_otro,
    'calificacion' => $calificacion_ticket,
    'tiempo_restante' => $tiempo_restante
];

$json = json_encode($response, JSON_INVALID_UTF8_SUBSTITUTE);
if ($json === false) {
    echo json_encode(['success' => false, 'message' => 'Error de codificación JSON: ' . json_last_error_msg()]);
} else {
    echo $json;
}
?>
