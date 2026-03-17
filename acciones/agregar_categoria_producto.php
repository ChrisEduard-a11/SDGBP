<?php
session_start();
require_once("../conexion.php");
require_once("../models/bitacora.php"); // Asegúrate de incluir el archivo donde está definida la función registrarAccion

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];

    // Validar que el nombre no esté vacío
    if (!empty($nombre)) {
        $sql = "INSERT INTO categorias_productos (nombre) VALUES (?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $nombre);

        if ($stmt->execute()) {
            // Registrar la acción en la bitácora
            registrarAccion($conexion, 'Agregar Categoría de Producto', $_SESSION['id']);
            $_SESSION['estatus'] = "success";
            $_SESSION['mensaje'] = "Categoría agregada correctamente.";
        } else {
            $_SESSION['estatus'] = "error";
            $_SESSION['mensaje'] = "Error al agregar la categoría: " . $conexion->error;
        }
    } else {
        $_SESSION['estatus'] = "error";
        $_SESSION['mensaje'] = "El nombre de la categoría es obligatorio.";
    }
    header("Location: ../vistas/agregar_categoria_producto.php");
    exit;
}
?>