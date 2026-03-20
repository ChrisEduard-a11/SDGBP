<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Incluir los archivos de PHPMailer manualmente
require ('../PHPMailer/src/PHPMailer.php');
require ('../PHPMailer/src/Exception.php');
require  ('../PHPMailer/src/SMTP.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los datos del formulario
    $nombre = htmlspecialchars($_POST['nombre']);
    $email = htmlspecialchars($_POST['email']);
    $mensaje = htmlspecialchars($_POST['mensaje']);

    // Obtener la fecha y hora actual
    $fechaEnvio = date("d/m/Y H:i:s"); // Formato: Día/Mes/Año Hora:Minuto:Segundo

    // Validar los datos
    if (!empty($nombre) && !empty($email) && !empty($mensaje) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Crear una instancia de PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Servidor SMTP de Gmail
            $mail->SMTPAuth = true;
            $mail->Username = 'soporte.sdgbp2024@gmail.com'; // Cambia esto por tu correo de Gmail
            $mail->Password = 'otht adre wzei pdeh'; // Cambia esto por tu contraseña o contraseña de aplicación
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Configuración del correo
            $mail->setFrom('cristianarcaya2003@gmail.com', 'EURIPYS 2024, C.A.'); // Remitente
            $mail->addAddress('cristianarcaya2003@gmail.com'); // Destinatario (tu correo)
            $mail->Subject = "Nuevo mensaje de contacto de $nombre";

            // Habilitar el formato HTML
            $mail->isHTML(true);

            // Cuerpo del mensaje con navbar, contenido principal, fecha y footer
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
                            <h1 style='color: #ffffff; font-size: 24px; font-weight: 700; margin: 0; letter-spacing: -0.5px;'>Nuevo Mensaje de Contacto</h1>
                            <p style='color: #94a3b8; font-size: 14px; margin: 5px 0 0 0;'>Sistema de Gestión de Bienes y Pagos</p>
                        </td>
                    </tr>
                    <!-- Body Content -->
                    <tr>
                        <td style='padding: 40px 40px 30px 40px;'>
                            <h2 style='color: #0f172a; font-size: 20px; font-weight: 600; margin-top: 0; margin-bottom: 20px;'>Notificación Administrativa</h2>
                            <p style='font-size: 16px; line-height: 1.6; color: #475569; margin-top: 0; margin-bottom: 25px;'>
                                Has recibido un nuevo mensaje a través del formulario de contacto público. A continuación, los detalles:
                            </p>
                            
                            <table width='100%' cellpadding='10' cellspacing='0' style='background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 20px;'>
                                <tr>
                                    <td width='30%' style='color: #64748b; font-weight: 700; border-bottom: 1px solid #e2e8f0;'>Nombre:</td>
                                    <td style='color: #0f172a; font-weight: 600; border-bottom: 1px solid #e2e8f0;'>$nombre</td>
                                </tr>
                                <tr>
                                    <td width='30%' style='color: #64748b; font-weight: 700; border-bottom: 1px solid #e2e8f0;'>Correo:</td>
                                    <td style='color: #0f172a; font-weight: 600; border-bottom: 1px solid #e2e8f0;'>$email</td>
                                </tr>
                                <tr>
                                    <td width='30%' style='color: #64748b; font-weight: 700;'>Fecha:</td>
                                    <td style='color: #0f172a; font-weight: 600;'>$fechaEnvio</td>
                                </tr>
                            </table>

                            <h3 style='color: #0f172a; font-size: 16px; margin: 0 0 10px 0;'>Mensaje del Usuario:</h3>
                            <div style='background-color: #f1f5f9; padding: 20px; border-radius: 8px; border-left: 4px solid #f18000; color: #475569; font-style: italic; font-size: 15px; line-height: 1.6;'>
                                \"$mensaje\"
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
                                Este es un correo automatizado desde el portal institucional. No responda a esta dirección de envío.
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

            // Enviar el correo
            $mail->send();
            $_SESSION['estatus'] = 'success';
            $_SESSION['mensaje'] = 'Correo enviado correctamente.';
            header("Location: ../index.php");
            exit();
        } catch (Exception $e) {
            $_SESSION['estatus'] = 'error';
            $_SESSION['mensaje'] = 'Error al enviar el correo: ' . $mail->ErrorInfo;
            header("Location: ../index.php");
            exit();
        }
    } else {
        $_SESSION['estatus'] = 'error';
        $_SESSION['mensaje'] = 'Por favor, completa todos los campos correctamente.';
        header("Location: ../index.php");
        exit();
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>