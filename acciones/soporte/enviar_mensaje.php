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

if (empty($id_ticket) && empty($mensaje) && !isset($_FILES['imagen'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos.']);
    exit;
}

$archivo_adjunto = null;
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Error en la subida del archivo (Código: ' . $_FILES['imagen']['error'] . ')']);
        exit;
    }

    $temp_path = $_FILES['imagen']['tmp_name'];
    $file_info = pathinfo($_FILES['imagen']['name']);
    $ext = strtolower($file_info['extension']);
    
    $allowed_extensions = ['jpg', 'jpeg', 'png'];
    if (!in_array($ext, $allowed_extensions)) {
        echo json_encode(['success' => false, 'message' => 'Tipo de archivo no permitido. Solo JPG, JPEG y PNG.']);
        exit;
    }

    $new_name = 'adjunto_' . uniqid() . '.' . $ext;
    $upload_dir = '../../assets/uploads/soporte/';
    
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            echo json_encode(['success' => false, 'message' => 'No se pudo crear el directorio de subida.']);
            exit;
        }
    }
    
    $dest_path = $upload_dir . $new_name;
    if (move_uploaded_file($temp_path, $dest_path)) {
        $archivo_adjunto = 'assets/uploads/soporte/' . $new_name;
    } else {
        echo json_encode(['success' => false, 'message' => 'No se pudo mover el archivo al directorio de destino. Verifique los permisos de carpeta.']);
        exit;
    }
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

$sql_msg = "INSERT INTO soporte_mensajes (id_ticket, enviado_por, mensaje, archivo_adjunto) VALUES ('$id_ticket', '$enviado_por', '$mensaje', " . ($archivo_adjunto ? "'$archivo_adjunto'" : "NULL") . ")";
if (mysqli_query($conexion, $sql_msg)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al enviar: ' . mysqli_error($conexion)]);
}
?>
