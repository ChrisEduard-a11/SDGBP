<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user']) || strtolower($_SESSION['tipo'] ?? '') !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}

$configFile = '../config/marketing_status.json';

if (!file_exists($configFile)) {
    file_put_contents($configFile, json_encode(['activo' => true]));
}

$data = json_decode(file_get_contents($configFile), true);
$data['activo'] = !$data['activo']; // Flip the boolean

if (file_put_contents($configFile, json_encode($data))) {
    echo json_encode(['success' => true, 'activo' => $data['activo']]);
} else {
    echo json_encode(['success' => false, 'message' => 'No se pudo guardar la configuración']);
}
?>
