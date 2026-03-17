<?php
session_start();
include('../conexion.php');

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Verificar el token
    $sql = "SELECT * FROM usuario WHERE token = '$token'";
    $result = mysqli_query($conexion, $sql);
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

    if ($row) {
        // Desbloquear el usuario
        $sql_desbloquear = "UPDATE usuario SET intentos = 0, token = NULL WHERE token = '$token'";
        mysqli_query($conexion, $sql_desbloquear);

        $_SESSION['estatus'] = 'success';
        $_SESSION['mensaje'] = 'Usuario desbloqueado exitosamente.';
    } else {
        $_SESSION['estatus'] = 'error';
        $_SESSION['mensaje'] = 'Token inválido.';
    }
} else {
    $_SESSION['estatus'] = 'error';
    $_SESSION['mensaje'] = 'Token no proporcionado.';
}

header("Location: ../vistas/login.php");
exit();
?>