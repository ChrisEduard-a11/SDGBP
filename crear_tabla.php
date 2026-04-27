<?php
include('conexion.php');

$sql = "CREATE TABLE IF NOT EXISTS `cierres_mensuales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mes` int(2) NOT NULL,
  `anio` int(4) NOT NULL,
  `estado` enum('abierto','cerrado') NOT NULL DEFAULT 'cerrado',
  `usuario_cierre_id` int(11) NOT NULL,
  `fecha_cierre` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mes_anio` (`mes`,`anio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if (mysqli_query($conexion, $sql)) {
    echo "Tabla cierres_mensuales creada con éxito.\n";
} else {
    echo "Error creando la tabla: " . mysqli_error($conexion) . "\n";
}

// Opcional: Insertar cierres pasados para no bloquear el sistema. Estamos en 2026.
// Por ejemplo, cerrar hasta el mes anterior por defecto, o dejarlo vacío y dejar que el admin cierre.
// Lo más sano es cerrarlos bajo demanda pero si está vacío, el mes = 0 cerrado de 2026? 
/* 
El requerimiento dice: no se puede cargar un pago si el mes anterior no está cerrado.
Si la tabla está vacía, ¿cómo cargan el primer mes?
Deberíamos insertar todos los meses hasta el mes "anterior" al actual (ej. Marzo 2026) como cerrados, o asumir que si no hay historial, se empieza a verificar desde el primer registro.
Pero es mejor crear el registro base.
*/
$mes_actual = (int)date('m');
$anio_actual = (int)date('Y');
$mes_anterior = $mes_actual - 1;
$anio_anterior = $anio_actual;
if ($mes_anterior == 0) {
    $mes_anterior = 12;
    $anio_anterior--;
}

// Vamos a insertar el mes pasado como cerrado para que puedan cargar el actual, por defecto (para que el sistema no rompa hoy).
$sql_insert = "INSERT IGNORE INTO cierres_mensuales (mes, anio, estado, usuario_cierre_id, fecha_cierre) VALUES ($mes_anterior, $anio_anterior, 'cerrado', 1, NOW())";
mysqli_query($conexion, $sql_insert);
echo "Mes anterior cerrado preventivamente para inicio de sistema.\n";

mysqli_close($conexion);
?>
