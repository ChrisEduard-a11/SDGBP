<?php
session_start();
include("../conexion.php");
include("../models/bitacora.php"); // Incluir el archivo de manejo de la bitácora

// Verificar si el usuario está autenticado
$id_usuario = $_SESSION['id'] ?? null;


if ($id_usuario) {
    registrarAccion($conexion, 'Fin de Sesión', $id_usuario);
    
    // Limpiar el session_token para permitir inicio de sesión inmediato
    $clear_session_sql = "UPDATE usuario SET session_token = NULL, ultima_actividad = NULL WHERE id_usuario = '$id_usuario'";
    mysqli_query($conexion, $clear_session_sql);
}

// Destruir la sesión
session_unset();
session_destroy();

// Redirigir al login
header("Location: ../vistas/login.php");
exit();
?>