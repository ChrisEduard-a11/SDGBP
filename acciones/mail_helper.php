<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/Exception.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';

/**
 * Envía el nombre de usuario recuperado al correo del usuario
 */
function enviarUsuarioCorreo($correo_dest, $usuario_nombre) {
    $mail = new PHPMailer(true);
    try {
        // Configuración del servidor (Basada en enviar_2fa_recu.php)
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'soporte.sdgbp2024@gmail.com';
        $mail->Password = 'ktwf cyvz rmyh lqfy';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        
        $mail->setFrom('soporte.sdgbp2024@gmail.com', 'SDGBP Security');
        $mail->addAddress($correo_dest);
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Recuperación de Nombre de Usuario - SDGBP';
        
        $mail->Body = "
        <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f8fafc;'>
            <div style='max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0;'>
                <div style='background-color: #0f172a; padding: 30px; border-bottom: 4px solid #f18000; text-align: center;'>
                    <h1 style='color: white; margin: 0;'>Recuperación de Usuario</h1>
                </div>
                <div style='padding: 40px;'>
                    <p style='font-size: 16px; color: #475569;'>Has solicitado recuperar tu nombre de usuario del sistema SDGBP.</p>
                    <div style='background: #f1f5f9; padding: 20px; border-radius: 8px; text-align: center; margin: 30px 0;'>
                        <p style='margin: 0; font-size: 14px; color: #64748b; text-transform: uppercase; font-weight: bold;'>Tu nombre de usuario es:</p>
                        <p style='margin: 10px 0 0 0; font-size: 24px; color: #0f172a; font-weight: 800;'>{$usuario_nombre}</p>
                    </div>
                    <p style='font-size: 14px; color: #94a3b8;'>Ya puedes usar este nombre para iniciar sesión en la plataforma.</p>
                </div>
                <div style='background-color: #f8fafc; padding: 20px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0;'>
                    &copy; " . date('Y') . " SDGBP. Todos los derechos reservados.
                </div>
            </div>
        </div>";

        return $mail->send();
    } catch (Exception $e) {
        return false;
    }
}
