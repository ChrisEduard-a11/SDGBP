<?php
session_start();
include('../conexion.php');
include('mail_helper.php');

$user = $_SESSION['usuario'];
$respuesta = $_POST['respuesta'];
$respuesta_encrip = sha1($respuesta);
$respuesta2 = $_POST['respuesta2'];
$respuesta_encrip2 = sha1($respuesta2);

$sql= "SELECT * FROM usuario WHERE usuario = ? and respuesta = ? and respuesta2 = ?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("sss", $user, $respuesta_encrip, $respuesta_encrip2);
$stmt->execute();
$resultado = $stmt->get_result();
if ($resultado->num_rows == 0) {
    $_SESSION["estatus"] = "error";
    $_SESSION["mensaje"] = "Respuestas Incorrectas";
    header("Location: ../vistas/pregunta.php");
    exit();
} else {
    $row = $resultado->fetch_assoc();
    $_SESSION["id_usuario"] = $row['id_usuario'];
    $_SESSION["usuario"] = $row['usuario'];
    if (isset($_SESSION['recuperar_modo']) && $_SESSION['recuperar_modo'] === 'usuario') {
        $correo_dest = $_SESSION["correo"];
        if (enviarUsuarioCorreo($correo_dest, $row['usuario'])) {
            $_SESSION["mensaje"] = "Tu nombre de usuario ha sido enviado a tu correo electrónico.";
            $_SESSION["estatus"] = "success";
        } else {
            $_SESSION["mensaje"] = "Tu usuario es: " . $row['usuario'] . " (Error al enviar correo).";
            $_SESSION["estatus"] = "info";
        }
        header("Location: ../vistas/login.php");
    } else {
        // Solo establecer para el flujo de cambio de clave
        $_SESSION["id"] = $row['id_usuario'];
        $_SESSION["user"] = $row['usuario'];
        $_SESSION['identidad_verificada'] = true;
        header("Location: ../vistas/nueva_clave.php");
    }
    exit();
}
?>