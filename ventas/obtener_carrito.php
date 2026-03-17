<?php
session_start();

header('Content-Type: application/json');

// Verificar si el carrito existe en la sesión
if (isset($_SESSION['carrito'])) {
    echo json_encode(['carrito' => $_SESSION['carrito']]);
} else {
    echo json_encode(['carrito' => []]); // Devolver un carrito vacío si no existe
}
?>