<?php
require_once("../models/header.php");
// Configurar la zona horaria correcta
date_default_timezone_set('America/Caracas'); // Cambia esto según tu ubicación

$hora = date("H:i:s");
if ($hora < 12) {
    $saludo = "Buenos días";
} elseif ($hora < 18) {
    $saludo = "Buenas tardes";
} else {
    $saludo = "Buenas noches";
}

// -----------------------------------------------------------------
$tipo_usuario = $_SESSION["tipo"];

if ($tipo_usuario == "admin") {
    $descripcion_sistema = "Tu centro de control para Inventarios, Usuarios y Contabilidad.";
} elseif ($tipo_usuario == "cont") {
    $descripcion_sistema = "Panel para la gestión y revisión de todos los pagos y transacciones.";
} elseif ($tipo_usuario == "inv") {
    $descripcion_sistema = "Panel para la gestión y control para Inventarios.";
}elseif ($tipo_usuario == "upu") {
    $descripcion_sistema = "Revisa el estado de tus pagos y gestiona tu información personal.";
} else {
    $descripcion_sistema = "Bienvenido a la plataforma de gestión.";
}
?>

<div id="layoutSidenav_content">
    <div class="container-fluid px-4">
        
        <div class="card shadow-lg mb-5 animate__animated animate__fadeInDown border-0 bg-white">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-1" style="color: #17a2b8; font-weight: 600;">
                            👋 <?php echo $saludo . ', ' . $_SESSION['nombre']; ?>!
                        </h1>
                        <p class="text-secondary mb-0"><?php echo $descripcion_sistema; ?></p>
                    </div>
                    
                    <small class="text-end" style="color: #6c757d;">
                        Última conexión: 
                        <span id="ultimaConexion" class="text-dark fw-bold"><?php echo $_SESSION['ultima_conexion']; ?></span>
                        <br>
                        <span class="badge bg-primary text-white mt-1"><?php echo ucfirst($tipo_usuario); ?></span>
                    </small>
                </div>
            </div>
        </div>

        <h3 class="mb-4 mt-4 text-dark border-bottom pb-2">🎯 Opciones de Gestión Rápida</h3>
        <div class="row g-4">
            
            <?php if ($_SESSION["tipo"] == "admin" || $_SESSION["tipo"] == "inv") { ?>
            <div class="col-xl-4 col-md-6 animate__animated animate__fadeInUp animate__fast">
                <div class="card bg-warning text-white h-100 shadow-sm border-0 transform-hover">
                    <div class="card-body d-flex flex-column">
                        <h4 class="card-title mb-3"><i class="fas fa-boxes me-2"></i> Listado de Inventarios</h4>
                        <p class="card-text small">Control y verificación de todos los bienes registrados en el sistema.</p>
                    </div>
                    <div class="card-footer bg-warning-dark d-flex align-items-center justify-content-between p-3">
                        <a class="small text-white stretched-link text-decoration-none" href="javascript:void(0);" onclick="navigateTo('lista_bienes.php')">Acceder a Inventarios</a>
                        <div class="small text-white"><i class="fas fa-arrow-circle-right"></i></div>
                    </div>
                </div>
            </div>
            <?php } ?>
            
            <?php if ($_SESSION["tipo"] == "admin") { ?>
            <div class="col-xl-4 col-md-6 animate__animated animate__fadeInUp animate__fast" style="animation-delay: 0.1s;">
                <div class="card bg-danger text-white h-100 shadow-sm border-0 transform-hover">
                    <div class="card-body d-flex flex-column">
                        <h4 class="card-title mb-3"><i class="fas fa-users me-2"></i> Gestión de Usuarios</h4>
                        <p class="card-text small">Administración, creación y edición de roles y cuentas del personal.</p>
                    </div>
                    <div class="card-footer bg-danger-dark d-flex align-items-center justify-content-between p-3">
                        <a class="small text-white stretched-link text-decoration-none" href="javascript:void(0);" onclick="navigateTo('usuario.php')">Administrar Usuarios</a>
                        <div class="small text-white"><i class="fas fa-arrow-circle-right"></i></div>
                    </div>
                </div>
            </div>
            <?php } ?>
            
            <?php 
                if ($_SESSION["tipo"] == "cont" || $_SESSION["tipo"] == "admin" || $_SESSION["tipo"] == "upu") { 
                    $link = ($_SESSION["tipo"] == "upu") ? 'ver_pagos.php' : 'ver_pagos_cont.php';
                    $titulo = ($_SESSION["tipo"] == "upu") ? 'Mis Pagos' : 'Lista de Pagos';
                    $texto_link = ($_SESSION["tipo"] == "upu") ? 'Ver mi Historial' : 'Revisar Pagos';
                    $color_class = ($_SESSION["tipo"] == "upu") ? 'bg-success' : 'bg-primary';
                    $color_footer = ($_SESSION["tipo"] == "upu") ? 'bg-success-dark' : 'bg-primary-dark';
            ?>
            <div class="col-xl-4 col-md-6 animate__animated animate__fadeInUp animate__fast" style="animation-delay: 0.2s;">
                <div class="card <?php echo $color_class; ?> text-white h-100 shadow-sm border-0 transform-hover">
                    <div class="card-body d-flex flex-column">
                        <h4 class="card-title mb-3"><i class="fas fa-file-invoice-dollar me-2"></i> <?php echo $titulo; ?></h4>
                        <p class="card-text small">Gestión y seguimiento de todas las transacciones y estados de pago.</p>
                    </div>
                    <div class="card-footer <?php echo $color_footer; ?> d-flex align-items-center justify-content-between p-3">
                        <a class="small text-white stretched-link text-decoration-none" href="javascript:void(0);" onclick="navigateTo('<?php echo $link; ?>')"><?php echo $texto_link; ?></a>
                        <div class="small text-white"><i class="fas fa-arrow-circle-right"></i></div>
                    </div>
                </div>
            </div>
            <?php } ?>

            <?php if ($_SESSION["tipo"] == "upu" || $_SESSION["tipo"] == "cont" || $_SESSION["tipo"] == "inv") { ?>
            <div class="col-xl-4 col-md-6 animate__animated animate__fadeInUp animate__fast" style="animation-delay: 0.3s;">
                <div class="card bg-info text-white h-100 shadow-sm border-0 transform-hover">
                    <div class="card-body d-flex flex-column">
                        <h4 class="card-title mb-3"><i class="fas fa-cogs me-2"></i> Configuración</h4>
                        <p class="card-text small">Actualiza tus datos personales y gestiona las preferencias de tu cuenta.</p>
                    </div>
                    <div class="card-footer bg-info-dark d-flex align-items-center justify-content-between p-3">
                        <a class="small text-white stretched-link text-decoration-none" href="javascript:void(0);" onclick="navigateTo('configuracion_usuario.php')">Ajustar Cuenta</a>
                        <div class="small text-white"><i class="fas fa-arrow-circle-right"></i></div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
        
        <h3 class="mb-4 mt-5 text-dark border-bottom pb-2">📚 Documentación y Ayuda</h3>
        <div class="row g-4 mb-5">

            <div class="col-xl-4 col-md-6 animate__animated animate__fadeInUp animate__fast" style="animation-delay: 0.4s;">
                <div class="card bg-secondary text-white h-100 shadow-sm border-0 transform-hover">
                    <div class="card-body d-flex flex-column">
                        <h4 class="card-title mb-3"><i class="fas fa-user-graduate me-2"></i> Manual de Usuario</h4>
                        <p class="card-text small">Guía detallada para el uso correcto de todas las funciones accesibles.</p>
                    </div>
                    <div class="card-footer bg-secondary-dark d-flex align-items-center justify-content-between p-3">
                        <a class="small text-white stretched-link text-decoration-none" onclick="window.open('../manuales/Manual_del_Usuario.pdf', '_blank')"><i class="fas fa-file-pdf text-white me-1"></i> Ver/Descargar</a>
                        <div class="small text-white"><i class="fas fa-external-link-alt"></i></div>
                    </div>
                </div>
            </div>
            
            <?php if ($_SESSION["tipo"] == "admin") { ?>
            <div class="col-xl-4 col-md-6 animate__animated animate__fadeInUp animate__fast" style="animation-delay: 0.5s;">
                <div class="card bg-success text-white h-100 shadow-sm border-0 transform-hover">
                    <div class="card-body d-flex flex-column">
                        <h4 class="card-title mb-3"><i class="fas fa-tools me-2"></i> Manual del Sistema</h4>
                        <p class="card-text small">Información técnica y de configuración para la administración total.</p>
                    </div>
                    <div class="card-footer bg-success-dark d-flex align-items-center justify-content-between p-3">
                        <a class="small text-white stretched-link text-decoration-none" onclick="window.open('../manuales/Manual_del_Software.pdf', '_blank')"><i class="fas fa-file-pdf text-white me-1"></i> Ver/Descargar</a>
                        <div class="small text-white"><i class="fas fa-external-link-alt"></i></div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
        
    </div>        
    
    <style>
        /* Estilos CSS adicionales para el aspecto moderno y dark footer */
        .transform-hover {
            transition: transform 0.2s ease-in-out;
        }
        .transform-hover:hover {
            transform: translateY(-5px);
        }
        .bg-warning-dark {
            background-color: #d39e00 !important; /* Un poco más oscuro que warning */
        }
        .bg-danger-dark {
            background-color: #bd2130 !important; /* Un poco más oscuro que danger */
        }
        .bg-primary-dark {
            background-color: #007bff !important; /* Un poco más oscuro que primary */
        }
        .bg-info-dark {
            background-color: #117a8b !important; /* Un poco más oscuro que info */
        }
        .bg-secondary-dark {
            background-color: #545b62 !important; /* Un poco más oscuro que secondary */
        }
        .bg-success-dark {
            background-color: #1e7e34 !important; /* Un poco más oscuro que success */
        }
    </style>
    
<?php
require_once("../models/footer.php");
?>