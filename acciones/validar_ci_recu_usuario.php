<?php
session_start();
include('../conexion.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cedula = $_POST['cedula'];

    // Buscar el usuario por cédula
    $sql = "SELECT id_usuario, usuario, correo, telegram_id, pregunta, pregunta2 FROM usuario WHERE cedula = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $cedula);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Guardar datos temporales en sesión para el siguiente paso
        $_SESSION['id_usuario'] = $row['id_usuario'];
        $_SESSION['usuario'] = $row['usuario']; 
        $_SESSION['correo'] = $row['correo'];
        $_SESSION['telegram_id'] = $row['telegram_id'];
        $_SESSION['pregunta'] = $row['pregunta'];
        $_SESSION['pregunta2'] = $row['pregunta2'];
        $_SESSION['recuperar_modo'] = 'usuario'; // Modo recuperación de usuario

        header("Location: ../vistas/seleccionar_meto_recu.php");
        exit();
    } else {
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "La cédula ingresada no coincide con ningún usuario registrado.";
        header("Location: ../vistas/recuperar_usuario.php");
        exit();
    }
}
