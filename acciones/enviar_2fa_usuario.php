<?php
session_start();
include('../conexion.php');
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo_ingresado = $_POST['correo'];

    if (empty($_SESSION['temp_recu_cedula']) || empty($_SESSION['temp_recu_correo_real'])) {
        header("Location: ../vistas/recuperar_usuario.php");
        exit();
    }

    if ($correo_ingresado === $_SESSION['temp_recu_correo_real']) {
        // Generar código 2FA
        $codigo = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $cedula = $_SESSION['temp_recu_cedula'];

        // Guardar código en la DB
        $sql = "UPDATE usuario SET codigo_verificacion = ? WHERE cedula = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ss", $codigo, $cedula);
        
        if ($stmt->execute()) {
            // Enviar correo con el código
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
                $mail->addAddress($correo_ingresado);
                $mail->isHTML(true);
                $mail->CharSet = 'UTF-8';
                $mail->Subject = 'Verificación 2FA - Recuperación de Usuario';

                $mail->Body = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #eee; padding: 20px; border-radius: 10px;'>
                        <h2 style='color: #0d6efd; text-align: center;'>Segundo Factor de Autenticación</h2>
                        <p>Hola <strong>" . $_SESSION['temp_recu_nombre'] . "</strong>,</p>
                        <p>Hemos verificado tu identidad básica. Para finalizar la recuperación de tu nombre de usuario, utiliza el siguiente código de seguridad:</p>
                        <div style='text-align: center; margin: 30px 0;'>
                            <span style='font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #212529; background: #f8f9fa; padding: 10px 20px; border-radius: 5px; border: 1px dashed #dee2e6;'>$codigo</span>
                        </div>
                        <hr style='border: 0; border-top: 1px solid #eee;'>
                        <p style='text-align: center; color: #adb5bd; font-size: 11px;'>&copy; " . date('Y') . " SDGBP - Sistema de Gestión de Bienes y Pagos</p>
                    </div>
                ";

                $mail->send();
                header("Location: ../vistas/confirmar_2fa_usuario_recu.php");
                exit();

            } catch (Exception $e) {
                $_SESSION["estatus"] = "error";
                $_SESSION["mensaje"] = "No se pudo enviar el código. Error: {$mail->ErrorInfo}";
                header("Location: ../vistas/verificar_email_usuario.php");
                exit();
            }
        }
    } else {
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "El correo electrónico no coincide con el registrado para esta cédula.";
        header("Location: ../vistas/verificar_email_usuario.php");
        exit();
    }
}
