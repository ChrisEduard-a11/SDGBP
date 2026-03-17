<?php
/**
 * SDGBP - Limpieza Automática de Comprobantes
 * Este script elimina archivos de comprobantes con más de 60 días de antigüedad
 * para optimizar el espacio en el servidor (especialmente en InfinityFree).
 */

$directorio = __DIR__ . "/../uploads/comprobantes/";
$dias_limite = 15;
$segundos_limite = $dias_limite * 24 * 60 * 60;
$ahora = time();

if (!is_dir($directorio)) {
    die("Directorio no encontrado: $directorio");
}

$archivos = scandir($directorio);
$eliminados = 0;

foreach ($archivos as $archivo) {
    if ($archivo === "." || $archivo === "..") continue;

    $ruta_completa = $directorio . $archivo;
    
    // Ignorar si no es un archivo
    if (!is_file($ruta_completa)) continue;

    // Obtener fecha de modificación del archivo
    $fecha_archivo = filemtime($ruta_completa);
    $antiguedad = $ahora - $fecha_archivo;

    if ($antiguedad > $segundos_limite) {
        if (unlink($ruta_completa)) {
            $eliminados++;
        }
    }
}

echo "Limpieza completada. Archivos eliminados: $eliminados\n";
?>
