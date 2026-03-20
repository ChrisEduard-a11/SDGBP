<?php
ob_start();
session_start();
error_reporting(0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents('php://input'), true);
if (isset($data['index']) && isset($_SESSION['carrito'][$data['index']])) {
    array_splice($_SESSION['carrito'], $data['index'], 1);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
exit();