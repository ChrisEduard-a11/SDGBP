<?php
session_start();
include('../conexion.php');
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST" || (isset($_GET['reenviar']) && isset($_SESSION['usuario_desbloqueo']))) {
    if (isset($_GET['reenviar'])) {
        $usuario = $_SESSION['usuario_desbloqueo'];
    } else {
        $usuario = $_POST['usuario'];
    }

    // Verificar si el usuario existe en la base de datos
    $sql = "SELECT * FROM usuario WHERE usuario = '$usuario'";
    $result = mysqli_query($conexion, $sql);
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

    if ($row) {
        if ($row['intentos'] == 3) {
            $correo = $row['correo'];
            
            // Generar un código de verificación de 6 dígitos
            $codigo = rand(100000, 999999);
            $_SESSION['codigo_2fa_desbloqueo'] = $codigo;
            $_SESSION['usuario_desbloqueo'] = $usuario;
            $_SESSION['correo_desbloqueo'] = $correo;

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
                        <td align='center' style='padding: 30px 20px; background-color: #0f172a; border-bottom: 4px solid #f18000;'>
                            <h1 style='color: #ffffff; font-size: 24px; font-weight: 700; margin: 0; letter-spacing: -0.5px;'>Autorización 2FA</h1>
                            <p style='color: #94a3b8; font-size: 14px; margin: 5px 0 0 0;'>Sistema de Gestión de Bienes y Pagos</p>
                        </td>
                    </tr>
                    <!-- Body Content -->
                    <tr>
                        <td style='padding: 40px 40px 30px 40px;'>
                            <h2 style='color: #0f172a; font-size: 20px; font-weight: 600; margin-top: 0; margin-bottom: 20px;'>Estimado/a {$row['nombre']},</h2>
                            <p style='font-size: 16px; line-height: 1.6; color: #475569; margin-top: 0; margin-bottom: 20px;'>
                                Hemos recibido una solicitud para desbloquear su cuenta administrativa protegida. Para continuar con el proceso de desbloqueo, por favor ingrese el siguiente código de autorización:
                            </p>
                            <!-- Code Block -->
                            <div style='text-align: center; margin: 30px 0;'>
                                <span style='display: inline-block; font-size: 36px; font-weight: 800; letter-spacing: 10px; color: #0f172a; background: #f8fafc; padding: 15px 30px; border-radius: 8px; border: 2px dashed #cbd5e1;'>
                                    {$codigo}
                                </span>
                            </div>
                            <p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 30px; margin-bottom: 0;'>
                                Este código es confidencial y válido para una sola autorización. Si no realizó esta acción, ignore el presente correo.
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

            $mail = new PHPMailer(true);
            try {
                // Configuración del servidor SMTP
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'soporte.sdgbp2024@gmail.com'; // Tu correo de Gmail
                $mail->Password = 'yfav yolv uuji pwhq'; // Tu contraseña de aplicación de Gmail
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                // Configuración del correo
                $mail->setFrom('soporte.sdgbp2024@gmail.com', 'Seguridad SDGBP');
                $mail->addAddress($correo);
                $mail->isHTML(true);
                $mail->Subject = 'Autorización de Desbloqueo (2FA)';
                $mail->Body = $mensaje;

                $mail->send();
                $_SESSION['estatus'] = 'success';
                $_SESSION['mensaje'] = 'Código de verificación enviado. Por favor, revise su bandeja de entrada.';
                header("Location: ../vistas/confirmar_2fa_desbloqueo.php");
                exit();
            } catch (Exception $e) {
                $_SESSION['estatus'] = 'error';
                $_SESSION['mensaje'] = "No se pudo enviar el correo de verificación. Error: {$mail->ErrorInfo}";
                header("Location: ../vistas/solicitar_desbloqueo.php");
                exit();
            }
        } else {
            $_SESSION['estatus'] = 'info';
            $_SESSION['mensaje'] = 'El usuario no está bloqueado.';
            header("Location: ../vistas/login.php");
            exit();
        }
    } else {
        $_SESSION['estatus'] = 'error';
        $_SESSION['mensaje'] = 'Usuario no encontrado.';
    }

    header("Location: ../vistas/solicitar_desbloqueo.php");
    exit();
}
?>