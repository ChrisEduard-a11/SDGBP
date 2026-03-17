<?php
session_start();
require_once("../conexion.php");
require_once("../models/bitacora.php");

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Eliminar el producto
    $query = "DELETE FROM productos WHERE id = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Registrar en bitácora
    if (isset($_SESSION['id'])) {
        registrarAccion($conexion, 'Eliminó un producto', $_SESSION['id']);
    }   

    $_SESSION["estatus"] = "success";
    $_SESSION["mensaje"] = "El producto ha sido eliminado exitosamente.";
    header("Location: ../vistas/productos.php");
    exit;
}
?>