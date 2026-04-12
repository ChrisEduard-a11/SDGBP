<?php
session_start();
require_once("../conexion.php");
require_once("../models/bitacora.php");

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Obtener nombre del producto antes de eliminar
    $nombre_producto = 'Desconocido';
    $sql_prod = "SELECT nombre FROM productos WHERE id = ?";
    $stmt_prod = $conexion->prepare($sql_prod);
    $stmt_prod->bind_param("i", $id);
    $stmt_prod->execute();
    if ($res_prod = $stmt_prod->get_result()) {
        if ($row_prod = $res_prod->fetch_assoc()) {
            $nombre_producto = $row_prod['nombre'];
        }
    }
    $stmt_prod->close();

    // Eliminar el producto
    $query = "DELETE FROM productos WHERE id = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Registrar en bitácora
    if (isset($_SESSION['id'])) {
        $accion_bitacora = 'Eliminó un producto - Nombre: ' . $nombre_producto;
        registrarAccion($conexion, $accion_bitacora, $_SESSION['id']);
    }   

    $_SESSION["estatus"] = "success";
    $_SESSION["mensaje"] = "El producto ha sido eliminado exitosamente.";
    header("Location: ../vistas/productos.php");
    exit;
}
?>