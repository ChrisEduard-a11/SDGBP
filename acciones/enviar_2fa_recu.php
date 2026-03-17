<?php
session_start();
require_once("../conexion.php");
require_once '../PHPMailer/src/PHPMailer.php';
require_once '../PHPMailer/src/Exception.php';
require_once '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (empty($_SESSION["usuario"]) || empty($_SESSION["correo"])) {
    header("Location: ../vistas/denegado_a.php");
    exit();
}

$usuario_nombre = $_SESSION["usuario"];
$correo_dest = $_SESSION["correo"];

// Generar código de 6 dígitos
$codigo = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

// Guardar en la base de datos
$sql = "UPDATE usuario SET codigo_verificacion = ? WHERE usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ss", $codigo, $usuario_nombre);

if ($stmt->execute()) {
    // Configurar PHPMailer
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
        $mail->addAddress($correo_dest);
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Código de Recuperación de Cuenta - 2FA';
        
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #eee; padding: 20px; border-radius: 10px;'>
                <h2 style='color: #f18000; text-align: center;'>Verificación de Seguridad</h2>
                <p>Has solicitado recuperar tu cuenta en el sistema <strong>SDGBP</strong> mediante verificación en dos pasos (2FA).</p>
                <p>Para continuar con la recuperación, utiliza el siguiente código de seguridad:</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <span style='font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #212529; background: #f8f9fa; padding: 10px 20px; border-radius: 5px; border: 1px dashed #dee2e6;'>$codigo</span>
                </div>
                <p style='color: #6c757d; font-size: 13px;'>Este código es para uso personal y expira pronto. Si no solicitaste este código, por favor ignora este correo.</p>
                <hr style='border: 0; border-top: 1px solid #eee;'>
                <p style='text-align: center; color: #adb5bd; font-size: 11px;'>&copy; " . date('Y') . " SDGBP - Sistema de Gestión de Bienes y Pagos</p>
            </div>
        ";

        $mail->send();
        header("Location: ../vistas/confirmar_2fa_recu.php");
        exit();

    } catch (Exception $e) {
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "No se pudo enviar el código. Error: {$mail->ErrorInfo}";
        header("Location: ../vistas/seleccionar_meto_recu.php");
        exit();
    }
} else {
    $_SESSION["estatus"] = "error";
    $_SESSION["mensaje"] = "Error al generar el código de seguridad.";
    header("Location: ../vistas/seleccionar_meto_recu.php");
    exit();
}
