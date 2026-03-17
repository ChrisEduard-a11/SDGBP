<?php
session_start();
require_once("../conexion.php");
require_once '../PHPMailer/src/PHPMailer.php';
require_once '../PHPMailer/src/Exception.php';
require_once '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nueva_clave = $_POST['clave'];
    $confirmar_clave = $_POST['clave1'];
    
    if ($nueva_clave !== $confirmar_clave) {
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "Las contraseñas no coinciden.";
        header("Location: ../vistas/nueva_clave.php");
        exit;
    }

    $id_usuario = $_SESSION['id'];
    
    // Obtener correo del usuario
    $sql_user = "SELECT correo, nombre FROM usuario WHERE id_usuario = '$id_usuario'";
    $res_user = mysqli_query($conexion, $sql_user);
    $user_data = mysqli_fetch_assoc($res_user);
    $correo_dest = $user_data['correo'];
    $nombre_dest = $user_data['nombre'];

    if (!$correo_dest) {
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "No tienes un correo registrado para recibir el código.";
        header("Location: ../vistas/nueva_clave.php");
        exit;
    }

    $clave_hash = sha1($nueva_clave);

    // OPTIMIZACIÓN: Si ya validó identidad (por 2FA de recuperación o preguntas), actualizamos directo
    if (isset($_SESSION['identidad_verificada']) && $_SESSION['identidad_verificada'] === true) {
        $sql_final = "UPDATE usuario SET clave = ?, codigo_verificacion = NULL, fecha_cambio_clave = CURRENT_DATE WHERE id_usuario = ?";
        $stmt_final = $conexion->prepare($sql_final);
        $stmt_final->bind_param("si", $clave_hash, $id_usuario);
        
        if ($stmt_final->execute()) {
            unset($_SESSION['identidad_verificada']); // Limpiar bandera
            $_SESSION["estatus"] = "success";
            $_SESSION["mensaje"] = "Tu contraseña ha sido actualizada exitosamente.";
            header("Location: ../vistas/login.php");
            exit;
        } else {
            $_SESSION["estatus"] = "error";
            $_SESSION["mensaje"] = "Error al actualizar la contraseña.";
            header("Location: ../vistas/nueva_clave.php");
            exit;
        }
    }

    // Flujo normal con código (para cambio de clave estando logueado normalmente)
    // Generar código de 6 dígitos
    $codigo = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

    // Guardar temporalmente en la base de datos
    $sql_update = "UPDATE usuario SET codigo_verificacion = '$codigo', clave = '$clave_hash', fecha_cambio_clave = CURRENT_DATE WHERE id_usuario = '$id_usuario'";
    if (mysqli_query($conexion, $sql_update)) {
        
        // Enviar Correo
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'soporte.sdgbp2024@gmail.com';
            $mail->Password = 'zqmk whnf jrlz mhpp';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->setFrom('soporte.sdgbp2024@gmail.com', 'SDGBP Security');
            $mail->addAddress($correo_dest, $nombre_dest);
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'Código de Verificación - Cambio de Contraseña';
            
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #eee; padding: 20px; border-radius: 10px;'>
                    <h2 style='color: #0d6efd; text-align: center;'>Confirmación de Seguridad</h2>
                    <p>Hola <strong>$nombre_dest</strong>,</p>
                    <p>Has solicitado cambiar tu contraseña en el sistema <strong>SDGBP</strong>. Para completar este proceso, utiliza el siguiente código de verificación:</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <span style='font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #212529; background: #f8f9fa; padding: 10px 20px; border-radius: 5px; border: 1px dashed #dee2e6;'>$codigo</span>
                    </div>
                    <p style='color: #6c757d; font-size: 13px;'>Este código es válido por tiempo limitado. Si no solicitaste este cambio, por favor ignora este correo.</p>
                    <hr style='border: 0; border-top: 1px solid #eee;'>
                    <p style='text-align: center; color: #adb5bd; font-size: 11px;'>&copy; " . date('Y') . " SDGBP - Sistema de Gestión de Bienes y Pagos</p>
                </div>
            ";

            $mail->send();
            
            // Redirigir a vista de ingreso de código
            header("Location: ../vistas/confirmar_codigo.php");
            exit;

        } catch (Exception $e) {
            $_SESSION["estatus"] = "error";
            $_SESSION["mensaje"] = "Error al enviar el correo: {$mail->ErrorInfo}";
            header("Location: ../vistas/nueva_clave.php");
            exit;
        }
    } else {
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "Error al procesar la solicitud.";
        header("Location: ../vistas/nueva_clave.php");
        exit;
    }
}
