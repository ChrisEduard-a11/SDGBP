<?php
include('../conexion.php');
$usuario = $_POST['usuario'] ?? '';
$sql = "SELECT bloqueado FROM usuario WHERE usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if ($row && $row['bloqueado'] == 1) {
    echo "bloqueado";
} else {
    echo "ok";
}
?>