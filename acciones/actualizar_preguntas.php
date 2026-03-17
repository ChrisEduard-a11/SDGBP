<?php
session_start();
require_once("../conexion.php");

$usuario_id = $_SESSION['id_usuario']; // Obtén el ID del usuario actual
$pregunta1 = $_POST['pregunta1'];
$respuesta1 = sha1($_POST['respuesta1']); // Encriptar la respuesta con SHA-1
$pregunta2 = $_POST['pregunta2'];
$respuesta2 = sha1($_POST['respuesta2']); // Encriptar la respuesta con SHA-1

// Actualizar las preguntas de seguridad en la base de datos
$sql = "UPDATE usuario SET pregunta = ?, respuesta = ?, pregunta2 = ?, respuesta2 = ? WHERE id_usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ssssi", $pregunta1, $respuesta1, $pregunta2, $respuesta2, $usuario_id);
if ($stmt->execute()) {
    $_SESSION['estatus'] = "success";
    $_SESSION['mensaje'] = "Preguntas de seguridad actualizadas con éxito.";
    header("Location: ../vistas/login.php");
    exit();
} else {
    $_SESSION['estatus'] = "error";
    $_SESSION['mensaje'] = "Error al actualizar las preguntas de seguridad.";
}
$stmt->close();

header("Location: ../vistas/cambiar_preguntas.php");
exit();
?>