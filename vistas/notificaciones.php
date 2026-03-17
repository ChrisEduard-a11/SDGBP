<?php
require_once("../models/header.php");
require_once("../conexion.php");
require_once("../models/notificaciones.php");

$usuario_id = $_SESSION['id']; // Obtén el ID del usuario actual
$tipo_usuario = $_SESSION['tipo']; // Obtén el tipo de usuario actual

if ($tipo_usuario == 'admin') {
    // Si el usuario es administrador, obtener todas las notificaciones
    $notificaciones = obtenerTodasLasNotificaciones($conexion);
} else {
    // Si el usuario no es administrador, obtener solo sus notificaciones
    $notificaciones = obtenerNotificaciones($conexion, $usuario_id);
    marcarNotificacionesComoLeidas($conexion, $usuario_id);
}
?>
<div id="layoutSidenav_content">
    <div class="container-fluid px-4">
        <h1 class="mt-4">Notificaciones</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigateTo('inicio.php')">Inicio</a></li>
            <li class="breadcrumb-item active">Notificaciones</li>
        </ol>
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-bell me-1"></i> Notificaciones
            </div>
            <div class="card-body">
                <ul>
                    <?php
                    foreach ($notificaciones as $notificacion) {
                        if ($tipo_usuario == 'admin') {
                            echo "<li>"."Fecha:" . $notificacion['fecha'] . " - " . $notificacion['mensaje'] . " para el Usuario: - "."<b>". $notificacion['nombre_usuario'] ."</b>"."</li>";
                        } else {
                            echo "<li>"."Fecha:" . $notificacion['fecha'] . " - "  . $notificacion['mensaje'] ."</li>";
                        }
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>
<?php
require_once("../models/footer.php");
?>