<?php
session_start(); 
require_once("../conexion.php");

$mes = $_GET['mes'] ?? '';
$id = $_SESSION['id'] ?? ''; // Usar el id de la sesión directamente

if (!$mes || !$id) {
    echo "Datos incompletos.";
    exit;
}

// Extrae año y mes
list($anio, $mes_num) = explode('-', $mes);

// Consulta el saldo_resultante del último pago aprobado del usuario en ese mes
$sql = "SELECT p.saldo_resultante
        FROM pagos p
        INNER JOIN usuario_pagos up ON up.pago_id = p.id
        WHERE YEAR(p.fecha_pago) = ? 
          AND MONTH(p.fecha_pago) = ? 
          AND up.usuario_id = ? 
          AND p.estado = 'aprobado'
        ORDER BY p.fecha_pago DESC, p.id DESC
        LIMIT 1";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("iii", $anio, $mes_num, $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$saldo = $row['saldo_resultante'] ?? 0;

echo "<strong>Saldo resultante del último pago del mes seleccionado:</strong> " . number_format($saldo, 2, ',', '.') . " Bs";
?>