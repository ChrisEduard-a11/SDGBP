<?php
session_start();
include('../conexion.php');

$usuario = $_POST["usuario"];

// Consulta para buscar al usuario por nombre de usuario
$sql = "SELECT * FROM usuario WHERE BINARY usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    $_SESSION["estatus"] = "error";
    $_SESSION["mensaje"] = "El nombre de usuario ingresado no está registrado";
    header("Location: ../vistas/recuperar.php");
    exit();
} else {
    $row = $resultado->fetch_assoc();
    $_SESSION["id_usuario"] = $row['id_usuario'];
    $_SESSION["usuario"] = $row['usuario'];
    $_SESSION["correo"] = $row['correo'];
    $_SESSION["telegram_id"] = $row['telegram_id'];
    $_SESSION['recuperar_modo'] = 'clave'; // Modo recuperación de contraseña
    $_SESSION["pregunta"] = $row['pregunta'];
    $_SESSION["respuesta"] = $row['respuesta'];
    $_SESSION["pregunta2"] = $row['pregunta2'];
    $_SESSION["respuesta2"] = $row['respuesta2'];

    header("Location: ../vistas/seleccionar_meto_recu.php");
    exit();
}
?>