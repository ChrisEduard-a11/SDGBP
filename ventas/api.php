<?php
ob_start();
session_start();
error_reporting(0);
require_once("../conexion.php");

ob_clean();
header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

try {
    switch ($action) {
        case 'get_categorias':
            $sql = "SELECT id, nombre FROM categorias_productos";
            $result = $conexion->query($sql);
            $cats = [];
            while ($row = $result->fetch_assoc()) {
                foreach($row as $k => $v) if(is_string($v)) $row[$k] = mb_convert_encoding($v, 'UTF-8', 'UTF-8');
                $cats[] = $row;
            }
            echo json_encode(['status' => 'success', 'data' => $cats]);
            break;

        case 'get_productos':
            $catId = $_GET['categoria'] ?? '';
            $sql = "SELECT p.id, p.nombre, p.descripcion, p.precio, p.stock, p.imagen, c.nombre AS categoria 
                    FROM productos p 
                    JOIN categorias_productos c ON p.categoria_productos_id = c.id";
            if (!empty($catId)) {
                $sql .= " WHERE p.categoria_productos_id = ?";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("i", $catId);
                $stmt->execute();
                $result = $stmt->get_result();
            } else {
                $result = $conexion->query($sql);
            }
            $prods = [];
            while ($row = $result->fetch_assoc()) {
                foreach($row as $k => $v) if(is_string($v)) $row[$k] = mb_convert_encoding($v, 'UTF-8', 'UTF-8');
                $prods[] = $row;
            }
            echo json_encode(['status' => 'success', 'data' => $prods]);
            break;

        case 'get_carrito':
            echo json_encode(['status' => 'success', 'data' => array_values($_SESSION['carrito'])]);
            break;

        case 'add_carrito':
            if ($method === 'POST' && isset($data['productoId'], $data['nombre'], $data['precio'], $data['cantidad'])) {
                $_SESSION['carrito'][] = [
                    'productoId' => $data['productoId'],
                    'nombre' => $data['nombre'],
                    'precio' => (float)$data['precio'],
                    'cantidad' => (int)$data['cantidad']
                ];
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Datos inválidos']);
            }
            break;

        case 'remove_carrito':
            if ($method === 'POST' && isset($data['index'])) {
                array_splice($_SESSION['carrito'], (int)$data['index'], 1);
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Índice inválido']);
            }
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
exit();
?>
