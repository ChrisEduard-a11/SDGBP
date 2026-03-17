<?php
session_start();
require_once("../conexion.php");
require_once("../models/bitacora.php"); // Asegúrate de incluir el archivo donde está definida la función registrarAccion

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);
    $precio = floatval($_POST['precio']);
    $stock = intval($_POST['stock']);
    $categoria_id = intval($_POST['categoria_id']);
    $imagen = null;

    if (empty($nombre) || empty($descripcion) || $precio <= 0 || $stock <= 0 || $categoria_id <= 0) {
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "Por favor, completa todos los campos correctamente.";
        header("Location: ../vistas/agregar_producto.php");
        exit();
    }

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $imagen = $_FILES['imagen'];
        $nombreImagen = time() . '_' . basename($imagen['name']);
        $rutaDestino = '../img/productos/' . $nombreImagen;
        $tipoArchivo = strtolower(pathinfo($nombreImagen, PATHINFO_EXTENSION));
        $tamañoArchivo = $imagen['size'];

        if ($tamañoArchivo > 10 * 1024 * 1024) {
            $_SESSION["estatus"] = "error";
            $_SESSION["mensaje"] = "El tamaño del archivo no debe exceder los 10 MB.";
            header("Location: ../vistas/agregar_producto.php");
            exit();
        }

        $tiposPermitidos = ['jpg', 'jpeg', 'png', 'svg'];
        if (!in_array($tipoArchivo, $tiposPermitidos)) {
            $_SESSION["estatus"] = "error";
            $_SESSION["mensaje"] = "Solo se permiten archivos JPG, JPEG, PNG y SVG.";
            header("Location: ../vistas/agregar_producto.php");
            exit();
        }

        if (move_uploaded_file($imagen['tmp_name'], $rutaDestino)) {
            $imagen = '../img/productos/' . $nombreImagen;
        } else {
            $_SESSION["estatus"] = "error";
            $_SESSION["mensaje"] = "Error al subir la imagen.";
            header("Location: ../vistas/agregar_producto.php");
            exit();
        }
    }

    $query = "INSERT INTO productos (nombre, descripcion, precio, stock, categoria_productos_id, imagen) 
              VALUES ('$nombre', '$descripcion', $precio, $stock, $categoria_id, '$imagen')";

    if (mysqli_query($conexion, $query)) {
        // Registrar la acción en la bitácora
        registrarAccion($conexion, 'Agregar Producto', $_SESSION['id']);

        $_SESSION["estatus"] = "success";
        $_SESSION["mensaje"] = "Producto agregado correctamente.";
        header("Location: ../vistas/productos.php");
    } else {
        die("Error en la consulta SQL: " . mysqli_error($conexion));
    }
    exit();
} else {
    $_SESSION["estatus"] = "error";
    $_SESSION["mensaje"] = "Método no permitido.";
    header("Location: ../vistas/agregar_producto.php");
    exit();
}
?>