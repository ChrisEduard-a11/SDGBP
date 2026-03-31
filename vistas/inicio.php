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

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
    /* Estilos Premium para la vista inicio */
    [data-theme="dark"] {
        --glass-bg: rgba(30, 41, 59, 0.7);
        --glass-border: rgba(255, 255, 255, 0.1);
    }

    body {
        font-family: 'Inter', sans-serif;
    }

    .premium-welcome-banner {
        background: linear-gradient(135deg, rgba(23, 162, 184, 0.05) 0%, rgba(102, 126, 234, 0.05) 100%);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(23, 162, 184, 0.2);
        border-radius: 1.5rem;
        position: relative;
        overflow: hidden;
        padding: 2.5rem;
    }

    [data-theme="dark"] .premium-welcome-banner {
        background: linear-gradient(135deg, rgba(30, 41, 59, 0.8) 0%, rgba(15, 23, 42, 0.9) 100%);
        border: 1px solid var(--glass-border);
    }

    .premium-welcome-banner::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(23,162,184,0.15) 0%, rgba(0,0,0,0) 70%);
        border-radius: 50%;
        pointer-events: none;
        z-index: 0;
    }

    .welcome-content {
        position: relative;
        z-index: 1;
    }

    .welcome-title {
        font-size: 2.2rem;
        font-weight: 800;
        background: linear-gradient(45deg, #17a2b8, #667eea);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 0.2rem;
    }

    [data-theme="dark"] .welcome-title {
        background: linear-gradient(45deg, #4facfe, #00f2fe);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .shortcut-btn {
        border-radius: 50px;
        padding: 0.6rem 1.5rem;
        font-weight: 600;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        backdrop-filter: blur(5px);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .shortcut-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }

    .shortcut-btn-primary {
        background: linear-gradient(135deg, #17a2b8 0%, #0dcaf0 100%);
        color: white !important;
        border: none;
        box-shadow: 0 4px 15px rgba(23,162,184,0.3);
    }

    .shortcut-btn-primary:hover {
        color: white !important;
        box-shadow: 0 8px 25px rgba(23,162,184,0.4);
    }

    .shortcut-btn-secondary {
        background: rgba(255, 255, 255, 0.8);
        color: #333 !important;
        border: 1px solid rgba(0,0,0,0.1);
    }
    
    .shortcut-btn-secondary:hover {
        color: #17a2b8 !important;
        background: white;
    }

    [data-theme="dark"] .shortcut-btn-secondary {
        background: rgba(30, 41, 59, 0.6);
        color: #f8fafc !important;
        border: 1px solid rgba(255,255,255,0.1);
    }
    
    [data-theme="dark"] .shortcut-btn-secondary:hover {
        background: rgba(30, 41, 59, 0.9);
        color: #0dcaf0 !important;
    }

    .user-avatar-placeholder {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background: linear-gradient(135deg, #17a2b8 0%, #667eea 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: bold;
        box-shadow: 0 8px 20px rgba(23, 162, 184, 0.3);
        flex-shrink: 0;
    }

    [data-theme="dark"] .text-dark {
        color: #f8fafc !important;
    }

    .metric-card {
        border: none;
        border-radius: 1.25rem;
        color: white;
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        overflow: hidden;
        position: relative;
    }
    
    .metric-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.2) !important;
    }
    
    .metric-card::after {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: rgba(255,255,255,0.1);
        transform: rotate(30deg);
        pointer-events: none;
    }

    .metric-icon {
        font-size: 5rem;
        opacity: 0.15;
        position: absolute;
        right: -0.5rem;
        bottom: -1rem;
        transition: transform 0.3s ease;
    }

    .metric-card:hover .metric-icon {
        transform: scale(1.1) rotate(-5deg);
    }

    /* Gradient Backgrounds */
    .bg-gradient-warning { background: linear-gradient(135deg, #f6d365 0%, #fda085 100%); }
    .bg-gradient-danger { background: linear-gradient(135deg, #ff0844 0%, #ffb199 100%); }
    .bg-gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .bg-gradient-success { background: linear-gradient(135deg, #2af598 0%, #009efd 100%); }
    .bg-gradient-info { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
    .bg-gradient-secondary { background: linear-gradient(135deg, #667eea 0%, #4facfe 100%); }
    .bg-gradient-success-alt { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }

    .card-footer-premium {
        background: rgba(0, 0, 0, 0.15);
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(5px);
    }
    
    .section-title {
        font-weight: 700;
        letter-spacing: -0.5px;
        position: relative;
        padding-bottom: 0.5rem;
    }
    
    .section-title::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: -2px;
        width: 60px;
        height: 3px;
        background: #17a2b8;
        border-radius: 3px;
    }
</style>

<div id="layoutSidenav_content">
    <div class="container-fluid px-4 py-4">
        
        <header class="page-header-standard d-flex justify-content-between align-items-center animate__animated animate__fadeIn">
            <div>
                <h1 class="fw-bold mb-0 text-primary"><i class="fas fa-home me-2"></i>Panel de Control</h1>
                <p class="text-muted">Resumen de actividades y accesos rápidos del sistema</p>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 m-0">
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </nav>
        </header>

        <div class="premium-welcome-banner shadow-lg mb-5 animate__animated animate__fadeInDown border-0">
            <div class="welcome-content d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4">
                <div class="d-flex align-items-center gap-4">
                    <div class="user-avatar-placeholder overflow-hidden">
                        <?php 
                        $foto_perfil = $_SESSION['foto'] ?? '';
                        if (!empty($foto_perfil) && file_exists($foto_perfil)): ?>
                            <img src="<?php echo htmlspecialchars($foto_perfil); ?>" alt="Avatar" class="w-100 h-100" style="object-fit: cover;">
                        <?php else: ?>
                            <?php echo strtoupper(substr($_SESSION['nombre'], 0, 1) ?: 'U'); ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h1 class="welcome-title">
                            <?php echo $saludo . ', ' . $_SESSION['nombre']; ?>! 👋
                        </h1>
                        <p class="text-secondary mb-0 fw-semibold fs-5"><?php echo $descripcion_sistema; ?></p>
                    </div>
                </div>
                
                <div class="d-flex flex-column align-items-lg-end gap-3">
                    <div class="d-flex gap-2 flex-wrap justify-content-lg-end">
                        <?php if ($_SESSION["tipo"] == "admin") { ?>
                            <button class="btn shortcut-btn shortcut-btn-primary" onclick="navigateTo('registro_u.php')">
                                <i class="fas fa-user-plus"></i>Nuevo Usuario
                            </button>
                            <button class="btn shortcut-btn shortcut-btn-secondary" onclick="navigateTo('registro_bien.php')">
                                <i class="fas fa-box-open"></i>Nuevo Bien
                            </button>
                            <button class="btn shortcut-btn shortcut-btn-secondary" onclick="navigateTo('registro_pagos_egresos.php')">
                                <i class="fas fa-file-invoice-dollar"></i>Reg. Egreso
                            </button>
                        <?php } elseif ($_SESSION["tipo"] == "cont") { ?>
                            <button class="btn shortcut-btn shortcut-btn-primary" onclick="navigateTo('registro_pagos_egresos.php')">
                                <i class="fas fa-plus-circle"></i>Registrar Egreso
                            </button>
                            <button class="btn shortcut-btn shortcut-btn-secondary" onclick="navigateTo('ver_pagos_cont.php')">
                                <i class="fas fa-search-dollar"></i>Revisar Pagos
                            </button>
                        <?php } elseif ($_SESSION["tipo"] == "inv") { ?>
                            <button class="btn shortcut-btn shortcut-btn-primary" onclick="navigateTo('registro_bien.php')">
                                <i class="fas fa-plus-circle"></i>Registrar Bien
                            </button>
                            <button class="btn shortcut-btn shortcut-btn-secondary" onclick="navigateTo('lista_bienes.php')">
                                <i class="fas fa-list"></i>Ver Inventario
                            </button>
                        <?php } elseif ($_SESSION["tipo"] == "upu") { ?>
                            <button class="btn shortcut-btn shortcut-btn-primary" onclick="navigateTo('registro_pagos.php')">
                                <i class="fas fa-upload"></i>Reportar Pago
                            </button>
                            <button class="btn shortcut-btn shortcut-btn-secondary" onclick="navigateTo('ver_pagos.php')">
                                <i class="fas fa-history"></i>Historial Pagos
                            </button>
                        <?php } ?>
                    </div>
                    
                    <div class="text-muted small d-flex align-items-center gap-3">
                        <span><i class="far fa-clock me-1"></i>Última conexión: <strong id="ultimaConexion" class="text-dark"><?php echo $_SESSION['ultima_conexion']; ?></strong></span>
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 rounded-pill">
                            <i class="fas fa-user-shield me-1"></i> <?php echo ucfirst($tipo_usuario); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <h3 class="mb-4 mt-4 text-dark section-title"><i class="fas fa-bolt text-warning me-2"></i> Opciones de Gestión Rápida</h3>
        <div class="row g-4">
            
            <?php if ($_SESSION["tipo"] == "admin" || $_SESSION["tipo"] == "inv") { ?>
            <div class="col-xl-4 col-md-6 animate__animated animate__fadeInUp animate__fast">
                <div class="card metric-card bg-gradient-warning h-100 shadow-lg">
                    <div class="card-body p-4 d-flex flex-column position-relative">
                        <h4 class="card-title text-white fw-bold mb-3"><i class="fas fa-boxes me-2"></i> Listado de Inventarios</h4>
                        <p class="card-text text-white opacity-75 mb-4 fw-medium">Control y verificación de todos los bienes registrados en el sistema.</p>
                        <i class="fas fa-box-open metric-icon"></i>
                    </div>
                    <div class="card-footer card-footer-premium d-flex align-items-center justify-content-between p-3 mt-auto">
                        <a class="small text-white stretched-link text-decoration-none fw-bold" href="javascript:void(0);" onclick="navigateTo('lista_bienes.php')">Acceder a Inventarios</a>
                        <div class="small text-white"><i class="fas fa-arrow-right"></i></div>
                    </div>
                </div>
            </div>
            <?php } ?>
            
            <?php if ($_SESSION["tipo"] == "admin") { ?>
            <div class="col-xl-4 col-md-6 animate__animated animate__fadeInUp animate__fast" style="animation-delay: 0.1s;">
                <div class="card metric-card bg-gradient-danger h-100 shadow-lg">
                    <div class="card-body p-4 d-flex flex-column position-relative">
                        <h4 class="card-title text-white fw-bold mb-3"><i class="fas fa-users me-2"></i> Gestión de Usuarios</h4>
                        <p class="card-text text-white opacity-75 mb-4 fw-medium">Administración, creación y edición de roles y cuentas del personal.</p>
                        <i class="fas fa-users-cog metric-icon"></i>
                    </div>
                    <div class="card-footer card-footer-premium d-flex align-items-center justify-content-between p-3 mt-auto">
                        <a class="small text-white stretched-link text-decoration-none fw-bold" href="javascript:void(0);" onclick="navigateTo('usuario.php')">Administrar Usuarios</a>
                        <div class="small text-white"><i class="fas fa-arrow-right"></i></div>
                    </div>
                </div>
            </div>
            <?php } ?>
            
            <?php 
                if ($_SESSION["tipo"] == "cont" || $_SESSION["tipo"] == "admin" || $_SESSION["tipo"] == "upu") { 
                    $link = ($_SESSION["tipo"] == "upu") ? 'ver_pagos.php' : 'ver_pagos_cont.php';
                    $titulo = ($_SESSION["tipo"] == "upu") ? 'Mis Pagos' : 'Lista de Pagos';
                    $texto_link = ($_SESSION["tipo"] == "upu") ? 'Ver mi Historial' : 'Revisar Pagos';
                    $color_class = ($_SESSION["tipo"] == "upu") ? 'bg-gradient-success' : 'bg-gradient-primary';
            ?>
            <div class="col-xl-4 col-md-6 animate__animated animate__fadeInUp animate__fast" style="animation-delay: 0.2s;">
                <div class="card metric-card <?php echo $color_class; ?> h-100 shadow-lg">
                    <div class="card-body p-4 d-flex flex-column position-relative">
                        <h4 class="card-title text-white fw-bold mb-3"><i class="fas fa-file-invoice-dollar me-2"></i> <?php echo $titulo; ?></h4>
                        <p class="card-text text-white opacity-75 mb-4 fw-medium">Gestión y seguimiento de todas las transacciones y estados de pago.</p>
                        <i class="fas fa-money-check-alt metric-icon"></i>
                    </div>
                    <div class="card-footer card-footer-premium d-flex align-items-center justify-content-between p-3 mt-auto">
                        <a class="small text-white stretched-link text-decoration-none fw-bold" href="javascript:void(0);" onclick="navigateTo('<?php echo $link; ?>')"><?php echo $texto_link; ?></a>
                        <div class="small text-white"><i class="fas fa-arrow-right"></i></div>
                    </div>
                </div>
            </div>
            <?php } ?>

            <?php if ($_SESSION["tipo"] == "upu" || $_SESSION["tipo"] == "cont" || $_SESSION["tipo"] == "inv") { ?>
            <div class="col-xl-4 col-md-6 animate__animated animate__fadeInUp animate__fast" style="animation-delay: 0.3s;">
                <div class="card metric-card bg-gradient-info h-100 shadow-lg">
                    <div class="card-body p-4 d-flex flex-column position-relative">
                        <h4 class="card-title text-white fw-bold mb-3"><i class="fas fa-cogs me-2"></i> Configuración</h4>
                        <p class="card-text text-white opacity-75 mb-4 fw-medium">Actualiza tus datos personales y gestiona las preferencias de tu cuenta.</p>
                        <i class="fas fa-user-cog metric-icon"></i>
                    </div>
                    <div class="card-footer card-footer-premium d-flex align-items-center justify-content-between p-3 mt-auto">
                        <a class="small text-white stretched-link text-decoration-none fw-bold" href="javascript:void(0);" onclick="navigateTo('configuracion_usuario.php')">Ajustar Cuenta</a>
                        <div class="small text-white"><i class="fas fa-arrow-right"></i></div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
        
        <h3 class="mb-4 mt-5 text-dark section-title"><i class="fas fa-book-reader text-primary me-2"></i> Documentación y Ayuda</h3>
        <div class="row g-4 mb-5">

            <div class="col-xl-4 col-md-6 animate__animated animate__fadeInUp animate__fast" style="animation-delay: 0.4s;">
                <div class="card metric-card bg-gradient-secondary h-100 shadow-lg">
                    <div class="card-body p-4 d-flex flex-column position-relative">
                        <h4 class="card-title text-white fw-bold mb-3"><i class="fas fa-user-graduate me-2"></i> Manual de Usuario</h4>
                        <p class="card-text text-white opacity-75 mb-4 fw-medium">Guía detallada para el uso correcto de todas las funciones accesibles.</p>
                        <i class="fas fa-book-open metric-icon"></i>
                    </div>
                    <div class="card-footer card-footer-premium d-flex align-items-center justify-content-between p-3 mt-auto">
                        <a class="small text-white stretched-link text-decoration-none fw-bold" onclick="window.open('../manuales/Manual_del_Usuario.pdf', '_blank')" style="cursor:pointer;"><i class="fas fa-file-pdf text-white me-1"></i> Ver/Descargar</a>
                        <div class="small text-white"><i class="fas fa-external-link-alt"></i></div>
                    </div>
                </div>
            </div>
            
            <?php if ($_SESSION["tipo"] == "admin") { ?>
            <div class="col-xl-4 col-md-6 animate__animated animate__fadeInUp animate__fast" style="animation-delay: 0.5s;">
                <div class="card metric-card bg-gradient-success-alt h-100 shadow-lg">
                    <div class="card-body p-4 d-flex flex-column position-relative">
                        <h4 class="card-title text-white fw-bold mb-3"><i class="fas fa-tools me-2"></i> Manual del Sistema</h4>
                        <p class="card-text text-white opacity-75 mb-4 fw-medium">Información técnica y de configuración para la administración total.</p>
                        <i class="fas fa-laptop-code metric-icon"></i>
                    </div>
                    <div class="card-footer card-footer-premium d-flex align-items-center justify-content-between p-3 mt-auto">
                        <a class="small text-white stretched-link text-decoration-none fw-bold" onclick="window.open('../manuales/Manual_del_Software.pdf', '_blank')" style="cursor:pointer;"><i class="fas fa-file-pdf text-white me-1"></i> Ver/Descargar</a>
                        <div class="small text-white"><i class="fas fa-external-link-alt"></i></div>
                    </div>
                </div>
            </div>
            <?php } ?>

            <div class="col-xl-4 col-md-6 animate__animated animate__fadeInUp animate__fast" style="animation-delay: 0.6s;">
                <div class="card metric-card bg-gradient-info h-100 shadow-lg">
                    <div class="card-body p-4 d-flex flex-column position-relative">
                        <h4 class="card-title text-white fw-bold mb-3"><i class="fas fa-headset me-2"></i> Soporte en Línea</h4>
                        <p class="card-text text-white opacity-75 mb-4 fw-medium">¿Necesitas ayuda inmediata? Chatea con nuestro equipo técnico.</p>
                        <i class="fas fa-comments metric-icon"></i>
                    </div>
                    <div class="card-footer card-footer-premium d-flex align-items-center justify-content-between p-3 mt-auto">
                        <a class="small text-white stretched-link text-decoration-none fw-bold" href="javascript:void(0);" onclick="abrirSoporte()">Chatear con Soporte</a>
                        <div class="small text-white"><i class="fas fa-comment-dots"></i></div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
    
<?php
require_once("../models/footer.php");
?>
