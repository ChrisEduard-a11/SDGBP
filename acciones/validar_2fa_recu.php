<?php
session_start();
require_once("../conexion.php");
require_once("mail_helper.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_SESSION["usuario"])) {
        header("Location: ../vistas/denegado_a.php");
        exit();
    }

    $usuario_nombre = $_SESSION["usuario"];
    $codigo_ingresado = $_POST['codigo'];

    // Verificar el código en la base de datos
    $sql = "SELECT id_usuario, usuario FROM usuario WHERE BINARY usuario = ? AND codigo_verificacion = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ss", $usuario_nombre, $codigo_ingresado);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $row = $resultado->fetch_assoc();
        
        // Limpiar el código para que no sea reutilizable
        $sql_clear = "UPDATE usuario SET codigo_verificacion = NULL WHERE id_usuario = ?";
        $stmt_clear = $conexion->prepare($sql_clear);
        $stmt_clear->bind_param("i", $row['id_usuario']);
        $stmt_clear->execute();

        if (isset($_SESSION['recuperar_modo']) && $_SESSION['recuperar_modo'] === 'usuario') {
            // Enviar correo con el usuario
            $correo_dest = $_SESSION["correo"];
            if (enviarUsuarioCorreo($correo_dest, $row['usuario'])) {
                $_SESSION["mensaje"] = "Tu nombre de usuario ha sido enviado a tu correo electrónico.";
                $_SESSION["estatus"] = "success"; 
            } else {
                $_SESSION["mensaje"] = "Tu usuario es: " . $row['usuario'] . " (No pudimos enviar el correo).";
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
    } else {
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "El código ingresado es incorrecto o ha expirado.";
        header("Location: ../vistas/confirmar_2fa_recu.php");
        exit();
    }
}
