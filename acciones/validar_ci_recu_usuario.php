<?php
session_start();
include('../conexion.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cedula = $_POST['cedula'];

    // Buscar el usuario por cédula
    $sql = "SELECT correo, nombre FROM usuario WHERE cedula = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $cedula);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Guardar datos temporales en sesión para el siguiente paso
        $_SESSION['temp_recu_cedula'] = $cedula;
        $_SESSION['temp_recu_correo_real'] = $row['correo'];
        $_SESSION['temp_recu_nombre'] = $row['nombre'];

        header("Location: ../vistas/verificar_email_usuario.php");
        exit();
    } else {
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "La cédula ingresada no coincide con ningún usuario registrado.";
        header("Location: ../vistas/recuperar_usuario.php");
        exit();
    }
}
