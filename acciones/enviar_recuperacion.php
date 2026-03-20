<?php
session_start();

include('../conexion.php');
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = $_POST['correo'];

    // Verificar si el correo existe en la base de datos
    $sql = "SELECT * FROM usuario WHERE correo = '$correo'";
    $result = mysqli_query($conexion, $sql);
    if (mysqli_num_rows($result) > 0) {
        $usuario = mysqli_fetch_assoc($result);
        $token = bin2hex(random_bytes(50)); // Generar un token único
        $expira = date("Y-m-d H:i:s", strtotime('+1 hour')); // Token expira en 1 hora

        // Guardar el token en la base de datos
        $sql = "INSERT INTO recuperacion (correo, token, expira) VALUES ('$correo', '$token', '$expira')";
        mysqli_query($conexion, $sql);

        // Enviar el correo electrónico
        $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $directorio = dirname($_SERVER['PHP_SELF'], 2); // Sube dos niveles para llegar a la raíz
        $link = $protocolo . $host . $directorio . "/vistas/restablecer_contraseña.php?token=$token";
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
                            <h1 style='color: #ffffff; font-size: 24px; font-weight: 700; margin: 0; letter-spacing: -0.5px;'>Restablecer Contraseña</h1>
                            <p style='color: #94a3b8; font-size: 14px; margin: 5px 0 0 0;'>Sistema de Gestión de Bienes y Pagos</p>
                        </td>
                    </tr>
                    <!-- Body Content -->
                    <tr>
                        <td style='padding: 40px 40px 30px 40px;'>
                            <h2 style='color: #0f172a; font-size: 20px; font-weight: 600; margin-top: 0; margin-bottom: 20px;'>Estimado Usuario,</h2>
                            <p style='font-size: 16px; line-height: 1.6; color: #475569; margin-top: 0; margin-bottom: 25px;'>
                                Hemos recibido una solicitud para restablecer la contraseña de acceso asociada a este correo electrónico. Haga clic en el botón inferior para establecer sus nuevas credenciales:
                            </p>
                            <!-- Action Button -->
                            <div style='text-align: center; margin: 35px 0;'>
                                <a href='{$link}' style='display: inline-block; padding: 14px 28px; background-color: #f18000; color: #ffffff; text-decoration: none; font-size: 16px; font-weight: bold; border-radius: 8px;'>Restablecer Mi Contraseña</a>
                            </div>
                            <p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 30px; margin-bottom: 0;'>
                                Si no solicitó restablecer su contraseña, no se requiere realizar ninguna acción. Este enlace es seguro y expirará en 1 hora por motivos de seguridad.
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
        $mail->CharSet = 'UTF-8'; // Configurar UTF-8
        try {
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'soporte.sdgbp2024@gmail.com'; // Tu correo de Gmail
            $mail->Password = 'llct ozaq rqqh nbtb'; // Tu contraseña de aplicación de Gmail
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            // Configuración del correo
            $mail->setFrom('soporte.sdgbp2024@gmail.com', 'Sistema de Recuperación de Contraseña');
            $mail->addAddress($correo);
            $mail->isHTML(true);
            $mail->Subject = 'Recuperación de Contraseña';
            $mail->Body = $mensaje;

            $mail->send();
            $_SESSION["estatus"] = "success";
            $_SESSION["mensaje"] = "Se ha enviado un enlace de recuperación a tu correo electrónico.";
            header("Location: ../vistas/login.php");
        } catch (Exception $e) {
            $_SESSION["estatus"] = "error";
            $_SESSION["mensaje"] = "No se pudo enviar el correo. Error: No tienes conexión a internet.";
            header("Location: ../vistas/login.php");
        }
    } else {
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "El correo electrónico no está registrado.";
        header("Location: ../vistas/recu_correo.php");
    }
}
?>