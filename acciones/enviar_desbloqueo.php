<?php
session_start();
include('../conexion.php');
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];

    // Verificar si el usuario existe en la base de datos
    $sql = "SELECT * FROM usuario WHERE usuario = '$usuario'";
    $result = mysqli_query($conexion, $sql);
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

    if ($row) {
        if ($row['intentos'] == 3) {
            $correo = $row['correo'];
            $token = bin2hex(random_bytes(50)); // Generar un token único
            $sql_token = "UPDATE usuario SET token = '$token' WHERE usuario = '$usuario'";
            mysqli_query($conexion, $sql_token);

            $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
            $host = $_SERVER['HTTP_HOST'];
            $directorio = dirname($_SERVER['PHP_SELF'], 2); // Sube dos niveles para llegar a la raíz del sistema
            $link = $protocolo . $host . $directorio . "/acciones/desbloquear_usuario.php?token=$token";
            $mensaje = "
            <html>
            <head>
                <title>Desbloqueo de Usuario</title>
            </head>
            <body style='font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4;'>
                <!-- Navbar -->
                <div style='background-color: #f18000; padding: 10px; text-align: center; color: white;'>
                    <h1 style='margin: 0;'>Sistema de Desbloqueo de Usuario</h1>
                </div>

                <!-- Contenido principal -->
                <div style='padding: 20px; background-color: white; margin: 20px auto; max-width: 600px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);'>
                    <h2 style='color: #f18000;'>Estimado/a {$row['nombre']},</h2>
                    <p>Hemos recibido una solicitud para desbloquear su cuenta. Si usted no realizó esta solicitud, por favor ignore este correo.</p>
                    <p>Para desbloquear su cuenta, haga clic en el siguiente botón:</p>
                    <p style='text-align: center;'>
                        <a href='$link' style='display: inline-block; padding: 10px 20px; font-size: 16px; color: #fff; background-color: #f18000; text-decoration: none; border-radius: 5px;'>Desbloquear Cuenta</a>
                    </p>
                </div>

                <!-- Footer -->
                <div style='background-color: #343a40; color: white; text-align: center; padding: 10px;'>
                    <p style='margin: 0; font-size: 0.9rem;'>Este mensaje fue enviado desde el sistema de desbloqueo de usuario.</p>
                    <p style='margin: 0; font-size: 0.9rem;'>© 2025 Sistema de Desbloqueo de Usuario. Todos los derechos reservados.</p>
                </div>
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
                $mail->setFrom('soporte.sdgbp2024@gmail.com', 'Sistema de Desbloqueo de Usuario');
                $mail->addAddress($correo);
                $mail->isHTML(true);
                $mail->Subject = 'Desbloqueo de Usuario';
                $mail->Body = $mensaje;

                $mail->send();
                $_SESSION['estatus'] = 'success';
                $_SESSION['mensaje'] = 'Correo de desbloqueo enviado. Por favor, revise su bandeja de entrada.';
            } catch (Exception $e) {
                $_SESSION['estatus'] = 'error';
                $_SESSION['mensaje'] = "No se pudo enviar el correo. Error: {$mail->ErrorInfo}";
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