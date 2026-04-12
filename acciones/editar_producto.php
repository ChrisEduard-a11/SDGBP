<?php
session_start();
require_once("../conexion.php");
require_once("../models/bitacora.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $categoria_id = $_POST['categoria_id'];
    $sql_imagen = "";

    // Procesar la imagen
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $imagen = $_FILES['imagen'];
        $nombreImagen = time() . '_' . $imagen['name'];
        $rutaDestino = '../img/productos/' . $nombreImagen;
        $tipoArchivo = strtolower(pathinfo($nombreImagen, PATHINFO_EXTENSION));
        $tamañoArchivo = $imagen['size'];

        // Validar el tamaño del archivo (10 MB = 10 * 1024 * 1024 bytes)
        if ($tamañoArchivo > 10 * 1024 * 1024) {
            $_SESSION["estatus"] = "error";
            $_SESSION["mensaje"] = "El tamaño del archivo no debe exceder los 10 MB.";
            header("Location: ../vistas/productos.php");
            exit();
        }

        // Validar el tipo de archivo
        $tiposPermitidos = ['jpg', 'jpeg', 'png', 'svg'];
        if (!in_array($tipoArchivo, $tiposPermitidos)) {
            $_SESSION["estatus"] = "error";
            $_SESSION["mensaje"] = "Solo se permiten archivos JPG, JPEG, PNG y SVG.";
            header("Location: ../vistas/productos.php");
            exit();
        }

        // Mover la imagen a la carpeta de destino
        if (move_uploaded_file($imagen['tmp_name'], $rutaDestino)) {
            // Obtener la ruta de la imagen anterior
            $sql = "SELECT imagen FROM productos WHERE id = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $producto = $result->fetch_assoc();
            $rutaImagenAnterior = $producto['imagen'];

            // Eliminar la imagen anterior si existe
            if (file_exists('../' . $rutaImagenAnterior)) {
                unlink('../' . $rutaImagenAnterior);
            }

            // Guardar la ruta relativa en la base de datos
            $sql_imagen = ", imagen='../img/productos/$nombreImagen'";
        } else {
            $_SESSION["estatus"] = "error";
            $_SESSION["mensaje"] = "Error al subir la imagen.";
            header("Location: ../vistas/productos.php");
            exit();
        }
    }

    // Actualizar el producto en la base de datos
    $sql = "UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, stock = ?, categoria_productos_id = ? $sql_imagen WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssdisi", $nombre, $descripcion, $precio, $stock, $categoria_id, $id);

    if ($stmt->execute()) {
        $_SESSION["estatus"] = "success";
        $_SESSION["mensaje"] = "Producto actualizado correctamente.";
        // Registrar en bitácora
        if (isset($_SESSION['id'])) {
            $accion_bitacora = 'Actualizó un producto - Nombre: ' . $nombre;
            registrarAccion($conexion, $accion_bitacora, $_SESSION['id']);
        }
        header("Location: ../vistas/productos.php");
    } else {
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "Error al actualizar el producto: " . $conexion->error;
        header("Location: ../vistas/productos.php");
    }
    exit();
}
?>