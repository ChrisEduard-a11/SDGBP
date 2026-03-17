<?php
require_once("../conexion.php");
$id_usuario = $_POST['id_usuario'] ?? null;
$bloquear = $_POST['bloquear'] ?? null;

if ($id_usuario === null || $bloquear === null) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

// Solo admin puede bloquear/desbloquear
session_start();
if ($_SESSION['tipo'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// No puede bloquearse a sí mismo
if ($id_usuario == $_SESSION['id']) {
    echo json_encode(['success' => false, 'message' => 'No puedes bloquearte a ti mismo']);
    exit;
}

$sql = "UPDATE usuario SET bloqueado = ? WHERE id_usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ii", $bloquear, $id_usuario);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos']);
}
?>