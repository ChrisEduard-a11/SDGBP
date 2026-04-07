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

    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,16}$/', $nueva_clave)) {
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "La contraseña no cumple con los requisitos de seguridad.";
        header("Location: ../vistas/nueva_clave.php");
        exit;
    }

    $id_usuario = $_SESSION['id'] ?? $_SESSION['id_usuario'];
    
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

    // CASO: Contraseña vencida (usuario ya logueado, no necesita verificación extra)
    if (isset($_POST['vencida']) && $_POST['vencida'] == '1' && isset($_SESSION['id'])) {
        $_SESSION['identidad_verificada'] = true;
    }

    // OPTIMIZACIÓN: Si ya validó identidad (por 2FA de recuperación, preguntas, o contraseña vencida), actualizamos directo
    if (isset($_SESSION['identidad_verificada']) && $_SESSION['identidad_verificada'] === true) {
        $sql_final = "UPDATE usuario SET clave = ?, codigo_verificacion = NULL, fecha_cambio_clave = CURRENT_DATE WHERE id_usuario = ?";
        $stmt_final = $conexion->prepare($sql_final);
        $stmt_final->bind_param("si", $clave_hash, $id_usuario);
        
        if ($stmt_final->execute()) {
            // Destruir sesión para forzar nuevo inicio de sesión con nueva clave
            session_unset();
            session_destroy();
            session_start();
            $_SESSION["estatus"] = "success";
            $_SESSION["mensaje"] = "Tu contraseña ha sido actualizada exitosamente. Por favor inicia sesión.";
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
                            <h1 style='color: #ffffff; font-size: 24px; font-weight: 700; margin: 0; letter-spacing: -0.5px;'>Confirmación de Seguridad</h1>
                            <p style='color: #94a3b8; font-size: 14px; margin: 5px 0 0 0;'>Sistema de Gestión de Bienes y Pagos</p>
                        </td>
                    </tr>
                    <!-- Body Content -->
                    <tr>
                        <td style='padding: 40px 40px 30px 40px;'>
                            <h2 style='color: #0f172a; font-size: 20px; font-weight: 600; margin-top: 0; margin-bottom: 20px;'>Hola {$nombre_dest},</h2>
                            <p style='font-size: 16px; line-height: 1.6; color: #475569; margin-top: 0; margin-bottom: 20px;'>
                                Has solicitado cambiar tu contraseña en el sistema <strong>SDGBP</strong>. Para completar este proceso, utiliza el siguiente código de verificación de 6 dígitos:
                            </p>
                            <!-- Code Block -->
                            <div style='text-align: center; margin: 30px 0;'>
                                <span style='display: inline-block; font-size: 36px; font-weight: 800; letter-spacing: 10px; color: #0f172a; background: #f8fafc; padding: 15px 30px; border-radius: 8px; border: 2px dashed #cbd5e1;'>
                                    {$codigo}
                                </span>
                            </div>
                            <p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 30px; margin-bottom: 0;'>
                                Este código es válido por tiempo limitado. Si no solicitaste este cambio, por favor ignora este correo y asegúrate de proteger tu cuenta.
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
