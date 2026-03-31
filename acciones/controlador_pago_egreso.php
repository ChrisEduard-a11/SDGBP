<?php
session_start();
include('../conexion.php'); // Conexión a la base de datos
include_once('../models/bitacora.php'); // Bitácora
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
        // Redirección de manera *SILENCIOSA* para evitar sobreescribir un "success" en caso de doble clic
        $redirect = ($_SESSION['tipo'] === 'admin' || $_SESSION['tipo'] === 'cont') ? "../vistas/ver_pagos_cont.php" : "../vistas/ver_pagos.php";
        header("Location: " . $redirect);
        exit();
    }
    
    // Eliminar el token de la sesión para evitar re-procesamiento
    unset($_SESSION['form_tokens'][$token]);

    // Validar campos obligatorios
    $usuario_id = $_POST["usuario_id"];
    $nombre_usuario = $_SESSION["nombre"];
    $monto_raw = $_POST["monto"];
    // Inteligente: Solo quitar puntos si hay comas (formato 1.234,56)
    if (strpos($monto_raw, ',') !== false) {
        $monto_clean = str_replace('.', '', $monto_raw);
        $monto_clean = str_replace(',', '.', $monto_clean);
    } else {
        $monto_clean = $monto_raw;
    }
    $monto = number_format((float)$monto_clean, 2, '.', '');
    $descripcion = mysqli_real_escape_string($conexion, $_POST["descripcion"] ?? null);
    $referencia = mysqli_real_escape_string($conexion, $_POST["referencia"]);
    $fecha_pago = mysqli_real_escape_string($conexion, $_POST["fecha_pago"]);
    $metodo_pago = mysqli_real_escape_string($conexion, $_POST["metodo_pago"]);
    
    // CAPTURA DEL VALOR DEL CLIENTE
    $cliente_input = $_POST["cliente"];
    $es_no_aplica = ($cliente_input === "No Aplica");

    // Verificar si el cliente está relacionado (SOLO SI NO ES "No Aplica")
    if (!$es_no_aplica) {
        $cliente_id = mysqli_real_escape_string($conexion, $cliente_input);
        $sql_cliente_usuario = "SELECT * FROM usuario_pagos WHERE usuario_id = ? AND cliente_id = ?";
        $stmt_cliente_usuario = $conexion->prepare($sql_cliente_usuario);
        $stmt_cliente_usuario->bind_param("ii", $usuario_id, $cliente_id);
        $stmt_cliente_usuario->execute();
        $result_cliente_usuario = $stmt_cliente_usuario->get_result();

        if ($result_cliente_usuario->num_rows === 0) {
            respond("warning", "Error: El cliente seleccionado no está asociado a tu cuenta.", "../vistas/registro_pagos_egresos.php");
        }
    } else {
        $cliente_id = null; // Para la tabla usuario_pagos guardaremos NULL
    }

    // Verificar el saldo del usuario (Lógica original intacta)
    $sql_saldo = "SELECT saldo FROM usuario WHERE id_usuario = ?";
    $stmt_saldo = $conexion->prepare($sql_saldo);
    $stmt_saldo->bind_param("i", $usuario_id);
    $stmt_saldo->execute();
    $result_saldo = $stmt_saldo->get_result();

    if ($result_saldo->num_rows > 0) {
        $saldo_actual = $result_saldo->fetch_assoc()['saldo'];
        if ($saldo_actual < $monto) {
            respond("warning", "Error: El saldo del usuario es insuficiente para registrar este egreso.", "../vistas/registro_pagos_egresos.php");
        }
    } else {
        respond("error", "Error: No se pudo obtener el saldo del usuario.", "../vistas/registro_pagos_egresos.php");
    }

    // Iniciar una transacción
    mysqli_begin_transaction($conexion);

    try {
        // OBTENER NOMBRE DEL CLIENTE
        if ($es_no_aplica) {
            // Si es Admin/Cont, el nombre que se guarda es "No Aplica"
            $nombre_cliente = "No Aplica";
        } else {
            // Obtener el nombre del cliente desde la tabla `cliente` para usuarios normales
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
        }

        // Manejo de la subida del comprobante
        $ruta_comprobante = null;
        if (isset($_FILES['comprobante']) && $_FILES['comprobante']['error'] === UPLOAD_ERR_OK) {
            $nombre_archivo = $_FILES['comprobante']['name'];
            $extension = pathinfo($nombre_archivo, PATHINFO_EXTENSION);
            $permitidos = ['jpg', 'jpeg', 'png', 'pdf'];

            if (in_array(strtolower($extension), $permitidos)) {
                $nuevo_nombre = "egreso_" . time() . "_" . uniqid() . "." . $extension;
                $ruta_destino = "../uploads/comprobantes/" . $nuevo_nombre;
                
                if (move_uploaded_file($_FILES['comprobante']['tmp_name'], $ruta_destino)) {
                    $ruta_comprobante = $nuevo_nombre;
                }
            }
        }

        // Determinar el estado inicial y saldo resultante
        $estado_inicial = 'pendiente';
        $saldo_resultante = 0;
        $usuario_aprobador = null;

        if ($_SESSION['tipo'] === 'admin' || $_SESSION['tipo'] === 'cont') {
            $estado_inicial = 'aprobado';
            // Cálculo con precisión arbitraria (bcmath) para 0 margen de error
            $saldo_resultante = bcsub($saldo_actual, $monto, 2);
            $usuario_aprobador = $_SESSION['nombre'];
        }

        // Insertar el registro en la tabla `pagos`
        $sql_pago = "INSERT INTO pagos (nombre_cliente, monto, descripcion, referencia, fecha_pago, estado, tipo, cliente, comprobante_archivo, saldo_resultante, usuario_aprobador, metodo_pago)
                     VALUES (?, ?, ?, ?, ?, ?, 'Egreso', ?, ?, ?, ?, ?)";
        $stmt_pago = $conexion->prepare($sql_pago);
        $stmt_pago->bind_param("ssssssssdss", $nombre_usuario, $monto, $descripcion, $referencia, $fecha_pago, $estado_inicial, $nombre_cliente, $ruta_comprobante, $saldo_resultante, $usuario_aprobador, $metodo_pago);
        if (!$stmt_pago->execute()) {
            throw new Exception("Error al registrar el egreso: " . $stmt_pago->error);
        }

        // Obtener el ID del pago recién insertado
        $pago_id = $conexion->insert_id;

        // Si se auto-aprobó, actualizar el saldo del usuario inmediatamente
        if ($estado_inicial === 'aprobado') {
            $sql_update_saldo = "UPDATE usuario SET saldo = ? WHERE id_usuario = ?";
            $stmt_update_saldo = $conexion->prepare($sql_update_saldo);
            $stmt_update_saldo->bind_param("di", $saldo_resultante, $usuario_id);
            if (!$stmt_update_saldo->execute()) {
                throw new Exception("Error al actualizar el saldo del usuario: " . $stmt_update_saldo->error);
            }
            $stmt_update_saldo->close();
        }

        // Insertar la relación en la tabla `usuario_pagos`
        $sql_relacion = "INSERT INTO usuario_pagos (usuario_id, pago_id, cliente_id) VALUES (?, ?, ?)";
        $stmt_relacion = $conexion->prepare($sql_relacion);
        $stmt_relacion->bind_param("iii", $usuario_id, $pago_id, $cliente_id);
        if (!$stmt_relacion->execute()) {
            throw new Exception("Error al registrar la relación usuario-pago-cliente: " . $stmt_relacion->error);
        }

        // ----------- ENVÍO DE CORREO A USUARIOS DE TIPO "cont" (Toda tu lógica original) -----------
        // SOLO si el usuario que registra NO es admin ni cont
        if ($_SESSION['tipo'] !== 'admin' && $_SESSION['tipo'] !== 'cont') {
            $sql_cont = "SELECT correo FROM usuario WHERE tipos = 'cont'";
            $result_cont = $conexion->query($sql_cont);

            if ($result_cont && $result_cont->num_rows > 0) {
                $asunto = "Nuevo egreso registrado";
                
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
                            <td align='center' style='padding: 30px 20px; background-color: #0f172a; border-bottom: 4px solid #ef4444;'>
                                <h1 style='color: #ffffff; font-size: 24px; font-weight: 700; margin: 0; letter-spacing: -0.5px;'>Nuevo Egreso Contabilizado</h1>
                                <p style='color: #94a3b8; font-size: 14px; margin: 5px 0 0 0;'>Sistema de Gestión de Bienes y Pagos</p>
                            </td>
                        </tr>
                        <!-- Body Content -->
                        <tr>
                            <td style='padding: 40px 40px 30px 40px;'>
                                <h2 style='color: #0f172a; font-size: 20px; font-weight: 600; margin-top: 0; margin-bottom: 20px;'>Notificación a Administrativos/Contables</h2>
                                <p style='font-size: 16px; line-height: 1.6; color: #475569; margin-top: 0; margin-bottom: 25px;'>
                                    Se ha registrado la declaración de un nuevo <strong>egreso</strong> logístico en el sistema. Todo egreso reportado deducirá fondos o aplicará en libros tras revisión.
                                </p>
                                
                                <!-- Payment Details Box -->
                                <div style='background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin-bottom: 30px;'>
                                    <p style='margin: 0 0 10px 0; color: #1e293b; font-size: 16px; border-bottom: 1px solid #cbd5e1; padding-bottom: 8px; font-weight: 700;'>Detalles del Egreso:</p>
                                    
                                    <p style='margin: 0 0 8px 0; font-size: 15px;'><span style='color: #64748b; font-weight: 600;'>Usuario Responsable:</span> <span style='color: #0f172a; font-weight: 600;'>{$nombre_usuario}</span></p>
                                    <p style='margin: 0 0 8px 0; font-size: 15px;'><span style='color: #64748b; font-weight: 600;'>Cliente/Destino:</span> <span style='color: #0f172a;'>{$nombre_cliente}</span></p>
                                    <p style='margin: 0 0 8px 0; font-size: 15px;'><span style='color: #64748b; font-weight: 600;'>Monto del Gasto:</span> <strong style='color: #ef4444;'>Bs. {$monto}</strong></p>
                                    <p style='margin: 0 0 8px 0; font-size: 15px;'><span style='color: #64748b; font-weight: 600;'>Número Referencia:</span> <span style='color: #0f172a;'>{$referencia}</span></p>
                                    <p style='margin: 0 0 8px 0; font-size: 15px;'><span style='color: #64748b; font-weight: 600;'>Fecha Ejecutado:</span> <span style='color: #0f172a;'>{$fecha_pago}</span></p>
                                    <p style='margin: 0 0 8px 0; font-size: 15px;'><span style='color: #64748b; font-weight: 600;'>Estado Actual:</span> <span style='color: #0f172a; font-weight: 700; text-transform: uppercase;'>{$estado_inicial}</span></p>
                                    <p style='margin: 0; font-size: 15px;'><span style='color: #64748b; font-weight: 600;'>Descripción Adjunta:</span> <i style='color: #475569;'>{$descripcion}</i></p>
                                </div>

                                <!-- Action Button -->
                                <div style='text-align: center;'>
                                    <a href='{$login_url}' style='display: inline-block; padding: 14px 28px; background-color: #0f172a; color: #ffffff; text-decoration: none; font-size: 16px; font-weight: bold; border-radius: 8px;'>Revisar en el Sistema</a>
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
                        $mail->Host = 'smtp.gmail.com'; 
                        $mail->SMTPAuth = true;
                        $mail->Username = 'soporte.sdgbp2024@gmail.com'; 
                        $mail->Password = 'ktwf cyvz rmyh lqfy'; 
                        $mail->SMTPSecure = 'tls';
                        $mail->Port = 587;

                        $mail->setFrom('soporte.sdgbp2024@gmail.com', 'Sistema de Pagos');
                        $mail->addAddress($correo_destino);

                        $mail->isHTML(true);
                        $mail->Subject = $asunto;
                        $mail->Body    = $mensaje;

                        $mail->send();
                    } catch (Exception $e) {
                        // Registro silencioso del error de correo
                    }
                }
            }
        }
        // ----------- FIN ENVÍO DE CORREO -----------

        // Confirmar la transacción
        mysqli_commit($conexion);

        $final_status = "success";
        
        if ($_SESSION['tipo'] === 'admin' || $_SESSION['tipo'] === 'cont') {
            $final_message = "Comisión bancaria registrada correctamente.";
            $accion_bitacora = 'Registrar Comisión Bancaria';
        } else {
            $final_message = "Egreso registrado correctamente.";
            $accion_bitacora = 'Registrar Egreso';
        }
        
        if (isset($_SESSION['id'])) {
            registrarAccion($conexion, $accion_bitacora, $_SESSION['id']);
        }
    } catch (Exception $e) {
        mysqli_rollback($conexion);
        $final_status = "error";
        $final_message = "Error al registrar el egreso: " . $e->getMessage();
    } finally {
        if (isset($stmt_cliente_usuario)) $stmt_cliente_usuario->close();
        if (isset($stmt_nombre_cliente)) $stmt_nombre_cliente->close();
        if (isset($stmt_pago)) $stmt_pago->close();
        if (isset($stmt_relacion)) $stmt_relacion->close();
        if (isset($stmt_saldo)) $stmt_saldo->close();
    }

    mysqli_close($conexion);

    $redirect = ($_SESSION['tipo'] === 'admin' || $_SESSION['tipo'] === 'cont') ? "../vistas/ver_pagos_cont.php" : "../vistas/ver_pagos.php";
    respond($final_status, $final_message, $redirect);
}
?>