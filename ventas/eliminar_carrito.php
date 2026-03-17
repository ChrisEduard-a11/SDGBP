<?php
session_start();
header('Content-Type: application/json');

// Obtener el índice del producto a eliminar
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['index']) && isset($_SESSION['carrito'][$data['index']])) {
    // Eliminar el producto del carrito
    unset($_SESSION['carrito'][$data['index']]);

    // Reindexar el array del carrito
    $_SESSION['carrito'] = array_values($_SESSION['carrito']);

    echo json_encode(['success' => true, 'carrito' => $_SESSION['carrito']]);
} else {
    echo json_encode(['success' => false, 'message' => 'Producto no encontrado.']);
}
?>