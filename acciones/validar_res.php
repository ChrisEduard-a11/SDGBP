<?php
session_start();
include('../conexion.php');

$user = $_SESSION['usuario'];
$respuesta = $_POST['respuesta'];
$respuesta_encrip = sha1($respuesta);
$respuesta2 = $_POST['respuesta2'];
$respuesta_encrip2 = sha1($respuesta2);

$sql= "SELECT * FROM usuario WHERE usuario = '$user' and respuesta ='$respuesta_encrip' and respuesta2 ='$respuesta_encrip2'";

$resultado = mysqli_query($conexion, $sql);
if (mysqli_num_rows($resultado) == 0) {
    $_SESSION["estatus"] = "error";
    $_SESSION["mensaje"] = "Respuestas Incorrectas";
    header("Location: ../vistas/pregunta.php");
    exit();
} else {
    $row = mysqli_fetch_assoc($resultado);
    $_SESSION["id_usuario"] = $row['id_usuario'];
    $_SESSION["usuario"] = $row['usuario'];
    // Variables compatibles con nueva_clave.php y solicitar_cambio_clave.php
    $_SESSION["id"] = $row['id_usuario'];
    $_SESSION["user"] = $row['usuario'];
    $_SESSION['identidad_verificada'] = true; // Bandera para omitir doble 2FA
    header("Location: ../vistas/nueva_clave.php");
    exit();
}
?>