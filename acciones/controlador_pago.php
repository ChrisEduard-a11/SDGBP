<?php
session_start();
include('../conexion.php'); // Conexión a la base de datos
include_once('../models/bitacora.php'); // Asegúrate de incluir el archivo donde está registrarAccion
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar campos obligatorios
    $usuario_id = $_POST["usuario_id"]; // ID del usuario relacionado
    $nombre_usuario = $_SESSION["nombre"]; // Nombre del usuario desde la sesión
    $monto = mysqli_real_escape_string($conexion, $_POST["monto"]);
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
        $_SESSION["mensaje"] = "Error: El cliente seleccionado no está asociado a tu cuenta.";
        $_SESSION["estatus"] = "warning";
        header("Location: ../vistas/registro_pagos.php");
        exit();
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
            $_SESSION["mensaje"] = "Error: La referencia numérica ya está registrada en el sistema.";
            $_SESSION["estatus"] = "warning";
            header("Location: ../vistas/registro_pagos.php");
            exit();
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
            $mensaje = "
            <html>
            <head>
                <title>Nuevo Pago Registrado</title>
            </head>
            <body style='font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4;'>
                <!-- Navbar -->
                <div style='background-color: #f18000; padding: 10px; text-align: center; color: white;'>
                    <h1 style='margin: 0;'>Sistema de Gestión de Pagos</h1>
                </div>

                <!-- Contenido principal -->
                <div style='padding: 20px; background-color: white; margin: 20px auto; max-width: 600px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);'>
                    <h2 style='color: #f18000;'>¡Se ha registrado un nuevo pago!</h2>
                    <p>Detalles del pago:</p>
                    <ul style='font-size: 16px;'>
                        <li><strong>Usuario que registró:</strong> {$nombre_usuario}</li>
                        <li><strong>Cliente/Proveedor:</strong> {$nombre_cliente}</li>
                        <li><strong>Monto:</strong> {$monto}</li>
                        <li><strong>Método de pago:</strong> {$metodo_pago}</li>
                        <li><strong>Descripción:</strong> {$descripcion}</li>
                        <li><strong>Referencia:</strong> {$referencia}</li>
                        <li><strong>Fecha de pago:</strong> {$fecha_pago}</li>
                        <li><strong>Estado:</strong> pendiente</li>
                        <li><strong>Tipo:</strong> Ingreso</li>
                    </ul>
                    <p style='color:rgb(255, 255, 255);'>Por favor, revise el sistema para más detalles.</p>
                </div>

                <!-- Footer -->
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

        $_SESSION["mensaje"] = "Ingreso registrado correctamente.";
        $_SESSION["estatus"] = "success";
        // Registrar en bitácora
        if (isset($_SESSION['id'])) {
            registrarAccion($conexion, 'Registrar Ingreso', $_SESSION['id']);
        }
    } catch (Exception $e) {
        mysqli_rollback($conexion);
        $_SESSION["mensaje"] = "Error al registrar el pago: " . $e->getMessage();
        $_SESSION["estatus"] = "error";
    } finally {
        // Cerrar los statements
        if (isset($stmt_cliente_usuario)) $stmt_cliente_usuario->close();
        if (isset($stmt_nombre_cliente)) $stmt_nombre_cliente->close();
        if (isset($stmt_pago)) $stmt_pago->close();
        if (isset($stmt_relacion)) $stmt_relacion->close();
    }

    // Cerrar la conexión
    mysqli_close($conexion);

    header("Location: ../vistas/ver_pagos.php");
    exit();
}
?>