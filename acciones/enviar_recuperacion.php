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
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Recuperación de Contraseña</title>
            <style>
                .btn-warning {
                    background-color: #f18000;
                    color: white;
                    padding: 10px 20px;
                    text-decoration: none;
                    border-radius: 5px;
                }
                .btn-warning:hover {
                    background-color: #d16900;
                }
            </style>
        </head>
        <body style='font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4;'>
            <!-- Encabezado -->
            <div style='background-color: #f18000; padding: 10px; text-align: center; color: white;'>
                <img src='https://lh5.googleusercontent.com/p/AF1QipMIuz9nSKZaDup5Zr7LIVwhyDKheMsfdeD_55hd=w408-h408-k-no' alt='Logo' style='width: 100px; height: auto;'>
                <h1 style='margin: 0;'>Sistema de Gestión de Bienes y Pagos</h1>
            </div>
 
            <!-- Contenido principal -->
            <div style='padding: 20px; background-color: white; margin: 20px auto; max-width: 600px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);'>
                <h2 style='color: #f18000;'>Hola,</h2>
                <p>Hemos recibido una solicitud para restablecer tu contraseña. Si realizaste esta solicitud, haz clic en el botón de abajo para continuar:</p>
                <p style='text-align: center;'>
                    <a href='$link' class='btn btn-warning'>Restablecer Contraseña</a>
                </p>
                <p>Si no realizaste esta solicitud, puedes ignorar este correo.</p>
                <p>Este enlace es válido por 1 hora.</p>
                <p>Atentamente,<br>El equipo de soporte de SDGBP</p>
            </div>
        
            <!-- Footer -->
            <div style='background-color: #343a40; color: white; text-align: center; padding: 10px;'>
                <p style='margin: 0; font-size: 0.9rem;'>Este mensaje fue enviado automáticamente por el sistema de gestión de Bienes y Pagos.</p>
                <p style='margin: 0; font-size: 0.9rem;'>© " . date("Y") . " Sistema de Gestión de Bienes y Pagos. Todos los derechos reservados.</p>
            </div>
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