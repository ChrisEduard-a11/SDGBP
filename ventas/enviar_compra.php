<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir la biblioteca PHPMailer
require_once '../PHPMailer/src/PHPMailer.php';
require_once '../PHPMailer/src/Exception.php';
require_once '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


// Verificar si los datos del carrito y del pago están disponibles en la sesión
$carrito = $_SESSION['carrito'] ?? [];
$datosPago = $_SESSION['datosPago'] ?? [];

// Verificar si el carrito tiene productos
if (empty($carrito)) {
    error_log('El carrito está vacío o no está definido.');
    echo json_encode(['success' => false, 'message' => 'El carrito está vacío.']);
    exit;
}

// Verificar si los datos del pago están disponibles
if (empty($datosPago)) {
    error_log('Los datos del pago no están definidos.');
    echo json_encode(['success' => false, 'message' => 'Los datos del pago no están disponibles.']);
    exit;
}

// Crear el contenido del correo
$mensaje = "<h1>Compra Realizada de, {$datosPago['nombreComprador']}</h1>";
$mensaje .= "<h2>Detalles del Pago:</h2>";
$mensaje .= "<ul>";
$mensaje .= "<li><strong>Método de Pago:</strong> {$datosPago['metodoPago']}</li>";
$mensaje .= "<li><strong>Monto Total:</strong> $" . number_format($datosPago['monto'], 2) . "</li>";
$mensaje .= "<li><strong>Fecha de Pago:</strong> {$datosPago['fechaPago']}</li>";
if (!empty($datosPago['telefono'])) {
    $mensaje .= "<li><strong>Teléfono:</strong> {$datosPago['telefono']}</li>";
}
if (!empty($datosPago['cedula'])) {
    $mensaje .= "<li><strong>Cédula:</strong> {$datosPago['cedula']}</li>";
}
if (!empty($datosPago['numeroCuenta'])) {
    $mensaje .= "<li><strong>Número de Cuenta:</strong> {$datosPago['numeroCuenta']}</li>";
}
if (!empty($datosPago['referencia'])) {
    $mensaje .= "<li><strong>Referencia:</strong> {$datosPago['referencia']}</li>";
}
$mensaje .= "</ul>";

$mensaje .= "<h2>Productos Comprados:</h2>";
$mensaje .= "<ul>";
foreach ($carrito as $producto) {
    $subtotal = $producto['precio'] * $producto['cantidad'];
    $mensaje .= "<li><strong>{$producto['nombre']}</strong>: \${$producto['precio']} x {$producto['cantidad']} unidades = <strong>\${$subtotal}</strong></li>";
}
$mensaje .= "</ul>";

$mensaje .= "<p>Gracias por confiar en nosotros. Si tienes alguna pregunta, no dudes en contactarnos.</p>";

// Configurar PHPMailer
$mail = new PHPMailer(true);

try {
    // Configuración del servidor SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'soporte.sdgbp2024@gmail.com'; // Cambia esto por tu correo
    $mail->Password = 'zqmk whnf jrlz mhpp'; // Cambia esto por tu contraseña
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Configuración del correo
    $mail->setFrom('soporte.sdgbp2024@gmail.com', 'EURIPYS 2024, C.A.');
    $mail->addAddress('soporte.sdgbp2024@gmail.com', $nombre); // Enviar al correo del comprador
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8'; // Configurar UTF-8 para el cuerpo del correo
    $mail->Subject = 'Confirmación de Compra - EURIPYS 2024';
    $mail->Body = $mensaje;

    // Enviar el correo
    $mail->send();
    $mail->CharSet = 'UTF-8'; // Configurar UTF-8 para el cuerpo del correo

    // Vaciar el carrito y los datos del pago después de enviar el correo
    $_SESSION['carrito'] = [];
    $_SESSION['datosPago'] = [];
    error_log('Correo enviado correctamente.');

    echo json_encode(['success' => true, 'message' => 'Correo enviado correctamente.']);
} catch (Exception $e) {
    error_log('Error al enviar el correo: ' . $mail->ErrorInfo);
    echo json_encode(['success' => false, 'message' => 'Error al enviar el correo: ' . $mail->ErrorInfo]);
}
?>