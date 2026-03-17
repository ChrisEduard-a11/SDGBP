<?php
function getSO($user_agent) {
    // Windows
    if (preg_match('/Windows NT 10.0/i', $user_agent)) return 'Windows 10';
    if (preg_match('/Windows NT 6.3/i', $user_agent)) return 'Windows 8.1';
    if (preg_match('/Windows NT 6.2/i', $user_agent)) return 'Windows 8';
    if (preg_match('/Windows NT 6.1/i', $user_agent)) return 'Windows 7';
    if (preg_match('/Windows NT 6.0/i', $user_agent)) return 'Windows Vista';
    if (preg_match('/Windows NT 5.1/i', $user_agent)) return 'Windows XP';
    if (preg_match('/Windows/i', $user_agent)) return 'Windows';

    // Android
    if (preg_match('/Android ([\d\.]+)/i', $user_agent, $match)) return 'Android ' . $match[1];

    // iOS
    if (preg_match('/iPhone OS ([\d_]+)/i', $user_agent, $match)) return 'iOS ' . str_replace('_', '.', $match[1]);
    if (preg_match('/iPad; CPU OS ([\d_]+)/i', $user_agent, $match)) return 'iOS ' . str_replace('_', '.', $match[1]);
    if (preg_match('/iPod; CPU iPhone OS ([\d_]+)/i', $user_agent, $match)) return 'iOS ' . str_replace('_', '.', $match[1]);

    // Mac OS
    if (preg_match('/Mac OS X ([\d_]+)/i', $user_agent, $match)) return 'Mac OS X ' . str_replace('_', '.', $match[1]);
    if (preg_match('/Macintosh|Mac OS X/i', $user_agent)) return 'Mac OS';

    // Linux
    if (preg_match('/Linux/i', $user_agent)) return 'Linux';

    return 'Otro';
}

function registrarAccion($conexion, $accion, $id_usuario) {
    date_default_timezone_set('America/Caracas');
    $ip = $_SERVER["REMOTE_ADDR"];
    $fecha = date('Y-m-d H:i:s');
    $so = getSO($_SERVER["HTTP_USER_AGENT"]);
    $system_info = $so . ' - User: ' . $_SESSION["nombre"];

    // Verificar si el id_usuario existe en la tabla usuario
    $sql_verificar = "SELECT COUNT(*) AS count FROM usuario WHERE id_usuario = ?";
    $stmt_verificar = $conexion->prepare($sql_verificar);
    $stmt_verificar->bind_param("i", $id_usuario);
    $stmt_verificar->execute();
    $result_verificar = $stmt_verificar->get_result();
    $fila = $result_verificar->fetch_assoc();

    if ($fila['count'] > 0) {
        // Insertar la acción en la tabla bitacora
        $sql_bitacora = "INSERT INTO bitacora (ip, fecha, system_info, accion) VALUES (?, ?, ?, ?)";
        $stmt_bitacora = $conexion->prepare($sql_bitacora);
        $stmt_bitacora->bind_param("ssss", $ip, $fecha, $system_info, $accion);

        if ($stmt_bitacora->execute()) {
            // Obtener el ID de la bitácora recién insertada
            $bitacora_id = $conexion->insert_id;

            // Insertar la relación en la tabla usuario_pagos
            $sql_relacion = "INSERT INTO usuario_pagos (usuario_id, bitacora_id) VALUES (?, ?)";
            $stmt_relacion = $conexion->prepare($sql_relacion);
            $stmt_relacion->bind_param("ii", $id_usuario, $bitacora_id);

            $stmt_relacion->execute();
        }
    }

    // Cerrar los statements
    $stmt_verificar->close();
    if (isset($stmt_bitacora)) $stmt_bitacora->close();
    if (isset($stmt_relacion)) $stmt_relacion->close();
}