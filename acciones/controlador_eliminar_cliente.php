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

// Verificar si se envió el ID del cliente
$id_cliente = $_GET['id'] ?? null;

if (!$id_cliente) {
    $_SESSION['estatus'] = 'error';
    $_SESSION['mensaje'] = 'Cliente no encontrado.';
    header("Location: ../vistas/ver_clientes.php");
    exit;
}

// Obtener nombre antes de eliminar para la bitácora
$nombre_cliente = 'Desconocido';
$sql_nombre = "SELECT nombre FROM cliente WHERE id_cliente = ?";
$stmt_nombre = $conexion->prepare($sql_nombre);
$stmt_nombre->bind_param("i", $id_cliente);
$stmt_nombre->execute();
if ($res_nombre = $stmt_nombre->get_result()) {
    if ($row_n = $res_nombre->fetch_assoc()) {
        $nombre_cliente = $row_n['nombre'];
    }
}

// Eliminar el cliente de la base de datos
$sql_delete = "DELETE FROM cliente WHERE id_cliente = ?";
$stmt_delete = $conexion->prepare($sql_delete);
$stmt_delete->bind_param("i", $id_cliente);

if ($stmt_delete->execute()) {
    $_SESSION['estatus'] = 'success';
    $_SESSION['mensaje'] = 'Cliente eliminado correctamente.';
    // Registrar en bitácora
    if (isset($_SESSION['id'])) {
        $accion_bitacora = 'Eliminó Cliente - Cliente: ' . $nombre_cliente;
        registrarAccion($conexion, $accion_bitacora, $_SESSION['id']);
    }
} else {
    $_SESSION['estatus'] = 'error';
    $_SESSION['mensaje'] = 'Error al eliminar el cliente.';
}

header("Location: ../vistas/ver_clientes.php");
exit;
?>