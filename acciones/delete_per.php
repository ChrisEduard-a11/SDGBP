<?php
session_start();
include('../conexion.php');
include('../models/notificaciones.php');
include_once('../models/bitacora.php'); // Asegúrate de incluir el archivo donde está registrarAccion
$id_personal = $_REQUEST['id'];

// Obtener la ruta de la imagen
$sql = "SELECT foto FROM personal WHERE id='$id_personal'";
$result = mysqli_query($conexion, $sql);
$row = mysqli_fetch_assoc($result);

if ($row) {
    $rutaImagen = $row['foto'];
    // Eliminar el registro de la base de datos
    $sql = "DELETE FROM personal WHERE id='$id_personal'";
    $result = mysqli_query($conexion, $sql);

    if ($result) {
        // Eliminar la imagen del servidor
        if (file_exists($rutaImagen)) {
            unlink($rutaImagen);

            // Agregar notificación
            $mensaje = "Personal eliminado";
            
            agregarNotificacion($conexion, $id_personal, $mensaje);
        }
        
        header('location: ../vistas/nomina.php');
    } else {
        $_SESSION["estatus"] = "Error al eliminar el registro de la base de datos.";
        header('location: ../vistas/nomina.php');
    }
} else {
    $_SESSION["estatus"] = "No se encontró el registro del personal.";
    header('location: ../vistas/nomina.php');
}
?>