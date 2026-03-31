<?php
require_once("../conexion.php");

/**
 * Obtiene todas las notificaciones del sistema (ideal para admin).
 */
function obtenerTodasLasNotificaciones($conexion) {
    // Left join para obtener el nombre del usuario, o global si es null
    $sql = "SELECT n.*, u.nombre as nombre_usuario, u.usuario as username
            FROM notificaciones n
            LEFT JOIN usuario u ON n.usuario_id = u.id_usuario
            ORDER BY n.fecha DESC LIMIT 100";
    
    $resultado = mysqli_query($conexion, $sql);
    $notificaciones = [];
    
    if ($resultado && mysqli_num_rows($resultado) > 0) {
        while ($fila = mysqli_fetch_assoc($resultado)) {
            // Si el nombre de usuario es nulo, significa que es global
            if (empty($fila['nombre_usuario'])) {
                $fila['nombre_usuario'] = "Todos los Usuarios (Global)";
            } else {
                $fila['nombre_usuario'] = $fila['nombre_usuario'] . " (" . $fila['username'] . ")";
            }
            $notificaciones[] = $fila;
        }
    }
    return $notificaciones;
}

/**
 * Obtiene las notificaciones de un usuario específico, y también las globales.
 */
function obtenerNotificaciones($conexion, $usuario_id) {
    // Filtramos las notificaciones cuyo usuario_id es el del usuario o NULL (global)
    $sql = "SELECT * FROM notificaciones 
            WHERE usuario_id = ? OR usuario_id IS NULL 
            ORDER BY fecha DESC LIMIT 50";
    
    $notificaciones = [];
    if ($stmt = mysqli_prepare($conexion, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $usuario_id);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $notificaciones[] = $fila;
        }
        mysqli_stmt_close($stmt);
    }
    return $notificaciones;
}

/**
 * Obtiene solo las notificaciones NO leídas de un usuario específico.
 */
function obtenerNotificacionesNoLeidas($conexion, $usuario_id) {
    $sql = "SELECT * FROM notificaciones 
            WHERE (usuario_id = ? OR usuario_id IS NULL) AND leida = 0 
            ORDER BY fecha DESC LIMIT 20";
    
    $notificaciones = [];
    if ($stmt = mysqli_prepare($conexion, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $usuario_id);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $notificaciones[] = $fila;
        }
        mysqli_stmt_close($stmt);
    }
    return $notificaciones;
}

/**
 * Marca todas las notificaciones de un usuario como leídas.
 */
function marcarNotificacionesComoLeidas($conexion, $usuario_id) {
    $sql = "UPDATE notificaciones SET leida = 1 WHERE (usuario_id = ? OR usuario_id IS NULL) AND leida = 0";
    
    if ($stmt = mysqli_prepare($conexion, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $usuario_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }
    return false;
}
?>
