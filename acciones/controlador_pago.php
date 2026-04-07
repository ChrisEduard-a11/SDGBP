<?php
session_start();
include('../conexion.php'); // Conexión a la base de datos
include_once('../models/bitacora.php'); // Asegúrate de incluir el archivo donde está registrarAccion
include_once('../models/notificaciones.php'); // Sistema de notificaciones
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function respond($status, $message, $redirect) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        if ($status === 'success') {
            $_SESSION["mensaje"] = $message;
            $_SESSION["estatus"] = $status;
        }
        header('Content-Type: application/json');
        echo json_encode(["status" => $status, "message" => $message, "redirect" => $redirect]);
        exit();
    }
    $_SESSION["mensaje"] = $message;
    $_SESSION["estatus"] = $status;
    header("Location: " . $redirect);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar token de idempotencia
    $token = $_POST['idempotency_token'] ?? '';
    
    if (empty($token) || !isset($_SESSION['form_tokens'][$token])) {
        // Redirigir de manera *SILENCIOSA* (sin tocar $_SESSION["mensaje"])
        // Para evitar que un doble clic rápido sobreescriba el mensaje de "éxito" del primer clic.
        header("Location: ../vistas/ver_pagos.php");
        exit();
    }
    
    // Eliminar el token de la sesión para evitar re-procesamiento
    unset($_SESSION['form_tokens'][$token]);

    // Validar campos obligatorios
    $usuario_id = $_POST["usuario_id"]; // ID del usuario relacionado
    $nombre_usuario = $_SESSION["nombre"]; // Nombre del usuario desde la sesión
    $monto_raw = $_POST["monto"] ?? "0";
    // Inteligente: Solo quitar puntos si hay comas (formato 1.234,56)
    if (strpos($monto_raw, ',') !== false) {
        $monto_clean = str_replace('.', '', $monto_raw);
        $monto_clean = str_replace(',', '.', $monto_clean);
    } else {
        $monto_clean = $monto_raw;
    }
    $monto = number_format((float)$monto_clean, 2, '.', '');
    $metodo_pago = mysqli_real_escape_string($conexion, $_POST["metodo_pago"]);
    $descripcion = mysqli_real_escape_string($conexion, $_POST["descripcion"] ?? null);
    $referencia = mysqli_real_escape_string($conexion, $_POST["referencia"]);
    $fecha_pago = mysqli_real_escape_string($conexion, $_POST["fecha_pago"]);
    $cliente_id = mysqli_real_escape_string($conexion, $_POST["cliente"]); // ID del cliente seleccionado

    // Verificar si el cliente está relacionado con el usuario actual
    $sql_cliente_usuario = "SELECT * FROM usuario_pagos WHERE usuario_id = ? AND cliente_id = ?";
    $stmt_cliente_usuario = $conexion->prepare($sql_cliente_usuario);
    $stmt_cliente_usuario->bind_param("ii", $usuario_id, $cliente_id);
    $stmt_cliente_usuario->execute();
    $result_cliente_usuario = $stmt_cliente_usuario->get_result();

    if ($result_cliente_usuario->num_rows === 0) {
        respond("warning", "Error: El cliente seleccionado no está asociado a tu cuenta.", "../vistas/registro_pagos.php");
    }

    // Verificar si la referencia ya existe en la base de datos
    /*if (is_numeric($referencia)) {
        // Si la referencia es numérica, verificar si ya existe en la base de datos
        $sql_verificar = "SELECT id FROM pagos WHERE referencia = ?";
        $stmt_verificar = $conexion->prepare($sql_verificar);
        $stmt_verificar->bind_param("s", $referencia);
        $stmt_verificar->execute();
        $result_verificar = $stmt_verificar->get_result();

        if ($result_verificar->num_rows > 0) {
            respond("warning", "Error: La referencia numérica ya está registrada en el sistema.", "../vistas/registro_pagos.php");
        }
    }*/

    // Iniciar una transacción
    mysqli_begin_transaction($conexion);

    try {
        // Obtener el nombre del cliente desde la tabla `cliente`
        $sql_nombre_cliente = "SELECT nombre FROM cliente WHERE id_cliente = ?";
        $stmt_nombre_cliente = $conexion->prepare($sql_nombre_cliente);
        $stmt_nombre_cliente->bind_param("i", $cliente_id);
        $stmt_nombre_cliente->execute();
        $result_nombre_cliente = $stmt_nombre_cliente->get_result();

        if ($result_nombre_cliente->num_rows > 0) {
            $nombre_cliente = $result_nombre_cliente->fetch_assoc()['nombre'];
        } else {
            throw new Exception("Error: No se encontró el cliente seleccionado.");
        }

        // Manejo de la subida del comprobante
        $ruta_comprobante = null;
        if (isset($_FILES['comprobante']) && $_FILES['comprobante']['error'] === UPLOAD_ERR_OK) {
            $nombre_archivo = $_FILES['comprobante']['name'];
            $tipo_archivo = $_FILES['comprobante']['type'];
            $extension = pathinfo($nombre_archivo, PATHINFO_EXTENSION);
            $permitidos = ['jpg', 'jpeg', 'png', 'pdf'];

            if (in_array(strtolower($extension), $permitidos)) {
                $nuevo_nombre = "ingreso_" . time() . "_" . uniqid() . "." . $extension;
                $ruta_destino = "../uploads/comprobantes/" . $nuevo_nombre;
                
                if (move_uploaded_file($_FILES['comprobante']['tmp_name'], $ruta_destino)) {
                    $ruta_comprobante = $nuevo_nombre; // Guardamos solo el nombre para ahorrar espacio
                }
            }
        }

        // Insertar el registro en la tabla `pagos` con el tipo "ingreso"
        $sql_pago = "INSERT INTO pagos (nombre_cliente, monto, metodo_pago, descripcion, referencia, fecha_pago, cliente, estado, tipo, comprobante_archivo)
                     VALUES (?, ?, ?, ?, ?, ?, ?, 'pendiente', 'Ingreso', ?)";
        $stmt_pago = $conexion->prepare($sql_pago);
        $stmt_pago->bind_param("ssssssss", $nombre_usuario, $monto, $metodo_pago, $descripcion, $referencia, $fecha_pago, $nombre_cliente, $ruta_comprobante);
        if (!$stmt_pago->execute()) {
            throw new Exception("Error al registrar el pago: " . $stmt_pago->error);
        }

        // Obtener el ID del pago recién insertado
        $pago_id = $conexion->insert_id;

        // Insertar la relación en la tabla `usuario_pagos`
        $sql_relacion = "INSERT INTO usuario_pagos (usuario_id, pago_id, cliente_id) VALUES (?, ?, ?)";
        $stmt_relacion = $conexion->prepare($sql_relacion);
        $stmt_relacion->bind_param("iii", $usuario_id, $pago_id, $cliente_id);
        if (!$stmt_relacion->execute()) {
            throw new Exception("Error al registrar la relación usuario-pago-cliente: " . $stmt_relacion->error);
        }

        // ----------- ENVÍO DE CORREO A USUARIOS DE TIPO "cont" -----------
        $sql_cont = "SELECT correo FROM usuario WHERE tipos = 'cont'";
        $result_cont = $conexion->query($sql_cont);

        if ($result_cont && $result_cont->num_rows > 0) {
            $asunto = "Nuevo pago registrado";
            
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
                        <td align='center' style='padding: 30px 20px; background-color: #0f172a; border-bottom: 4px solid #3b82f6;'>
                            <h1 style='color: #ffffff; font-size: 24px; font-weight: 700; margin: 0; letter-spacing: -0.5px;'>Nuevo Ingreso Pendiente</h1>
                            <p style='color: #94a3b8; font-size: 14px; margin: 5px 0 0 0;'>Sistema de Gestión de Bienes y Pagos</p>
                        </td>
                    </tr>
                    <!-- Body Content -->
                    <tr>
                        <td style='padding: 40px 40px 30px 40px;'>
                            <h2 style='color: #0f172a; font-size: 20px; font-weight: 600; margin-top: 0; margin-bottom: 20px;'>Notificación a Administrativos/Contables</h2>
                            <p style='font-size: 16px; line-height: 1.6; color: #475569; margin-top: 0; margin-bottom: 25px;'>
                                El sistema ha captado el registro de un nuevo <strong>ingreso</strong> financiero cargado por un usuario corriente, el cual requiere verificación por parte del departamento contable o administrativo.
                            </p>
                            
                            <!-- Payment Details Box -->
                            <div style='background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin-bottom: 30px;'>
                                <p style='margin: 0 0 10px 0; color: #1e293b; font-size: 16px; border-bottom: 1px solid #cbd5e1; padding-bottom: 8px; font-weight: 700;'>Detalles del Pago:</p>
                                
                                <p style='margin: 0 0 8px 0; font-size: 15px;'><span style='color: #64748b; font-weight: 600;'>Usuario Responsable:</span> <span style='color: #0f172a; font-weight: 600;'>{$nombre_usuario}</span></p>
                                <p style='margin: 0 0 8px 0; font-size: 15px;'><span style='color: #64748b; font-weight: 600;'>Cliente/Operador:</span> <span style='color: #0f172a;'>{$nombre_cliente}</span></p>
                                <p style='margin: 0 0 8px 0; font-size: 15px;'><span style='color: #64748b; font-weight: 600;'>Monto Registrado:</span> <strong style='color: #3b82f6;'>Bs. {$monto}</strong></p>
                                <p style='margin: 0 0 8px 0; font-size: 15px;'><span style='color: #64748b; font-weight: 600;'>Método:</span> <span style='color: #0f172a;'>{$metodo_pago}</span></p>
                                <p style='margin: 0 0 8px 0; font-size: 15px;'><span style='color: #64748b; font-weight: 600;'>Número Referencia:</span> <span style='color: #0f172a;'>{$referencia}</span></p>
                                <p style='margin: 0 0 8px 0; font-size: 15px;'><span style='color: #64748b; font-weight: 600;'>Fecha Ejecutado:</span> <span style='color: #0f172a;'>{$fecha_pago}</span></p>
                                <p style='margin: 0 0 8px 0; font-size: 15px;'><span style='color: #64748b; font-weight: 600;'>Estado Actual:</span> <span style='color: #eab308; font-weight: 700;'>PENDIENTE POR REVISIÓN</span></p>
                                <p style='margin: 0; font-size: 15px;'><span style='color: #64748b; font-weight: 600;'>Descripción Adjunta:</span> <i style='color: #475569;'>{$descripcion}</i></p>
                            </div>

                            <!-- Action Button -->
                            <div style='text-align: center;'>
                                <a href='{$login_url}' style='display: inline-block; padding: 14px 28px; background-color: #0f172a; color: #ffffff; text-decoration: none; font-size: 16px; font-weight: bold; border-radius: 8px;'>Entrar al Sistema y Aprobar</a>
                            </div>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style='background-color: #f1f5f9; padding: 20px 40px; text-align: center; border-top: 1px solid #e2e8f0;'>
                            <p style='margin: 0 0 10px 0; font-size: 12px; color: #64748b; font-weight: 600;'>
                                &copy; " . date('Y') . " SDGBP. Todos los derechos reservados.
                            </p>
                            <p style='margin: 0; font-size: 11px; color: #94a3b8;'>
                                Aviso automatizado a buzón contable general, no responder a esta emisión.
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

            while ($row = $result_cont->fetch_assoc()) {
                $correo_destino = $row['correo'];
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com'; // Cambia esto por tu servidor SMTP
                    $mail->SMTPAuth = true;
                    $mail->Username = 'soporte.sdgbp2024@gmail.com'; // Cambia esto por tu usuario SMTP
                    $mail->Password = 'ktwf cyvz rmyh lqfy'; // Cambia esto por tu contraseña SMTP
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;

                    $mail->setFrom('soporte.sdgbp2024@gmail.com', 'Sistema de Pagos');
                    $mail->addAddress($correo_destino);

                    $mail->isHTML(true);
                    $mail->Subject = $asunto;
                    $mail->Body    = $mensaje;

                    $mail->send();
                } catch (Exception $e) {
                    // Puedes registrar el error si lo deseas, pero no interrumpas el flujo
                }
            }
        }
        // ----------- FIN ENVÍO DE CORREO -----------

        // Confirmar la transacción
        mysqli_commit($conexion);

        $final_status = "success";
        $final_message = "Ingreso registrado correctamente.";
        
        // --- NOTIFICACIÓN PARA ADMINISTRADORES Y CONTABLES ---
        // Se crea una sola notificación para el rol 'staff' (admins y conts)
        $titulo_notif = "Nuevo Pago Pendiente";
        $msj_notif = "La UPU {$nombre_usuario} ha registrado un pago de Bs. {$monto} (Ref: {$referencia}) que requiere revisión.";
        crearNotificacion($conexion, null, $titulo_notif, $msj_notif, 'warning', 'fas fa-sack-dollar', $pago_id, 'staff');
        // -----------------------------------------------------
        
        // Registrar en bitácora
        if (isset($_SESSION['id'])) {
            registrarAccion($conexion, 'Registrar Ingreso', $_SESSION['id']);
        }
    } catch (Exception $e) {
        mysqli_rollback($conexion);
        $final_status = "error";
        $final_message = "Error al registrar el pago: " . $e->getMessage();
    } finally {
        // Cerrar los statements
        if (isset($stmt_cliente_usuario)) $stmt_cliente_usuario->close();
        if (isset($stmt_nombre_cliente)) $stmt_nombre_cliente->close();
        if (isset($stmt_pago)) $stmt_pago->close();
        if (isset($stmt_relacion)) $stmt_relacion->close();
    }

    // Cerrar la conexión
    mysqli_close($conexion);

    respond($final_status, $final_message, "../vistas/ver_pagos.php");
}
?>