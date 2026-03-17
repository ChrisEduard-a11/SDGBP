<?php
session_start();
require_once("../conexion.php");

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

        // Establecer variables de sesión definitivas para nueva_clave.php
        $_SESSION["id"] = $row['id_usuario'];
        $_SESSION["user"] = $row['usuario'];
        $_SESSION['identidad_verificada'] = true; // Bandera para omitir doble 2FA
        
        $_SESSION["estatus"] = "success";
        $_SESSION["mensaje"] = "Identidad verificada con éxito.";
        header("Location: ../vistas/nueva_clave.php");
        exit();
    } else {
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "El código ingresado es incorrecto o ha expirado.";
        header("Location: ../vistas/confirmar_2fa_recu.php");
        exit();
    }
}
