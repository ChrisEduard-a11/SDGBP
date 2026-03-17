<?php
session_start();
include('../conexion.php'); // Conexión a la base de datos
include_once('../models/bitacora.php'); // Bitácora
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar campos obligatorios
    $usuario_id = $_POST["usuario_id"];
    $nombre_usuario = $_SESSION["nombre"];
    $monto = mysqli_real_escape_string($conexion, $_POST["monto"]);
    $descripcion = mysqli_real_escape_string($conexion, $_POST["descripcion"] ?? null);
    $referencia = mysqli_real_escape_string($conexion, $_POST["referencia"]);
    $fecha_pago = mysqli_real_escape_string($conexion, $_POST["fecha_pago"]);
    
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
            $_SESSION["mensaje"] = "Error: El cliente seleccionado no está asociado a tu cuenta.";
            $_SESSION["estatus"] = "warning";
            header("Location: ../vistas/registro_pagos_egresos.php");
            exit();
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
            $_SESSION["mensaje"] = "Error: El saldo del usuario es insuficiente para registrar este egreso.";
            $_SESSION["estatus"] = "warning";
            header("Location: ../vistas/registro_pagos_egresos.php");
            exit();
        }
    } else {
        $_SESSION["mensaje"] = "Error: No se pudo obtener el saldo del usuario.";
        $_SESSION["estatus"] = "error";
        header("Location: ../vistas/registro_pagos_egresos.php");
        exit();
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
            $saldo_resultante = $saldo_actual - $monto;
            $usuario_aprobador = $_SESSION['nombre'];
        }

        // Insertar el registro en la tabla `pagos`
        $sql_pago = "INSERT INTO pagos (nombre_cliente, monto, descripcion, referencia, fecha_pago, estado, tipo, cliente, comprobante_archivo, saldo_resultante, usuario_aprobador)
                     VALUES (?, ?, ?, ?, ?, ?, 'Egreso', ?, ?, ?, ?)";
        $stmt_pago = $conexion->prepare($sql_pago);
        $stmt_pago->bind_param("ssssssssds", $nombre_usuario, $monto, $descripcion, $referencia, $fecha_pago, $estado_inicial, $nombre_cliente, $ruta_comprobante, $saldo_resultante, $usuario_aprobador);
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
        $sql_cont = "SELECT correo FROM usuario WHERE tipos = 'cont'";
        $result_cont = $conexion->query($sql_cont);

        if ($result_cont && $result_cont->num_rows > 0) {
            $asunto = "Nuevo egreso registrado";
            $mensaje = "
            <html>
            <head>
                <title>Nuevo Egreso Registrado</title>
            </head>
            <body style='font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4;'>
                <div style='background-color: #dc3545; padding: 10px; text-align: center; color: white;'>
                    <h1 style='margin: 0;'>Sistema de Gestión de Pagos</h1>
                </div>
                <div style='padding: 20px; background-color: white; margin: 20px auto; max-width: 600px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);'>
                    <h2 style='color: #dc3545;'>¡Se ha registrado un nuevo egreso!</h2>
                    <p>Detalles del egreso:</p>
                    <ul style='font-size: 16px;'>
                        <li><strong>Usuario que registró:</strong> {$nombre_usuario}</li>
                        <li><strong>Cliente/Proveedor:</strong> {$nombre_cliente}</li>
                        <li><strong>Monto:</strong> {$monto}</li>
                        <li><strong>Descripción:</strong> {$descripcion}</li>
                        <li><strong>Referencia:</strong> {$referencia}</li>
                        <li><strong>Fecha de egreso:</strong> {$fecha_pago}</li>
                        <li><strong>Estado:</strong> {$estado_inicial}</li>
                        <li><strong>Tipo:</strong> Egreso</li>
                    </ul>
                    <p style='color: #555;'>Por favor, revise el sistema para más detalles.</p>
                </div>
                <div style='background-color: #343a40; color: white; text-align: center; padding: 10px;'>
                    <p style='margin: 0; font-size: 0.9rem;'>Este mensaje fue enviado desde el Sistema de Gestión de Bienes y Pagos.</p>
                    <p style='margin: 0; font-size: 0.9rem;'>© 2025 Sistema de Gestión de Bienes y Pagos. Todos los derechos reservados.</p>
                </div>
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
        // ----------- FIN ENVÍO DE CORREO -----------

        // Confirmar la transacción
        mysqli_commit($conexion);

        $_SESSION["mensaje"] = "Egreso registrado correctamente.";
        $_SESSION["estatus"] = "success";
        if (isset($_SESSION['id'])) {
            registrarAccion($conexion, 'Registrar Egreso', $_SESSION['id']);
        }
    } catch (Exception $e) {
        mysqli_rollback($conexion);
        $_SESSION["mensaje"] = "Error al registrar el egreso: " . $e->getMessage();
        $_SESSION["estatus"] = "error";
    } finally {
        if (isset($stmt_cliente_usuario)) $stmt_cliente_usuario->close();
        if (isset($stmt_nombre_cliente)) $stmt_nombre_cliente->close();
        if (isset($stmt_pago)) $stmt_pago->close();
        if (isset($stmt_relacion)) $stmt_relacion->close();
        if (isset($stmt_saldo)) $stmt_saldo->close();
    }

mysqli_close($conexion);

    // Redirección dinámica según el rol
    if ($_SESSION['tipo'] === 'admin' || $_SESSION['tipo'] === 'cont') {
        header("Location: ../vistas/ver_pagos_cont.php");
    } else {
        header("Location: ../vistas/ver_pagos.php");
    }
    
    exit();
}
?>