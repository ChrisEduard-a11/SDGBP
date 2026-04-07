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
    /* 
       ================================================================
       ESTILOS ULTRA-PREMIUM 2026 - DASHBOARD (inicio.php)
       ================================================================
    */
    
    body { font-family: 'Plus Jakarta Sans', 'Inter', sans-serif; }

    /* Glass Container Base */
    .glass-premium {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px) saturate(180%);
        -webkit-backdrop-filter: blur(12px) saturate(180%);
        border: 1px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
    }

    [data-theme="dark"] .glass-premium {
        background: rgba(15, 23, 42, 0.8);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    /* 1. WELCOME BANNER REVOLUTION */
    .premium-welcome-banner {
        position: relative;
        padding: 3rem 2.5rem;
        border-radius: 2rem;
        background: linear-gradient(135deg, rgba(23, 162, 184, 0.08) 0%, rgba(102, 126, 234, 0.08) 100%);
        border: 1px solid rgba(23, 162, 184, 0.15);
        overflow: hidden;
        transition: all 0.4s ease;
    }

    /* Mesh Gradient Animation */
    .premium-welcome-banner::before {
        content: "";
        position: absolute;
        top: -50%;
        left: -20%;
        width: 140%;
        height: 200%;
        background: radial-gradient(circle at center, rgba(59, 130, 246, 0.05) 0%, transparent 50%),
                    radial-gradient(circle at 80% 20%, rgba(251, 146, 60, 0.03) 0%, transparent 40%);
        animation: meshRotate 20s linear infinite;
        pointer-events: none;
        z-index: 0;
    }

    @keyframes meshRotate {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .welcome-title {
        font-size: 2.75rem;
        font-weight: 800;
        letter-spacing: -1.5px;
        background: linear-gradient(135deg, #0284c7 0%, #3b82f6 100%);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        line-height: 1.1;
    }

    [data-theme="dark"] .welcome-title {
        background: linear-gradient(135deg, #7dd3fc 0%, #38bdf8 100%);
        -webkit-background-clip: text;
        background-clip: text;
    }

    /* Avatar Glow */
    .user-avatar-premium {
        width: 85px; height: 85px;
        border-radius: 2rem;
        padding: 4px;
        background: linear-gradient(45deg, #0ea5e9, #6366f1);
        box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.4);
        position: relative;
    }

    .user-avatar-premium img {
        width: 100%; height: 100%;
        border-radius: 1.7rem;
        object-fit: cover;
        border: 3px solid #fff;
    }

    [data-theme="dark"] .user-avatar-premium img { border-color: #0f172a; }

    /* 2. ACTION CHIPS (Shortcuts) */
    .action-bar-premium {
        display: flex;
        gap: 10px;
        margin-top: 1rem;
    }

    .shortcut-chip {
        padding: 0.7rem 1.25rem;
        border-radius: var(--radius-pill);
        font-weight: 700;
        font-size: 0.88rem;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border: 1px solid rgba(0,0,0,0.05);
    }

    .shortcut-chip:hover {
        transform: translateY(-4px) scale(1.05);
        box-shadow: 0 12px 20px -5px rgba(0,0,0,0.1);
    }

    .chip-primary {
        background: #0ea5e9;
        color: #fff !important;
        box-shadow: 0 8px 16px -4px rgba(14, 165, 233, 0.4);
    }

    .chip-secondary {
        background: rgba(255, 255, 255, 0.85);
        color: #1e293b !important;
        border: 1px solid rgba(0,0,0,0.05);
    }

    [data-theme="dark"] .chip-secondary {
        background: rgba(30, 41, 59, 0.8);
        color: #f8fafc !important;
        border-color: rgba(255,255,255,0.05);
    }

    /* 3. METRIC CARDS REFINEMENT */
    /* Preserving original gradients but improving feel */
    .metric-card {
        border: none;
        border-radius: 1.5rem;
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .metric-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15) !important;
    }

    .metric-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background: linear-gradient(225deg, rgba(255,255,255,0.2) 0%, transparent 50%);
        pointer-events: none;
    }

    .metric-icon {
        font-size: 5.5rem;
        opacity: 0.12;
        position: absolute;
        right: -10px; bottom: -20px;
        transform: rotate(-15deg);
        transition: all 0.5s ease;
    }

    .metric-card:hover .metric-icon {
        transform: rotate(0deg) scale(1.1);
        opacity: 0.2;
    }

    .bg-gradient-warning { background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%); }
    .bg-gradient-danger { background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); }
    .bg-gradient-primary { background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); }
    .bg-gradient-success { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
    .bg-gradient-info { background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); }
    .bg-gradient-secondary { background: linear-gradient(135deg, #64748b 0%, #475569 100%); }
    .bg-gradient-success-alt { background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); }

    /* footer transition */
    .card-footer-premium {
        background: rgba(0, 0, 0, 0.08);
        border-top: 1px solid rgba(255, 255, 255, 0.05);
        padding: 1.25rem !important;
        backdrop-filter: blur(5px);
    }

    .section-title {
        font-weight: 800;
        font-size: 1.75rem;
        letter-spacing: -1px;
        position: relative;
        padding-bottom: 0.75rem;
        color: var(--text-main);
    }

    .section-title i {
        background: var(--accent-gradient);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    /* DASHBOARD RESPONSIVENESS */
    @media (max-width: 991.98px) {
        .premium-welcome-banner { padding: 2rem 1.5rem; border-radius: 1.5rem; }
        .welcome-title { font-size: 2rem; }
        .user-avatar-premium { width: 70px; height: 70px; }
        .action-bar-premium { flex-direction: column; width: 100%; }
        .shortcut-chip { width: 100%; justify-content: center; }
    }

    @media (max-width: 767.98px) {
        .page-header-standard {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 0.5rem !important;
            margin-bottom: 1.5rem;
        }
        .page-header-standard .breadcrumb { display: none; }
        .welcome-content .d-flex.align-items-center.gap-4 {
            flex-direction: column;
            text-align: center;
        }
    }
</style>

<div id="layoutSidenav_content">
    <div class="container-fluid px-4 py-4">
        
        <header class="page-header-standard d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 animate__animated animate__fadeIn">
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
                    <div class="user-avatar-premium overflow-hidden">
                        <?php 
                        $foto_perfil = $_SESSION['foto'] ?? '';
                        if (!empty($foto_perfil) && file_exists($foto_perfil)): ?>
                            <img src="<?php echo htmlspecialchars($foto_perfil); ?>" alt="Avatar">
                        <?php else: ?>
                            <div class="d-flex align-items-center justify-content-center w-100 h-100 bg-white bg-opacity-20 text-white fw-bold fs-2" style="border-radius: 1.5rem;">
                                <?php echo strtoupper(substr($_SESSION['nombre'], 0, 1) ?: 'U'); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h1 class="welcome-title mb-1">
                            <?php echo $saludo . ', ' . $_SESSION['nombre']; ?>! 👋
                        </h1>
                        <p class="text-secondary mb-0 fw-semibold fs-5 opacity-75"><?php echo $descripcion_sistema; ?></p>
                    </div>
                </div>
                
                <div class="d-flex flex-column align-items-lg-end gap-3">
                    <div class="action-bar-premium flex-wrap justify-content-lg-end">
                        <?php if ($_SESSION["tipo"] == "admin") { ?>
                            <a href="javascript:void(0);" onclick="navigateTo('registro_u.php')" class="shortcut-chip chip-primary text-decoration-none">
                                <i class="fas fa-user-plus"></i>Nuevo Usuario
                            </a>
                            <a href="javascript:void(0);" onclick="navigateTo('registro_bien.php')" class="shortcut-chip chip-secondary text-decoration-none">
                                <i class="fas fa-box-open"></i>Nuevo Bien
                            </a>
                            <a href="javascript:void(0);" onclick="navigateTo('registro_pagos_egresos.php')" class="shortcut-chip chip-secondary text-decoration-none">
                                <i class="fas fa-file-invoice-dollar"></i>Reg. Egreso
                            </a>
                        <?php } elseif ($_SESSION["tipo"] == "cont") { ?>
                            <a href="javascript:void(0);" onclick="navigateTo('registro_pagos_egresos.php')" class="shortcut-chip chip-primary text-decoration-none">
                                <i class="fas fa-plus-circle"></i>Registrar Egreso
                            </a>
                            <a href="javascript:void(0);" onclick="navigateTo('ver_pagos_cont.php')" class="shortcut-chip chip-secondary text-decoration-none">
                                <i class="fas fa-search-dollar"></i>Revisar Pagos
                            </a>
                        <?php } elseif ($_SESSION["tipo"] == "inv") { ?>
                            <a href="javascript:void(0);" onclick="navigateTo('registro_bien.php')" class="shortcut-chip chip-primary text-decoration-none">
                                <i class="fas fa-plus-circle"></i>Registrar Bien
                            </a>
                            <a href="javascript:void(0);" onclick="navigateTo('lista_bienes.php')" class="shortcut-chip chip-secondary text-decoration-none">
                                <i class="fas fa-list"></i>Ver Inventario
                            </a>
                        <?php } elseif ($_SESSION["tipo"] == "upu") { ?>
                            <a href="javascript:void(0);" onclick="navigateTo('registro_pagos.php')" class="shortcut-chip chip-primary text-decoration-none">
                                <i class="fas fa-upload"></i>Reportar Pago
                            </a>
                            <a href="javascript:void(0);" onclick="navigateTo('ver_pagos.php')" class="shortcut-chip chip-secondary text-decoration-none">
                                <i class="fas fa-history"></i>Historial Pagos
                            </a>
                        <?php } ?>
                    </div>
                    
                    <div class="text-muted small d-flex align-items-center gap-3">
                        <span class="opacity-75"><i class="far fa-clock me-1"></i>Última conexión: <strong class="text-dark bg-white bg-opacity-10 px-2 py-0.5 rounded"><?php echo $_SESSION['ultima_conexion']; ?></strong></span>
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-1 rounded-pill fw-bold">
                            <i class="fas fa-user-shield me-1"></i> <?php echo strtoupper($tipo_usuario); ?>
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
