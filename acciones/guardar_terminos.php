<?php
session_start();
require_once("../conexion.php");

// Solo administradores
if ($_SESSION["tipo"] != "admin") {
    header("Location: ../vistas/denegado_a.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nuevos_terminos = $_POST['terminos'] ?? '';
    $nuevo_estado = isset($_POST['status']) ? '1' : '0';

    // No permitir términos vacíos si el sistema está activo
    if ($nuevo_estado == '1' && empty(trim($nuevos_terminos))) {
        header("Location: ../vistas/editar_terminos.php?msg=error");
        exit();
    }

    // Actualizar en la base de datos
    // El campo ultima_actualizacion se actualiza solo por el DEFAULT CURRENT_TIMESTAMP con ON UPDATE CURRENT_TIMESTAMP
    // Actualizar Contenido
    $sql = "UPDATE ajustes_sistema SET valor = ? WHERE clave = 'terminos_condiciones'";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "s", $nuevos_terminos);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Actualizar Estado
    $sql_status = "UPDATE ajustes_sistema SET valor = ? WHERE clave = 'terminos_status'";
    $stmt_s = mysqli_prepare($conexion, $sql_status);
    mysqli_stmt_bind_param($stmt_s, "s", $nuevo_estado);
    
    if (mysqli_stmt_execute($stmt_s)) {
        // Éxito
        mysqli_stmt_close($stmt_s);
        header("Location: ../vistas/editar_terminos.php?msg=success");
        exit();
    } else {
        // Error
        mysqli_stmt_close($stmt);
        header("Location: ../vistas/editar_terminos.php?msg=error");
        exit();
    }
} else {
    header("Location: ../vistas/editar_terminos.php");
    exit();
}
?>
