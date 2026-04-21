<?php
header('Content-Type: application/json');
session_start();
require_once("../conexion.php");
include('../models/bitacora.php');

// Solo los administradores pueden cambiar este estado
if (!isset($_SESSION['tipo']) || strtolower($_SESSION['tipo']) !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
    exit;
}

// Obtener estado actual
$query = mysqli_query($conexion, "SELECT activo FROM config_mantenimiento WHERE id = 1");
$row = mysqli_fetch_assoc($query);
$new_state = $row['activo'] == 1 ? 0 : 1;

// Actualizar estado
$update = mysqli_query($conexion, "UPDATE config_mantenimiento SET activo = $new_state WHERE id = 1");

if ($update) {
    $msg = $new_state ? 'Modo Mantenimiento ACTIVADO' : 'Modo Mantenimiento DESACTIVADO';
    registrarAccion($conexion, $msg, $_SESSION['id']);
    echo json_encode(['success' => true, 'activo' => (bool)$new_state]);
} else {
    echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el estado en la base de datos.']);
}
?>
