<?php
session_start();
require_once("../conexion.php");
require_once("../models/bitacora.php");

$categoria_id = $_GET['categoria_id'];

// Obtener nombre de la categoría antes de eliminar para el registro
$nombre_categoria = 'Desconocida';
$sql_cat = "SELECT nombre FROM categorias WHERE id = ?";
$stmt_cat = $conexion->prepare($sql_cat);
$stmt_cat->bind_param("i", $categoria_id);
if ($stmt_cat->execute()) {
    if ($res_cat = $stmt_cat->get_result()) {
        if ($row_cat = $res_cat->fetch_assoc()) {
            $nombre_categoria = $row_cat['nombre'];
        }
    }
}
$stmt_cat->close();

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
        $accion_bitacora = 'Eliminó Categoría - Categoría: ' . $nombre_categoria;
        registrarAccion($conexion, $accion_bitacora, $_SESSION['id']);
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