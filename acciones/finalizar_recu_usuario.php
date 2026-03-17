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

            $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #eee; padding: 20px; border-radius: 10px;'>
                    <h1 style='color: #28a745; text-align: center;'>Identidad Confirmada</h1>
                    <p>Estimado/a <strong>$nombre_real</strong>,</p>
                    <p>Tras completar satisfactoriamente los 3 pasos de seguridad (CI, Correo y 2FA), te entregamos tu nombre de usuario para acceder al sistema <strong>SDGBP</strong>:</p>
                    <div style='text-align: center; margin: 30px 0; background: #f0fdf4; padding: 25px; border-radius: 12px; border: 1px solid #bbf7d0;'>
                        <p style='margin: 0; color: #166534; font-size: 14px; text-transform: uppercase; font-weight: bold;'>Tu Nombre de Usuario es:</p>
                        <h2 style='margin: 10px 0 0 0; color: #14532d; font-size: 30px;'>$nombre_usuario</h2>
                    </div>
                    <p>Por seguridad, te recomendamos nunca compartir tus credenciales. Ya puedes volver a la pantalla de entrada e iniciar sesión.</p>
                    <div style='text-align: center; margin-top: 30px;'>
                         <a href='https://sdgbp.wuaze.com/vistas/login.php' style='background: #f18000; color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; font-weight: bold;'>Ir al Inicio de Sesión</a>
                    </div>
                </div>
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
