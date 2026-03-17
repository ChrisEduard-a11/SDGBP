<?php
session_start();
require_once '../conexion.php';
require_once '../models/bitacora.php'; // Asegúrate de incluir el archivo donde está definida la función registrarAccion

// Procesar el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"]);
    $usuario_id = $_SESSION["id"]; // ID del usuario actual (upu)

    if (!empty($nombre)) {
        // Iniciar una transacción para garantizar la consistencia de los datos
        mysqli_begin_transaction($conexion);

        try {
            // Insertar el cliente en la tabla `cliente`
            $sql_cliente = "INSERT INTO cliente (nombre) VALUES (?)";
            $stmt_cliente = $conexion->prepare($sql_cliente);
            $stmt_cliente->bind_param("s", $nombre);
            if (!$stmt_cliente->execute()) {
                throw new Exception("Error al agregar el cliente/proveedor: " . $stmt_cliente->error);
            }

            // Obtener el ID del cliente recién insertado
            $cliente_id = $conexion->insert_id;

            // Insertar la relación en la tabla `usuario_pagos`
            $sql_usuario_pagos = "INSERT INTO usuario_pagos (usuario_id, cliente_id) VALUES (?, ?)";
            $stmt_usuario_pagos = $conexion->prepare($sql_usuario_pagos);
            $stmt_usuario_pagos->bind_param("ii", $usuario_id, $cliente_id);
            if (!$stmt_usuario_pagos->execute()) {
                throw new Exception("Error al registrar la relación usuario-cliente/proveedor: " . $stmt_usuario_pagos->error);
            }

            // Confirmar la transacción
            mysqli_commit($conexion);

            // Registrar la acción en la bitácora
            registrarAccion($conexion, 'Agregar Cliente/Proveedor', $usuario_id);

            $_SESSION['estatus'] = 'success';
            $_SESSION['mensaje'] = 'Se agregado correctamente.';
            header("Location: ../vistas/ver_clientes.php");
            exit;
        } catch (Exception $e) {
            // Revertir la transacción en caso de error
            mysqli_rollback($conexion);

            $_SESSION['estatus'] = 'error';
            $_SESSION['mensaje'] = $e->getMessage();
        } finally {
            // Cerrar los statements
            if (isset($stmt_cliente)) $stmt_cliente->close();
            if (isset($stmt_usuario_pagos)) $stmt_usuario_pagos->close();
        }
    } else {
        $_SESSION['estatus'] = 'error';
        $_SESSION['mensaje'] = 'El nombre del cliente/proveedor no puede estar vacío.';
    }
}
?>