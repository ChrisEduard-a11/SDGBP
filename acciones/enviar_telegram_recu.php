<?php
session_start();
require_once("../conexion.php");
require_once("telegram_helper.php");

if (empty($_SESSION["usuario"]) || empty($_SESSION["id_usuario"]) || empty($_SESSION["telegram_id"])) {
    $_SESSION["estatus"] = "error";
    $_SESSION["mensaje"] = "Información de sesión insuficiente para recuperación vía Telegram.";
    header("Location: ../vistas/seleccionar_meto_recu.php");
    exit();
}

$id_usuario = $_SESSION["id_usuario"];
$usuario_nombre = $_SESSION["usuario"];
$chat_id = $_SESSION["telegram_id"];

// Generar código de 6 dígitos
$codigo = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

// Guardar en la base de datos
$sql = "UPDATE usuario SET codigo_verificacion = ? WHERE id_usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("si", $codigo, $id_usuario);

if ($stmt->execute()) {
    // Enviar vía Telegram
    if (enviarOTPTelegram($chat_id, $codigo, $usuario_nombre)) {
        header("Location: ../vistas/confirmar_2fa_recu.php");
        exit();
    } else {
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "No se pudo enviar el código vía Telegram. Verifique la configuración del Bot o su Chat ID.";
        header("Location: ../vistas/seleccionar_meto_recu.php");
        exit();
    }
} else {
    $_SESSION["estatus"] = "error";
    $_SESSION["mensaje"] = "Error al generar el código de seguridad.";
    header("Location: ../vistas/seleccionar_meto_recu.php");
    exit();
}
?>
