<?php
require_once("../conexion.php");
header('Content-Type: application/json');

$maint_query = mysqli_query($conexion, "SELECT * FROM config_mantenimiento WHERE id = 1");
$data = mysqli_fetch_assoc($maint_query);

if (!$data) {
    echo json_encode(['activo' => false]);
    exit;
}

$is_active = (bool)($data['activo'] ?? false);
$fecha_maint = $data['fecha'] ?? null;
$hora_inicio = !empty($data['hora_inicio']) ? date('H:i', strtotime($data['hora_inicio'])) : '';
$hora_fin = !empty($data['hora_fin']) ? date('H:i', strtotime($data['hora_fin'])) : '';

// Lógica de horario automático
if (!$is_active && !empty($hora_inicio) && !empty($hora_fin)) {
    date_default_timezone_set('America/Caracas');
    $fecha_actual = date('Y-m-d');
    $hora_actual = date('H:i');
    if (empty($fecha_maint) || $fecha_maint === $fecha_actual) {
        if ($hora_inicio <= $hora_fin) {
            if ($hora_actual >= $hora_inicio && $hora_actual < $hora_fin) $is_active = true;
        } else {
            if ($hora_actual >= $hora_inicio || $hora_actual < $hora_fin) $is_active = true;
        }
    }
}

echo json_encode([
    'activo' => $is_active,
    'titulo' => $data['titulo'] ?? 'Plataforma en Mantenimiento',
    'descripcion' => $data['descripcion'] ?? 'Realizando mejoras técnicas.',
    'hora_inicio' => $data['hora_inicio'],
    'hora_fin' => $data['hora_fin']
]);
