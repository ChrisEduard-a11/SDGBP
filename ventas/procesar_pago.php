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
    
    // Extraer datos de Logística
    $tipoEntrega = $data['tipoEntrega'] ?? 'No especificado';
    $agenciaEnvio = $data['agenciaEnvio'] ?? 'N/A';
    $direccionEnvio = $data['direccionEnvio'] ?? 'N/A';
    
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

        // Generar filas para los correos (Vendedor / Comprador)
        $productosHTML = '';
        $productosHTMLBuyer = '';
        foreach ($carrito as $producto) {
            // Vendedor: Muestra precio base explícito
            $productosHTML .= "
                <tr>
                    <td><strong>{$producto['nombre']}</strong></td>
                    <td style='text-align: center;'>{$producto['cantidad']}</td>
                    <td>$" . number_format($producto['precio'], 2) . "</td>
                    <td style='text-align: right; font-weight: bold;'>$" . number_format($producto['precio'] * $producto['cantidad'], 2) . "</td>
                </tr>
            ";
            // Comprador: Resumen elegante
            $productosHTMLBuyer .= "
                <tr>
                    <td><strong>{$producto['nombre']}</strong><br><span style='color: #888; font-size: 12px;'>$" . number_format($producto['precio'], 2) . " c/u</span></td>
                    <td style='text-align: center;'>{$producto['cantidad']}</td>
                    <td style='text-align: right; font-weight: bold; color: #333;'>$" . number_format($producto['precio'] * $producto['cantidad'], 2) . "</td>
                </tr>
            ";
        }

        // --- PLANTILLA 1: CORREO AL VENDEDOR ---
        $correoHTML = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <style>
                body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f5f7; margin: 0; padding: 20px; color: #333333; }
                .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
                .header { background-color: #001e6a; padding: 25px; text-align: center; }
                .header h1 { color: #fff159; margin: 0; font-size: 24px; letter-spacing: 1px; }
                .content { padding: 30px; }
                .alert-box { background-color: #e8f5e9; border-left: 4px solid #4caf50; padding: 15px; margin-bottom: 25px; border-radius: 4px; }
                .alert-box h2 { color: #2e7d32; margin: 0 0 5px 0; font-size: 18px; }
                .alert-box p { margin: 0; font-size: 14px; color: #388e3c; }
                .section-title { font-size: 16px; font-weight: bold; color: #001e6a; border-bottom: 2px solid #eee; padding-bottom: 8px; margin-top: 0; margin-bottom: 15px; text-transform: uppercase; }
                .info-grid { width: 100%; margin-bottom: 25px; }
                .info-grid td { padding: 8px 0; font-size: 14px; vertical-align: top; }
                .info-label { font-weight: bold; color: #666; width: 40%; }
                .info-value { color: #333; }
                .table-products { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
                .table-products th { background-color: #f8f9fa; padding: 12px; text-align: left; font-size: 13px; color: #666; border-bottom: 2px solid #ddd; }
                .table-products td { padding: 12px; border-bottom: 1px solid #eee; font-size: 14px; }
                .total-box { background-color: #fcfcfc; border: 1px dashed #ccc; padding: 15px; border-radius: 6px; text-align: right; }
                .total-row { font-size: 14px; color: #666; margin-bottom: 8px; }
                .total-final { font-size: 20px; font-weight: bold; color: #001e6a; margin-top: 10px; border-top: 1px solid #eee; padding-top: 10px; }
                .footer { background-color: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #999; border-top: 1px solid #eee; }
                .highlight { background-color: #fff3cd; color: #856404; padding: 2px 6px; border-radius: 4px; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='header'>
                    <h1>EURIPYS 2024</h1>
                </div>
                <div class='content'>
                    <div class='alert-box'>
                        <h2>¡Nueva venta confirmada!</h2>
                        <p>El cliente ha reportado un pago por <strong>" . number_format($montoBsPago, 2) . " Bs</strong>.</p>
                    </div>

                    <h3 class='section-title'>Datos del Comprador</h3>
                    <table class='info-grid'>
                        <tr><td class='info-label'>Nombre:</td><td class='info-value'>$nombreComprador</td></tr>
                        <tr><td class='info-label'>Correo:</td><td class='info-value'><a href='mailto:$correoComprador'>$correoComprador</a></td></tr>
                        <tr><td class='info-label'>Teléfono:</td><td class='info-value'>$telefonoComprador</td></tr>
                    </table>

                    <h3 class='section-title'>Logística y Despacho</h3>
                    <table class='info-grid' style='background-color: #e3f2fd; padding: 15px; border-radius: 6px; border-left: 4px solid #2196f3;'>
                        <tr><td class='info-label' style='color: #0d47a1;'>Modalidad:</td><td class='info-value'><strong style='color: #0d47a1; font-size: 16px;'>$tipoEntrega</strong></td></tr>
                        " . ($tipoEntrega === 'Envio' ? "
                        <tr><td class='info-label' style='color: #0d47a1;'>Agencia/Empresa:</td><td class='info-value'><strong>$agenciaEnvio</strong></td></tr>
                        <tr><td class='info-label' style='color: #0d47a1;'>Destino Exacto:</td><td class='info-value'>$direccionEnvio</td></tr>
                        " : "<tr><td colspan='2' style='color: #0d47a1; padding-top: 10px;'><em>El cliente se acercará a retirar en la sede principal.</em></td></tr>") . "
                    </table>

                    <h3 class='section-title'>Detalles del Pago (Para Conciliar)</h3>
                    <table class='info-grid' style='background-color: #f8f9fa; padding: 15px; border-radius: 6px;'>
                        <tr><td class='info-label'>Método:</td><td class='info-value'><strong>$metodoPago</strong></td></tr>
                        <tr><td class='info-label'>Banco Emisor:</td><td class='info-value'>$banco</td></tr>
                        <tr><td class='info-label'>Fecha Transferencia:</td><td class='info-value'>$fechaPago</td></tr>
                        <tr><td class='info-label'>Monto Reportado:</td><td class='info-value highlight'>" . number_format($montoBsPago, 2) . " Bs</td></tr>
                        <tr><td class='info-label'>Referencia (Ref):</td><td class='info-value'><strong style='font-size: 16px; color: #001e6a;'>$referencia</strong></td></tr>
                        <tr><td class='info-label'>Cédula Origen:</td><td class='info-value'>$cedula</td></tr>
                        <tr><td class='info-label'>Teléfono Origen:</td><td class='info-value'>$telefono</td></tr>
                        <!-- SI ES TRANSFERENCIA -->
                        " . ($numeroCuenta ? "<tr><td class='info-label'>Nro. Cuenta Origen:</td><td class='info-value'>$numeroCuenta</td></tr>" : "") . "
                    </table>

                    <h3 class='section-title'>Mercancía Solicitada</h3>
                    <table class='table-products'>
                        <thead>
                            <tr>
                                <th style='width: 45%;'>Producto</th>
                                <th style='text-align: center;'>Cant.</th>
                                <th>Precio/U</th>
                                <th style='text-align: right;'>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            $productosHTML
                        </tbody>
                    </table>

                    <div class='total-box'>
                        <div class='total-row'>Monto Base del Pedido: <strong>$" . number_format($montoTotalUSD, 2) . " USD</strong></div>
                        <div class='total-final'>A VERIFICAR EN BANCO: " . number_format($montoBsPago, 2) . " Bs</div>
                    </div>
                </div>
                <div class='footer'>
                    Aviso automático de tu sistema de ventas EURIPYS.<br>
                    Verifica siempre los fondos en el banco antes de entregar la mercancía.
                </div>
            </div>
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
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <style>
                body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f5f7; margin: 0; padding: 20px; color: #333333; }
                .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
                .header { background-color: #fff159; padding: 25px; text-align: center; border-bottom: 4px solid #001e6a; }
                .header h1 { color: #001e6a; margin: 0; font-size: 26px; font-weight: 800; letter-spacing: -0.5px; }
                .content { padding: 35px; }
                .greeting { font-size: 20px; font-weight: bold; color: #001e6a; margin-top: 0; margin-bottom: 10px; }
                .intro-text { font-size: 15px; line-height: 1.6; color: #555; margin-bottom: 30px; }
                .table-products { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
                .table-products th { background-color: #f8f9fa; padding: 12px; text-align: left; font-size: 13px; color: #888; border-bottom: 2px solid #ddd; text-transform: uppercase; }
                .table-products td { padding: 15px 12px; border-bottom: 1px solid #eee; font-size: 15px; color: #444; }
                .total-box { background-color: #fdfdfd; border: 1px solid #eaeaea; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
                .total-row { display: table; width: 100%; margin-bottom: 10px; }
                .total-label { display: table-cell; text-align: right; width: 60%; font-size: 15px; color: #666; padding-right: 15px; }
                .total-value { display: table-cell; text-align: right; font-size: 16px; font-weight: bold; color: #333; }
                .total-final { display: table; width: 100%; margin-top: 15px; padding-top: 15px; border-top: 2px solid #eee; }
                .total-final .total-label { font-size: 18px; font-weight: bold; color: #000; }
                .total-final .total-value { font-size: 22px; font-weight: bold; color: #00a650; }
                .info-box { background-color: #e3f2fd; border-left: 4px solid #2196f3; padding: 15px; border-radius: 4px; font-size: 14px; color: #0d47a1; line-height: 1.5; }
                .footer { background-color: #f8f9fa; padding: 25px; text-align: center; font-size: 12px; color: #999; line-height: 1.6; border-top: 1px solid #eee; }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='header'>
                    <h1>EURIPYS 2024</h1>
                </div>
                <div class='content'>
                    <h2 class='greeting'>¡Hola, $nombreComprador!</h2>
                    <p class='intro-text'>
                        Queremos darte las gracias por confiar en nosotros. Hemos recibido tu comprobante de pago exitosamente y estamos alistando todo para ti. A continuación tienes el recibo digital de tu pedido:
                    </p>
                    
                    <table class='table-products'>
                        <thead>
                            <tr>
                                <th style='width: 50%;'>Artículo</th>
                                <th style='text-align: center; width: 15%;'>Cant.</th>
                                <th style='text-align: right; width: 35%;'>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            $productosHTMLBuyer
                        </tbody>
                    </table>

                    <div class='total-box'>
                        <div class='total-row'>
                            <div class='total-label'>Subtotal USD:</div>
                            <div class='total-value'>$" . number_format($montoTotalUSD, 2) . "</div>
                        </div>
                        <div class='total-final'>
                            <div class='total-label'>Pago Confirmado:</div>
                            <div class='total-value'>" . number_format($montoBsPago, 2) . " Bs</div>
                        </div>
                    </div>

                    <div class='info-box' style='background-color: #fff8e1; border-left: 4px solid #f18000; margin-bottom: 25px; color: #8d6e00; font-size: 15px;'>
                        <strong style='color: #f18000; font-size: 16px;'><span style='font-size: 20px;'>🚚</span> Logística Solicitada: $tipoEntrega</strong><br><br>
                        " . ($tipoEntrega === 'Envio' ? "
                        Acordaste el envío asegurado (COD) por medio de <strong>$agenciaEnvio</strong>.<br>
                        <strong>Dirección Registrada:</strong> $direccionEnvio
                        " : "Acordaste <strong>retiro personal</strong>. Puedes acercarte por nuestra sede corporativa principal en horario hábil para la entrega física de tus equipos.") . "
                    </div>

                    <div class='info-box'>
                        <strong>¿Qué sigue ahora en el Estatus Financiero?</strong><br>
                        Nuestro equipo administrativo verificará tu lote de transacción <strong>(Ref: $referencia)</strong>. Una vez que este pago se refleje efectivo, un operador nuestro validará la orden por tu teléfono (<strong>$telefonoComprador</strong>).
                    </div>
                </div>
                <div class='footer'>
                    <strong>EURIPYS 2024, C.A.</strong><br>
                    Innovación, Desarrollo y Producción Tecnológica.<br><br>
                    <span style='font-size: 10px;'>Este correo electrónico es generado automáticamente. Por favor, asegúrate de mantener a la mano tu número de referencia ante cualquier consulta.</span>
                </div>
            </div>
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