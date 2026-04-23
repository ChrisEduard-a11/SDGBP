<?php
require_once("conexion.php");

$sql1 = "CREATE TABLE IF NOT EXISTS soporte_tickets (
    id_ticket VARCHAR(20) PRIMARY KEY,
    id_usuario INT(11) NOT NULL,
    asunto VARCHAR(255) NOT NULL,
    estado ENUM('Abierto', 'En Proceso', 'Resuelto') DEFAULT 'Abierto',
    prioridad ENUM('Baja', 'Normal', 'Alta', 'Urgente') DEFAULT 'Normal',
    fecha_apertura DATETIME DEFAULT CURRENT_TIMESTAMP,
    ultima_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

$sql2 = "CREATE TABLE IF NOT EXISTS soporte_mensajes (
    id_mensaje INT(11) AUTO_INCREMENT PRIMARY KEY,
    id_ticket VARCHAR(20) NOT NULL,
    enviado_por VARCHAR(50) NOT NULL COMMENT 'Puede ser ID del usuario o ADMIN',
    mensaje TEXT NOT NULL,
    adjunto VARCHAR(255) DEFAULT NULL,
    fecha_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ticket FOREIGN KEY (id_ticket) REFERENCES soporte_tickets(id_ticket) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

if(mysqli_query($conexion, $sql1)) {
    echo "Tabla soporte_tickets creada\n";
} else {
    echo "Error: " . mysqli_error($conexion) . "\n";
}

if(mysqli_query($conexion, $sql2)) {
    echo "Tabla soporte_mensajes creada\n";
} else {
    echo "Error: " . mysqli_error($conexion) . "\n";
}

// Actualización para permitir invitados
$sql3 = "ALTER TABLE soporte_tickets MODIFY id_usuario INT(11) NULL;";
$sql4 = "ALTER TABLE soporte_tickets ADD COLUMN IF NOT EXISTS nombre_visitante VARCHAR(100) NULL AFTER id_usuario;";
$sql5 = "ALTER TABLE soporte_tickets ADD COLUMN IF NOT EXISTS correo_visitante VARCHAR(100) NULL AFTER nombre_visitante;";

mysqli_query($conexion, $sql3);
mysqli_query($conexion, $sql4);
mysqli_query($conexion, $sql5);
echo "Tablas actualizadas para invitados.\n";
?>
