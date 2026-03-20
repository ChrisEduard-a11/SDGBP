<?php
ob_start();
session_start();
error_reporting(0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['nombre'], $data['precio'], $data['cantidad'], $data['productoId'])) {
    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = [];
    }
    $_SESSION['carrito'][] = [
        'productoId' => $data['productoId'],
        'nombre' => $data['nombre'],
        'precio' => $data['precio'],
        'cantidad' => $data['cantidad']
    ];
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
}
exit();