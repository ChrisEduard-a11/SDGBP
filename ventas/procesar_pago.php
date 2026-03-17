<?php
require_once("../conexion.php");

// Incluir la biblioteca PHPMailer
require_once '../PHPMailer/src/PHPMailer.php';
require_once '../PHPMailer/src/Exception.php';
require_once '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

// Mostrar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json'); // Asegurar que la respuesta sea JSON

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Leer los datos enviados desde el frontend
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Error al decodificar los datos JSON: ' . json_last_error_msg()
        ]);
        exit;
    }

    // Extraer los datos del pago
    $nombreComprador = $data['nombreComprador'] ?? null;
    $correoComprador = $data['correoComprador'] ?? null;
    $telefonoComprador = $data['telefonoComprador'] ?? null;
    $metodoPago = $data['metodoPago'] ?? null;
    $fechaPago = $data['fechaPago'] ?? null;
    $telefono = $data['telefono'] ?? null;
    $cedula = $data['cedula'] ?? null;
    $numeroCuenta = $data['numeroCuenta'] ?? null;
    $referencia = $data['referencia'] ?? null;
    $montoTotalUSD = floatval($data['monto'] ?? 0);
    $montoTotalBS = floatval($data['montoBs'] ?? 0);
    $carrito = $data['carrito'] ?? []; // Productos comprados
    $banco = $data['banco'] ?? null; // Capturar el banco enviado desde el frontend
    $montoBsPago = floatval($data['montoBsPago'] ?? 0); // Capturar el monto en bolívares (pago)
    
    // Validar los datos requeridos
    if (!$nombreComprador || !$correoComprador || !$telefonoComprador || !$metodoPago || !$fechaPago || !$referencia || !$banco || $montoTotalUSD <= 0 || $montoBsPago <= 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Datos incompletos o inválidos.'
        ]);
        exit;
    }

    // Iniciar una transacción para garantizar la consistencia de los datos
    $conexion->begin_transaction();

    try {
        // Insertar los datos del pago en la tabla `pagos_productos`
        $query = "INSERT INTO pagos_productos (nombre_comprador, correo_comprador, telefono_comprador, monto, metodo_pago, fecha_pago, telefono, cedula, numero_cuenta, referencia, monto_bs_pago, banco) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($query);
        if (!$stmt) {
            throw new Exception('Error al preparar la consulta: ' . $conexion->error);
        }
        $stmt->bind_param("sdssssssssds", 
            $nombreComprador, 
            $correoComprador, 
            $telefonoComprador, 
            $montoTotalUSD, 
            $metodoPago, 
            $fechaPago, 
            $telefono, 
            $cedula, 
            $numeroCuenta, 
            $referencia, 
            $montoBsPago, 
            $banco
        );
        if (!$stmt->execute()) {
            throw new Exception('Error al ejecutar la consulta: ' . $stmt->error);
        }

        // Reducir el stock de los productos comprados
        foreach ($carrito as $producto) {
            $productoId = $producto['productoId'];
            $cantidad = $producto['cantidad'];

            $sqlStock = "UPDATE productos SET stock = stock - ? WHERE id = ? AND stock >= ?";
            $stmtStock = $conexion->prepare($sqlStock);
            if (!$stmtStock) {
                throw new Exception('Error al preparar la consulta de stock: ' . $conexion->error);
            }

            $stmtStock->bind_param("iii", $cantidad, $productoId, $cantidad);
            if (!$stmtStock->execute() || $stmtStock->affected_rows <= 0) {
                throw new Exception("No se pudo reducir el stock para el producto ID: $productoId. Verifica el stock disponible.");
            }
        }

        // Confirmar la transacción
        $conexion->commit();

        // Vaciar el carrito después de completar la compra
        unset($_SESSION['carrito']);

        // Crear el contenido del correo
        $productosHTML = '';
        foreach ($carrito as $producto) {
            $productosHTML .= "
                <tr>
                    <td>{$producto['nombre']}</td>
                    <td>{$producto['cantidad']}</td>
                    <td>$" . number_format($producto['precio'], 2) . "</td>
                    <td>$" . number_format($producto['precio'] * $producto['cantidad'], 2) . "</td>
                </tr>
            ";
        }

        $correoHTML = "
            <html>
            <head>
                <style>
                    table {
                        width: 100%;
                        border-collapse: collapse;
                    }
                    th, td {
                        border: 1px solid #ddd;
                        padding: 8px;
                        text-align: left;
                    }
                    th {
                        background-color: #f2f2f2;
                    }
                </style>
            </head>
            <body>
                <h2>Detalles de la Compra</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        $productosHTML
                    </tbody>
                </table>
                <h3>Total en USD: $" . number_format($montoTotalUSD, 2) . "</h3>
                <h3>Total en Bs (Pago): " . number_format($montoBsPago, 2) . " Bs</h3>
                <hr>
                    <h2 class='text-center'>Información</h2>
                    <h3>Detalles del Vendedor</h3>
                    <p><strong>Nombre del Comprador:</strong> $nombreComprador</p>
                    <p><strong>Correo del Comprador:</strong> $correoComprador</p>
                    <p><strong>Teléfono del Comprador:</strong> $telefonoComprador</p>
                    <hr>
                    <h3>Detalles del Pago</h3>
                    <p><strong>Método de Pago:</strong> $metodoPago</p>
                    <p><strong>Banco de Origen:</strong> $banco</p>
                    <p><strong>Monto Pagado:</strong> $montoBsPago Bs</p></p>
                    <p><strong>Fecha de Pago:</strong> $fechaPago</p>
                    <p><strong>Referencia:</strong> $referencia</p>
                    <p><strong>Teléfono:</strong> $telefono</p>
                    <p><strong>Cédula:</strong> $cedula</p>
                    <p><strong>Número de Cuenta:</strong> $numeroCuenta</p>
                </body>
            </html>
        ";

        // Configurar PHPMailer
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'soporte.sdgbp2024@gmail.com'; // Cambia esto por tu correo
        $mail->Password = 'zqmk whnf jrlz mhpp'; // Cambia esto por tu contraseña
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->setFrom('soporte.sdgbp2024@gmail.com', 'EURIPYS 2024, C.A.');
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';

        // Enviar correo al vendedor
        $mail->addAddress('cristianarcaya2003@gmail.com', 'Vendedor');
        $mail->Subject = 'Confirmación de Compra - EURIPYS 2024';
        $mail->Body = $correoHTML;
        $mail->send();

        // Enviar correo al comprador
        $mail->clearAddresses(); // Limpiar direcciones anteriores
        $mail->addAddress($correoComprador, $nombreComprador); // Correo del comprador
        $mail->Subject = 'Gracias por tu compra - EURIPYS 2024';
        $mail->Body = "
            <html>
            <head>
                <style>
                    table {
                        width: 100%;
                        border-collapse: collapse;
                    }
                    th, td {
                        border: 1px solid #ddd;
                        padding: 8px;
                        text-align: left;
                    }
                    th {
                        background-color: #f2f2f2;
                    }
                </style>
            </head>
            <body>
                <h2>Gracias por tu compra, $nombreComprador</h2>
                <p>Hemos recibido tu pedido y estamos procesándolo. Aquí tienes los detalles de tu compra:</p>
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        $productosHTML
                    </tbody>
                </table>
                <h3>Total en USD: $" . number_format($montoTotalUSD, 2) . "</h3>
                <h3>Total en Bs (Pago): " . number_format($montoBsPago, 2) . " Bs</h3>
                <p>Si tienes alguna pregunta, no dudes en contactarnos.</p>
                <p><strong>EURIPYS 2024, C.A.</strong></p>
            </body>
            </html>
        ";
        $mail->send();

        // Respuesta de éxito
        echo json_encode([
            'status' => 'success',
            'message' => 'Pago procesado correctamente, carrito vaciado y correos enviados.'
        ]);
        exit;
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $conexion->rollback();
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
        exit;
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Método no permitido.'
    ]);
}
?>