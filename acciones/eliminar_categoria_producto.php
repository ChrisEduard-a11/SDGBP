<?php
session_start();
// filepath: c:\xampp\htdocs\SistemaProyectoxampp\acciones\eliminar_categoria_producto.php
require_once("../conexion.php");

// Validar el parámetro id recibido por GET
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../vistas/agregar_categoria_producto.php");
    exit;
}

$id = intval($_GET['id']);

// Verificar si hay productos asociados a la categoría antes de eliminar
$sql_check = "SELECT COUNT(*) AS total FROM productos WHERE categoria_productos_id = ?";
$stmt_check = $conexion->prepare($sql_check);
$stmt_check->bind_param("i", $id);
$stmt_check->execute();
$res_check = $stmt_check->get_result()->fetch_assoc();
$stmt_check->close();

if ($res_check['total'] > 0) {
    // No eliminar si hay productos asociados
    $_SESSION["estatus"] = "error";
    $_SESSION["mensaje"] = "No se puede eliminar la categoría porque tiene productos asociados.";
    header("Location: ../vistas/agregar_categoria_producto.php");
    exit;
}

// Eliminar la categoría
$sql = "DELETE FROM categorias_productos WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $stmt->close();
    $_SESSION["estatus"] = "success";
    $_SESSION["mensaje"] = "Categoría eliminada correctamente.";
    header("Location: ../vistas/agregar_categoria_producto.php");
    exit;
} else {
    $stmt->close();
    $_SESSION["estatus"] = "error";
    $_SESSION["mensaje"] = "Error al eliminar la categoría.";
    header("Location: ../vistas/agregar_categoria_producto.php");
    exit;
}
?>