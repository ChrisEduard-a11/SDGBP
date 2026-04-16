<?php
session_start();
include('../conexion.php');
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

$superAdminId = 8;
$loggedUserId = $_SESSION['id'] ?? 0;

if ($loggedUserId != $superAdminId) {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado.']);
    exit();
}

$idSolicitud = intval($_GET['id_solicitud'] ?? 0);
if (!$idSolicitud) {
    echo json_encode(['status' => 'error', 'message' => 'ID de solicitud no válido.']);
    exit();
}

// Generar código 2FA
$codigo = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
$expiracion = date('Y-m-d H:i:s', strtotime('+15 minutes'));

$sql = "UPDATE solicitudes_eliminacion_u SET codigo_2fa = ?, expiracion_2fa = ? WHERE id_solicitud = ? AND estado = 'pendiente'";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ssi", $codigo, $expiracion, $idSolicitud);

if ($stmt->execute()) {
    // Obtener correo del ID 8
    $sqlCorreo = "SELECT correo, nombre FROM usuario WHERE id_usuario = ?";
    $stmtC = $conexion->prepare($sqlCorreo);
    $stmtC->bind_param("i", $superAdminId);
    $stmtC->execute();
    $res = $stmtC->get_result()->fetch_assoc();
    $correoSA = $res['correo'];
    $nombreSA = $res['nombre'];
    $stmtC->close();

    // Enviar correo
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        // Usamos la configuración de correo que tienen en sus otros archivos
        $mail->Username = 'soporte.sdgbp2024@gmail.com';
        $mail->Password = 'zqmk whnf jrlz mhpp';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('soporte.sdgbp2024@gmail.com', 'Seguridad SDGBP');
        $mail->addAddress($correoSA);
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Aprobación de Eliminación - 2FA Código';

        $mail->Body = "
<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
</head>
<body style='margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f8fafc; color: #334155; -webkit-font-smoothing: antialiased;'>
    <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #f8fafc; padding: 40px 0;'>
        <tr>
            <td align='center'>
                <table width='100%' style='max-width: 600px; background-color: #ffffff; border-radius: 12px; overflow: hidden; margin: 0 auto; border: 1px solid #e2e8f0;' cellpadding='0' cellspacing='0'>
                    <tr>
                        <td align='center' style='padding: 30px 20px; background-color: #ef4444; border-bottom: 4px solid #b91c1c;'>
                            <h1 style='color: #ffffff; font-size: 24px; font-weight: 700; margin: 0;'>Código de Aprobación 2FA</h1>
                            <p style='color: #fecaca; font-size: 14px; margin: 5px 0 0 0;'>Administrador Principal del Sistema</p>
                        </td>
                    </tr>
                    <tr>
                        <td style='padding: 40px 40px 30px 40px;'>
                            <h2 style='color: #0f172a; font-size: 20px; font-weight: 600; margin-top: 0; margin-bottom: 20px;'>Hola " . htmlspecialchars($nombreSA) . ",</h2>
                            <p style='font-size: 16px; line-height: 1.6; color: #475569; margin-top: 0; margin-bottom: 20px;'>
                                Has recibido una solicitud para aprobar la eliminación de un usuario en el sistema. Utiliza el siguiente código para confirmar la acción:
                            </p>
                            <div style='text-align: center; margin: 30px 0;'>
                                <span style='display: inline-block; font-size: 36px; font-weight: 800; letter-spacing: 10px; color: #b91c1c; background: #fef2f2; padding: 15px 30px; border-radius: 8px; border: 2px dashed #fca5a5;'>
                                    {$codigo}
                                </span>
                            </div>
                            <p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 30px; margin-bottom: 0;'>
                                El código expirará en 15 minutos. Si no autorizaste esta acción, tu cuenta está segura pero alguien con acceso a ella solicitó este borrado.
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
        echo json_encode(['status' => 'success', 'message' => 'Código de 6 dígitos enviado exitosamente a tu correo de Super Administrador.']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo enviar el correo de verificación. Error: ' . $mail->ErrorInfo]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error al guardar el código en la base de datos de solicitudes.']);
}
$stmt->close();
?>
