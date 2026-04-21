<?php
header('Content-Type: application/json');
session_start();
require_once("../conexion.php");
include('../models/bitacora.php');

if (!isset($_SESSION['tipo']) || strtolower($_SESSION['tipo']) !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
    exit;
}

// Obtener datos del POST
$titulo = $_POST['titulo'] ?? '';
$descripcion = $_POST['descripcion'] ?? '';
$hora_inicio = $_POST['hora_inicio'] ?? '';
$hora_fin = $_POST['hora_fin'] ?? '';
$fecha = $_POST['fecha'] ?? null;
if (empty($fecha)) $fecha = null;

// Actualizar en DB
$sql_update = "UPDATE config_mantenimiento SET titulo = ?, descripcion = ?, hora_inicio = ?, hora_fin = ?, fecha = ? WHERE id = 1";
$stmt = $conexion->prepare($sql_update);
$stmt->bind_param("sssss", $titulo, $descripcion, $hora_inicio, $hora_fin, $fecha);

if ($stmt->execute()) {
    registrarAccion($conexion, 'Actualización de ajustes de mantenimiento (SQL)', $_SESSION['id']);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al guardar en base de datos.']);
}
?>
