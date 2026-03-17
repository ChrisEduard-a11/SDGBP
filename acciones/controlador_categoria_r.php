<?php
session_start();
include('../conexion.php');
include_once('../models/bitacora.php'); // Asegúrate de incluir el archivo donde está registrarAccion

    $nombre = $_POST['nombre'];
    if (!empty($nombre)) {
        $sql = "INSERT INTO categorias (nombre) VALUES ('$nombre')";
        if (mysqli_query($conexion, $sql)) {
            $_SESSION["estatus"] = "success";
            $_SESSION["mensaje"] = "Categoría registrada exitosamente.";
            // Registrar en bitácora
            if (isset($_SESSION['id'])) {
                registrarAccion($conexion, 'Registrar Categoría', $_SESSION['id']);
            }
            header("Location: ../vistas/categorias.php");
        } else {
            $_SESSION["estatus"] = "error";
            $_SESSION["mensaje"] = "Error al registrar la categoría: " . mysqli_error($conexion);
            header("Location: ../vistas/categorias.php");
        }
    } else {
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "El nombre de la categoría no puede estar vacío.";
        header("Location: ../vistas/categorias.php");
    }
?>