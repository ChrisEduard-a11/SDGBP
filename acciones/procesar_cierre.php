<?php
session_start();
require_once("../conexion.php");
include_once('../models/bitacora.php');

header('Content-Type: application/json');

// Check ajax request
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    echo json_encode(["status" => "error", "message" => "Petición no autorizada."]);
    exit();
}

$usuario_id = $_SESSION['id'] ?? null;
$tipo_usuario = $_SESSION['tipo'] ?? null;

if (!$usuario_id || ($tipo_usuario !== 'admin' && $tipo_usuario !== 'cont')) {
    echo json_encode(["status" => "error", "message" => "No tienes permisos para cerrar el mes."]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mes = (int)($_POST['mes'] ?? 0);
    $anio = (int)($_POST['anio'] ?? 0);

    if ($mes < 1 || $mes > 12 || $anio < 2000) {
        echo json_encode(["status" => "error", "message" => "Datos de período inválidos."]);
        exit();
    }

    // Comprobar si ya existe un cierre
    $sql_check = "SELECT id FROM cierres_mensuales WHERE mes = ? AND anio = ?";
    $stmt_check = $conexion->prepare($sql_check);
    $stmt_check->bind_param("ii", $mes, $anio);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();

    if ($res_check->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Este período ya fue cerrado anteriormente."]);
        exit();
    }
    $stmt_check->close();

    // Comprobar si el mes anterior está cerrado (forzar secuencia)
    $mes_ant = $mes - 1;
    $anio_ant = $anio;
    if ($mes_ant == 0) {
        $mes_ant = 12;
        $anio_ant--;
    }
    
    // Verificamos si existe en la bitácora histórica el mes anterior cerrado
    $sql_ant = "SELECT id FROM cierres_mensuales WHERE mes = ? AND anio = ?";
    $stmt_ant = $conexion->prepare($sql_ant);
    $stmt_ant->bind_param("ii", $mes_ant, $anio_ant);
    $stmt_ant->execute();
    $res_ant = $stmt_ant->get_result();
    
    // Solo validamos estrictamente si NO es el primer cierre del sistema (si hay más de 0 registros totales)
    $sql_total = "SELECT COUNT(id) as total FROM cierres_mensuales";
    $res_total = mysqli_query($conexion, $sql_total);
    $total_cierres = mysqli_fetch_assoc($res_total)['total'];

    if ($total_cierres > 0 && $res_ant->num_rows === 0) {
        echo json_encode(["status" => "error", "message" => "No puedes cerrar este mes sin haber cerrado previamente el período anterior ($mes_ant/$anio_ant)."]);
        exit();
    }
    $stmt_ant->close();

    // Proceder al cierre
    $sql_insert = "INSERT INTO cierres_mensuales (mes, anio, estado, usuario_cierre_id, fecha_cierre) VALUES (?, ?, 'cerrado', ?, NOW())";
    $stmt_insert = $conexion->prepare($sql_insert);
    $stmt_insert->bind_param("iii", $mes, $anio, $usuario_id);
    
    if ($stmt_insert->execute()) {
        $nombres_meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        
        $nombre_mes = $nombres_meses[$mes];
        registrarAccion($conexion, 'Ejecutó Cierre de Mes: ' . $nombre_mes . ' ' . $anio, $usuario_id);
        
        echo json_encode(["status" => "success", "message" => "El período ha sido sellado y cerrado correctamente."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error interno al ejecutar el cierre: " . $stmt_insert->error]);
    }
    $stmt_insert->close();
}

mysqli_close($conexion);
?>
