<?php
session_start();
require_once("../conexion.php");
require_once("../models/bitacora.php");
require_once("../models/comision_helper.php");
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"]) && isset($_POST["accion"])) {
    $id = $_POST["id"];
    $accion = $_POST["accion"];
    $descripcion = $_POST["descripcion"] ?? null; // Descripción para rechazos
    $comision = isset($_POST["comision"]) ? floatval($_POST["comision"]) : 0; // Comisión para aprobar

    $estado = $accion == "aprobar" ? "aprobado" : "rechazado";

    // PRIMERO: Obtener los datos del pago y el usuario asociado
    $query = "SELECT 
        usuario.correo, 
        usuario.nombre, 
        usuario.id_usuario, 
        usuario.saldo, 
        pagos.monto, 
        pagos.referencia, 
        pagos.tipo,
        pagos.nombre_cliente,
        pagos.cliente,
        pagos.descripcion,
        pagos.metodo_pago,
        pagos.fecha_pago,
        pagos.comprobante_archivo
    FROM 
        usuario
    INNER JOIN 
        usuario_pagos 
    ON 
        usuario.id_usuario = usuario_pagos.usuario_id
    INNER JOIN 
        pagos 
    ON 
        pagos.id = usuario_pagos.pago_id
    WHERE 
        pagos.id = ?";
    $stmt2 = $conexion->prepare($query);
    $stmt2->bind_param("i", $id);
    $stmt2->execute();
    $result = $stmt2->get_result();

    // Variables para usar fuera del while
    $correo = $nombre = $referencia = $tipo = $nombre_cliente = $cliente = $descripcion_pago = $metodo_pago = $comprobante_archivo = "";
    $usuario_id = $id_pago = 0;
    $monto = $saldo_actual = 0;
    $nuevo_saldo = 0;

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $correo = $row['correo'];
        $nombre = $row['nombre'];
        $referencia = $row['referencia'];
        $monto = $row['monto'];
        $tipo = $row['tipo'];
        $usuario_id = $row['id_usuario'];
        $saldo_actual = $row['saldo'];
        $nombre_cliente = $row['nombre_cliente'];
        $cliente = $row['cliente'];
        $descripcion_pago = $row['descripcion'];
        $metodo_pago = $row['metodo_pago'];
        $fecha_pago = $row['fecha_pago'];
        $comprobante_archivo = $row['comprobante_archivo'];
        
        $usuario_aprobador = isset($_SESSION['nombre']) ? $_SESSION['nombre'] : '';

        if ($estado == "aprobar" || $estado == "aprobado") { // Solo para pagos aprobados
            // 1. Validar saldo suficiente para comisión y egreso
            $saldo_despues_comision = $saldo_actual;
            if ($comision > 0) {
                $saldo_despues_comision = $saldo_actual - $comision;
                if ($saldo_despues_comision < 0) {
                    $_SESSION["mensaje"] = "Error: El saldo del usuario es insuficiente para descontar la comisión.";
                    $_SESSION["estatus"] = "warning";
                    header("Location: ../vistas/aprobar_pago.php");
                    exit();
                }
            }

            // 2. Validar saldo suficiente para el pago principal (si es egreso)
            if ($tipo == "Egreso" && $saldo_despues_comision < $monto) {
                $_SESSION["mensaje"] = "Error: El saldo del usuario es insuficiente para aprobar este pago.";
                $_SESSION["estatus"] = "warning";
                header("Location: ../vistas/aprobar_pago.php");
                exit();
            }

            // 3. Registrar comisión SOLO si todo es válido
            $nuevo_id_pago_comision = null;
            if ($comision > 0) {
                $nuevo_id_pago_comision = registrarComision(
                    $conexion,
                    $usuario_id,
                    $nombre_cliente,
                    $comision,
                    $referencia, // referencia original
                    $fecha_pago,
                    $cliente,
                    null, // aún no hay id de pago principal
                    $saldo_despues_comision,
                    $metodo_pago,
                    $usuario_aprobador
                );
            }

            // 4. Procesar el pago principal (nuevo registro)
            if ($tipo == "Ingreso") {
                $nuevo_saldo = $saldo_despues_comision + $monto;
            } elseif ($tipo == "Egreso") {
                $nuevo_saldo = $saldo_despues_comision - $monto;
            } else {
                $nuevo_saldo = $saldo_despues_comision;
            }

            $sql_insert = "INSERT INTO pagos (nombre_cliente, monto, descripcion, referencia, fecha_pago, estado, tipo, cliente, saldo_resultante, des_rechazo, usuario_aprobador, metodo_pago, comprobante_archivo)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_insert = $conexion->prepare($sql_insert);
            $stmt_insert->bind_param(
                "sdssssssdssss",
                $nombre_cliente,    // s
                $monto,             // d
                $descripcion_pago,  // s
                $referencia,        // s
                $fecha_pago,        // s
                $estado,            // s
                $tipo,              // s
                $cliente,           // s
                $nuevo_saldo,       // d
                $descripcion,       // s
                $_SESSION['nombre'],// s
                $metodo_pago,       // s
                $comprobante_archivo // s
            );
            $stmt_insert->execute();
            $nuevo_id_pago = $conexion->insert_id;
            $stmt_insert->close();

            // Relacionar el nuevo pago aprobado con el usuario en usuario_pagos
            $sql_relacion = "INSERT INTO usuario_pagos (usuario_id, pago_id) VALUES (?, ?)";
            $stmt_relacion = $conexion->prepare($sql_relacion);
            $stmt_relacion->bind_param("ii", $usuario_id, $nuevo_id_pago);
            $stmt_relacion->execute();
            $stmt_relacion->close();

            // Actualizar el saldo del usuario tras el pago principal
            $sql_actualizar_saldo = "UPDATE usuario SET saldo = ? WHERE id_usuario = ?";
            $stmt_actualizar_saldo = $conexion->prepare($sql_actualizar_saldo);
            $stmt_actualizar_saldo->bind_param("di", $nuevo_saldo, $usuario_id);
            if (!$stmt_actualizar_saldo->execute()) {
                error_log("Error al actualizar el saldo del usuario tras pago principal: " . $stmt_actualizar_saldo->error);
                $_SESSION["mensaje"] = "Error al actualizar el saldo tras pago principal.";
                $_SESSION["estatus"] = "error";
                header("Location: ../vistas/aprobar_pago.php");
                exit();
            }
            $stmt_actualizar_saldo->close();

            // SOLO AQUÍ eliminar el pago pendiente
            $sql_delete = "DELETE FROM pagos WHERE id = ?";
            $stmt_delete = $conexion->prepare($sql_delete);
            $stmt_delete->bind_param("i", $id);
            $stmt_delete->execute();
            $stmt_delete->close();
        } else {
            // Si es rechazo, guardar el pago como rechazado en la tabla pagos
            $sql_insert = "INSERT INTO pagos (nombre_cliente, monto, descripcion, referencia, fecha_pago, estado, tipo, cliente, saldo_resultante, des_rechazo, usuario_aprobador, metodo_pago)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_insert = $conexion->prepare($sql_insert);
            $stmt_insert->bind_param(
                "sdssssssdsss",
                $nombre_cliente,    // s
                $monto,             // d
                $descripcion_pago,  // s
                $referencia,        // s
                $fecha_pago,        // s
                $estado,            // s ("rechazado")
                $tipo,              // s
                $cliente,           // s
                $saldo_actual,      // d (no cambia el saldo)
                $descripcion,       // s (motivo rechazo)
                $_SESSION['nombre'],// s
                $metodo_pago        // s
            );
            $stmt_insert->execute();
            $nuevo_id_pago = $conexion->insert_id;
            $stmt_insert->close();

            // Relacionar el nuevo pago rechazado con el usuario en usuario_pagos
            $sql_relacion = "INSERT INTO usuario_pagos (usuario_id, pago_id) VALUES (?, ?)";
            $stmt_relacion = $conexion->prepare($sql_relacion);
            $stmt_relacion->bind_param("ii", $usuario_id, $nuevo_id_pago);
            $stmt_relacion->execute();
            $stmt_relacion->close();

            // Si hay comprobante y es rechazo, eliminar archivo físico
            if (!empty($comprobante_archivo)) {
                $ruta_comprobante = "../uploads/comprobantes/" . $comprobante_archivo;
                if (file_exists($ruta_comprobante)) {
                    unlink($ruta_comprobante);
                }
            }

            // Eliminar el pago pendiente original
            $sql_delete = "DELETE FROM pagos WHERE id = ?";
            $stmt_delete = $conexion->prepare($sql_delete);
            $stmt_delete->bind_param("i", $id);
            $stmt_delete->execute();
            $stmt_delete->close();
        }
    }

    // Enviar correo (usando los datos ya obtenidos)
    $_SESSION["mensaje"] = "El pago ha sido " . ($estado == "aprobado" ? "aprobado" : "rechazado") . " correctamente.";
    $_SESSION["estatus"] = "success";

    // Crear el mensaje del correo
    $asunto = "Notificación de Estado de Pago";
    $mensaje = "
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Notificación de Estado de Pago</title>
    </head>
    <body style='font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4;'>
        <!-- Encabezado -->
        <div style='background-color: #f18000; padding: 10px; text-align: center; color: white;'>
            <img src='https://lh5.googleusercontent.com/p/AF1QipMIuz9nSKZaDup5Zr7LIVwhyDKheMsfdeD_55hd=w408-h408-k-no' alt='Logo' style='width: 100px; height: auto;'>
            <h1 style='margin: 0;'>Sistema de Gestión de Bienes y Pagos</h1>
        </div>

        <!-- Contenido principal -->
        <div style='padding: 20px; background-color: white; margin: 20px auto; max-width: 600px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);'>
            <h2 style='color: #f18000;'>Estimado/a $nombre,</h2>
            <p>Le informamos que el pago con referencia: <strong>$referencia</strong> por un monto de: Bs. <strong>$monto</strong> ha sido <strong>" . ($estado == "aprobado" ? "aprobado" : "rechazado") . "</strong>.</p>
            " . ($estado == "rechazado" && $descripcion ? "<p>Motivo del rechazo: <strong>$descripcion</strong></p>" : "") . "
            <p>Si tiene alguna duda o requiere más información, no dude en contactarnos.</p>
            <p style='text-align: center;'>
                <a href='mailto:soporte.sdgbp2024@gmail.com' style='display: inline-block; padding: 10px 20px; font-size: 16px; color: #fff; background-color: #f18000; text-decoration: none; border-radius: 5px;'>Contactar Soporte</a>
            </p>
        </div>

        <!-- Footer -->
        <div style='background-color: #343a40; color: white; text-align: center; padding: 10px;'>
            <p style='margin: 0; font-size: 0.9rem;'>Este mensaje fue enviado automáticamente por el sistema de gestión de Bienes y Pagos.</p>
            <p style='margin: 0; font-size: 0.9rem;'>© " . date("Y") . " Sistema de Gestión de Bienes y Pagos. Todos los derechos reservados.</p>
        </div>
    </body>
    </html>
    ";

    // Configurar PHPMailer
    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8'; 
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'soporte.sdgbp2024@gmail.com';
        $mail->Password = 'ktwf cyvz rmyh lqfy';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('soporte.sdgbp2024@gmail.com', 'Sistema de Gestión de Bienes y Pagos');
        $mail->addAddress($correo);
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body = $mensaje;

        $mail->send();
    } catch (Exception $e) {
        error_log("Error al enviar correo: {$mail->ErrorInfo}");
        $_SESSION['estatus'] = 'error';
        $_SESSION['mensaje'] = "No se pudo enviar el correo. Error: {$mail->ErrorInfo}";
    }

    // Registrar la acción en la bitácora
    if (isset($_SESSION['id'])) {
        $accion_bitacora = ($estado == "aprobado" ? "Aprobar Pago" : "Rechazar Pago");
        registrarAccion($conexion, $accion_bitacora, $_SESSION['id']);
    }

    $conexion->close();

    header("Location: ../vistas/aprobar_pago.php");
    exit();
}
?>