<?php
require_once("../conexion.php");

$cedula = $_POST['cedula'] ?? '';
$usuario_id = $_POST['usuario_id'] ?? null;

if ($usuario_id) {
    // Si se está editando, ignorar coincidencia con el propio usuario
    $sql = "SELECT COUNT(*) as total FROM usuario WHERE cedula = ? AND id_usuario != ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("si", $cedula, $usuario_id);
} else {
    // Si es registro nuevo, cualquier coincidencia es error
    $sql = "SELECT COUNT(*) as total FROM usuario WHERE cedula = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $cedula);
}

$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
echo $result['total'] > 0 ? 'existe' : 'no_existe';
?>