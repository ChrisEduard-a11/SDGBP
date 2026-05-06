<?php
session_start();
require_once __DIR__ . '/../conexion.php';

if (isset($_FILES['archivoBD']) && $_FILES['archivoBD']['error'] == 0) {
    $archivo_tmp = $_FILES['archivoBD']['tmp_name'];
    $sql_content = file_get_contents($archivo_tmp);
    
    if (empty($sql_content)) {
        $_SESSION['mensaje'] = "El archivo SQL está vacío o no se pudo leer.";
        $_SESSION['estatus'] = "warning";
    } else {
        mysqli_query($conexion, "SET FOREIGN_KEY_CHECKS=0");
        
        $error_count = 0;
        
        if (mysqli_multi_query($conexion, $sql_content)) {
            do {
                // Verificar si hay errores en esta consulta u otras
                if ($res = mysqli_store_result($conexion)) {
                    mysqli_free_result($res);
                }
            } while (mysqli_more_results($conexion) && mysqli_next_result($conexion));
            
            if (mysqli_errno($conexion) !== 0) {
                $error_count++;
            }
        } else {
            $error_count++;
        }
        
        mysqli_query($conexion, "SET FOREIGN_KEY_CHECKS=1");
        
        if ($error_count === 0) {
            $_SESSION['mensaje'] = "Base de datos importada correctamente.";
            $_SESSION['estatus'] = "success";
        } else {
            $_SESSION['mensaje'] = "Error al ejecutar algunas consultas de la base de datos.";
            $_SESSION['estatus'] = "danger";
        }
    }
} else {
    $_SESSION['mensaje'] = "No se seleccionó ningún archivo o hubo un error en la subida.";
    $_SESSION['estatus'] = "warning";
}

header("Location: ../vistas/backup_db.php");
exit;
?>