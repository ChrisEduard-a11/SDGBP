<?php
session_start();
include('../models/bitacora.php');

// Verificación Estricta y Despejada de Permisos Core
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    die("Infracción Crítica Detectada CPT. Reportando y Abortando ejecución.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['archivo'])) {
    
    // Limpieza agresiva del nombre usando BaseName para extinguir Path Traversals o Salidas Directivas ("../../") - Hack Preventivo
    $archivo_securizado = basename($_POST['archivo']); 
    
    if (empty($archivo_securizado)) {
        $_SESSION['estatus'] = "error";
        $_SESSION['mensaje'] = "Variables POST huérfanas o dañadas en tránsito HTTP.";
        header("Location: ../vistas/gestionar_flyers.php");
        exit();
    }

    $ruta_real_local = '../img/flyers/' . $archivo_securizado;
    
    // Comprobar la existencia del archivo ANTES de borrar evitando errores y bloqueos fatales OS (Operating System)
    if (file_exists($ruta_real_local) && is_file($ruta_real_local)) {
        
        if (unlink($ruta_real_local)) {
            // Procedimiento Terminado -> Auditoria Tracker On
            require_once('../conexion.php');
            if (function_exists('registrarAccion')) {
                $accion_bitacora = 'Eliminó Flyer Publicitario - Archivo: ' . $archivo_securizado;
                @registrarAccion($conexion, $accion_bitacora, $_SESSION['id']);
            }
            $_SESSION['estatus'] = "success";
            $_SESSION['mensaje'] = "Ecosistema purgado. El Banner fue eliminado y retirado de la web para siempre.";
        } else {
            $_SESSION['estatus'] = "error";
            $_SESSION['mensaje'] = "Error Operacional 101. Falla al borrar fichero del disco duro físico. Contacte Soporte Kernel.";
        }
    } else {
        $_SESSION['estatus'] = "error";
        $_SESSION['mensaje'] = "Phantom Error 404: El archivo ya no existe. Fue modificado en otro contexto.";
    }
    
    header("Location: ../vistas/gestionar_flyers.php");
    exit();
    
} else {
    header("Location: ../vistas/gestionar_flyers.php");
    exit();
}
