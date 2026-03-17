<?php
session_start();
require_once("../conexion.php");

$usuarioid = $_SESSION['id'] ?? null;
$session_token = $_SESSION['session_token'] ?? '';

// Lógica de validación de sesión (INTACTA)
if (!$usuarioid || !$session_token) {
    header("Location: ../vistas/denegado_a.php");
    exit;
} // Consulta consolidada de seguridad y vigencia de clave
// Usamos una sola consulta para optimizar y evitar inconsistencias
$sql_seguridad = "SELECT session_token, fecha_cambio_clave, tipos FROM usuario WHERE id_usuario = '$usuarioid'";
$res_seguridad = mysqli_query($conexion, $sql_seguridad);
$row = mysqli_fetch_assoc($res_seguridad);

// Validar Token de sesión (Sincronización con lineas 18-25 del archivo original)
if (!$row || $row['session_token'] !== $session_token) {
    session_unset();
    session_destroy();
    header("Location: ../vistas/login.php?msg=Sesion%20invalida");
    exit;
}

$tipo_usuario = $row['tipos'];
$nombre_usuario = $_SESSION['nombre'];

// Lógica de Vencimiento de Contraseña
$fecha_db = $row['fecha_cambio_clave'] ?? '';
if (empty($fecha_db) || $fecha_db == '0000-00-00') {
    $fecha_cambio = '2000-01-01'; // Forzar actualización inmediata
}
else {
    $fecha_cambio = $fecha_db;
}

// Cálculo robusto de días para vencimiento
$dias_transcurridos = floor((time() - strtotime($fecha_cambio)) / 86400);
$dias_para_vencer = 180 - $dias_transcurridos;

$notificaciones = [];
if ($dias_para_vencer <= 15) {
    if ($dias_para_vencer <= 0) {
        $notificaciones[] = [
            'titulo' => 'Contraseña Vencida',
            'mensaje' => 'Tu contraseña ha vencido. Por favor cámbiala por seguridad.',
            'tipo' => 'danger',
            'icono' => 'fas fa-exclamation-triangle'
        ];
    }
    else {
        $notificaciones[] = [
            'titulo' => 'Cambio de Contraseña',
            'mensaje' => "Tu contraseña vencerá en $dias_para_vencer días.",
            'tipo' => 'warning',
            'icono' => 'fas fa-key'
        ];
    }
}

// BLOQUEO OBLIGATORIO (Universal: Incluye Admins)
$current_page = basename($_SERVER['PHP_SELF']);
if ($dias_para_vencer <= 0 && $current_page !== 'nueva_clave.php' && $current_page !== 'restablecer_contraseña.php') {
    // Calculamos la ruta absoluta para evitar fallos de redirección
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
    $redir_url = "$protocol://" . $_SERVER['HTTP_HOST'] . rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/') . "/vistas/nueva_clave.php?vencida=1";

    // Intento de redirección por Header (Limpios)
    if (!headers_sent()) {
        header("Location: $redir_url");
        exit;
    }
    else {
        // Respaldo por HTML/JS si las cabeceras ya se enviaron (Fallos silenciosos corregidos)
        echo "<html><body><script>window.location.href='$redir_url';</script></body></html>";
        exit;
    }
}
?>
<!DOCTYPE html>
    <html lang="es">
        <head>
            <meta charset="utf-8" />
            <meta http-equiv="X-UA-Compatible" content="IE=edge" />
            <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
            <meta name="description" content="" />
            <meta name="author" content="" />
            <!--<link rel="canonical" href="https://sdgbp.wuaze.com/<?php echo basename($_SERVER['REQUEST_URI']); ?>" />-->
            <title>SDGBP - Sistema de Gestión de Bienes y Pagos</title>

            <link rel="icon" type="image/x-icon" href="../img/favicon.ico">

            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
            <link href="../css/styles.css" rel="stylesheet" />
            <link href="../css/estilos.css" rel="stylesheet" />
            <link rel="stylesheet" type="text/css" href="../css/header.css">
            
            <link href="../css/font_google.css" rel="stylesheet">

            <link rel="stylesheet" type="text/css" href="../sweetalert/sweetalert2.min.css">
            <script src="../sweetalert/sweetalert2.js"></script>

            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/css/bootstrap-datepicker.min.css">
            <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/locales/bootstrap-datepicker.es.min.js"></script>

            <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
            <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
            <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

            <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
            <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
            <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
            
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
            <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
            
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

            <!-- DATA TABLES JQUERY + BOOTSTRAP 5 -->
            <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
            <?php require_once("../models/validation.php"); ?>

            <style>
                @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

                /* Estilos 2026 Premium - Contable/Inventario Profesional */
                :root {
                    /* Mesh Gradient Premium "Soft Aurora" */
                    --bg-app: fixed linear-gradient(135deg, #f8fafc 0%, #e2e8f0 50%, #f1f5f9 100%),
                              fixed radial-gradient(at 0% 0%, rgba(251, 146, 60, 0.05) 0, transparent 50%),
                              fixed radial-gradient(at 50% 0%, rgba(59, 130, 246, 0.05) 0, transparent 50%);
                    --sidebar-width: 280px;
                    --sidebar-bg: #bdbdbdff; /* Sidebar Blanco Puro (Corporate Admin Style) */
                    --navbar-bg: #fb923c; 
                    --navbar-text: #ffffff; 
                    
                    --accent-color: #ea580c; 
                    --accent-glow: rgba(234, 88, 12, 0.15);
                    --accent-gradient: linear-gradient(135deg, #f97316 0%, #fb923c 100%);
                    
                    /* Textos adaptados para fondos claros */
                    --text-main: #1e293b; /* Azul medianoche muy oscuro (casi negro) */
                    --text-muted: #475569; /* Gris pizarra oscuro para legibilidad */
                    --sidebar-text: #334155; 
                    --sidebar-text-muted: #64748b; 
                    --sidebar-border: #e2e8f0; 

                    --glass-border-light: rgba(255, 255, 255, 0.6);
                    --glass-border-dark: rgba(0, 0, 0, 0.05);

                    --shadow-sm: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
                    --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03);
                    --shadow-float: 0 10px 25px -5px rgba(0,0,0,0.08);
                    --shadow-sidebar: 2px 0 10px rgba(0,0,0,0.04); /* Sombra lateral suave para despegar el blanco del fondo gris app */
                    
                    --radius-xl: 12px; /* Menos redondo, más corporativo/serio */
                    --radius-pill: 9999px;
                    
                    /* Nueva variable para fondo adaptable con imagen */
                    --content-overlay: rgba(244, 247, 249, 0.88); 

                    /* Variables para que el footer y otros componentes sean dinámicos */
                    --footer-bg: #f8fafc; /* Fondo claro para el footer en modo claro */
                    --footer-text: #475569; /* Texto gris oscuro legible */
                }

                /* ================= TEMA OSCURO (Dark Mode) ================= */
                [data-theme="dark"] {
                    /* Mesh Gradient Premium "Deep Nebula" */
                    --bg-app: fixed linear-gradient(135deg, #0f172a 0%, #1e293b 100%),
                              fixed radial-gradient(at 100% 0%, rgba(251, 146, 60, 0.1) 0, transparent 50%),
                              fixed radial-gradient(at 0% 100%, rgba(59, 130, 246, 0.1) 0, transparent 50%);
                    --sidebar-bg: #1e293b; 
                    --navbar-bg: #1e293b; 
                    --navbar-text: #f8fafc; 
                    
                    --accent-color: #fb923c; 
                    --accent-glow: rgba(251, 146, 60, 0.25);
                    --accent-gradient: linear-gradient(135deg, #ea580c 0%, #f97316 100%);
                    
                    --text-main: #f8fafc; 
                    --text-muted: #cbd5e1; /* Gris mucho más claro para legibilidad */
                    --sidebar-text: #e2e8f0; 
                    --sidebar-border: rgba(255,255,255,0.05); 
                    --footer-bg: #0b1120; 
                    --footer-text: #cbd5e1; /* Gris claro para el footer */
                    
                    --shadow-sm: 0 1px 3px rgba(0,0,0,0.3);
                    --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.4);
                    --shadow-float: 0 10px 25px -5px rgba(0,0,0,0.5);
                    --shadow-sidebar: 2px 0 12px rgba(0,0,0,0.3); 
                    
                    /* Overlay oscuro para que la imagen de fondo sea sutil */
                    --content-overlay: rgba(15, 23, 42, 0.94);
                }

                /* Forzado de colores Dark para elementos estándar de Bootstrap */
                [data-theme="dark"] .bg-white, 
                [data-theme="dark"] .card,
                [data-theme="dark"] .modal-content {
                    background-color: #1e293b !important;
                    color: #f8fafc !important;
                    border-color: rgba(255,255,255,0.05) !important;
                }
                [data-theme="dark"] .text-dark:not(.btn *),
                [data-theme="dark"] .card-title,
                [data-theme="dark"] .modal-title,
                [data-theme="dark"] .breadcrumb-item.active,
                [data-theme="dark"] strong:not(.alert *), [data-theme="dark"] b:not(.alert *), 
                [data-theme="dark"] span:not(.badge):not(.alert *),
                [data-theme="dark"] label:not(.btn),
                [data-theme="dark"] .table td, [data-theme="dark"] .table th,
                [data-theme="dark"] .card-body:not(.alert *),
                [data-theme="dark"] p:not(.alert *) {
                    color: #f8fafc !important;
                }
                [data-theme="dark"] .text-secondary,
                [data-theme="dark"] .text-muted,
                [data-theme="dark"] .small,
                [data-theme="dark"] p.text-secondary {
                    color: #cbd5e1 !important; /* Forzar gris legible */
                }
                [data-theme="dark"] .text-primary, [data-theme="dark"] .page-title-icon { color: #60a5fa !important; }
                [data-theme="dark"] .text-success, [data-theme="dark"] .saldo-display { color: #4ade80 !important; }
                [data-theme="dark"] .text-info { color: #22d3ee !important; }
                
                [data-theme="dark"] .breadcrumb {
                    background-color: #1a2235 !important;
                    border: 1px solid rgba(255,255,255,0.05) !important;
                }
                [data-theme="dark"] .breadcrumb-item a {
                    color: #fb923c !important;
                }
                [data-theme="dark"] .alert-super-admin {
                    background-color: #1e293b !important;
                    color: #f8fafc !important;
                    border-left-color: #fb923c !important;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.2) !important;
                }
                [data-theme="dark"] .alert-super-admin .text-muted,
                [data-theme="dark"] .alert-super-admin .small,
                [data-theme="dark"] .alert-super-admin strong {
                    color: #94a3b8 !important;
                }
                [data-theme="dark"] .img-circular {
                    border-color: rgba(255,255,255,0.1) !important;
                    box-shadow: 0 0 8px rgba(0,0,0,0.4) !important;
                }
                
                /* Correcciones específicas para estilos embebidos en Vistas */
                [data-theme="dark"] #layoutSidenav_content .bg-light,
                [data-theme="dark"] #layoutSidenav_content form.bg-light,
                [data-theme="dark"] #layoutSidenav_content .bg-white,
                [data-theme="dark"] #layoutSidenav_content .card {
                    background-color: #1e293b !important;
                    color: #f8fafc !important;
                    border-color: rgba(255,255,255,0.05) !important;
                }
                [data-theme="dark"] #layoutSidenav_content .table th {
                    background-color: #111827 !important;
                    color: #f8fafc !important;
                }
                [data-theme="dark"] #layoutSidenav_content .ingreso-cell {
                    background-color: rgba(16, 185, 129, 0.1) !important;
                    color: #4ade80 !important;
                    border: none !important;
                }
                [data-theme="dark"] #layoutSidenav_content .egreso-cell {
                    background-color: rgba(239, 68, 68, 0.1) !important;
                    color: #f87171 !important;
                    border: none !important;
                }
                [data-theme="dark"] #layoutSidenav_content .saldo-final {
                    background-color: rgba(59, 130, 246, 0.1) !important;
                    color: #60a5fa !important;
                }
                [data-theme="dark"] #layoutSidenav_content .text-primary {
                    color: #fb923c !important; 
                }
                [data-theme="dark"] .card-header-custom,
                [data-theme="dark"] .card-header-secondary {
                    background-color: #334155 !important;
                    border-bottom-color: rgba(255,255,255,0.1) !important;
                }
                
                /* Forzado total de color de texto en el area de contenido */
                [data-theme="dark"] #layoutSidenav_content * {
                    border-color: rgba(255,255,255,0.05);
                }
                [data-theme="dark"] .table-striped>tbody>tr:nth-of-type(odd)>* {
                    --bs-table-accent-bg: rgba(255,255,255,0.02) !important;
                    color: #f8fafc !important;
                }

                /* Estilos para Alertas en Modo Oscuro (Texto Negro s/ fondo color) */
                [data-theme="dark"] .alert {
                    border: none !important;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.2) !important;
                    color: #1e293b !important; /* Texto oscuro para legibilidad */
                }
                [data-theme="dark"] .alert-info { background-color: #7dd3fc !important; }
                [data-theme="dark"] .alert-success { background-color: #6ee7b7 !important; }
                [data-theme="dark"] .alert-warning { background-color: #fde047 !important; }
                [data-theme="dark"] .alert-danger { background-color: #fca5a5 !important; }
                [data-theme="dark"] .alert,
                [data-theme="dark"] .alert *,
                [data-theme="dark"] .alert .text-dark, 
                [data-theme="dark"] .alert .alert-heading {
                    color: #000000 !important;
                    border-color: rgba(0,0,0,0.1) !important;
                }
                
                /* --- Estilos Flatpickr (Calendario) Modo Oscuro --- */
                [data-theme="dark"] .flatpickr-calendar {
                    background: #1e293b !important;
                    box-shadow: 0 10px 25px rgba(0,0,0,0.5) !important;
                    border: 1px solid rgba(255,255,255,0.1) !important;
                }
                [data-theme="dark"] .flatpickr-day, 
                [data-theme="dark"] .flatpickr-month,
                [data-theme="dark"] .flatpickr-weekday,
                [data-theme="dark"] .flatpickr-current-month,
                [data-theme="dark"] .flatpickr-monthDropdown-months,
                [data-theme="dark"] .cur-month, [data-theme="dark"] .numInput {
                    color: #f8fafc !important;
                    fill: #f8fafc !important;
                }
                [data-theme="dark"] .flatpickr-day.nextMonthDay, 
                [data-theme="dark"] .flatpickr-day.prevMonthDay {
                    color: rgba(255,255,255,0.2) !important;
                }
                [data-theme="dark"] .flatpickr-day:hover,
                [data-theme="dark"] .flatpickr-day.prevMonthDay:hover,
                [data-theme="dark"] .flatpickr-day.nextMonthDay:hover {
                    background: rgba(255,255,255,0.1) !important;
                    color: #fff !important;
                }
                [data-theme="dark"] .flatpickr-day.selected {
                    background: var(--accent-color) !important;
                    border-color: var(--accent-color) !important;
                    color: #fff !important;
                }
                [data-theme="dark"] .flatpickr-months .flatpickr-prev-month, 
                [data-theme="dark"] .flatpickr-months .flatpickr-next-month {
                    color: #f8fafc !important;
                    fill: #f8fafc !important;
                }

                /* --- Estilos SweetAlert2 Modo Oscuro --- */
                [data-theme="dark"] .swal2-popup {
                    background-color: #1e293b !important;
                    color: #f8fafc !important;
                    border: 1px solid rgba(255,255,255,0.1) !important;
                    box-shadow: 0 10px 25px rgba(0,0,0,0.5) !important;
                }
                [data-theme="dark"] .swal2-title {
                    color: #ffffff !important; /* Blanco puro para el título solicitado */
                }
                [data-theme="dark"] .swal2-html-container {
                    color: #cbd5e1 !important; /* Gris claro para el cuerpo del texto */
                }
                [data-theme="dark"] .swal2-icon.swal2-warning {
                    border-color: #facc15 !important;
                    color: #facc15 !important;
                }
                [data-theme="dark"] .swal2-icon.swal2-info {
                    border-color: #38bdf8 !important;
                    color: #38bdf8 !important;
                }
                [data-theme="dark"] .swal2-success-circular-line, [data-theme="dark"] .swal2-success-fix {
                    background-color: transparent !important;
                }

                /* --- Fix para Modales con Fondo Adaptable --- */
                .modal {
                    z-index: 1060 !important;
                }
                .modal-backdrop {
                    z-index: 1050 !important;
                }
                
                body {
                    font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif !important;
                    background: var(--bg-app) !important; /* Corregido: background permite degradados */
                    color: var(--text-main) !important; 
                    animation: fadeInBody 0.6s ease-out;
                    min-height: 100vh;
                }
                
                /* Forzado de visibilidad para Títulos en todas las vistas */
                h1, h2, h3, h4, h5, h6, .page-title, .page-title *, h3 * {
                    color: var(--text-main) !important;
                    text-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
                    font-weight: 700 !important;
                }

                /* Forzado para el Footer */
                #footer, #footer *, #footer p, #footer span {
                    color: var(--footer-text) !important;
                }
                #footer .fw-bold, #footer span.fw-bold {
                    color: var(--text-main) !important;
                }

                /* ================= NAVBAR SUPERIOR (Adaptable) ================= */
                .bg-navbar {
                    background-color: var(--navbar-bg) !important;
                    background-image: linear-gradient(to right, #ea580c, #f97316); /* Gradiente naranja premium */
                    border-bottom: 2px solid rgba(0,0,0,0.1); 
                    box-shadow: 0 4px 12px rgba(234, 88, 12, 0.2);
                    padding-top: 0.5rem;
                    padding-bottom: 0.5rem;
                    z-index: 1040;
                }
                [data-theme="dark"] .bg-navbar {
                    background-image: none !important;
                    border-bottom: 1px solid rgba(255,255,255,0.05);
                    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                }
                
                .navbar-brand {
                    font-weight: 800;
                    font-size: 1.4rem;
                    letter-spacing: -0.5px;
                    color: var(--navbar-text) !important;
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    text-shadow: 1px 1px 2px rgba(0,0,0,0.1); /* Ligera sombra para asegurar legibilidad */
                }
                .navbar-brand span {
                    color: var(--navbar-text) !important;
                }
                .navbar-brand img {
                    border: 3px solid #fff;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                    border-radius: 12px !important;
                    transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
                }
                .navbar-brand:hover img {
                    transform: scale(1.1) rotate(-5deg);
                }

                /* Control Lateral (Hamburguesa) */
                #sidebarToggle {
                    color: var(--navbar-text) !important;
                    background: rgba(255, 255, 255, 0.15); /* Fondo semitransparente sobre naranja */
                    border-radius: var(--radius-xl);
                    width: 42px;
                    height: 42px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border: 1px solid rgba(255,255,255,0.2);
                    transition: all 0.3s ease;
                }
                #sidebarToggle:hover {
                    background: rgba(255, 255, 255, 0.3);
                    color: #fff !important;
                    transform: translateY(-2px);
                }

                /* Reloj Navbar */
                #fecha {
                    color: var(--navbar-text) !important;
                    font-weight: 600;
                    font-size: 0.95rem;
                    padding: 8px 16px;
                    background: rgba(0,0,0,0.15); /* Fondo oscuro semitransparente sobre naranja */
                    border-radius: var(--radius-pill);
                }

                /* Iconos Navbar (Notif / User) */
                .navbar-nav .nav-link {
                    color: var(--navbar-text) !important;
                    opacity: 0.9;
                    padding: 0.5rem 0.8rem !important;
                    position: relative;
                    transition: all 0.3s ease;
                }
                .navbar-nav .nav-link:hover {
                    color: #fff !important;
                    opacity: 1;
                    transform: scale(1.1);
                }
                .navbar-nav .nav-link i {
                    font-size: 1.25rem;
                }
                .navbar-nav .dropdown-toggle::after {
                    display: none; /* Quitamos flecha por defecto */
                }

                /* Notificaciones Badge */
                .badge-pulse {
                    animation: pulse-badge 2s infinite;
                    box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4);
                    border: 2px solid #fff;
                    padding: 0.35em 0.55em;
                }
                @keyframes pulse-badge {
                    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
                    70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(220, 53, 69, 0); }
                    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
                }

                /* ================= SUPER DROPDOWNS FLOTANTES ================= */
                .dropdown-menu {
                    border-radius: 20px;
                    box-shadow: var(--shadow-float);
                    border: 1px solid var(--glass-border-light);
                    padding: 1rem;
                    margin-top: 15px !important;
                    background: rgba(255, 255, 255, 0.95);
                    backdrop-filter: blur(20px);
                    animation: dropFade 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
                    transform-origin: top right;
                }
                @keyframes dropFade {
                    0% { opacity: 0; transform: translateY(-10px) scale(0.95); }
                    100% { opacity: 1; transform: translateY(0) scale(1); }
                }
                .dropdown-header {
                    font-size: 1rem;
                    color: var(--text-main);
                    padding: 0.5rem 1rem;
                }
                .dropdown-divider {
                    border-color: rgba(0,0,0,0.06);
                    margin: 0.5rem 0;
                }

                /* Dropdown en Modo Oscuro */
                [data-theme="dark"] .dropdown-menu {
                    background: rgba(30, 41, 59, 0.98) !important;
                    border-color: rgba(255,255,255,0.1) !important;
                    box-shadow: 0 10px 25px rgba(0,0,0,0.5) !important;
                }
                [data-theme="dark"] .dropdown-item {
                    color: #f8fafc !important;
                }
                [data-theme="dark"] .dropdown-item:hover {
                    background-color: rgba(255,255,255,0.05) !important;
                }
                [data-theme="dark"] .dropdown-divider {
                    border-color: rgba(255,255,255,0.1) !important;
                }
                [data-theme="dark"] .dropdown-header,
                [data-theme="dark"] .dropdown-header * {
                    color: #94a3b8 !important; /* Gris azulado para el titulo del header */
                }
                [data-theme="dark"] .dropdown-header .text-dark {
                    color: #ffffff !important; /* Forzar blanco para el nombre del usuario */
                }
                
                /* User Dropdown Profile */
                .dropdown-menu img.rounded-circle.shadow-sm {
                    border: 4px solid #fff !important;
                    box-shadow: var(--shadow-md) !important;
                    width: 110px!important; 
                    height: 110px!important;
                }
                .btn-danger.rounded-pill {
                    background: #fee2e2;
                    color: #ef4444;
                    border: none;
                    font-weight: 600;
                    padding: 10px 20px;
                    box-shadow: none;
                    transition: all 0.3s ease;
                }
                .btn-danger.rounded-pill:hover {
                    background: #ef4444;
                    color: #fff;
                    transform: translateY(-2px);
                    box-shadow: 0 8px 15px -3px rgba(239, 68, 68, 0.3);
                }

                /* Notif Dropdown Items */
                .notif-item {
                    border: 1px solid transparent;
                    border-left-width: 4px !important;
                    background: #fff !important;
                    box-shadow: var(--shadow-sm);
                    margin-bottom: 8px;
                    border-radius: 12px !important;
                    transition: all 0.3s ease;
                }
                .notif-item:hover {
                    transform: translateX(5px);
                    box-shadow: var(--shadow-md);
                    border-color: rgba(0,0,0,0.03);
                    border-left-color: inherit !important;
                }


                /* Toggle Dark Mode Button */
                #btn-dark-mode {
                    background: rgba(255, 255, 255, 0.15);
                    border-radius: var(--radius-pill);
                    border: 1px solid rgba(255,255,255,0.2);
                    width: 40px;
                    height: 40px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    cursor: pointer;
                    color: #fff;
                    transition: all 0.3s ease;
                }
                #btn-dark-mode:hover {
                    background: rgba(255,255,255,0.25);
                    transform: scale(1.1);
                }
                [data-theme="dark"] #btn-dark-mode {
                    background: rgba(255,255,255,0.05);
                    color: #fbbf24;
                }

                /* ================= SIDEBAR (Light/Dark Switchable) ================= */
                .sb-sidenav-dark {
                    background: var(--sidebar-bg) !important;
                    box-shadow: var(--shadow-sidebar);
                    border-right: 1px solid var(--sidebar-border);
                }
                
                /* Títulos de sección */
                .sb-sidenav-menu-heading-pro {
                    padding: 1.5rem 1.25rem 0.6rem;
                    font-size: 0.72rem;
                    color: #64748b; /* Gris corporativo */
                    font-weight: 800;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                }

                /* Enlaces del Sidebar */
                .sb-sidenav .sb-sidenav-menu .nav-link {
                    color: var(--sidebar-text) !important; /* Forzar gris oscuro para que sea legible sobre blanco */
                    border-radius: 8px; /* Redondez suave para look corporativo */
                    margin: 0.25rem 0.85rem;
                    padding: 0.75rem 1rem;
                    display: flex;
                    align-items: center;
                    font-weight: 600; /* Letras un poco más fuertes y legibles */
                    font-size: 0.92rem;
                    position: relative;
                    transition: all 0.2s ease-in-out;
                    border: 1px solid transparent; /* Reserva de espacio para borde hover */
                }
                
                /* Efecto Hover Enlaces */
                .sb-sidenav-menu .nav-link:hover {
                    color: var(--accent-color) !important; 
                    background: #fff7ed; /* Naranjoso Ultra Claro */
                    border: 1px solid #ffedd5;
                    transform: translateX(2px); /* Desplazamiento sutil */
                }
                
                /* Enlace Activo */
                .sb-sidenav-menu .nav-link.active {
                    background: #fff2e5; /* Fondo naranjoso claro pero definido para el activo */
                    color: var(--accent-color) !important;
                    font-weight: 700;
                    border-left: 4px solid var(--accent-color); /* Indicador izquierdo estilo admin robusto */
                    border-radius: 4px 8px 8px 4px; /* Ajuste para el borde grueso izquierdo */
                    padding-left: 0.75rem; /* Ajustar padding por el borde izquierdo grueso */
                }
                
                /* Iconos del menú */
                .sb-sidenav .sb-sidenav-menu .sb-nav-link-icon {
                    color: var(--accent-color) !important; /* Iconos siempre naranjas fijos por solicitud */
                    font-size: 1.25rem;
                    width: 2.25rem;
                    transition: all 0.2s ease;
                    display: flex;
                    align-items: center;
                }
                /* Efectos Hover Iconos */
                .sb-sidenav-menu .nav-link:hover .sb-nav-link-icon,
                .sb-sidenav-menu .nav-link.active .sb-nav-link-icon {
                    color: var(--accent-color) !important;
                    transform: scale(1.15); /* Efecto pop más notable */
                }

                /* Flecha Rotativa */
                .sb-sidenav-collapse-arrow {
                    margin-left: auto;
                    transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
                    opacity: 0.5;
                }
                .sb-sidenav-menu .nav-link:hover .sb-sidenav-collapse-arrow {
                    opacity: 1;
                }
                .nav-link:not(.collapsed) .sb-sidenav-collapse-arrow {
                    transform: rotate(180deg);
                    opacity: 1;
                }

                /* Submenús Dropdowns Administrativos */
                .sb-sidenav-menu-nested {
                    margin-left: 1.2rem;
                    border-left: 1px solid var(--sidebar-border); /* Separador estricto contable */
                    padding-left: 0.25rem;
                    margin-bottom: 0.25rem;
                }
                .sb-sidenav-menu-nested .nav-link {
                    color: var(--sidebar-text) !important; /* Forzar legibilidad de submenús */
                    font-size: 0.85rem;
                    padding: 0.5rem 1rem;
                    margin: 0.15rem 0.5rem;
                    font-weight: 500;
                }

                /* Footer del Sidebar Claro/Oscuro */
                .sb-sidenav-footer {
                    background: var(--bg-app) !important; 
                    border-top: 1px solid var(--sidebar-border);
                    padding: 1.25rem !important;
                    transition: background 0.3s ease;
                }
                .sb-sidenav-footer .text-white-50 {
                    color: #64748b !important; /* Gris solido en vez de class white */
                    font-size: 0.75rem;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    margin-bottom: 2px;
                    font-weight: 600;
                }
                .sb-sidenav-footer .text-primary {
                    color: #0f172a !important; /* Rol de usuario en negro/gris muy oscuro */
                    font-size: 1rem;
                    font-weight: 700;
                }

                /* Scrollbar Minimalista Global */
                ::-webkit-scrollbar {
                    width: 8px;
                    height: 8px;
                }
                ::-webkit-scrollbar-track {
                    background: transparent;
                }
                ::-webkit-scrollbar-thumb {
                    background-color: rgba(148, 163, 184, 0.3);
                    border-radius: var(--radius-pill);
                    border: 2px solid var(--bg-app);
                }
                ::-webkit-scrollbar-thumb:hover {
                    background-color: rgba(148, 163, 184, 0.6);
                }

                /* Custom Premium Styles for Select2/Flatpickr si las hay en header */
                .select2-container--bootstrap-5 .select2-selection {
                    border-radius: var(--radius-xl);
                    padding: 0.5rem 1rem;
                    height: auto;
                    border: 1px solid rgba(0,0,0,0.1);
                    box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
                }
                .select2-container--bootstrap-5 .select2-selection:focus {
                    border-color: var(--accent-color);
                    box-shadow: 0 0 0 4px var(--accent-glow);
                }
                .flatpickr-calendar {
                    border-radius: 20px;
                    box-shadow: var(--shadow-float);
                    border: none;
                    padding: 10px;
                }
            </style>
        </head>
        <body class="sb-nav-fixed" >
        <nav class="sb-topnav navbar navbar-expand bg-navbar">
            <a class="navbar-brand ps-4 pe-2" href="javascript:void(0);" onclick="navigateTo('inicio.php')">
                <img src="<?php echo $_SESSION['foto']; ?>" alt="foto" width="38" height="38" style="object-fit: cover;"> 
                <span>SDGBP</span>
            </a>

            <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!">
                <i class="fas fa-bars"></i>
            </button>
            
            <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
                <div class="input-group"> 
                    <div onload="actualizarFechaHora()">
                        <h6 class="text-white mb-0" id="fecha"></h6>
                    </div>
                </div>
            </form>
            
            <ul class="navbar-nav ms-auto ms-md-0 me-3 d-flex align-items-center">
                
                <li class="nav-item me-2">
                    <div id="btn-dark-mode" onclick="toggleDarkMode()">
                        <i class="fas fa-moon"></i>
                    </div>
                </li>

                <!-- Dropdown de Notificaciones -->
                <li class="nav-item dropdown px-2">
                    <a class="nav-link dropdown-toggle" id="navbarDropdownNotif" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell"></i>
                        <?php if (count($notificaciones) > 0): ?>
                            <span class="position-absolute top-10 start-90 translate-middle badge rounded-pill bg-danger badge-pulse">
                                <?php echo count($notificaciones); ?>
                            </span>
                        <?php
endif; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-2" aria-labelledby="navbarDropdownNotif" style="width: 300px; background-color: #1e293b !important; border: 1px solid rgba(255,255,255,0.05) !important;">
                        <li class="dropdown-header text-center fw-bold text-white">Notificaciones</li>
                        <li><hr class="dropdown-divider border-secondary"></li>
                        <?php if (count($notificaciones) > 0): ?>
                            <?php foreach ($notificaciones as $notif): ?>
                                <li class="p-2">
                                        <div class="d-flex align-items-center p-3 rounded border-start border-4 border-<?php echo $notif['tipo']; ?> notif-item cursor-pointer" style="background-color: #1a2235 !important; color: #fff !important;">
                                        <div class="flex-shrink-0 me-3">
                                            <i class="<?php echo $notif['icono']; ?> text-<?php echo $notif['tipo']; ?> fs-4"></i>
                                        </div>
                                        <div>
                                            <div class="small fw-bold text-white"><?php echo $notif['titulo']; ?></div>
                                            <div class="small text-white-50"><?php echo $notif['mensaje']; ?></div>
                                        </div>
                                    </div>
                                </li>
                            <?php
    endforeach; ?>
                        <?php
else: ?>
                            <li class="dropdown-item text-center text-white-50 small py-3" style="background-color: transparent !important;">No tienes notificaciones pendientes</li>
                        <?php
endif; ?>
                    </ul>
                </li>

                <li class="nav-item dropdown px-2">
                    <a class="nav-link dropdown-toggle" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-astronaut"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-3" aria-labelledby="navbarDropdown">
                        <li class="dropdown-header text-center pb-2">
                            <span class="text-dark fw-bold d-block"><?php echo $nombre_usuario; ?></span>
                            <span class="small text-primary"><?php echo ucfirst($tipo_usuario); ?></span>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        
                        <li class="text-center mb-3">
                            <img src="<?php echo $_SESSION['foto']; ?>" alt="User Image" class="rounded-circle p-1 shadow-sm" width="100" height="100" style="object-fit: cover; border: 3px solid var(--accent-color);">
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        
                        <li class="text-center pt-2">
                            <button class="btn btn-danger btn-sm w-75 rounded-pill" onclick="confsalir(event)">
                                <i class="fas fa-sign-out-alt me-1"></i> Salir
                            </button>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
        
        <div id="layoutSidenav">
            <div id="layoutSidenav_nav">
                <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                    <div class="sb-sidenav-menu">
                        <div class="nav">
                            
                            <div class="sb-sidenav-menu-heading-pro">Navegación General</div>
                            <a class="nav-link" href="javascript:void(0);" onclick="navigateTo('../vistas/inicio.php')">
                                <div class="sb-nav-link-icon"><i class="fas fa-house-chimney fa-fw"></i></div>
                                Dashboard
                            </a>

                            <div class="sb-sidenav-menu-heading-pro">Mi Cuenta</div>
                            <a class="nav-link collapsed" data-bs-toggle="collapse" data-bs-target="#collapseUsuario" aria-expanded="false" aria-controls="collapseUsuario">
                                <div class="sb-nav-link-icon"><i class="fas fa-user-shield fa-fw"></i></div>
                                Perfil & Seguridad
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            <div class="collapse" id="collapseUsuario" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                                <nav class="sb-sidenav-menu-nested nav">
                                    <a class="nav-link" href="javascript:void(0);" onclick="navigateTo('bitacora.php')">
                                        <div class="sb-nav-link-icon"><i class="fas fa-book-open"></i></div>
                                        Bitácora
                                    </a>
                                    <?php if ($_SESSION["tipo"] == "admin") { ?>
                                        <a class="nav-link" href="javascript:void(0);" onclick="navigateTo('usuario.php')">
                                            <div class="sb-nav-link-icon"><i class="fas fa-user-gear"></i></div>
                                            Administrar Usuarios
                                        </a>
                                    <?php
}?>
                                    <?php if ($_SESSION["tipo"] == "upu" || $_SESSION["tipo"] == "cont" || $_SESSION["tipo"] == "inv") { ?>
                                        <a class="nav-link" href="javascript:void(0);" onclick="navigateTo('configuracion_usuario.php')">
                                            <div class="sb-nav-link-icon"><i class="fas fa-sliders-h"></i></div>
                                            Configuración
                                        </a>
                                    <?php
}?>
                                </nav>
                            </div>

                            <?php if ($_SESSION["tipo"] == "inv" || $_SESSION["tipo"] == "admin" || $_SESSION["tipo"] == "upu") { ?>
                            <div class="sb-sidenav-menu-heading-pro">Operaciones y Registros</div>
                            <?php
}?>

                            <?php if ($_SESSION["tipo"] == "inv" || $_SESSION["tipo"] == "admin") { ?>
                            <a class="nav-link collapsed" data-bs-toggle="collapse" data-bs-target="#collapseBienes" aria-expanded="false" aria-controls="collapseBienes">
                                <div class="sb-nav-link-icon"><i class="fas fa-boxes-stacked fa-fw"></i></div>
                                Inventario
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            <div class="collapse" id="collapseBienes" aria-labelledby="headingTwo" data-bs-parent="#sidenavAccordion">
                                <nav class="sb-sidenav-menu-nested nav">
                                    <a class="nav-link" href="javascript:void(0);" onclick="navigateTo('../vistas/registro_bien.php')">
                                        <div class="sb-nav-link-icon"><i class="fas fa-plus-circle"></i></div>
                                        Registrar Bien
                                    </a>
                                    <a class="nav-link" href="javascript:void(0);" onclick="navigateTo('../vistas/lista_bienes.php')">
                                        <div class="sb-nav-link-icon"><i class="fas fa-clipboard-list"></i></div>
                                        Lista de Bienes
                                    </a>
                                    <a class="nav-link" href="javascript:void(0);" onclick="navigateTo('../vistas/categorias.php')">
                                        <div class="sb-nav-link-icon"><i class="fas fa-tags"></i></div>
                                        Gestión de Categorías
                                    </a>
                                </nav>
                            </div>
                            <?php
}?>
                            
                            <?php if ($_SESSION["tipo"] == "admin") { ?>
                            <a class="nav-link collapsed" data-bs-toggle="collapse" data-bs-target="#collapseMarketing" aria-expanded="false" aria-controls="collapseBienes">
                                <div class="sb-nav-link-icon"><i class="fas fa-bullhorn fa-fw"></i></div>
                                Marketing
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            <div class="collapse" id="collapseMarketing" aria-labelledby="headingTwo" data-bs-parent="#sidenavAccordion">
                                <nav class="sb-sidenav-menu-nested nav">
                                    <a class="nav-link" href="javascript:void(0);" onclick="navigateTo('../vistas/agregar_producto.php')">
                                        <div class="sb-nav-link-icon"><i class="fas fa-cart-plus"></i></div>
                                        Registrar Producto
                                    </a>
                                    <a class="nav-link" href="javascript:void(0);" onclick="navigateTo('../vistas/productos.php')">
                                        <div class="sb-nav-link-icon"><i class="fas fa-store-alt"></i></div>
                                        Ver Productos
                                    </a>
                                </nav>
                            </div>
                            <?php
}?>

                            <?php if ($_SESSION["tipo"] == "upu") { ?>
                            <a class="nav-link collapsed" data-bs-toggle="collapse" data-bs-target="#collapseClientes" aria-expanded="false" aria-controls="collapseClientes">
                                <div class="sb-nav-link-icon"><i class="fas fa-address-book fa-fw"></i></div>
                                Clientes
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            <div class="collapse" id="collapseClientes" aria-labelledby="headingClientes" data-bs-parent="#sidenavAccordion">
                                <nav class="sb-sidenav-menu-nested nav">
                                    <a class="nav-link" href="javascript:void(0);" onclick="navigateTo('../vistas/ver_clientes.php')">
                                        <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                                        Directorio de Clie/Prov
                                    </a>
                                    <a class="nav-link" href="javascript:void(0);" onclick="navigateTo('../vistas/agregar_cliente.php')">
                                        <div class="sb-nav-link-icon"><i class="fas fa-user-plus"></i></div>
                                        Agregar Nuevo
                                    </a>
                                </nav>
                            </div>
                            <?php
}?>
                            
                            <?php if ($_SESSION["tipo"] == "cont" || $_SESSION["tipo"] == "admin" || $_SESSION["tipo"] == "upu") { ?>
                            <div class="sb-sidenav-menu-heading-pro">Tesorería y Finanzas</div>
                            <?php
}?>

                            <?php if ($_SESSION["tipo"] == "cont" || $_SESSION["tipo"] == "admin") { ?>
                            <a class="nav-link collapsed" data-bs-toggle="collapse" data-bs-target="#collapseComprobante" aria-expanded="false" aria-controls="collapseComprobante">
                                <div class="sb-nav-link-icon"><i class="fas fa-file-invoice-dollar fa-fw"></i></div>
                                Comprobantes de Egreso
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            <div class="collapse" id="collapseComprobante" aria-labelledby="headingComprobante" data-bs-parent="#sidenavAccordion">
                                <nav class="sb-sidenav-menu-nested nav">
                                    <a class="nav-link" href="javascript:void(0);" onclick="navigateTo('../vistas/formulario_comprobante.php')">
                                        <div class="sb-nav-link-icon"><i class="fas fa-file-circle-plus"></i></div>
                                        Crear Nuevo
                                    </a>
                                    <a class="nav-link" href="javascript:void(0);" onclick="navigateTo('../vistas/listar_comprobantes.php')">
                                        <div class="sb-nav-link-icon"><i class="fas fa-list-check"></i></div>
                                        Revisar Comprobantes
                                    </a>
                                </nav>
                            </div>
                            <?php
}?>
                            
                            <?php if ($_SESSION["tipo"] == "upu" || $_SESSION["tipo"] == "admin" || $_SESSION["tipo"] == "cont") { ?>
                            <a class="nav-link collapsed" data-bs-toggle="collapse" data-bs-target="#collapsePagos" aria-expanded="false" aria-controls="collapsePagos">
                                <div class="sb-nav-link-icon"><i class="fas fa-money-check-dollar fa-fw"></i></div>
                                Pagos
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            <div class="collapse" id="collapsePagos" aria-labelledby="headingPagos" data-bs-parent="#sidenavAccordion">
                                <nav class="sb-sidenav-menu-nested nav">
                                    <?php if ($_SESSION["tipo"] == "upu") { ?>
                                        <a class="nav-link" href="javascript:void(0);" onclick="navigateTo('registro_pagos.php')">
                                            <div class="sb-nav-link-icon"><i class="fas fa-arrow-up-right-from-square"></i></div>
                                            Registrar Ingreso
                                        </a>
                                        <a class="nav-link" href="javascript:void(0);" onclick="navigateTo('registro_pagos_egresos.php')">
                                            <div class="sb-nav-link-icon"><i class="fas fa-arrow-down-wide-short"></i></div>
                                            Registrar Egreso
                                        </a>
                                        <a class="nav-link" href="javascript:void(0);" onclick="navigateTo('ver_pagos.php')">
                                            <div class="sb-nav-link-icon"><i class="fas fa-clock-rotate-left"></i></div>
                                            Historial de Pagos
                                        </a>
                                    <?php
    }?>
                                    <?php if ($_SESSION["tipo"] == "cont" || $_SESSION["tipo"] == "admin") { ?>
                                        <a class="nav-link" href="javascript:void(0);" onclick="navigateTo('aprobar_pago.php')">
                                            <div class="sb-nav-link-icon"><i class="fas fa-circle-check"></i></div>
                                            Aprobar Pagos
                                        </a>
                                        <!--<a class="nav-link" href="javascript:void(0);" onclick="navigateTo('registro_pagos.php')">
                                            <div class="sb-nav-link-icon"><i class="fas fa-arrow-up-right-from-square"></i></div>
                                            Registrar Ingresos Bancarios
                                        </a>-->
                                        <a class="nav-link" href="javascript:void(0);" onclick="navigateTo('registro_pagos_egresos.php')">
                                            <div class="sb-nav-link-icon"><i class="fas fa-arrow-down-wide-short"></i></div>
                                            Registrar Comiciones Bancarias
                                        </a>
                                        <a class="nav-link" href="javascript:void(0);" onclick="navigateTo('ver_pagos_cont.php')">
                                            <div class="sb-nav-link-icon"><i class="fas fa-table-list"></i></div>
                                            Reporte de Pagos
                                        </a>
                                    <?php
    }?>
                                </nav>
                            </div>
                            <?php
}?>
                        </div>
                    </div>
                    <div class="sb-sidenav-footer small text-center py-3">
                        <div class="small text-white-50">Sesión Activa:</div>
                        <span class="fw-bold text-primary"><?php echo ucfirst($tipo_usuario); ?></span>
                    </div>
                </nav>
            </div>
            <!--Start of Tawk.to Script-->
            <script type="text/javascript">
            var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
            (function(){
            var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
            s1.async=true;
            s1.src='https://embed.tawk.to/69222aed34679319611b35ee/1jamnfbva';
            s1.charset='UTF-8';
            s1.setAttribute('crossorigin','*');
            s0.parentNode.insertBefore(s1,s0);
            })();
            </script>
            <!--End of Tawk.to Script-->
            <?php
// Inicia el contenedor de contenido principal (layoutSidenav_content)
include("../models/sweetalert.php");
?>

            <script>
                // Aplicar el tema inmediatamente para evitar parpadeo blanco
                (function() {
                    const savedTheme = localStorage.getItem('sdgbp_theme') || 'light';
                    document.documentElement.setAttribute('data-theme', savedTheme);
                })();

                function toggleDarkMode() {
                    const htmlElement = document.documentElement;
                    const isDark = htmlElement.getAttribute('data-theme') === 'dark';
                    const newTheme = isDark ? 'light' : 'dark';
                    
                    htmlElement.setAttribute('data-theme', newTheme);
                    localStorage.setItem('sdgbp_theme', newTheme);
                    updateDarkModeIcon(newTheme);
                }

                function updateDarkModeIcon(theme) {
                    const btnIcon = document.querySelector('#btn-dark-mode i');
                    if (!btnIcon) return;
                    if (theme === 'dark') {
                        btnIcon.classList.replace('fa-moon', 'fa-sun');
                    } else {
                        btnIcon.classList.replace('fa-sun', 'fa-moon');
                    }
                }

                document.addEventListener('DOMContentLoaded', () => {
                   const currentTheme = document.documentElement.getAttribute('data-theme');
                   updateDarkModeIcon(currentTheme);
                });
            </script>