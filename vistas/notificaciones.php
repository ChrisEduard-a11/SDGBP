<?php
require_once("../models/header.php");
require_once("../conexion.php");
require_once("../models/notificaciones.php");

$usuario_id = $_SESSION['id'] ?? null;
$tipo_usuario = $_SESSION['tipo'] ?? null;

// Preservar notificaciones locales (como las alertas de contraseña)
$notificaciones_locales = [];
if (isset($notificaciones) && is_array($notificaciones)) {
    foreach ($notificaciones as $n) {
        if (!isset($n['id'])) {
            $n['leida'] = 0; // Marcar como no leída para que resalte
            if (!isset($n['fecha'])) $n['fecha'] = date('Y-m-d H:i:s');
            $notificaciones_locales[] = $n;
        }
    }
}

if ($tipo_usuario == 'admin') {
    $notificaciones_db = obtenerTodasLasNotificaciones($conexion);
} else {
    $notificaciones_db = obtenerNotificaciones($conexion, $usuario_id);
    marcarNotificacionesComoLeidas($conexion, $usuario_id);
}

// Unir las notificaciones locales con el historial completo de la base de datos
$notificaciones = array_merge($notificaciones_locales, $notificaciones_db);
?>
<style>
    /* =========================================
       SISTEMA SDGBP - DISEÑO ULTRA PREMIUM 2026
       CENTRO DE NOTIFICACIONES
       ========================================= */
    :root {
        --primary: #f18000;
        --primary-dark: #d67100;
        --bg-body: #f8fafc;
        --text-main: #1e293b;
        --text-muted: #64748b;
        --border-color: #e2e8f0;
        --radius-premium: 20px;
        --shadow-premium: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
        --glass: rgba(255, 255, 255, 0.8);
        --glass-border: rgba(255, 255, 255, 0.3);
    }

    .breadcrumb-premium {
        background: var(--glass) !important;
        backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border) !important;
        border-radius: 12px !important;
        box-shadow: var(--shadow-premium);
    }

    .card-premium {
        background: transparent;
        border: none !important;
        border-radius: var(--radius-premium) !important;
        overflow: hidden;
    }

    .card-premium-header {
        padding: 1.5rem 2rem;
        border: none !important;
        color: white;
    }

    .header-notif { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }

    .card-premium-header h5 {
        margin: 0;
        font-weight: 700;
        letter-spacing: 0.5px;
    }

    .notif-item {
        transition: all 0.2s ease;
        border-bottom: 1px solid var(--border-color);
        background: #ffffff;
    }
    
    .notif-item:last-child {
        border-bottom: none;
    }

    .notif-item:hover {
        background-color: #f8fafc;
        transform: translateX(5px);
    }

    .notif-unread {
        background-color: #f1f5f9;
        border-left: 4px solid var(--primary);
    }

    .avatar-icon-circle {
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: 1.25rem;
    }

    [data-theme="dark"] .notif-item {
        background: #111111;
        border-color: #333;
    }
    
    [data-theme="dark"] .notif-item:hover {
        background: #1a1a1a;
    }
    
    [data-theme="dark"] .notif-unread {
        background: #1e1e1e;
    }
</style>

<div id="layoutSidenav_content">
    <div class="container-fluid px-4 py-4">
        
        <header class="page-header-standard d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
            <div>
                <h1 class="fw-bold mb-0 text-primary"><i class="fas fa-bell me-2"></i>Centro de Notificaciones</h1>
                <p class="text-muted">Revisa tus alertas y mensajes del sistema</p>
            </div>
            <nav aria-label="breadcrumb" class="d-none d-lg-block">
                <ol class="breadcrumb bg-transparent p-0 m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigateTo('inicio.php')" class="text-decoration-none fw-bold">Dashboard</a></li>
                    <li class="breadcrumb-item active fw-bold">Notificaciones</li>
                </ol>
            </nav>
        </header>

        <!-- LISTADO DE NOTIFICACIONES -->
        <div class="card card-premium shadow-lg border-0 animate__animated animate__fadeInUp">
            <div class="card-premium-header header-notif d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-inbox me-2"></i> Tus Mensajes Recientes</h5>
                <span class="badge bg-white text-dark rounded-pill px-3 py-2 fw-bold">
                    Total: <?php echo count($notificaciones); ?>
                </span>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush rounded-bottom">
                    <?php if (count($notificaciones) > 0) { 
                        foreach ($notificaciones as $notificacion) { 
                            $bg_class = ($notificacion['leida'] == 0) ? 'notif-unread' : '';
                            ?>
                            <div class="notif-item d-flex gap-3 p-4 align-items-center <?php echo $bg_class; ?>">
                                <div class="flex-shrink-0">
                                    <div class="avatar-icon-circle bg-<?php echo $notificacion['tipo']; ?> bg-opacity-10 text-<?php echo $notificacion['tipo']; ?>">
                                        <i class="<?php echo $notificacion['icono']; ?>"></i>
                                    </div>
                                </div>
                                <div class="d-flex flex-column flex-md-row gap-2 w-100 justify-content-between align-items-md-center">
                                    <div>
                                        <h6 class="mb-1 fw-bold text-dark"><?php echo htmlspecialchars($notificacion['titulo']); ?></h6>
                                        <p class="mb-1 text-muted" style="font-size: 0.9rem; letter-spacing: 0.2px;"><?php echo htmlspecialchars($notificacion['mensaje']); ?></p>
                                        <?php if ($tipo_usuario == 'admin' && !empty($notificacion['nombre_usuario'])) { ?>
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 mt-2 rounded-pill shadow-sm">
                                                <i class="fas fa-user-circle me-1"></i> Para: <?php echo htmlspecialchars($notificacion['nombre_usuario']); ?>
                                            </span>
                                        <?php } ?>
                                    </div>
                                    <div class="opacity-50 text-nowrap mt-2 mt-md-0 d-flex align-items-center small fw-semibold">
                                        <i class="far fa-clock me-1"></i>
                                        <?php echo date('d/m/Y h:i A', strtotime($notificacion['fecha'])); ?>
                                    </div>
                                </div>
                            </div>
                        <?php } 
                    } else { ?>
                        <div class="p-5 text-center text-muted bg-white">
                            <i class="far fa-bell-slash fa-4x mb-4 text-secondary opacity-50"></i>
                            <h4 class="fw-bold text-dark">Bandeja vacía</h4>
                            <p class="fs-6">Actualmente no tienes ninguna notificación registrada en el sistema.</p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
<?php
require_once("../models/footer.php");
?>
