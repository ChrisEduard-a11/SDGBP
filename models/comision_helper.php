<?php
function registrarComision(
    $conexion,
    $usuario_id,
    $nombre_cliente,
    $comision,
    $referencia,
    $fecha_pago,
    $cliente,
    $pago_origen_id,
    $nuevo_saldo_comision,
    $metodo_pago,
    $usuario_aprobador
) {
    error_log("registrarComisionComoPago: comision=$comision, usuario_id=$usuario_id, ...");

    $referencia_comision = "Comisión por pago REF: $referencia";
    $descripcion_comision = "Comisión por pago";
    $estado = "aprobado";
    $tipo = "Egreso";
    $des_rechazo = "";

    // 1. Insertar la comisión como un nuevo pago (egreso)
    $sql = "INSERT INTO pagos (
        nombre_cliente, monto, descripcion, referencia, fecha_pago, estado, tipo, cliente, saldo_resultante, des_rechazo, usuario_aprobador, metodo_pago
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param(
        "sdssssssdsss",
        $nombre_cliente,         // s
        $comision,               // d
        $descripcion_comision,   // s
        $referencia_comision,    // s
        $fecha_pago,             // s
        $estado,                 // s
        $tipo,                   // s
        $cliente,                // s
        $nuevo_saldo_comision,   // d
        $des_rechazo,            // s
        $usuario_aprobador,      // s
        $metodo_pago             // s
    );
    if (!$stmt->execute()) {
        error_log("Error al insertar comisión como pago: " . $stmt->error);
    }
    $nuevo_id_pago_comision = $conexion->insert_id;
    $stmt->close();

    // 2. Relacionar el pago de comisión con el usuario en usuario_pagos
    $sql_rel = "INSERT INTO usuario_pagos (usuario_id, pago_id) VALUES (?, ?)";
    $stmt_rel = $conexion->prepare($sql_rel);
    $stmt_rel->bind_param("ii", $usuario_id, $nuevo_id_pago_comision);
    if (!$stmt_rel->execute()) {
        error_log("Error al insertar relación usuario-pago comisión: " . $stmt_rel->error);
    }
    $stmt_rel->close();
    
    // --- Actualizar el saldo del usuario ---
    $sql_saldo = "UPDATE usuario SET saldo = ? WHERE id_usuario = ?";
    $stmt_saldo = $conexion->prepare($sql_saldo);
    $stmt_saldo->bind_param("di", $nuevo_saldo_comision, $usuario_id);
    if (!$stmt_saldo->execute()) {
        error_log("Error al actualizar saldo tras comisión: " . $stmt_saldo->error);
    }
    $stmt_saldo->close();

    return $nuevo_id_pago_comision;
}