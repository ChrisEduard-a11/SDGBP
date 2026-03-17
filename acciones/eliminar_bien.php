<?php
header('Content-Type: application/json');
session_start();
include('../conexion.php');
include('../models/bitacora.php');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (isset($data['id'])) {
    $id = intval($data['id']);
    $query = "DELETE FROM bienes WHERE id = $id";
    $result = mysqli_query($conexion, $query);

    if ($result) {
        if (isset($_SESSION['id'])) {
            registrarAccion($conexion, 'Eliminó un bien nacional', $_SESSION['id']);
        }
        echo json_encode(['success' => true, 'message' => 'Bien eliminado correctamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar el bien.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ID de la bien no proporcionado.']);
}
exit;