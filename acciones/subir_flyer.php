<?php
session_start();
include('../models/bitacora.php');

// Restricción Ultra-Estricta: Solo Entidades Administradoras pueden interactuar aquí físicamente
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    die("Error 403. Fallo en la Directiva Central de Seguridad. Permisos denegados para manipulación de Assets Core.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['flyer_img'])) {
    
    $file = $_FILES['flyer_img'];
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];
    
    // Check de errores de PHP Nativo de Interfaz
    if ($file_error !== UPLOAD_ERR_OK) {
        $_SESSION['estatus'] = "error";
        $_SESSION['mensaje'] = "Error del Sistema en Transferencia Lógica de Datos HTTP. Code: {$file_error}";
        header("Location: ../vistas/gestionar_flyers.php");
        exit();
    }
    
    // Extraemos limpiamente la extensión con pathinfo para protección inyectada
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    
    // Verificamos si es un vector legal de imagen
    if (!in_array($file_ext, $allowed_exts)) {
        $_SESSION['estatus'] = "error";
        $_SESSION['mensaje'] = "Protocolo de Archivo Denegado. Intentas vulnerar el portal usando ($file_ext). Solo Imágenes!";
        header("Location: ../vistas/gestionar_flyers.php");
        exit();
    }
    
    // Filtro de Inyección Excesiva de Memoria Fija Server (Máx: 5MB -> 5242880 bytes)
    if ($file_size > 5242880) {
        $_SESSION['estatus'] = "error";
        $_SESSION['mensaje'] = "Peso Extremo Localizado. El flyer excede los 5 MB pre-asginados al disco.";
        header("Location: ../vistas/gestionar_flyers.php");
        exit();
    }
    
    // Entorno Seguro: Instanciamos el directorio en caso de que no lo haya por purgas 
    $upload_dir = '../img/flyers/';
    if (!is_dir($upload_dir)) {
        @mkdir($upload_dir, 0777, true); // Crear de forma silenciosa si faltan.
    }
    
    // Nombre único aleatorio usando HASH criptografico de la hora MD5 para el archivo (Collision Preventation)
    $clean_hash = substr(hash('sha256', uniqid(rand(), true)), 0, 10);
    $new_file_name_secure = 'Banner_' . date("Ymd_His") . '_' . $clean_hash . '.' . $file_ext;
    
    $destination = $upload_dir . $new_file_name_secure;
    
    // Transferencia Interna en el Servidor hacia el entorno de Apache
    if (move_uploaded_file($file_tmp, $destination)) {
        // Enlazar Bitácora para rastreabilidad y auditorias operacionales
        if (function_exists('registrarAccion')) {
            require_once('../conexion.php'); 
            $accion_bitacora = 'Agregó Flyer Publicitario - Archivo: ' . $new_file_name_secure;
            @registrarAccion($conexion, $accion_bitacora, $_SESSION['id']);
        }
        
        $_SESSION['estatus'] = "success";
        $_SESSION['mensaje'] = "Flyer cargado a la memoria externa e inyectado en el portal index correctamente.";
    } else {
        $_SESSION['estatus'] = "error";
        $_SESSION['mensaje'] = "Excepción irrecuperable de guardado (Move_Uploaded_File Falló en Kernel).";
    }
    
    header("Location: ../vistas/gestionar_flyers.php");
    exit();
    
} else {
    // Escaneo fantasma, denegado, aborto
    header("Location: ../vistas/gestionar_flyers.php");
    exit();
}
