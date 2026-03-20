<?php
session_start();
include('../conexion.php');
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $codigo_ingresado = $_POST['codigo'];

    if (isset($_SESSION['codigo_2fa_desbloqueo']) && $codigo_ingresado == $_SESSION['codigo_2fa_desbloqueo']) {
        // Validación exitosa
        $usuario = $_SESSION['usuario_desbloqueo'];
        $correo = $_SESSION['correo_desbloqueo'];

        // Obtener el nombre
        $sql = "SELECT nombre FROM usuario WHERE usuario = '$usuario'";
        $result = mysqli_query($conexion, $sql);
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        $nombre = $row ? $row['nombre'] : 'Usuario';

        // Ahora generar el token real
        $token = bin2hex(random_bytes(50));
        $sql_token = "UPDATE usuario SET token = '$token' WHERE usuario = '$usuario'";
        mysqli_query($conexion, $sql_token);

        $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $directorio = dirname($_SERVER['PHP_SELF'], 2); // Sube dos niveles para llegar a la raíz del sistema
        $link = $protocolo . $host . $directorio . "/acciones/desbloquear_usuario.php?token=$token";

        // Correo final de desbloqueo
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
                        <td align='center' style='padding: 30px 20px; background-color: #0f172a; border-bottom: 4px solid #10b981;'>
                            <h1 style='color: #ffffff; font-size: 24px; font-weight: 700; margin: 0; letter-spacing: -0.5px;'>Desbloqueo Autorizado</h1>
                            <p style='color: #94a3b8; font-size: 14px; margin: 5px 0 0 0;'>Sistema de Gestión de Bienes y Pagos</p>
                        </td>
                    </tr>
                    <!-- Body Content -->
                    <tr>
                        <td style='padding: 40px 40px 30px 40px;'>
                            <h2 style='color: #0f172a; font-size: 20px; font-weight: 600; margin-top: 0; margin-bottom: 20px;'>Estimado/a {$nombre},</h2>
                            <p style='font-size: 16px; line-height: 1.6; color: #475569; margin-top: 0; margin-bottom: 25px;'>
                                Usted ha completado exitosamente la verificación de doble factor (2FA). Su enlace de seguridad ha sido generado y autorizado.
                            </p>
                            <p style='font-size: 16px; line-height: 1.6; color: #475569; margin-top: 0; margin-bottom: 30px;'>
                                Haga clic en el siguiente botón para reactivar el acceso completo a su cuenta administrativa:
                            </p>
                            <!-- Action Button -->
                            <div style='text-align: center; margin: 35px 0;'>
                                <a href='{$link}' style='display: inline-block; padding: 14px 28px; background-color: #10b981; color: #ffffff; text-decoration: none; font-size: 16px; font-weight: bold; border-radius: 8px;'>Desbloquear Mi Cuenta</a>
                            </div>
                            <p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 30px; margin-bottom: 0;'>
                                Este enlace es de un solo uso y expirará pronto.
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
            $mail->Subject = 'Desbloqueo de Cuenta Autorizado';
            $mail->Body = $mensaje;

            $mail->send();
            
            // Limpiar sesiones 2FA tras enviar el token definitivo
            unset($_SESSION['codigo_2fa_desbloqueo']);
            unset($_SESSION['usuario_desbloqueo']);
            unset($_SESSION['correo_desbloqueo']);

            $_SESSION['estatus'] = 'success';
            $_SESSION['mensaje'] = '¡Verificación exitosa! Se ha enviado el enlace definitivo a su correo para desbloquear la cuenta.';
            header("Location: ../vistas/solicitar_desbloqueo.php");
            exit();

        } catch (Exception $e) {
            $_SESSION['estatus'] = 'error';
            $_SESSION['mensaje'] = "Validación correcta, pero el enlace falló al enviarse. Error: {$mail->ErrorInfo}";
            header("Location: ../vistas/solicitar_desbloqueo.php");
            exit();
        }

    } else {
        // Código incorrecto
        $_SESSION['estatus'] = 'error';
        $_SESSION['mensaje'] = 'El código de verificación de 6 dígitos es inválido.';
        header("Location: ../vistas/confirmar_2fa_desbloqueo.php");
        exit();
    }
} else {
    header("Location: ../vistas/login.php");
    exit();
}
