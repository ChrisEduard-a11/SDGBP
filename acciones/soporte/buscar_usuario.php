<?php
session_start();
require_once("../../conexion.php");

header('Content-Type: application/json');

if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$query = isset($_GET['q']) ? mysqli_real_escape_string($conexion, trim($_GET['q'])) : '';

if (strlen($query) < 3) {
    echo json_encode(['success' => true, 'usuarios' => []]);
    exit;
}

$sql = "SELECT id_usuario, nombre, cedula, usuario, foto FROM usuario 
        WHERE nombre LIKE '%$query%' OR cedula LIKE '%$query%' OR usuario LIKE '%$query%' 
        LIMIT 10";
$res = mysqli_query($conexion, $sql);

$usuarios = [];
while ($row = mysqli_fetch_assoc($res)) {
    $usuarios[] = $row;
}

echo json_encode(['success' => true, 'usuarios' => $usuarios]);
?>
