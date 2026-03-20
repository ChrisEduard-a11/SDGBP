<?php
ob_start();
session_start();
error_reporting(0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (isset($_SESSION['carrito'])) {
    echo json_encode(['carrito' => $_SESSION['carrito']]);
} else {
    echo json_encode(['carrito' => []]);
}
exit();