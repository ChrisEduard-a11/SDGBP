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

                /* ================= TEMA OSCURO (Dark Mode)                /* ================= TEMA OSCURO PROFUNDO (Deep Dark Mode) ================= */
                [data-theme="dark"] {
                    --bg-app: #000000 !important; /* Negro Puro por solicitud */
                    --sidebar-bg: #111827; 
                    --navbar-bg: #111827; 
                    --navbar-text: #ffffff; 
                    
                    --accent-color: #f18000; 
                    --accent-glow: rgba(241, 128, 0, 0.3);
                    --accent-gradient: linear-gradient(135deg, #ea580c 0%, #f18000 100%);
                    
                    --text-main: #ffffff; 
                    --text-muted: #e2e8f0; 
                    --sidebar-text: #ffffff; 
                    --sidebar-text-muted: #ffffff; 
                    --sidebar-border: rgba(255,255,255,0.1); 
                    --footer-bg: #000000; 
                    --footer-text: #ffffff; 
                    
                    --shadow-sm: 0 1px 3px rgba(255,255,255,0.1);
                    --shadow-md: 0 4px 6px -1px rgba(255,255,255,0.15);
                    --shadow-float: 0 10px 25px -5px rgba(255,255,255,0.2);
                    
                    --content-overlay: rgba(0, 0, 0, 0.96);
                }

                /* Forzado Universal para Modo Oscuro */
                [data-theme="dark"] body { 
                    background-color: #000000 !important; 
                    color: #ffffff !important; 
                }
                
                [data-theme="dark"] .bg-white, 
                [data-theme="dark"] .card,
                [data-theme="dark"] .modal-content,
                [data-theme="dark"] .dropdown-menu {
                    background-color: #121212 !important;
                    color: #ffffff !important;
                    border: 1px solid #333 !important;
                }

                [data-theme="dark"] .card-header {
                    background-color: #1a1a1a !important;
                    border-bottom: 1px solid #333 !important;
                    color: #ffffff !important;
                }

                /* Formularios y Campos */
                [data-theme="dark"] .form-control, 
                [data-theme="dark"] .form-select,
                [data-theme="dark"] textarea {
                    background-color: #000000 !important;
                    color: #ffffff !important;
                    border: 1px solid #444 !important;
                }
                [data-theme="dark"] .form-control:focus, 
                [data-theme="dark"] .form-select:focus {
                    border-color: var(--accent-color) !important;
                    box-shadow: 0 0 0 0.25rem rgba(241, 128, 0, 0.25) !important;
                }
                [data-theme="dark"] .form-control::placeholder {
                    color: #888 !important;
                }
                [data-theme="dark"] .input-group-text {
                    background-color: #1a1a1a !important;
                    color: #ffffff !important;
                    border: 1px solid #444 !important;
                }

                /* Tablas y DataTables */
                [data-theme="dark"] .table {
                    color: #ffffff !important;
                    border-color: #333 !important;
                }
                [data-theme="dark"] .table thead th {
                    background-color: #1a1a1a !important;
                    color: #ffffff !important;
                    border-bottom: 2px solid #444 !important;
                }
                [data-theme="dark"] .table-striped tbody tr:nth-of-type(odd) {
                    background-color: rgba(255,255,255,0.03) !important;
                }
                [data-theme="dark"] .table-hover tbody tr:hover {
                    background-color: rgba(255,255,255,0.07) !important;
                    color: #ffffff !important;
                }
                [data-theme="dark"] .page-link {
                    background-color: #121212 !important;
                    border-color: #333 !important;
                    color: #ffffff !important;
                }
                [data-theme="dark"] .page-item.active .page-link {
                    background-color: var(--accent-color) !important;
                    border-color: var(--accent-color) !important;
                }

                /* Select2 Dark Support */
                [data-theme="dark"] .select2-container--bootstrap-5 .select2-selection {
                    background-color: #000000 !important;
                    color: #ffffff !important;
                    border: 1px solid #444 !important;
                }
                [data-theme="dark"] .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
                    color: #ffffff !important;
                }
                [data-theme="dark"] .select2-dropdown {
                    background-color: #121212 !important;
                    color: #ffffff !important;
                    border: 1px solid #444 !important;
                }
                [data-theme="dark"] .select2-results__option--highlighted {
                    background-color: var(--accent-color) !important;
                }

                /* Flatpickr Dark Theme */
                [data-theme="dark"] .flatpickr-calendar {
                    background: #121212 !important;
                    box-shadow: 0 10px 20px rgba(255,255,255,0.05) !important;
                    color: #ffffff !important;
                    border: 1px solid #333 !important;
                }
                [data-theme="dark"] .flatpickr-day { color: #ffffff !important; }
                [data-theme="dark"] .flatpickr-day.today { border-color: var(--accent-color) !important; }
                [data-theme="dark"] .flatpickr-day.selected { background: var(--accent-color) !important; }
                [data-theme="dark"] .flatpickr-current-month, [data-theme="dark"] .flatpickr-month {
                    fill: #ffffff !important;
                    color: #ffffff !important;
                }

                /* SweetAlert2 Deep Dark Mode Override */
                body.swal2-shown [data-theme="dark"] .swal2-popup {
                    background: #121212 !important;
                    color: #ffffff !important;
                }

                /* Colores de Texto Generales */
                [data-theme="dark"] .text-dark, 
                [data-theme="dark"] .card-title,
                [data-theme="dark"] .modal-title,
                [data-theme="dark"] label,
                [data-theme="dark"] span:not(.badge),
                [data-theme="dark"] p,
                [data-theme="dark"] h1, [data-theme="dark"] h2, [data-theme="dark"] h3, [data-theme="dark"] h4, [data-theme="dark"] h5, [data-theme="dark"] h6 {
                    color: #ffffff !important;
                }
                [data-theme="dark"] .text-muted, [data-theme="dark"] .small {
                    color: #cccccc !important;
                }
                /* Navbar y Sidebar en Modo Oscuro */
                [data-theme="dark"] .sb-topnav {
                    background-color: #000000 !important;
                    border-bottom: 1px solid #333 !important;
                }
                [data-theme="dark"] .sb-sidenav {
                    background-color: #000000 !important;
                    border-right: 1px solid #333 !important;
                }
                [data-theme="dark"] .sb-sidenav-footer {
                    background-color: #000000 !important;
                    border-top: 1px solid #333 !important;
                }

                /* ================= NUCLEAR CSS OVERRIDES FOR DARK MODE ================= */
                /* Estas reglas tienen máxima especificidad para machacar estilos inline de las vistas */
                html[data-theme="dark"] body #layoutSidenav_content .card-premium,
                html[data-theme="dark"] body #layoutSidenav_content .card,
                html[data-theme="dark"] body #layoutSidenav_content .glass-card,
                html[data-theme="dark"] body #layoutSidenav_content .welcome-card {
                    background-color: #000000 !important;
                    background: #000000 !important;
                    color: #ffffff !important;
                    border: 1px solid #333 !important;
                    box-shadow: none !important;
                }

                html[data-theme="dark"] body #layoutSidenav_content .breadcrumb-premium,
                html[data-theme="dark"] body #layoutSidenav_content .filter-section-premium {
                    background-color: #0c0c0c !important;
                    background: #0c0c0c !important;
                    border: 1px solid #444 !important;
                    color: #ffffff !important;
                }

                /* Tablas DataTables - Forzado Nuclear */
                html[data-theme="dark"] body #layoutSidenav_content #datatablesSimple {
                    background-color: #000000 !important;
                    color: #ffffff !important;
                }
                html[data-theme="dark"] body #layoutSidenav_content #datatablesSimple thead th {
                    background-color: #1a1a1a !important;
                    color: #ffffff !important;
                    border-bottom: 2px solid #444 !important;
                }
                html[data-theme="dark"] body #layoutSidenav_content #datatablesSimple tbody tr {
                    background-color: #000000 !important;
                    color: #ffffff !important;
                    border-bottom: 1px solid #222 !important;
                }
                html[data-theme="dark"] body #layoutSidenav_content #datatablesSimple td {
                    color: #ffffff !important;
                    background-color: transparent !important;
                }
                html[data-theme="dark"] body #layoutSidenav_content #datatablesSimple tbody tr:hover {
                    background-color: #111111 !important;
                }

                /* Footer, Alerts e Inputs - Forzado Nuclear */
                html[data-theme="dark"] footer#footer {
                    background-color: #000000 !important;
                    color: #ffffff !important;
                    border-top: 1px solid #333 !important;
                }
                html[data-theme="dark"] footer#footer .text-muted {
                    color: #888 !important;
                }
                html[data-theme="dark"] .alert, 
                html[data-theme="dark"] .instruction-alert {
                    background-color: #1e1e1e !important;
                    color: #ffffff !important;
                    border: 1px solid #444 !important;
                }
                html[data-theme="dark"] .input-group-text,
                html[data-theme="dark"] .input-group-text-premium {
                    background-color: #222 !important;
                    color: #ffffff !important;
                    border-color: #444 !important;
                }
                html[data-theme="dark"] .bg-light {
                    background-color: #111 !important;
                }

                /* SweetAlert2 - Forzado Nuclear */
                html[data-theme="dark"] .swal2-popup {
                    background-color: #000000 !important;
                    background: #000000 !important;
                    color: #ffffff !important;
                    border: 1px solid #333 !important;
                    box-shadow: 0 0 20px rgba(0,0,0,0.8) !important;
                }
                html[data-theme="dark"] .swal2-title,
                html[data-theme="dark"] .swal2-html-container,
                html[data-theme="dark"] .swal2-content {
                    color: #ffffff !important;
                }
                html[data-theme="dark"] .swal2-input,
                html[data-theme="dark"] .swal2-textarea,
                html[data-theme="dark"] .swal2-select {
                    background-color: #111111 !important;
                    color: #ffffff !important;
                    border: 1px solid #444 !important;
                    box-shadow: none !important;
                }
                html[data-theme="dark"] .swal2-input:focus,
                html[data-theme="dark"] .swal2-textarea:focus {
                    border-color: #f18000 !important;
                    box-shadow: 0 0 0 2px rgba(241,128,0,0.2) !important;
                }

                /* Arreglo de Iconos SweetAlert2 en Modo Oscuro */
                html[data-theme="dark"] .swal2-icon.swal2-success [class^='swal2-success-circular-line'],
                html[data-theme="dark"] .swal2-icon.swal2-success .swal2-success-fix,
                html[data-theme="dark"] .swal2-icon.swal2-error [class^='swal2-x-mark-line'],
                html[data-theme="dark"] .swal2-icon.swal2-error {
                    background-color: transparent !important;
                }
                html[data-theme="dark"] .swal2-icon {
                    border-color: rgba(255,255,255,0.2) !important;
                }
                html[data-theme="dark"] .swal2-progress-steps .swal2-progress-step.swal2-active-progress-step {
                    background: #f18000 !important;
                }

                /* Select2 Search - Forzado Nuclear */
                html[data-theme="dark"] .select2-container--bootstrap-5 .select2-search__field {
                    background-color: #1a1a1a !important;
                    color: #ffffff !important;
                    border-color: #444 !important;
                }
                html[data-theme="dark"] .select2-container--bootstrap-5 .select2-dropdown {
                    background-color: #111111 !important;
                    color: #ffffff !important;
                    border-color: #333 !important;
                }
                html[data-theme="dark"] .select2-container--bootstrap-5 .select2-results__option {
                    color: #ffffff !important;
                }
                html[data-theme="dark"] .select2-container--bootstrap-5 .select2-results__option--highlighted {
                    background-color: #f18000 !important;
                    color: #ffffff !important;
                }

                /* Dropdowns - Forzado Nuclear */
                html[data-theme="dark"] .dropdown-menu {
                    background-color: #111111 !important;
                    border: 1px solid #333 !important;
                    box-shadow: 0 10px 15px -3px rgba(0,0,0,0.5) !important;
                }
                html[data-theme="dark"] .dropdown-item {
                    color: #ffffff !important;
                }
                html[data-theme="dark"] .dropdown-item:hover {
                    background-color: #222222 !important;
                    color: #f18000 !important;
                }
                html[data-theme="dark"] .dropdown-header,
                html[data-theme="dark"] .dropdown-header span,
                html[data-theme="dark"] .dropdown-header .text-dark {
                    color: #ffffff !important;
                }
                html[data-theme="dark"] .dropdown-divider {
                    border-color: #333 !important;
                    opacity: 1 !important;
                }

                /* Botón de Modo Oscuro */
                #btn-dark-mode {
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
                    background: rgba(255,255,255,0.1) !important;
                    color: #ffcc00 !important;
                    border-color: #444 !important;
                }

                /* ================= SIDEBAR (Light/Dark Switchable) ================= */
                .sb-sidenav-dark {
                    background: var(--sidebar-bg) !important;
                    box-shadow: var(--shadow-sidebar);
                    border-right: 1px solid var(--sidebar-border);
                }
                
                /* Títulos de sección */
                .sb-sidenav-menu-heading {
                    padding: 1.5rem 1.25rem 0.6rem;
                    font-size: 0.72rem;
                    color: var(--sidebar-text-muted) !important;
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
                
                /* Enlace Activo - Naranja corporativo con letras blancas para resaltar */
                .sb-sidenav-menu .nav-link.active {
                    background: var(--accent-color) !important; 
                    color: #ffffff !important;
                    font-weight: 700;
                    border-radius: 8px;
                }
                
                /* Iconos del menú - Siempre naranja vibrante */
                .sb-sidenav .sb-sidenav-menu .sb-nav-link-icon {
                    color: var(--accent-color) !important; 
                    font-size: 1.25rem;
                    width: 2.25rem;
                    transition: all 0.2s ease;
                    display: flex;
                    align-items: center;
                    opacity: 1; /* Asegurar opacidad total para resaltar */
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

                /* --- Nuclear Overrides for Stat Cards (Pure Black) --- */
        html[data-theme="dark"] .metric-card,
        html[data-theme="dark"] .card.metric-card,
        html[data-theme="dark"] .welcome-card,
        html[data-theme="dark"] .card.bg-gradient-warning,
        html[data-theme="dark"] .card.bg-gradient-danger,
        html[data-theme="dark"] .card.bg-gradient-primary,
        html[data-theme="dark"] .card.bg-gradient-success,
        html[data-theme="dark"] .card.bg-gradient-info,
        html[data-theme="dark"] .card.bg-gradient-secondary,
        html[data-theme="dark"] .card.bg-gradient-success-alt,
        html[data-theme="dark"] .card.bg-gradient-blue,
        html[data-theme="dark"] .card.bg-gradient-rose,
        html[data-theme="dark"] .card.bg-gradient-teal,
        html[data-theme="dark"] .card.bg-gradient-indigo,
        html[data-theme="dark"] .card.bg-gradient-wallet,
        html[data-theme="dark"] .card.bg-gradient-pending,
        html[data-theme="dark"] .card.bg-gradient-activity,
        html[data-theme="dark"] .card.glass-card {
            background: #000000 !important;
            background-color: #000000 !important;
            border: 1px solid #333 !important;
            box-shadow: none !important;
            background-image: none !important;
        }

        /* Contenedores de iconos opacos */
        html[data-theme="dark"] .bg-primary.bg-opacity-10,
        html[data-theme="dark"] .bg-success.bg-opacity-10,
        html[data-theme="dark"] .bg-info.bg-opacity-10,
        html[data-theme="dark"] .bg-warning.bg-opacity-10,
        html[data-theme="dark"] .bg-danger.bg-opacity-10,
        html[data-theme="dark"] .bg-indigo.bg-opacity-10,
        html[data-theme="dark"] .avatar-circle.bg-light {
            background-color: rgba(255, 255, 255, 0.05) !important;
            color: #fff !important;
        }

        /* Mantener indicador de color lateral para categorización */
        html[data-theme="dark"] .bg-gradient-blue, html[data-theme="dark"] .bg-gradient-primary { border-left: 5px solid #4361ee !important; }
        html[data-theme="dark"] .bg-gradient-rose, html[data-theme="dark"] .bg-gradient-danger { border-left: 5px solid #e71d36 !important; }
        html[data-theme="dark"] .bg-gradient-teal, html[data-theme="dark"] .bg-gradient-success, html[data-theme="dark"] .bg-gradient-success-alt { border-left: 5px solid #2ec4b6 !important; }
        html[data-theme="dark"] .bg-gradient-indigo, html[data-theme="dark"] .bg-gradient-wallet { border-left: 5px solid #4f46e5 !important; }
        html[data-theme="dark"] .bg-gradient-warning, html[data-theme="dark"] .bg-gradient-pending { border-left: 5px solid #f7b731 !important; }
        html[data-theme="dark"] .bg-gradient-info { border-left: 5px solid #4facfe !important; }
        html[data-theme="dark"] .bg-gradient-secondary { border-left: 5px solid #6c757d !important; }

        html[data-theme="dark"] .metric-card::before,
        html[data-theme="dark"] .metric-card::after {
            display: none !important;
        }

        html[data-theme="dark"] .metric-icon {
            opacity: 0.1 !important;
        }

        /* --- Fin de Nuclear Overrides --- */

        /* Forzado Logo sin bordes */
        .navbar-brand img, .profile-pic, .img-circular, .avatar-img {
            border: none !important;
            background: transparent !important;
            background-color: transparent !important;
            padding: 0 !important;
            box-shadow: none !important;
            mix-blend-mode: normal !important;
            filter: none !important; /* Prevenir cambios de color en fotos */
        }

        /* Avatar Placeholder en Navbar */
        .nav-user-avatar-placeholder {
            width: 32px;
            height: 32px;
            background: rgba(255, 255, 255, 0.25) !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            color: #ffffff !important;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        [data-theme="dark"] .nav-user-avatar-placeholder {
            background: rgba(255, 255, 255, 0.15) !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }

        /* Estilos Globales para Encabezados de Vistas */
        header.page-header-standard {
            margin-bottom: 2.5rem;
        }
        header.page-header-standard h1 {
            font-size: 1.85rem;
            letter-spacing: -0.025em;
        }
        header.page-header-standard .breadcrumb {
            background: transparent;
            padding: 0;
            margin: 0;
            font-size: 0.9rem;
        }
        [data-theme="dark"] header.page-header-standard .breadcrumb-item a {
            color: #60a5fa !important;
        }
        [data-theme="dark"] header.page-header-standard .breadcrumb-item.active {
            color: #94a3b8 !important;
        }

        /* Transición suave para todos los elementos */
        * { transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease; }

        /* Nuclear Dropdown Overrides */
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
                    background: #0f172a !important; 
                    border-top: 1px solid var(--sidebar-border);
                    padding: 1.25rem !important;
                }
                .sb-sidenav-footer .small {
                    color: var(--sidebar-text-muted) !important;
                    font-size: 0.75rem;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    margin-bottom: 2px;
                    font-weight: 600;
                }
                .sb-sidenav-footer span {
                    color: var(--sidebar-text) !important;
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
        <nav class="sb-topnav navbar navbar-expand navbar-dark" style="background-color: #f18000 !important;">
            <a class="navbar-brand ps-3 d-flex align-items-center gap-2" href="javascript:void(0);" onclick="navigateTo('inicio.php')">
                <img src="../img/Logo-OP2_V4.webp" alt="Logo" style="width: 40px; height: 40px; object-fit: contain;"> 
                <span class="text-white fw-bold">SDGBP</span>
            </a>

            <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0 text-white" id="sidebarToggle" href="#!">
                <i class="fas fa-bars"></i>
            </button>
            
            <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
                <div class="input-group"> 
                    <div onload="actualizarFechaHora()">
                        <h6 class="text-white mb-0" id="fecha"></h6>
                    </div>
                </div>
            </form>
            
            <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4 d-flex align-items-center gap-2">
                
                <li class="nav-item">
                    <div id="btn-dark-mode" onclick="toggleDarkMode()">
                        <i class="fas fa-moon"></i>
                    </div>
                </li>

                <!-- Dropdown de Notificaciones -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle position-relative px-2" id="navbarDropdownNotif" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell"></i>
                        <?php if (count($notificaciones) > 0) { ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem; padding: 0.35em 0.65em;">
                                <?php echo count($notificaciones); ?>
                            </span>
                        <?php } ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="navbarDropdownNotif" style="width: 300px;">
                        <li class="dropdown-header text-center fw-bold">Notificaciones</li>
                        <li><hr class="dropdown-divider"></li>
                        <?php if (count($notificaciones) > 0) {
                            foreach ($notificaciones as $notif) { ?>
                                <li class="px-2">
                                    <div class="d-flex align-items-center p-2 rounded border-start border-4 border-<?php echo $notif['tipo']; ?> hover:bg-light mb-1">
                                        <div class="flex-shrink-0 me-3">
                                            <i class="<?php echo $notif['icono']; ?> text-<?php echo $notif['tipo']; ?> fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="small fw-bold text-dark"><?php echo $notif['titulo']; ?></div>
                                            <div class="small text-muted"><?php echo $notif['mensaje']; ?></div>
                                        </div>
                                    </div>
                                </li>
                            <?php }
                        } else { ?>
                            <li class="dropdown-item text-center text-muted small py-3">No tienes notificaciones pendientes</li>
                        <?php } ?>
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php if (!empty($_SESSION['foto']) && file_exists("../" . $_SESSION['foto'])) { ?>
                            <img src="../<?php echo $_SESSION['foto']; ?>" alt="Avatar" class="rounded-circle border border-white border-opacity-25" style="width: 32px; height: 32px; object-fit: cover;">
                        <?php } else { ?>
                            <div class="nav-user-avatar-placeholder">
                                <i class="fas fa-user-circle fs-5"></i>
                            </div>
                        <?php } ?>
                        <span class="d-none d-md-inline small text-white fw-semibold"><?php echo $nombre_usuario; ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 min-w-[200px]" aria-labelledby="navbarDropdown">
                        <li class="dropdown-header text-center">
                            <span class="fw-bold d-block text-dark"><?php echo $nombre_usuario; ?></span>
                            <span class="small text-muted"><?php echo ucfirst($tipo_usuario); ?></span>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-2" onclick="navigateTo('configuracion_usuario.php')"><i class="fas fa-cog fa-fw me-2 text-muted"></i> Mi Cuenta</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li class="px-3 py-1">
                            <button class="btn btn-danger btn-sm w-100 rounded-pill" onclick="confsalir(event)">
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
                            
                            <div class="sb-sidenav-menu-heading">Navegación General</div>
                            <a class="nav-link" href="javascript:void(0);" onclick="navigateTo('../vistas/inicio.php')">
                                <div class="sb-nav-link-icon"><i class="fas fa-house-chimney fa-fw"></i></div>
                                Dashboard
                            </a>

                            <div class="sb-sidenav-menu-heading">Mi Cuenta</div>
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
                            <div class="sb-sidenav-menu-heading">Operaciones y Registros</div>
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
                            <a class="nav-link collapsed" data-bs-toggle="collapse" data-bs-target="#collapseMarketing" aria-expanded="false" aria-controls="collapseMarketing">
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
                                    <a class="nav-link" href="javascript:void(0);" onclick="navigateTo('../vistas/aprobar_marketing.php')">
                                        <div class="sb-nav-link-icon"><i class="fas fa-check-circle"></i></div>
                                        Aprobar Pagos Marketing
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
                            <div class="sb-sidenav-menu-heading">Tesorería y Finanzas</div>
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
                    <div class="sb-sidenav-footer">
                        <div class="small">Sesión Activa:</div>
                        <span class="fw-bold"><?php echo ucfirst($tipo_usuario); ?></span>
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

                // Inicialización Global de Select2 para todo el sistema
                $(document).ready(function() {
                    function initSelect2() {
                        $('select:not(.no-select2):not(.select2-hidden-accessible):not(.swal2-select)').each(function() {
                            // No inicializar si está dentro de un contenedor de SweetAlert
                            if ($(this).closest('.swal2-container').length) return;

                            $(this).select2({
                                theme: 'bootstrap-5',
                                width: '100%',
                                placeholder: $(this).attr('placeholder') || $(this).data('placeholder') || 'Seleccione una opción',
                                dropdownParent: $(this).closest('.modal').length ? $(this).closest('.modal') : $(document.body)
                            });
                        });
                    }
                    
                    initSelect2();
                    
                    // Soporte para elementos cargados dinámicamente
                    $(document).ajaxComplete(function() {
                        initSelect2();
                    });
                });
            </script>