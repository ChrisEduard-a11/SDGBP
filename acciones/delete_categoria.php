<?php
session_start();
require_once("../conexion.php");
require_once("../models/bitacora.php");

$categoria_id = $_GET['categoria_id'];

// Eliminar la categoría
$sql = "DELETE FROM categorias WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $categoria_id);

if ($stmt->execute()) {
    // Operación exitosa
    $_SESSION['mensaje'] = "Categoría eliminada exitosamente.";
    $_SESSION['estatus'] = "success";
    // Registrar en bitácora
    if (isset($_SESSION['id'])) {
        registrarAccion($conexion, 'Eliminar Categoría', $_SESSION['id']);
    }
} else {
    // Operación fallida
    $_SESSION['mensaje'] = "Error al eliminar la categoría.";
    $_SESSION['estatus'] = "error";
}

$stmt->close();

// Redirigir de vuelta a la página de categorías
header("Location: ../vistas/categorias.php");
exit();
?>