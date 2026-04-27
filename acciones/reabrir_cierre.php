<?php
session_start();
require_once("../conexion.php");
include_once('../models/bitacora.php');

header('Content-Type: application/json');

if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    echo json_encode(["status" => "error", "message" => "Petición no autorizada."]);
    exit();
}

$usuario_id = $_SESSION['id'] ?? null;
$tipo_usuario = $_SESSION['tipo'] ?? null;

if (!$usuario_id || ($tipo_usuario !== 'admin' && $tipo_usuario !== 'cont')) {
    echo json_encode(["status" => "error", "message" => "No tienes permisos."]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cierre_id = (int)($_POST['id'] ?? 0);

    if ($cierre_id <= 0) {
        echo json_encode(["status" => "error", "message" => "ID inválido."]);
        exit();
    }

    // Validar que el cierre es uno de los dos últimos
    $sql_top = "SELECT id FROM cierres_mensuales ORDER BY anio DESC, mes DESC LIMIT 2";
    $res_top = $conexion->query($sql_top);
    $is_valid = false;
    if ($res_top) {
        while ($rt = $res_top->fetch_assoc()) {
            if ($rt['id'] == $cierre_id) {
                $is_valid = true;
                break;
            }
        }
    }
    
    if (!$is_valid) {
        echo json_encode(["status" => "error", "message" => "Protección Contable: Solo está permitido revertir los últimos 2 cierres de mes registrados."]);
        exit();
    }

    // Obtener datos antes de borrar para la bitácora
    $sql_info = "SELECT mes, anio FROM cierres_mensuales WHERE id = ?";
    $stmt_info = $conexion->prepare($sql_info);
    $stmt_info->bind_param("i", $cierre_id);
    $stmt_info->execute();
    $res_info = $stmt_info->get_result();
    
    if ($res_info->num_rows > 0) {
        $row = $res_info->fetch_assoc();
        $mes = $row['mes'];
        $anio = $row['anio'];
        
        $sql_delete = "DELETE FROM cierres_mensuales WHERE id = ?";
        $stmt_delete = $conexion->prepare($sql_delete);
        $stmt_delete->bind_param("i", $cierre_id);
        
        if ($stmt_delete->execute()) {
            $nombres_meses = [
                1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 
                5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 
                9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
            ];
            $nombre_mes = $nombres_meses[$mes];
            registrarAccion($conexion, 'Reabrió Período Fiscal: ' . $nombre_mes . ' ' . $anio, $usuario_id);
            
            echo json_encode(["status" => "success", "message" => "El mes ha sido reabierto exitosamente."]);
        } else {
            echo json_encode(["status" => "error", "message" => "No se pudo reabrir el mes."]);
        }
        $stmt_delete->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Registro no encontrado."]);
    }
    $stmt_info->close();
}

mysqli_close($conexion);
?>
