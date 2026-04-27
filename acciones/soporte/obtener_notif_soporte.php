<?php
/**
 * obtener_notif_soporte.php
 * Devuelve el conteo de mensajes de soporte no leídos y el detalle
 * para mostrar en la campana de notificaciones del header.
 *
 * Para USUARIOS (upu/cont/inv): mensajes de admin no leídos en sus tickets.
 * Para ADMINS: mensajes de usuarios/guests no leídos en cualquier ticket.
 */
error_reporting(0);
if (session_status() === PHP_SESSION_NONE) session_start();
require_once("../../conexion.php");
header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['count' => 0, 'items' => []]);
    exit;
}

$uid  = (int) $_SESSION['id'];
$tipo = strtolower($_SESSION['tipo'] ?? '');

if ($tipo === 'admin') {
    // ── ADMIN: mensajes NO leídos que vienen de usuarios/invitados ──────────
    $sql = "
        SELECT
            t.id_ticket,
            t.nombre_visitante,
            u.nombre AS nombre_user,
            COUNT(m.id_mensaje) AS cnt,
            MAX(m.fecha_envio)  AS ultima
        FROM soporte_mensajes m
        INNER JOIN soporte_tickets t ON m.id_ticket = t.id_ticket
        LEFT  JOIN usuario u         ON t.id_usuario = u.id_usuario
        WHERE m.leido = 0
          AND m.enviado_por NOT IN (
                SELECT CAST(id_usuario AS CHAR)
                FROM   usuario
                WHERE  tipos = 'admin'
              )
          AND m.enviado_por != 'admin'
          AND t.estado != 'Resuelto'
        GROUP BY t.id_ticket
        ORDER BY ultima DESC
        LIMIT 15
    ";
    $res = mysqli_query($conexion, $sql);

    $items = [];
    $total = 0;
    while ($row = mysqli_fetch_assoc($res)) {
        $nombre = $row['nombre_user'] ?? $row['nombre_visitante'] ?? 'Invitado';
        $cnt    = (int) $row['cnt'];
        $total += $cnt;
        $items[] = [
            'ticket'  => $row['id_ticket'],
            'nombre'  => $nombre,
            'mensaje' => $cnt === 1
                ? "$nombre envió 1 mensaje nuevo"
                : "$nombre envió $cnt mensajes nuevos",
            'url'     => '../vistas/gestionar_tickets.php',
            'icono'   => 'fas fa-headset',
            'tipo'    => 'primary',
        ];
    }

} else {
    // ── USUARIO (upu / cont / inv): mensajes de admin no leídos ─────────────
    $sql = "
        SELECT
            t.id_ticket,
            COUNT(m.id_mensaje) AS cnt,
            MAX(m.fecha_envio)  AS ultima
        FROM soporte_mensajes m
        INNER JOIN soporte_tickets t ON m.id_ticket = t.id_ticket
        WHERE t.id_usuario = $uid
          AND m.leido = 0
          AND (
                m.enviado_por IN (
                    SELECT CAST(id_usuario AS CHAR)
                    FROM   usuario
                    WHERE  tipos = 'admin'
                )
                OR m.enviado_por = 'admin'
              )
          AND t.estado != 'Resuelto'
        GROUP BY t.id_ticket
        ORDER BY ultima DESC
        LIMIT 10
    ";
    $res = mysqli_query($conexion, $sql);

    $items = [];
    $total = 0;
    while ($row = mysqli_fetch_assoc($res)) {
        $cnt    = (int) $row['cnt'];
        $total += $cnt;
        $ticket = htmlspecialchars($row['id_ticket']);
        $items[] = [
            'ticket'  => $ticket,
            'mensaje' => $cnt === 1
                ? "Tienes 1 mensaje nuevo de Soporte Técnico"
                : "Tienes $cnt mensajes nuevos de Soporte Técnico",
            'url'     => '../vistas/soporte_usuario.php',
            'icono'   => 'fas fa-headset',
            'tipo'    => 'info',
        ];
    }
}

echo json_encode(['count' => $total, 'items' => $items]);
?>
