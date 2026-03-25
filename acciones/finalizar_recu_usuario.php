<?php
session_start();
include('../conexion.php');
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $codigo_ingresado = $_POST['codigo'];

    if (empty($_SESSION['temp_recu_cedula']) || empty($_SESSION['temp_recu_correo_real'])) {
        header("Location: ../vistas/recuperar_usuario.php");
        exit();
    }

    $cedula = $_SESSION['temp_recu_cedula'];

    // Verificar el código y obtener los datos finales del usuario
    $sql = "SELECT usuario, nombre, correo FROM usuario WHERE cedula = ? AND codigo_verificacion = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ss", $cedula, $codigo_ingresado);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $nombre_usuario = $row['usuario'];
        $nombre_real = $row['nombre'];
        $correo = $row['correo'];

        // Limpiar el código en la base de datos
        $sql_clear = "UPDATE usuario SET codigo_verificacion = NULL WHERE cedula = ?";
        $stmt_clear = $conexion->prepare($sql_clear);
        $stmt_clear->bind_param("s", $cedula);
        $stmt_clear->execute();

        // Enviar el correo final con el nombre de usuario
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'soporte.sdgbp2024@gmail.com';
            $mail->Password = 'zqmk whnf jrlz mhpp';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('soporte.sdgbp2024@gmail.com', 'SDGBP Soporte');
            $mail->addAddress($correo);
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'Entrega de Credenciales - Nombre de Usuario';

            // Obtener la URL base dinámica para el enlace del correo
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $script_dirname = dirname($_SERVER['SCRIPT_NAME']);
            $base_dir = preg_replace('/\/acciones$/i', '', $script_dirname);
            $login_url = rtrim($protocol . '://' . $host . $base_dir, '/') . '/vistas/login.php';

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
                        <td align='center' style='padding: 30px 20px; background-color: #0f172a; border-bottom: 4px solid #10b981;'>
                            <h1 style='color: #ffffff; font-size: 24px; font-weight: 700; margin: 0; letter-spacing: -0.5px;'>Identidad Confirmada</h1>
                            <p style='color: #94a3b8; font-size: 14px; margin: 5px 0 0 0;'>Sistema de Gestión de Bienes y Pagos</p>
                        </td>
                    </tr>
                    <!-- Body Content -->
                    <tr>
                        <td style='padding: 40px 40px 30px 40px;'>
                            <h2 style='color: #0f172a; font-size: 20px; font-weight: 600; margin-top: 0; margin-bottom: 20px;'>Estimado/a {$nombre_real},</h2>
                            <p style='font-size: 16px; line-height: 1.6; color: #475569; margin-top: 0; margin-bottom: 20px;'>
                                Tras completar satisfactoriamente las distintas capas de seguridad, te entregamos tu nombre de usuario oficial para que puedas acceder al sistema <strong>SDGBP</strong>:
                            </p>
                            <!-- Username Block -->
                            <div style='text-align: center; margin: 30px 0;'>
                                <p style='margin: 0 0 10px 0; color: #64748b; font-size: 12px; text-transform: uppercase; font-weight: 700; letter-spacing: 1px;'>Tu Nombre de Usuario</p>
                                <span style='display: inline-block; font-size: 26px; font-weight: 800; letter-spacing: 2px; color: #0f172a; background: #f0fdf4; padding: 15px 30px; border-radius: 8px; border: 2px dashed #bbf7d0;'>
                                    {$nombre_usuario}
                                </span>
                            </div>
                            <p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 30px; margin-bottom: 30px;'>
                                Por seguridad, te recomendamos nunca compartir tus credenciales con terceros. Ya puedes volver a la pantalla principal e iniciar sesión.
                            </p>
                            <!-- Action Button -->
                            <div style='text-align: center;'>
                                <a href='{$login_url}' style='display: inline-block; padding: 14px 28px; background-color: #10b981; color: #ffffff; text-decoration: none; font-size: 16px; font-weight: bold; border-radius: 8px;'>Ir al Acceso del Sistema</a>
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

            // Limpiar variables de sesión temporales
            unset($_SESSION['temp_recu_cedula']);
            unset($_SESSION['temp_recu_correo_real']);
            unset($_SESSION['temp_recu_nombre']);

            $_SESSION["estatus"] = "success";
            $_SESSION["mensaje"] = "Tu identidad ha sido verificada. Hemos enviado tu nombre de usuario por correo.";
            header("Location: ../vistas/recuperar.php");
            exit();

        } catch (Exception $e) {
             $_SESSION["estatus"] = "error";
             $_SESSION["mensaje"] = "Código correcto, pero error al enviar correo final: {$mail->ErrorInfo}";
             header("Location: ../vistas/confirmar_2fa_usuario_recu.php");
             exit();
        }
    } else {
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "El código ingresado es incorrecto.";
        header("Location: ../vistas/confirmar_2fa_usuario_recu.php");
        exit();
    }
}
