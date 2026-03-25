<?php
session_start();
require_once("../conexion.php");

// Verificar permisos (Solo admin o contabilidad)
$tipo = $_SESSION["tipo"] ?? '';
if ($tipo != "cont" && $tipo != "admin") {
    echo "Acceso denegado.";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id']);
    $accion = $_POST['accion']; // 'aprobar' o 'rechazar'

    if (!$id || !$accion) {
        $_SESSION['estatus'] = 'error';
        $_SESSION['mensaje'] = 'Datos inválidos o faltantes.';
        header("Location: ../vistas/aprobar_marketing.php");
        exit();
    }

    $conexion->begin_transaction();
    try {
        // Bloquear la fila para evitar race conditions
        $stmt = $conexion->prepare("SELECT estado, carrito_json FROM pagos_productos WHERE id = ? FOR UPDATE");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 0) {
            throw new Exception("No se encontró el pago especificado en la base de datos.");
        }
        $pago = $res->fetch_assoc();

        if ($pago['estado'] != 'Pendiente') {
            throw new Exception("Esta transacción ya fue procesada anteriormente y está marcada como: " . $pago['estado']);
        }

        if ($accion == 'aprobar') {
            // Decodificar el carrito y DESCONTAR el stock ahora.
            $carrito = json_decode($pago['carrito_json'], true);
            
            if (is_array($carrito) && count($carrito) > 0) {
                foreach ($carrito as $producto) {
                    $productoId = intval($producto['productoId']);
                    $cantidad = intval($producto['cantidad']);

                    $sqlStock = "UPDATE productos SET stock = stock - ? WHERE id = ? AND stock >= ?";
                    $stmtStock = $conexion->prepare($sqlStock);
                    $stmtStock->bind_param("iii", $cantidad, $productoId, $cantidad);
                    
                    if (!$stmtStock->execute() || $stmtStock->affected_rows <= 0) {
                        throw new Exception("¡ALERTA! Posible quiebre de stock. No hay inventario suficiente para el producto ID: $productoId. No se puede aprobar la venta.");
                    }
                }
            }

            // Si hay éxito, marcar la venta como aprobada
            $stmtUpdate = $conexion->prepare("UPDATE pagos_productos SET estado = 'Aprobado' WHERE id = ?");
            $stmtUpdate->bind_param("i", $id);
            $stmtUpdate->execute();
            
            $conexion->commit();
            $_SESSION['estatus'] = 'success';
            $_SESSION['mensaje'] = 'Venta Aprobada: El inventario ha sido descontado correctamente.';
            
            header("Location: ../vistas/aprobar_marketing.php");
            exit();

        } elseif ($accion == 'rechazar') {
            // Simplemente marcar como rechazado (el stock nunca fue tocado, no hay que devolverlo)
            $stmtUpdate = $conexion->prepare("UPDATE pagos_productos SET estado = 'Rechazado' WHERE id = ?");
            $stmtUpdate->bind_param("i", $id);
            $stmtUpdate->execute();
            
            $conexion->commit();
            $_SESSION['estatus'] = 'success';
            $_SESSION['mensaje'] = 'Orden de venta rechazada exitosamente.';
            header("Location: ../vistas/aprobar_marketing.php");
            exit();
        } else {
            throw new Exception("Acción no reconocida.");
        }

    } catch (Exception $e) {
        $conexion->rollback();
        $_SESSION['estatus'] = 'error';
        $_SESSION['mensaje'] = $e->getMessage();
        header("Location: ../vistas/aprobar_marketing.php");
        exit();
    }
}
?>
