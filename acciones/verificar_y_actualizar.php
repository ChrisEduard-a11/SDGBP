<?php
session_start();
require_once("../conexion.php");
require_once('../models/bitacora.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo_ingresado = $_POST['codigo'];
    $id_usuario = $_SESSION['id'] ?? $_SESSION['id_usuario'];

    // Verificar el código en la base de datos
    $sql = "SELECT codigo_verificacion, clave FROM usuario WHERE id_usuario = '$id_usuario'";
    $result = mysqli_query($conexion, $sql);
    $row = mysqli_fetch_assoc($result);

    if ($row && $row['codigo_verificacion'] === $codigo_ingresado) {
        // Código correcto: La clave ya fue hasheada y guardada temporalmente en el paso anterior.
        // Ahora solo limpiamos el código y actualizamos la fecha de cambio.
        $sql_final = "UPDATE usuario SET codigo_verificacion = NULL, fecha_cambio_clave = CURRENT_DATE WHERE id_usuario = '$id_usuario'";
        
        if (mysqli_query($conexion, $sql_final)) {
            $_SESSION["estatus"] = "success";
            $_SESSION["mensaje"] = "Contraseña Actualizada con Éxito";
            registrarAccion($conexion, 'Cambio de Contraseña (2FA)', $id_usuario);
            
            // Redirigir al login para que inicie sesión con su nueva clave
            header("Location: ../vistas/login.php");
            exit;
        } else {
            $_SESSION["estatus"] = "error";
            $_SESSION["mensaje"] = "Error al confirmar los cambios.";
            header("Location: ../vistas/confirmar_codigo.php");
            exit;
        }
    } else {
        // Código incorrecto
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "El código ingresado es incorrecto.";
        header("Location: ../vistas/confirmar_codigo.php");
        exit;
    }
}
?>
