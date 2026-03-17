<?php
session_start();
include('../conexion.php');
include_once('../models/bitacora.php'); // Asegúrate de incluir el archivo donde está registrarAccion

// Aprobar usuario
if (isset($_POST['aprobar'])) {
    $usuario_id = $_POST['usuario_id'];
    $sql = "UPDATE usuario SET aprobado = 1 WHERE id_usuario = $usuario_id";
    if (mysqli_query($conexion, $sql)) {
        // Registrar en bitácora
        if (isset($_SESSION['id'])) {
            registrarAccion($conexion, 'Aprobar Usuario', $_SESSION['id']);
        }
        $_SESSION["estatus"] = "success";
        $_SESSION["mensaje"] = "Aprobación de usuario exitosa.";
        header("Location: ../vistas/usuarios_a.php");
        exit();
    } else {
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "Error al aprobar usuario: " . mysqli_error($conexion);
        header("Location: ../vistas/usuarios_a.php");
        exit();
    }
}

// Rechazar usuario
if (isset($_POST['rechazar'])) {
    $usuario_id = $_POST['usuario_id'];
    $sql = "DELETE FROM usuario WHERE id_usuario = $usuario_id";
    if (mysqli_query($conexion, $sql)) {
        // Registrar en bitácora
        if (isset($_SESSION['id'])) {
            registrarAccion($conexion, 'Rechazar Usuario', $_SESSION['id']);
        }
        $_SESSION["estatus"] = "success";
        $_SESSION["mensaje"] = "El usuario ha sido rechazado y eliminado correctamente.";
        header("Location: ../vistas/usuarios_a.php");
        exit();
    } else {
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "Error al rechazar usuario: " . mysqli_error($conexion);
        header("Location: ../vistas/usuarios_a.php");
        exit();
    }
}
?>