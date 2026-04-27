<?php
session_start();
require_once("../conexion.php");

header('Content-Type: application/json');

if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    echo json_encode(["status" => "error", "message" => "Petición no autorizada."]);
    exit();
}

$mes = (int)($_GET['mes'] ?? 0);
$anio = (int)($_GET['anio'] ?? 0);

if ($mes < 1 || $mes > 12 || $anio < 2000) {
    echo json_encode(["status" => "error", "message" => "Datos inválidos."]);
    exit();
}

// 1. Pagos pendientes
$sql_pend = "SELECT COUNT(*) as total FROM pagos WHERE MONTH(fecha_pago) = ? AND YEAR(fecha_pago) = ? AND estado NOT IN ('aprobado', 'rechazado')";
$stmt_pend = $conexion->prepare($sql_pend);
$stmt_pend->bind_param("ii", $mes, $anio);
$stmt_pend->execute();
$pend_count = $stmt_pend->get_result()->fetch_assoc()['total'];

echo json_encode([
    "status" => "success",
    "pagos_pendientes" => $pend_count,
    "total" => $pend_count
]);

mysqli_close($conexion);
?>
