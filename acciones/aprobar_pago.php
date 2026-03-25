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
    // Verificar token de idempotencia
    $token = $_POST['idempotency_token'] ?? '';
    if (empty($token) || !isset($_SESSION['form_tokens'][$token])) {
        $_SESSION["mensaje"] = "Error: Esta acción ya ha sido procesada o el token es inválido.";
        $_SESSION["estatus"] = "error";
        header("Location: ../vistas/aprobar_pago.php");
        exit();
    }
    // Eliminar el token de la sesión para evitar re-procesamiento
    unset($_SESSION['form_tokens'][$token]);

    $id = $_POST["id"];
    $accion = $_POST["accion"];
    $descripcion = $_POST["descripcion"] ?? null; // Descripción para rechazos
    $comision_raw = $_POST["comision"] ?? "0";
    if (strpos($comision_raw, ',') !== false && strpos($comision_raw, '.') !== false) {
        $comision_raw = str_replace('.', '', $comision_raw);
        $comision_raw = str_replace(',', '.', $comision_raw);
    } elseif (strpos($comision_raw, ',') !== false) {
        $comision_raw = str_replace(',', '.', $comision_raw);
    }
    $comision = floatval($comision_raw); // Comisión para aprobar

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
            // Calcular saldo resultante con bcmath para máxima precisión
            $saldo_resultante = bcadd($saldo_actual, $monto, 2);
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
    $color_banner = ($estado == "aprobado") ? "#10b981" : "#ef4444";
    $titulo_banner = ($estado == "aprobado") ? "Pago Aprobado" : "Pago Rechazado";
    
    // Obtener la URL base dinámica para el enlace del correo
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $script_dirname = dirname($_SERVER['SCRIPT_NAME']);
    $base_dir = preg_replace('/\/acciones$/i', '', $script_dirname);
    $login_url = rtrim($protocol . '://' . $host . $base_dir, '/') . '/vistas/login.php';

    $mensaje = "
<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
</head>
<body style='margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f8fafc; color: #334155; -webkit-font-smoothing: antialiased;'>
    <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #f8fafc; padding: 40px 0;'>
        <tr>
            <td align='center'>
                <table width='100%' style='max-width: 600px; background-color: #ffffff; border-radius: 12px; overflow: hidden; margin: 0 auto; border: 1px solid #e2e8f0;' cellpadding='0' cellspacing='0'>
                    <!-- Header -->
                    <tr>
                        <td align='center' style='padding: 30px 20px; background-color: #0f172a; border-bottom: 4px solid {$color_banner};'>
                            <h1 style='color: #ffffff; font-size: 24px; font-weight: 700; margin: 0; letter-spacing: -0.5px;'>Estado de Pago Notificado</h1>
                            <p style='color: #94a3b8; font-size: 14px; margin: 5px 0 0 0;'>Sistema de Gestión de Bienes y Pagos</p>
                        </td>
                    </tr>
                    <!-- Body Content -->
                    <tr>
                        <td style='padding: 40px 40px 30px 40px;'>
                            <h2 style='color: #0f172a; font-size: 20px; font-weight: 600; margin-top: 0; margin-bottom: 20px;'>Estimado/a {$nombre},</h2>
                            <p style='font-size: 16px; line-height: 1.6; color: #475569; margin-top: 0; margin-bottom: 25px;'>
                                Le informamos oficialmente el estado de revisión de la siguiente transacción registrada a su nombre:
                            </p>
                            
                            <table width='100%' cellpadding='10' cellspacing='0' style='background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 20px;'>
                                <tr>
                                    <td width='35%' style='color: #64748b; font-weight: 700; border-bottom: 1px solid #e2e8f0;'>Monto Transado:</td>
                                    <td style='color: #0f172a; font-weight: 800; font-size: 18px; border-bottom: 1px solid #e2e8f0;'>Bs. {$monto}</td>
                                </tr>
                                <tr>
                                    <td width='35%' style='color: #64748b; font-weight: 700; border-bottom: 1px solid #e2e8f0;'>Nro. Referencia:</td>
                                    <td style='color: #0f172a; font-weight: 600; border-bottom: 1px solid #e2e8f0;'>{$referencia}</td>
                                </tr>
                                <tr>
                                    <td width='35%' style='color: #64748b; font-weight: 700;'>Estado Final:</td>
                                    <td style='color: {$color_banner}; font-weight: 800; text-transform: uppercase;'>{$titulo_banner}</td>
                                </tr>
                            </table>

                            " . ($estado == "rechazado" && $descripcion ? "
                            <div style='background-color: #fef2f2; padding: 15px 20px; border-radius: 8px; border-left: 4px solid #ef4444; margin-bottom: 25px;'>
                                <p style='margin: 0; color: #b91c1c; font-size: 14px; font-weight: 700; text-transform: uppercase; margin-bottom: 5px;'>Motivo del Rechazo:</p>
                                <p style='margin: 0; color: #991b1b; font-size: 15px;'>{$descripcion}</p>
                            </div>
                            " : "") . "

                            <!-- Action Button -->
                            <div style='text-align: center; margin: 35px 0;'>
                                <a href='{$login_url}' style='display: inline-block; padding: 14px 28px; background-color: #0f172a; color: #ffffff; text-decoration: none; font-size: 16px; font-weight: bold; border-radius: 8px;'>Consultar mi Cuenta</a>
                            </div>
                            <p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 30px; margin-bottom: 0;'>
                                Si tiene alguna duda o requiere mayor información técnica respecto al estado de esta operación, puede responder directamente a este correo para contactar al servicio de soporte de <strong>SDGBP</strong>.
                            </p>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style='background-color: #f1f5f9; padding: 20px 40px; text-align: center; border-top: 1px solid #e2e8f0;'>
                            <p style='margin: 0 0 10px 0; font-size: 12px; color: #64748b; font-weight: 600;'>
                                &copy; " . date('Y') . " SDGBP. Todos los derechos reservados.
                            </p>
                            <p style='margin: 0; font-size: 11px; color: #94a3b8;'>
                                Mensaje emitido por el sistema contable automatizado.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
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