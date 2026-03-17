<?php
session_start();

if (isset($_GET['archivo'])) {
    $archivo = basename($_GET['archivo']); // Evitar rutas maliciosas
    $rutaArchivo = "../comprobantes/" . $archivo;

    if (file_exists($rutaArchivo)) {
        unlink($rutaArchivo); // Eliminar el archivo
        $_SESSION["mensaje"] = "Archivo eliminado correctamente.";
        $_SESSION["estatus"] = "success";
    } else {
        $_SESSION["mensaje"] = "El archivo no existe.";
        $_SESSION["estatus"] = "danger";
    }
} else {
    $_SESSION["mensaje"] = "No se especificó ningún archivo.";
    $_SESSION["estatus"] = "warning";
}

header("Location: ../vistas/listar_comprobantes.php");