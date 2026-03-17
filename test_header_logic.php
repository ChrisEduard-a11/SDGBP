<?php
include('conexion.php');

// Simulamos un ID de usuario (el primero que encontremos para probar)
$res_user = mysqli_query($conexion, "SELECT id_usuario, usuario, fecha_cambio_clave FROM usuario LIMIT 1");
$user = mysqli_fetch_assoc($res_user);

echo "--- TEST DE LOGICA DE SEGURIDAD ---\n";
echo "Usuario: " . $user['usuario'] . " (ID: " . $user['id_usuario'] . ")\n";
echo "Fecha en DB: [" . ($user['fecha_cambio_clave'] ?? 'NULL') . "]\n";

// Logica replicada de header.php
$fecha_db = $user['fecha_cambio_clave'] ?? '';
if (empty($fecha_db) || $fecha_db == '0000-00-00') {
    $fecha_cambio = '2000-01-01'; 
    echo "Deteccion: Fecha vacia/0 -> Forzado a 2000-01-01\n";
} else {
    $fecha_cambio = $fecha_db;
    echo "Deteccion: Fecha valida -> Usando $fecha_cambio\n";
}

$now = time();
$last_change = strtotime($fecha_cambio);
$diff = $now - $last_change;
$dias_transcurridos = floor($diff / 86400);
$dias_para_vencer = 180 - $dias_transcurridos;

echo "Timestamp Now: $now (" . date('Y-m-d H:i:s', $now) . ")\n";
echo "Timestamp Last: $last_change (" . date('Y-m-d H:i:s', $last_change) . ")\n";
echo "Dias Transcurridos: $dias_transcurridos\n";
echo "Dias para Vencer: $dias_para_vencer\n";

if ($dias_para_vencer <= 15) {
    if ($dias_para_vencer <= 0) {
        echo "RESULTADO: BLOQUEO / NOTIF PELIGRO (DANGER)\n";
    } else {
        echo "RESULTADO: NOTIF ADVERTENCIA (WARNING)\n";
    }
} else {
    echo "RESULTADO: TODO OK (NO NOTIF / NO BLOQUEO)\n";
}
?>
