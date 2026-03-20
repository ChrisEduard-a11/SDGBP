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
                        <td align='center' style='padding: 30px 20px; background-color: #0f172a; border-bottom: 4px solid #f18000;'>
                            <h1 style='color: #ffffff; font-size: 24px; font-weight: 700; margin: 0; letter-spacing: -0.5px;'>Segundo Factor (2FA)</h1>
                            <p style='color: #94a3b8; font-size: 14px; margin: 5px 0 0 0;'>Sistema de Gestión de Bienes y Pagos</p>
                        </td>
                    </tr>
                    <!-- Body Content -->
                    <tr>
                        <td style='padding: 40px 40px 30px 40px;'>
                            <h2 style='color: #0f172a; font-size: 20px; font-weight: 600; margin-top: 0; margin-bottom: 20px;'>Hola " . $_SESSION['temp_recu_nombre'] . ",</h2>
                            <p style='font-size: 16px; line-height: 1.6; color: #475569; margin-top: 0; margin-bottom: 20px;'>
                                Hemos verificado tu identidad básica. Para finalizar el proceso de recuperación y enviarte tu nombre de usuario, utiliza el siguiente código numérico de seguridad:
                            </p>
                            <!-- Code Block -->
                            <div style='text-align: center; margin: 30px 0;'>
                                <span style='display: inline-block; font-size: 36px; font-weight: 800; letter-spacing: 10px; color: #0f172a; background: #f8fafc; padding: 15px 30px; border-radius: 8px; border: 2px dashed #cbd5e1;'>
                                    {$codigo}
                                </span>
                            </div>
                            <p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 30px; margin-bottom: 0;'>
                                Si no solicitaste este código, por favor ignora este correo. Tu cuenta administrativa se encuentra protegida de terceros.
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
                                Este es un correo automatizado, por favor no responda a este mensaje.
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
