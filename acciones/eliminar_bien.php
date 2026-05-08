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
    $sql_bien = "SELECT nombre FROM bienes WHERE id = ?";
    $stmt_bien = $conexion->prepare($sql_bien);
    $stmt_bien->bind_param("i", $id);
    $stmt_bien->execute();
    $res_bien = $stmt_bien->get_result();
    if ($row_bien = $res_bien->fetch_assoc()) {
        $nombre_bien = $row_bien['nombre'];
    }

    $query = "DELETE FROM bienes WHERE id = ?";
    $stmt_del = $conexion->prepare($query);
    $stmt_del->bind_param("i", $id);
    $result = $stmt_del->execute();

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