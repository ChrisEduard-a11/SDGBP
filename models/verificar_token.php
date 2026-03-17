<?php
session_start();
require_once("../conexion.php");

$usuarioid = $_SESSION['id'] ?? null;
$session_token = $_SESSION['session_token'] ?? '';

if (!$usuarioid || !$session_token) {
    echo json_encode(['status' => 'logout']);
    exit;
}

$sql = "SELECT session_token FROM usuario WHERE id_usuario = '$usuarioid'";
$res = mysqli_query($conexion, $sql);
$row = mysqli_fetch_assoc($res);

if (!$row || $row['session_token'] !== $session_token) {
    echo json_encode(['status' => 'logout']);
} else {
    echo json_encode(['status' => 'active']);
}
exit;
?>