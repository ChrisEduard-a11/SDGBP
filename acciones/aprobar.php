<?php
session_start();
include('../conexion.php');
include('../models/bitacora.php');

// Validar parámetros
if (!isset($_POST['usuario_id']) || empty($_POST['usuario_id'])) {
    $_SESSION['estatus'] = 'error';
    $_SESSION['mensaje'] = 'ID de usuario no proporcionado.';
    header("Location: ../vistas/usuarios_a.php");
    exit();
}

$id_usuario = (int)$_POST['usuario_id'];

// Obtener nombre de usuario para notificar
$sql_info = "SELECT usuario FROM usuario WHERE id_usuario = $id_usuario";
$resultado = mysqli_query($conexion, $sql_info);
$nombre_usuario = "";
if ($row = mysqli_fetch_assoc($resultado)) {
    $nombre_usuario = $row['usuario'];
}

// Aprobar al usuario (aprobado = 1)
$sql = "UPDATE usuario SET aprobado = 1 WHERE id_usuario = $id_usuario";
if (mysqli_query($conexion, $sql)) {
    $_SESSION['estatus'] = 'success';
    $_SESSION['mensaje'] = "Usuario $nombre_usuario ha sido aprobado exitosamente y ya puede iniciar sesión.";
    registrarAccion($conexion, "Aprobación de Usuario ($nombre_usuario)", $_SESSION['id']);
} else {
    $_SESSION['estatus'] = 'error';
    $_SESSION['mensaje'] = 'Error al aprobar el usuario: ' . mysqli_error($conexion);
}

header("Location: ../vistas/usuarios_a.php");
exit();
?>
