<?php
session_start();
include('../conexion.php');
include('../models/bitacora.php');

// Verificar si el usuario está logueado
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

$rutaCarpeta = "../comprobantes/";

// Leer los datos JSON de la entrada estándar
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['archivos']) && is_array($data['archivos'])) {
    $archivos_eliminados = 0;
    $archivos_fallidos = 0;
    $nombres_eliminados = [];

    foreach ($data['archivos'] as $archivo) {
        // Sanear el nombre del archivo para evitar Directory Traversal
        $archivo_saneado = basename($archivo);
        $rutaCompleta = $rutaCarpeta . $archivo_saneado;

        // Verificar si existe y si es un archivo (no un directorio)
        if (file_exists($rutaCompleta) && is_file($rutaCompleta)) {
            if (unlink($rutaCompleta)) {
                $archivos_eliminados++;
                $nombres_eliminados[] = $archivo_saneado;
            } else {
                $archivos_fallidos++;
            }
        } else {
            $archivos_fallidos++;
        }
    }

    if ($archivos_eliminados > 0) {
        // Registrar en bitácora si es necesario
        $nombres_str = implode(", ", $nombres_eliminados);
        $accion_bitacora = 'Eliminó Comprobantes (Físico) - Cantidad: ' . $archivos_eliminados . ' | Archivos: ' . $nombres_str;
        registrarAccion($conexion, $accion_bitacora, $_SESSION['id']);
        
        echo json_encode([
            'success' => true, 
            'message' => "Se han eliminado $archivos_eliminados archivo(s) correctamente." . ($archivos_fallidos > 0 ? " ($archivos_fallidos fallaron)." : ""),
            'eliminados' => $archivos_eliminados
        ]);
    } else {
         echo json_encode([
            'success' => false, 
            'message' => "No se pudo eliminar ningún archivo. Verifique permisos o si el archivo existe."
        ]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'No se recibieron archivos para eliminar.']);
}
?>
