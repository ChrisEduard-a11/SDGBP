<?php
// filepath: c:\xampp\htdocs\SistemaProyectoxampp\acciones\editar_categoria_producto.php
session_start();
require_once("../conexion.php");
require_once("../models/bitacora.php"); // Si usas bitácora

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';

    if ($id > 0 && !empty($nombre)) {
        $sql = "UPDATE categorias_productos SET nombre = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("si", $nombre, $id);

        if ($stmt->execute()) {
            // Registrar en bitácora si lo usas
            if (isset($_SESSION['id'])) {
                registrarAccion($conexion, 'Editar Categoría de Producto', $_SESSION['id']);
            }
            $_SESSION['estatus'] = "success";
            $_SESSION['mensaje'] = "Categoría editada correctamente.";
        } else {
            $_SESSION['estatus'] = "error";
            $_SESSION['mensaje'] = "Error al editar la categoría: " . $conexion->error;
        }
        $stmt->close();
    } else {
        $_SESSION['estatus'] = "error";
        $_SESSION['mensaje'] = "Datos inválidos para editar la categoría.";
    }
    header("Location: ../vistas/agregar_categoria_producto.php");
    exit;
} else {
    header("Location: ../vistas/agregar_categoria_producto.php");
    exit;
}
?>