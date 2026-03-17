<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $metodo = $_POST['metodo'];

    if ($metodo == 'correo') {
        header("Location: ../vistas/recu_correo.php");
    } elseif ($metodo == '2fa') {
        header("Location: ../acciones/enviar_2fa_recu.php");
    } elseif ($metodo == 'preguntas') {
        header("Location: ../vistas/pregunta.php");
    } else {
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "Método de recuperación no válido.";
        header("Location: ../vistas/login.php");
    }
    exit();
}
?>