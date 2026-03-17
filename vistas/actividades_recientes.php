<?php
require_once("../models/header.php");
require_once("../conexion.php"); // Asegúrate de incluir la conexión a la base de datos
?>
<div id="layoutSidenav_content">
    <div class="container-fluid px-4">
        <h1 class="mt-4">Actividades Recientes</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigateTo('inicio.php')">Inicio</a></li>
            <li class="breadcrumb-item active">Actividades Recientes</li>
        </ol>
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-tasks me-1"></i> Actividades Recientes
            </div>
            <div class="card-body">
                <h5>Últimos registros de asistencia</h5>
                <ul>
                    <?php
                    $sql = "SELECT a.fecha, p.nombre FROM asistencia AS a JOIN personal AS p ON a.id_personal = p.id ORDER BY a.fecha DESC LIMIT 5";
                    $result = mysqli_query($conexion, $sql);
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<li>" . $row['nombre'] . " - " . $row['fecha'] . "</li>";
                    }
                    ?>
                </ul>
                <h5>Nuevos usuarios registrados</h5>
                <p> Proximamente.....!</p>   
                <h5>Últimas modificaciones de perfil</h5>
                <p> Proximamente.....!</p>  
            </div>
        </div>
    </div>
<?php
require_once("../models/footer.php");
?>