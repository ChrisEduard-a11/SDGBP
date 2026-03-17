<?php
session_start();

if (isset($_POST['archivo'])) {
    $archivo = $_POST['archivo'];
    $rutaArchivo = "../doc/" . $archivo;

    if (file_exists($rutaArchivo)) {
        if (unlink($rutaArchivo)) {
            $_SESSION['mensaje'] = "El archivo '$archivo' se ha borrado correctamente.";
            $_SESSION['estatus'] = "success";
        } else {
            $_SESSION['mensaje'] = "No se pudo borrar el archivo '$archivo'.";
            $_SESSION['estatus'] = "error";
        }
    } else {
        $_SESSION['mensaje'] = "El archivo '$archivo' no existe.";
        $_SESSION['estatus'] = "error";
    }
} else {
    $_SESSION['mensaje'] = "No se especificó ningún archivo para borrar.";
    $_SESSION['estatus'] = "error";
}

header("Location: ../vistas/gestionar_movimientos.php");
exit();
?>