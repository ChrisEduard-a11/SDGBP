<?php
session_start();
require_once("../conexion.php");
require_once("../models/notificaciones.php");

$usuario_id = $_SESSION['id'] ?? null;
$tipo_usuario = $_SESSION['tipo'] ?? null;

if (!$usuario_id) {
    echo json_encode(['status' => 'error', 'message' => 'Sesión no iniciada']);
    exit();
}

$id = isset($_POST['id']) ? intval($_POST['id']) : null;
$eliminar_todas = isset($_POST['all']) && $_POST['all'] == 'true';

if ($eliminar_todas) {
    if (eliminarTodasLasNotificaciones($conexion, $usuario_id, $tipo_usuario)) {
        echo json_encode(['status' => 'success', 'message' => 'Todas las notificaciones eliminadas']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se pudieron eliminar las notificaciones']);
    }
} else if ($id) {
    if (eliminarNotificacion($conexion, $id, $usuario_id, $tipo_usuario)) {
        echo json_encode(['status' => 'success', 'message' => 'Notificación eliminada']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo eliminar la notificación']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'ID no proporcionado']);
}
?>
