<?php
header('Content-Type: application/json');
session_start();
include('../conexion.php');
include('../models/bitacora.php');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (isset($data['id'])) {
    $id = intval($data['id']);

    $nombre_bien = 'Desconocido';
    $sql_bien = "SELECT nombre FROM bienes WHERE id = $id";
    if ($res_bien = mysqli_query($conexion, $sql_bien)) {
        if ($row_bien = mysqli_fetch_assoc($res_bien)) {
            $nombre_bien = $row_bien['nombre'];
        }
    }

    $query = "DELETE FROM bienes WHERE id = $id";
    $result = mysqli_query($conexion, $query);

    if ($result) {
        if (isset($_SESSION['id'])) {
            $accion_bitacora = 'Eliminó un bien nacional - Nombre: ' . $nombre_bien;
            registrarAccion($conexion, $accion_bitacora, $_SESSION['id']);
        }
        echo json_encode(['success' => true, 'message' => 'Bien eliminado correctamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar el bien.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ID de la bien no proporcionado.']);
}
exit;