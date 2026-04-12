<?php
session_start();
require_once("../conexion.php");
require_once("../models/bitacora.php");

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    die("Acceso denegado.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = isset($_POST['status']) ? '1' : '0';
    $texto = mysqli_real_escape_string($conexion, $_POST['texto']);
    $fecha_inicio = mysqli_real_escape_string($conexion, $_POST['fecha_inicio']);

    $queries = [
        "UPDATE config_sistema SET valor = '$status' WHERE clave = 'bienvenida_login_status'",
        "UPDATE config_sistema SET valor = '$texto' WHERE clave = 'bienvenida_login_texto'",
        "UPDATE config_sistema SET valor = '$fecha_inicio' WHERE clave = 'bienvenida_login_fecha_inicio'"
    ];

    $success = true;
    foreach ($queries as $sql) {
        if (!mysqli_query($conexion, $sql)) {
            $success = false;
            break;
        }
    }

    if ($success) {
        if (function_exists('registrarAccion')) {
            $est_lbl = ($status == '1' ? 'Activado' : 'Desactivado');
            $accion_bitacora = 'Actualizó Configuración - Módulo: Aviso de Login | Estatus: ' . $est_lbl;
            @registrarAccion($conexion, $accion_bitacora, $_SESSION['id']);
        }
        $_SESSION['estatus'] = "success";
        $_SESSION['mensaje'] = "Aviso de bienvenida actualizado correctamente.";
    } else {
        $_SESSION['estatus'] = "error";
        $_SESSION['mensaje'] = "Error al actualizar la configuración.";
    }

    header("Location: ../vistas/gestionar_flyers.php");
    exit();
}
?>
