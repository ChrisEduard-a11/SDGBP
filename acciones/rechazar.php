<?php
session_start();
include('../conexion.php');
include('../models/bitacora.php');

// Validar parámetros
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['estatus'] = 'error';
    $_SESSION['mensaje'] = 'ID de usuario no proporcionado.';
    header("Location: ../vistas/usuarios_a.php");
    exit();
}

$id_usuario = (int)$_GET['id'];

// Obtener datos del usuario antes de eliminar para la bitácora
$sql_info = "SELECT nombre, usuario, foto FROM usuario WHERE id_usuario = $id_usuario";
$resultado = mysqli_query($conexion, $sql_info);

if ($row = mysqli_fetch_assoc($resultado)) {
    $usuario = $row['usuario'];
    $foto = $row['foto'];
    
    // Rechazar (eliminar de la BD)
    $sql_delete = "DELETE FROM usuario WHERE id_usuario = $id_usuario";
    if (mysqli_query($conexion, $sql_delete)) {
        // Eliminar foto de perfil si no es la por defecto
        if (!empty($foto) && file_exists($foto) && strpos($foto, 'default_profile.png') === false) {
            unlink($foto);
        }

        $_SESSION['estatus'] = 'success';
        $_SESSION['mensaje'] = "El usuario $usuario ha sido rechazado y su solicitud fue eliminada.";
        registrarAccion($conexion, "Rechazo de Usuario ($usuario)", $_SESSION['id']);
    } else {
        $_SESSION['estatus'] = 'error';
        $_SESSION['mensaje'] = 'Error al rechazar el usuario: ' . mysqli_error($conexion);
    }
} else {
    $_SESSION['estatus'] = 'error';
    $_SESSION['mensaje'] = 'La solicitud no existe o ya fue procesada anteriormente.';
}

header("Location: ../vistas/usuarios_a.php");
exit();
?>
