<?php
include('../conexion.php');

$sql = "CREATE TABLE IF NOT EXISTS `solicitudes_eliminacion_u` (
  `id_solicitud` int(11) NOT NULL AUTO_INCREMENT,
  `id_solicitante` int(11) NOT NULL,
  `id_objetivo` int(11) NOT NULL,
  `estado` enum('pendiente','aprobado','rechazado') NOT NULL DEFAULT 'pendiente',
  `fecha_solicitud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `codigo_2fa` varchar(10) DEFAULT NULL,
  `expiracion_2fa` datetime DEFAULT NULL,
  PRIMARY KEY (`id_solicitud`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if (mysqli_query($conexion, $sql)) {
    echo "Tabla creada correctamente.\n";
} else {
    echo "Error creando tabla: " . mysqli_error($conexion) . "\n";
}
?>
