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

    // Buscar el usuario asociado al correo
    $sql = "SELECT usuario, nombre FROM usuario WHERE correo = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $nombre_usuario = $row['usuario'];
        $nombre_real = $row['nombre'];

        // Enviar el correo
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
            $mail->Subject = 'Recuperación de Nombre de Usuario - SDGBP';

            $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #eee; padding: 20px; border-radius: 10px;'>
                    <h2 style='color: #f18000; text-align: center;'>Recordatorio de Usuario</h2>
                    <p>Hola <strong>$nombre_real</strong>,</p>
                    <p>Has solicitado recordar tu nombre de usuario para el sistema <strong>SDGBP</strong>.</p>
                    <div style='text-align: center; margin: 30px 0; background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0;'>
                        <p style='margin: 0; color: #64748b; font-size: 14px; text-transform: uppercase; font-weight: bold;'>Tu Nombre de Usuario es:</p>
                        <h3 style='margin: 10px 0 0 0; color: #1e293b; font-size: 24px;'>$nombre_usuario</h3>
                    </div>
                    <p>Ahora puedes volver al sistema e iniciar sesión o continuar con la recuperación de tu contraseña si lo necesitas.</p>
                    <hr style='border: 0; border-top: 1px solid #eee;'>
                    <p style='text-align: center; color: #adb5bd; font-size: 11px;'>&copy; " . date('Y') . " SDGBP - Sistema de Gestión de Bienes y Pagos</p>
                </div>
            ";

            $mail->send();
            $_SESSION["estatus"] = "success";
            $_SESSION["mensaje"] = "Tu nombre de usuario ha sido enviado a tu correo electrónico.";
            header("Location: ../vistas/recuperar.php");
            exit();

        } catch (Exception $e) {
            $_SESSION["estatus"] = "error";
            $_SESSION["mensaje"] = "No se pudo enviar el correo. Error: {$mail->ErrorInfo}";
            header("Location: ../vistas/recuperar_usuario.php");
            exit();
        }
    } else {
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "El correo electrónico no está registrado en nuestro sistema.";
        header("Location: ../vistas/recuperar_usuario.php");
        exit();
    }
}
