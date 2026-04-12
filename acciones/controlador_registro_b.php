<?php
session_start();
include_once('../models/bitacora.php'); // Asegúrate de incluir el archivo donde está registrarAccion
    // Obtener los datos del formulario
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $categoria = $_POST['categoria'];
    $cantidad = $_POST['cantidad'];
    $fecha_adquisicion = $_POST['fecha_adquisicion'];

    // Validar los datos (esto es un ejemplo básico, puedes agregar más validaciones)
    if (empty($nombre) || empty($descripcion) || empty($categoria) || empty($cantidad) || empty($fecha_adquisicion)) {
        $error = "Todos los campos son obligatorios.";
    } else {
        // Insertar los datos en la base de datos
        $sql = "INSERT INTO bienes (nombre, descripcion, categoria, cantidad, fecha_adquisicion) VALUES ('$nombre', '$descripcion', '$categoria', '$cantidad', '$fecha_adquisicion')";

        if ($conn->query($sql) === TRUE) {
            $success = "Bien nacional registrado exitosamente.";
            // Registrar en bitácora
            if (isset($_SESSION['id'])) {
                $accion_bitacora = 'Registró Bien Nacional - Nombre: ' . $nombre;
                registrarAccion($conn, $accion_bitacora, $_SESSION['id']);
            }
        } else {
            $error = "Error al registrar el bien: " . $conn->error;
        }

        // Cerrar la conexión
        $conn->close();
    }
?>