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
                <div style='font-family: Arial, sans-serif;'>
                    <!-- Navbar -->
                    <div style='background-color: #f18000; padding: 10px; text-align: center; color: white;'>
                        <img src='https://lh5.googleusercontent.com/p/AF1QipMIuz9nSKZaDup5Zr7LIVwhyDKheMsfdeD_55hd=w408-h408-k-no' alt='Logo' style='width: 100px; height: auto;'>
                        <h1 style='margin: 0;'>EURIPYS 2024, C.A.</h1>
                    </div>

                    <!-- Contenido principal -->
                    <div style='padding: 20px;'>
                        <h2 style='color: #f18000;'>Nuevo mensaje de contacto</h2>
                        <p><strong>Detalles del remitente:</strong></p>
                        <ul style='list-style: none; padding: 0;'>
                            <li><strong>Nombre:</strong> $nombre</li>
                            <li><strong>Correo:</strong> $email</li>
                            <li><strong>Fecha y hora de envío:</strong> $fechaEnvio</li>
                        </ul>
                        <p><strong>Mensaje:</strong></p>
                        <blockquote style='font-style: italic; color: #555; border-left: 4px solid #f18000; padding-left: 10px;'>$mensaje</blockquote>
                    </div>

                    <!-- Footer -->
                    <div style='background-color: #343a40; color: white; text-align: center; padding: 10px;'>
                        <p style='margin: 0; font-size: 0.9rem;'>Este mensaje fue enviado desde el formulario de contacto de EURIPYS 2024, C.A.</p>
                        <p style='margin: 0; font-size: 0.9rem;'>© 2025 Sistema de Gestion Bienes y Pagos. Todos los derechos reservados.</p>
                    </div>
                </div>
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