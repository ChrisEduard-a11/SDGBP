<?php
session_start();
require_once("../conexion.php");
require_once("../models/bitacora.php");
require_once("../models/comision_helper.php");
require_once("../models/notificaciones.php"); // Sistema de notificaciones
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';
require_once '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dompdf\Dompdf;
use Dompdf\Options;

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

    // --- PERFILAMIENTO DE RENDIMIENTO ---
    $t_inicio = microtime(true);

    // --- SEGURIDAD SENIOR: INICIO DE TRANSACCIÓN ---
    $conexion->begin_transaction();

    try {
        // 1. VERIFICACIÓN DE EXISTENCIA Y ESTADO (Anti-duplicados)
        $check_query = "SELECT id FROM pagos WHERE id = ? FOR UPDATE";
        $check_stmt = $conexion->prepare($check_query);
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows === 0) {
            throw new Exception("Esta transacción ya fue procesada por otro administrador o ya no existe.");
        }
        $check_stmt->close();

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
                    throw new Exception("El saldo del usuario es insuficiente para descontar la comisión administrativa.");
                }
            }

            // 2. Validar saldo suficiente para el pago principal (si es egreso)
            if ($tipo == "Egreso" && $saldo_despues_comision < $monto) {
                throw new Exception("El saldo del usuario es insuficiente para realizar este egreso contable.");
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

            $sql_delete = "DELETE FROM pagos WHERE id = ?";
            $stmt_delete = $conexion->prepare($sql_delete);
            $stmt_delete->bind_param("i", $id);
            $stmt_delete->execute();
            $stmt_delete->close();
        }

        // --- MANEJO DE NOTIFICACIONES ---
        // 1. Eliminar avisos pendientes de los administradores sobre este pago
        eliminarNotificacionesPorPago($conexion, $id);

        // 2. Notificar a la UPU sobre el resultado del pago
        if ($estado == "aprobado") {
            $titulo_notif = "Pago Aprobado ✅";
            $msj_notif = "Tu pago de Bs. {$monto} (Ref: {$referencia}) ha sido aprobado con éxito.";
            if ($comision > 0) {
                $msj_notif .= " (Comisión: Bs. {$comision})";
            }
            $tipo_notif = 'success';
            $icono_notif = 'fas fa-check-circle';
        } else {
            $titulo_notif = "Pago Rechazado ❌";
            $msj_notif = "Tu pago de Bs. {$monto} (Ref: {$referencia}) ha sido rechazado. Motivo: " . ($descripcion ?: 'Sin observaciones');
            $tipo_notif = 'danger';
            $icono_notif = 'fas fa-times-circle';
        }
        crearNotificacion($conexion, $usuario_id, $titulo_notif, $msj_notif, $tipo_notif, $icono_notif);

        // --- Registro en Bitácora de la UPU ---
        $nombre_aprobador = $_SESSION['nombre'];
        if ($estado == "aprobado") {
            $accion_upu = "Pago Aprobado - Aprobado por: " . $nombre_aprobador . " | Monto: Bs. " . $monto . " | Ref: " . $referencia . ($comision > 0 ? " | Comisión: Bs. " . $comision : "");
        } else {
            $accion_upu = "Pago Rechazado - Rechazado por: " . $nombre_aprobador . " | Monto: Bs. " . $monto . " | Ref: " . $referencia . (!empty($descripcion) ? " | Motivo: " . $descripcion : "");
        }
        registrarAccion($conexion, $accion_upu, $usuario_id);
        // --------------------------------
        }

        // --- SEGURIDAD SENIOR: CONSOLIDACIÓN DE DATOS ---
        $conexion->commit();
        error_log("PERF: DB Ops took " . (microtime(true) - $t_inicio) . "s");

        $current_year = date('Y'); // Definido aquí para estar disponible en los HEREDOC de abajo

    } catch (Exception $e) {
        // En caso de error, revertimos absolutamente todo para mantener integridad
        $conexion->rollback();
        error_log("FALLO CRÍTICO EN APROBACIÓN: " . $e->getMessage());
        
        $_SESSION["mensaje"] = "Operación Cancelada: " . $e->getMessage();
        $_SESSION["estatus"] = "error";
        header("Location: ../vistas/aprobar_pago.php");
        exit();
    }


    // Enviar correo (usando los datos ya obtenidos)
    $_SESSION["mensaje"] = "El pago ha sido " . ($estado == "aprobado" ? "aprobado" : "rechazado") . " correctamente.";
    $_SESSION["estatus"] = "success";

    $nuevo_id_pago_string = isset($nuevo_id_pago) ? $nuevo_id_pago : $id;

    // Generar PDF con DomPDF solo si fue aprobado
    $pdfOutput = null;
    if ($estado == "aprobado") {
        try {
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'Helvetica');
            $dompdf = new Dompdf($options);

            // ELIMINADO LOGO PESADO -> USANDO INSIGNIA CSS PROFESIONAL (SDGBP)
            $logoSrc = ""; // Ya no se usa imagen externa

            $formatted_id = str_pad($nuevo_id_pago_string, 6, "0", STR_PAD_LEFT);
            $fecha_hora = date('d/m/Y', strtotime($fecha_pago));
            $desc = empty($descripcion_pago) ? 'Declaración de Movimiento Operativo' : htmlspecialchars($descripcion_pago);
            $monto_format = number_format($monto, 2, ',', '.');
            $comision_format = number_format($comision, 2, ',', '.');
            $nombre_aprobador = $_SESSION['nombre'] ?? 'Administración Central';

            // Lógica dinámica de etiquetas según Ingreso/Egreso
            if ($tipo == "Ingreso") {
                $label_origen  = "Remitente / Pagador";
                $valor_origen  = $cliente;
                $label_destino = "Receptor / Unidad Ejecutora";
                $valor_destino = $nombre;
            } else { // Egreso
                $label_origen  = "Emisor / Unidad Ejecutora";
                $valor_origen  = $nombre;
                $label_destino = "Beneficiario / Receptor";
                $valor_destino = $cliente;
            }

            $fila_comision = "";
            if ($comision > 0) {
                $fila_comision = "
                <tr>
                    <td style='padding: 12px; border-bottom: 1px solid #eee;'>Comisión por Gestión Administrativa</td>
                    <td style='padding: 12px; border-bottom: 1px solid #eee;'>Débito Directo</td>
                    <td style='padding: 12px; border-bottom: 1px solid #eee;'>-</td>
                    <td style='padding: 12px; border-bottom: 1px solid #eee; text-align: right; color: #ef4444;'>- Bs. {$comision_format}</td>
                </tr>";
            }

            $htmlPdf = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }
        body { font-family: 'Helvetica', sans-serif; margin: 0; padding: 0; color: #1e293b; background-color: #ffffff; }
        .banner { background-color: #0f172a; height: 180px; width: 100%; position: relative; color: white; overflow: hidden; }
        .banner-accent { position: absolute; top: 0; right: 0; width: 35%; height: 100%; background-color: #f97316; }
        .header-content { position: absolute; top: 50px; left: 40px; width: 60%; }
        .logo-container { position: absolute; top: 0; right: 0; width: 35%; height: 180px; text-align: center; display: table; }
        .insignia-wrapper { display: table-cell; vertical-align: middle; }
        .insignia { background-color: #ffffff; color: #0f172a; padding: 12px 20px; border-radius: 8px; font-weight: 900; font-size: 28px; display: inline-block; border-left: 6px solid #f97316; letter-spacing: 2px; }
        .insignia span { color: #f97316; }
        .invoice-title { font-size: 34px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #ffffff; margin: 0; }
        .invoice-number { font-size: 16px; color: #94a3b8; margin-top: 5px; }
        
        .main-content { padding: 40px; margin-top: -20px; }
        .grid { width: 100%; margin-bottom: 40px; }
        .col-50 { width: 50%; vertical-align: top; }
        
        .section-box { background-color: #f8fafc; border-radius: 12px; padding: 25px; border: 1px solid #e2e8f0; }
        .section-title { font-size: 12px; font-weight: 800; color: #f97316; text-transform: uppercase; margin-bottom: 15px; letter-spacing: 0.5px; border-bottom: 2px solid #f97316; display: inline-block; padding-bottom: 4px; }
        .info-p { margin: 6px 0; font-size: 14px; line-height: 1.4; color: #334155; }
        .info-p strong { color: #0f172a; }

        .details-table { width: 100%; border-collapse: collapse; margin-top: 30px; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; }
        .details-table th { background-color: #f1f5f9; color: #475569; padding: 15px 12px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #e2e8f0; }
        .details-table td { padding: 15px 12px; font-size: 14px; color: #334155; border-bottom: 1px solid #f1f5f9; }
        
        .total-section { margin-top: 30px; width: 100%; }
        .total-box { background-color: #0f172a; color: white; border-radius: 12px; padding: 25px; text-align: right; }
        .total-label { font-size: 14px; color: #94a3b8; text-transform: uppercase; margin-bottom: 8px; }
        .total-amount { font-size: 38px; font-weight: 800; color: #f97316; }
        
        .approver-info { margin-top: 50px; font-size: 12px; color: #64748b; border-left: 4px solid #f97316; padding-left: 15px; }
        
        .footer { position: fixed; bottom: 0; width: 100%; padding: 30px 40px; background-color: #f1f5f9; text-align: center; border-top: 1px solid #e2e8f0; }
        .footer p { margin: 4px 0; font-size: 10px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; }
    </style>
</head>
<body>
    <div class="banner">
        <div class="banner-accent"></div>
        <div class="header-content">
            <h1 class="invoice-title">Reporte de Pago</h1>
            <p class="invoice-number">Control Correlativo Oficial: #UPU-{$formatted_id}</p>
        </div>
        <div class="logo-container">
            <div class="insignia-wrapper">
                <div class="insignia">SD<span>GBP</span></div>
            </div>
        </div>
    </div>

    <div class="main-content">
        <table class="grid">
            <tr>
                <td class="col-50" style="padding-right: 15px;">
                    <div class="section-box">
                        <span class="section-title">Información de Origen</span>
                        <p class="info-p"><strong>{$label_origen}:</strong><br>{$valor_origen}</p>
                        <p class="info-p"><strong>Fecha de Reporte:</strong><br>{$fecha_hora}</p>
                    </div>
                </td>
                <td class="col-50" style="padding-left: 15px;">
                    <div class="section-box">
                        <span class="section-title">Información de Destino</span>
                        <p class="info-p"><strong>{$label_destino}:</strong><br>{$valor_destino}</p>
                        <p class="info-p"><strong>Referencia Bancaria:</strong><br>{$referencia}</p>
                    </div>
                </td>
            </tr>
        </table>

        <table class="details-table">
            <thead>
                <tr>
                    <th width="40%">Concepto de Operación</th>
                    <th width="20%">Método</th>
                    <th width="20%">Referencia</th>
                    <th width="20%" style="text-align: right;">Monto</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #eee;">{$desc} [{$tipo}]</td>
                    <td style="padding: 12px; border-bottom: 1px solid #eee;">{$metodo_pago}</td>
                    <td style="padding: 12px; border-bottom: 1px solid #eee;">{$referencia}</td>
                    <td style="padding: 12px; border-bottom: 1px solid #eee; text-align: right; font-weight: bold;">Bs. {$monto_format}</td>
                </tr>
                {$fila_comision}
            </tbody>
        </table>

        <table class="grid" style="margin-top: 30px;">
            <tr>
                <td width="55%">
                    <div class="approver-info">
                        <strong>Certificado de Validación Digital</strong><br>
                        Esta transacción ha sido verificada y liquidada electrónicamente por:<br>
                        <strong>{$nombre_aprobador}</strong> - Administración Central SDGBP por EURIPYS.
                    </div>
                </td>
                <td width="45%" style="padding-left: 20px;">
                    <div class="total-box">
                        <div class="total-label">Balance Liquidado</div>
                        <div class="total-amount">Bs. {$monto_format}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Sistema de Gestión de Bienes y Pagos (SDGBP) &copy; {$current_year}</p>
        <p>Documento generado para fines de auditoría, control operativo e inventario contable.</p>
        <p>Verificado por EURIPYS - Transparencia y Eficiencia Administrativa.</p>
    </div>
</body>
</html>
HTML;

            $dompdf->loadHtml($htmlPdf);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $pdfOutput = $dompdf->output();
            error_log("PERF: PDF Gen took " . (microtime(true) - $t_inicio) . "s (cumulative)");

        } catch (\Exception $ex) {
            error_log("Error generando PDF con DomPDF: " . $ex->getMessage());
        }
    }

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

    $motivo_rechazo_html = "";
    if ($estado == "rechazado" && $descripcion) {
        $motivo_rechazo_html = "
        <div style='background-color: #fef2f2; padding: 15px 20px; border-radius: 8px; border-left: 4px solid #ef4444; margin-bottom: 25px;'>
            <p style='margin: 0; color: #b91c1c; font-size: 14px; font-weight: 700; text-transform: uppercase; margin-bottom: 5px;'>Motivo del Rechazo:</p>
            <p style='margin: 0; color: #991b1b; font-size: 15px;'>{$descripcion}</p>
        </div>";
    }

    $aprobado_html = "";
    if ($estado == "aprobado") {
        $aprobado_html = "
        <div style='background-color: #f0fdf4; padding: 15px 20px; border-radius: 8px; border-left: 4px solid #10b981; margin-bottom: 25px;'>
            <p style='margin: 0; color: #047857; font-size: 15px; font-weight: bold;'>¡Buenas noticias! Hemos adjuntado tu Factura Electrónica Oficial (PDF) en este correo, lista para que la descargues o la declares.</p>
        </div>";
    }

    $current_year = date('Y');

    $mensaje = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f8fafc; color: #334155; -webkit-font-smoothing: antialiased;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f8fafc; padding: 40px 0;">
        <tr>
            <td align="center">
                <table width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 12px; overflow: hidden; margin: 0 auto; border: 1px solid #e2e8f0;" cellpadding="0" cellspacing="0">
                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding: 30px 20px; background-color: #0f172a; border-bottom: 4px solid {$color_banner};">
                            <h1 style="color: #ffffff; font-size: 24px; font-weight: 700; margin: 0; letter-spacing: -0.5px;">Estado de Pago Notificado</h1>
                            <p style="color: #94a3b8; font-size: 14px; margin: 5px 0 0 0;">Sistema de Gestión de Bienes y Pagos</p>
                        </td>
                    </tr>
                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 40px 40px 30px 40px;">
                            <h2 style="color: #0f172a; font-size: 20px; font-weight: 600; margin-top: 0; margin-bottom: 20px;">Estimado/a {$nombre},</h2>
                            <p style="font-size: 16px; line-height: 1.6; color: #475569; margin-top: 0; margin-bottom: 25px;">
                                Le informamos oficialmente el estado de revisión de la siguiente transacción registrada a su nombre:
                            </p>
                            
                            <table width="100%" cellpadding="10" cellspacing="0" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 20px;">
                                <tr>
                                    <td width="35%" style="color: #64748b; font-weight: 700; border-bottom: 1px solid #e2e8f0;">Monto Transado:</td>
                                    <td style="color: #0f172a; font-weight: 800; font-size: 18px; border-bottom: 1px solid #e2e8f0;">Bs. {$monto}</td>
                                </tr>
                                <tr>
                                    <td width="35%" style="color: #64748b; font-weight: 700; border-bottom: 1px solid #e2e8f0;">Nro. Referencia:</td>
                                    <td style="color: #0f172a; font-weight: 600; border-bottom: 1px solid #e2e8f0;">{$referencia}</td>
                                </tr>
                                <tr>
                                    <td width="35%" style="color: #64748b; font-weight: 700;">Estado Final:</td>
                                    <td style="color: {$color_banner}; font-weight: 800; text-transform: uppercase;">{$titulo_banner}</td>
                                </tr>
                            </table>

                            {$motivo_rechazo_html}
                            {$aprobado_html}

                            <!-- Action Button -->
                            <div style="text-align: center; margin: 35px 0;">
                                <a href="{$login_url}" style="display: inline-block; padding: 14px 28px; background-color: #0f172a; color: #ffffff; text-decoration: none; font-size: 16px; font-weight: bold; border-radius: 8px;">Consultar mi Cuenta</a>
                            </div>
                            <p style="font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 30px; margin-bottom: 0;">
                                Si tiene alguna duda o requiere mayor información técnica respecto al estado de esta operación, puede responder directamente a este correo para contactar al servicio de soporte de <strong>SDGBP</strong>.
                            </p>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f1f5f9; padding: 20px 40px; text-align: center; border-top: 1px solid #e2e8f0;">
                            <p style="margin: 0 0 10px 0; font-size: 12px; color: #64748b; font-weight: 600;">
                                &copy; {$current_year} SDGBP. Todos los derechos reservados.
                            </p>
                            <p style="margin: 0; font-size: 11px; color: #94a3b8;">
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
HTML;


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

        if ($pdfOutput !== null) {
            $mail->addStringAttachment($pdfOutput, 'Ref_' . $referencia . '_Factura_Contable.pdf', 'base64', 'application/pdf');
        }

        $mail->send();
        error_log("PERF: Email Send took " . (microtime(true) - $t_inicio) . "s (cumulative)");
    } catch (\PHPMailer\PHPMailer\Exception $e) { 
        error_log("Error al enviar correo (PHPMailer): {$mail->ErrorInfo}");
        $_SESSION["mensaje"] .= " Sin embargo, hubo un error al enviar el correo: " . $mail->ErrorInfo;
        $_SESSION["estatus"] = "warning";
    }

    // Registrar la acción en la bitácora del administrador
    if (isset($_SESSION['id'])) {
        $accion_label = ($estado == "aprobado" ? "Aprobar" : "Rechazar");
        $accion_bitacora = $accion_label . ' - Cliente: ' . $nombre_cliente . ' | Monto: Bs. ' . $monto . ' | Ref: ' . $referencia . (!empty($descripcion) ? ' | Motivo: ' . $descripcion : '');
        registrarAccion($conexion, $accion_bitacora, $_SESSION['id']);
    }

    $conexion->close();

    header("Location: ../vistas/aprobar_pago.php");
    exit();
}
?>