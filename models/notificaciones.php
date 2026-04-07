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
 * Obtiene las notificaciones de un usuario específico, y también las globales o de su rol.
 */
function obtenerNotificaciones($conexion, $usuario_id, $tipo_usuario = null) {
    // Filtramos las notificaciones cuyo usuario_id es el del usuario, NULL (global) 
    // o cuyo tipo_usuario_destino sea el rol del usuario
    $sql = "SELECT * FROM notificaciones 
            WHERE usuario_id = ? 
               OR (usuario_id IS NULL AND tipo_usuario_destino IS NULL)
                OR (LOWER(tipo_usuario_destino) = LOWER(?))
                OR (LOWER(tipo_usuario_destino) = 'staff' AND LOWER(?) IN ('admin', 'cont'))
            ORDER BY fecha DESC LIMIT 50";
    
    $notificaciones = [];
    if ($stmt = mysqli_prepare($conexion, $sql)) {
        mysqli_stmt_bind_param($stmt, "iss", $usuario_id, $tipo_usuario, $tipo_usuario);
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
 * Obtiene solo las notificaciones NO leídas de un usuario específico o de su rol.
 */
function obtenerNotificacionesNoLeidas($conexion, $usuario_id, $tipo_usuario = null) {
    // Normalizar role para la consulta
    $role = strtolower($tipo_usuario);
    
    $sql = "SELECT * FROM notificaciones 
            WHERE leida = 0 AND (
                usuario_id = ? 
                OR (usuario_id IS NULL AND tipo_usuario_destino IS NULL)
                OR (LOWER(tipo_usuario_destino) = ?)
                OR (LOWER(tipo_usuario_destino) = 'staff' AND ? IN ('admin', 'cont'))
            )
            ORDER BY fecha DESC LIMIT 20";
    
    $notificaciones = [];
    if ($stmt = mysqli_prepare($conexion, $sql)) {
        mysqli_stmt_bind_param($stmt, "iss", $usuario_id, $role, $role);
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
 * Marca las notificaciones de un usuario (o de su rol) como leídas.
 */
function marcarNotificacionesComoLeidas($conexion, $usuario_id, $tipo_usuario = null) {
    $sql = "UPDATE notificaciones SET leida = 1 
            WHERE leida = 0 AND (
                usuario_id = ? 
                OR (usuario_id IS NULL AND tipo_usuario_destino IS NULL)
                OR (LOWER(tipo_usuario_destino) = LOWER(?))
                OR (LOWER(tipo_usuario_destino) = 'staff' AND LOWER(?) IN ('admin', 'cont'))
            )";
    
    if ($stmt = mysqli_prepare($conexion, $sql)) {
        mysqli_stmt_bind_param($stmt, "iss", $usuario_id, $tipo_usuario, $tipo_usuario);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }
    return false;
}

/**
 * Crea una nueva notificación en el sistema.
 * 
 * @param mysqli $conexion Conexión a la BD
 * @param int|null $usuario_id ID del usuario (null para global)
 * @param string $titulo Título de la alerta
 * @param string $mensaje Cuerpo del mensaje
 * @param string $tipo Tipo (success, warning, danger, info)
 * @param string $icono Icono FontAwesome
 * @param int|null $pago_id ID del pago vinculado (opcional)
 * @param string|null $tipo_usuario_destino Rol destinatario (opcional)
 */
function crearNotificacion($conexion, $usuario_id, $titulo, $mensaje, $tipo = 'info', $icono = 'fas fa-bell', $pago_id = null, $tipo_usuario_destino = null) {
    $sql = "INSERT INTO notificaciones (usuario_id, titulo, mensaje, tipo, icono, pago_id, tipo_usuario_destino, leida, fecha) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 0, NOW())";
    
    if ($stmt = mysqli_prepare($conexion, $sql)) {
        mysqli_stmt_bind_param($stmt, "issssis", $usuario_id, $titulo, $mensaje, $tipo, $icono, $pago_id, $tipo_usuario_destino);
        $res = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $res;
    }
    return false;
}

/**
 * Elimina las notificaciones vinculadas a un pago específico (ej. al ser aprobado/rechazado).
 */
function eliminarNotificacionesPorPago($conexion, $pago_id) {
    if (!$pago_id) return false;
    
    $sql = "DELETE FROM notificaciones WHERE pago_id = ?";
    
    if ($stmt = mysqli_prepare($conexion, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $pago_id);
        $res = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $res;
    }
    return false;
}

/**
 * Elimina una notificación específica (borrado manual por el usuario).
 */
function eliminarNotificacion($conexion, $id, $usuario_id, $tipo_usuario = null) {
    if (!$id) return false;
    
    // El usuario solo puede borrar notificaciones que:
    // 1. Le pertenezcan directamente (usuario_id = ?)
    // 2. Sean para su rol (staff / admin / etc)
    $sql = "DELETE FROM notificaciones 
            WHERE id = ? AND (
                usuario_id = ? 
                OR tipo_usuario_destino = ?
                OR (tipo_usuario_destino = 'staff' AND ? IN ('admin', 'cont'))
            )";
    
    if ($stmt = mysqli_prepare($conexion, $sql)) {
        mysqli_stmt_bind_param($stmt, "iiss", $id, $usuario_id, $tipo_usuario, $tipo_usuario);
        $res = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $res;
    }
    return false;
}

/**
 * Elimina TODAS las notificaciones de un usuario (o de su rol).
 */
function eliminarTodasLasNotificaciones($conexion, $usuario_id, $tipo_usuario = null) {
    // Normalizar rol
    $tipo_usuario = strtolower($tipo_usuario);
    
    $sql = "DELETE FROM notificaciones 
            WHERE usuario_id = ? 
               OR tipo_usuario_destino = ?
               OR (tipo_usuario_destino = 'staff' AND ? IN ('admin', 'cont'))";
    
    if ($stmt = mysqli_prepare($conexion, $sql)) {
        mysqli_stmt_bind_param($stmt, "iss", $usuario_id, $tipo_usuario, $tipo_usuario);
        $res = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $res;
    }
    return false;
}

/**
 * Elimina las notificaciones de 'Nuevo Usuario Pendiente' para un usuario específico.
 */
function eliminarNotificacionUsuarioPendiente($conexion, $usuario) {
    if (!$usuario) return false;
    
    // Buscamos notificaciones con el título específico y que mencionen al usuario en el mensaje
    $titulo = 'Nuevo Usuario Pendiente';
    $mensaje_LIKE = "%@$usuario %"; // El espacio previene coincidencias parciales con nombres más largos
    
    $sql = "DELETE FROM notificaciones WHERE titulo = ? AND mensaje LIKE ?";
    
    if ($stmt = mysqli_prepare($conexion, $sql)) {
        mysqli_stmt_bind_param($stmt, "ss", $titulo, $mensaje_LIKE);
        $res = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $res;
    }
    return false;
}
?>
