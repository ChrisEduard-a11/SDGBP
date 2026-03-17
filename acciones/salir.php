<?php
session_start();
include("../conexion.php");
include("../models/bitacora.php"); // Incluir el archivo de manejo de la bitácora

// Verificar si el usuario está autenticado
$id_usuario = $_SESSION['id'] ?? null;


if ($id_usuario) {
    registrarAccion($conexion, 'Fin de Sesión', $id_usuario);
}

// Destruir la sesión
session_unset();
session_destroy();

// Redirigir al login
header("Location: ../vistas/login.php");
exit();
?>