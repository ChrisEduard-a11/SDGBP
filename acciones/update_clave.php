<?php
session_start();
include('../conexion.php');
include('../models/bitacora.php');

// Obtener los datos del formulario
$recu_clave01 = $_POST['clave'];
$clavee = sha1($recu_clave01); 
$recu_clave02 = $_POST['clave1'];
$claveee = sha1($recu_clave02); 

// Verificar que las contraseñas coincidan
if ($clavee === $claveee) {
    $id_usuario = $_SESSION['id_usuario'];
    $sql = "UPDATE usuario SET clave='$claveee', fecha_cambio_clave=CURRENT_DATE WHERE id_usuario='$id_usuario'";
    $result = mysqli_query($conexion, $sql);

    if ($result) {
        $_SESSION["estatus"] = "success";
        $_SESSION["mensaje"] = "Contraseña Modificada";
        registrarAccion($conexion, 'Cambio de Contraseña', $id_usuario);
        header("Location: ../vistas/login.php");
    } else {
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "Error al modificar la contraseña";
        header("Location: ../vistas/nueva_clave.php");
    }
} else {
    $_SESSION["estatus"] = "error";
    $_SESSION["mensaje"] = "Combinación Incorrecta";
    header("Location: ../vistas/nueva_clave.php");
}
exit();
?>