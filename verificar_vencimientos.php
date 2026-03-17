<?php
include('conexion.php');
date_default_timezone_set('America/Caracas');

$res = mysqli_query($conexion, "SELECT id_usuario, usuario, tipos, fecha_cambio_clave FROM usuario");

echo "<h1>Auditoría de Vigencia de Contraseñas (180 días)</h1>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%; font-family: sans-serif;'>";
echo "<tr style='background: #f4f4f4;'><th>ID</th><th>Usuario</th><th>Rol</th><th>Último Cambio</th><th>Días Transcurridos</th><th>Estado</th></tr>";

$now = time();

while($row = mysqli_fetch_assoc($res)) {
    $fecha_db = $row['fecha_cambio_clave'];
    
    if (empty($fecha_db) || $fecha_db == '0000-00-00') {
        $fecha_cambio = '2000-01-01';
        $label_fecha = "Sín registro (Forzado 2000)";
    } else {
        $fecha_cambio = $fecha_db;
        $label_fecha = $fecha_db;
    }

    $last_change = strtotime($fecha_cambio);
    $diff = $now - $last_change;
    $dias = floor($diff / 86400);
    $vence_en = 180 - $dias;

    if ($vence_en <= 0) {
        $estado = "<b style='color: red;'>VENCIDA</b>";
        $bg = "#fee2e2";
    } elseif ($vence_en <= 15) {
        $estado = "<b style='color: orange;'>POR VENCER ({$vence_en} días)</b>";
        $bg = "#fff7ed";
    } else {
        $estado = "<b style='color: green;'>VIGENTE ({$vence_en} días restantes)</b>";
        $bg = "#f0fdf4";
    }

    echo "<tr style='background: $bg;'>";
    echo "<td>{$row['id_usuario']}</td>";
    echo "<td>{$row['usuario']}</td>";
    echo "<td>" . strtoupper($row['tipos']) . "</td>";
    echo "<td>$label_fecha</td>";
    echo "<td>$dias días</td>";
    echo "<td>$estado</td>";
    echo "</tr>";
}
echo "</table>";
?>
