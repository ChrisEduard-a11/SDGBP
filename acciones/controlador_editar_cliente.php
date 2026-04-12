<?php
require_once("../conexion.php");
include_once('../models/bitacora.php'); // Asegúrate de incluir el archivo donde está registrarAccion

session_start();

// Verificar si el usuario tiene el tipo "upu"
if ($_SESSION["tipo"] !== "upu") {
    $_SESSION['estatus'] = 'error';
    $_SESSION['mensaje'] = 'Acceso denegado.';
    header("Location: ../vistas/inicio.php");
    exit;
}

// Verificar si se envió el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_cliente = $_POST['id_cliente'] ?? null;
    $nuevo_nombre = mysqli_real_escape_string($conexion, $_POST['nombre'] ?? '');

    if (!$id_cliente || empty($nuevo_nombre)) {
        $_SESSION['estatus'] = 'error';
        $_SESSION['mensaje'] = 'Datos incompletos.';
        header("Location: ../vistas/ver_clientes.php");
        exit;
    }

    // Actualizar el cliente en la base de datos
    $sql_update = "UPDATE cliente SET nombre = ? WHERE id_cliente = ?";
    $stmt_update = $conexion->prepare($sql_update);
    $stmt_update->bind_param("si", $nuevo_nombre, $id_cliente);

    if ($stmt_update->execute()) {
        $_SESSION['estatus'] = 'success';
        $_SESSION['mensaje'] = 'Cliente actualizado correctamente.';
        // Registrar en bitácora
        if (isset($_SESSION['id'])) {
            $accion_bitacora = 'Actualizó Cliente - Cliente: ' . $nuevo_nombre;
            registrarAccion($conexion, $accion_bitacora, $_SESSION['id']);
        }
    } else {
        $_SESSION['estatus'] = 'error';
        $_SESSION['mensaje'] = 'Error al actualizar el cliente.';
    }

    header("Location: ../vistas/ver_clientes.php");
    exit;
}
?>