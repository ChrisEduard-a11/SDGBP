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

// Obtener estado actual y acción deseada
$action = $_GET['action'] ?? null;
$query = mysqli_query($conexion, "SELECT activo FROM config_mantenimiento WHERE id = 1");
$row = mysqli_fetch_assoc($query);
$current_state = (int)$row['activo'];

if ($action === 'on') {
    $new_state = 1;
} elseif ($action === 'off') {
    $new_state = 0;
} else {
    // Fallback al toggle ciego si no hay acción definida
    $new_state = $current_state == 1 ? 0 : 1;
}

// Solo actualizar si el estado es diferente
if ($new_state !== $current_state || $action === 'off') {
    // Si la acción es 'off', desactivamos también el uso del horario para permitir el bypass
    $set_usar_horario = ($action === 'off') ? ", usar_horario = 0" : "";
    
    $update = mysqli_query($conexion, "UPDATE config_mantenimiento SET activo = $new_state $set_usar_horario WHERE id = 1");
    if ($update) {
        $msg = $new_state ? 'Modo Mantenimiento ACTIVADO (Manual)' : 'Modo Mantenimiento DESACTIVADO (Manual/Bypass)';
        registrarAccion($conexion, $msg, $_SESSION['id']);
        echo json_encode(['success' => true, 'activo' => (bool)$new_state]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el estado en la base de datos.']);
    }
} else {
    // Si ya estaba en el estado deseado, retornamos éxito igualmente
    echo json_encode(['success' => true, 'activo' => (bool)$new_state, 'no_change' => true]);
}
?>
