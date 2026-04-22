<?php
session_start();
require_once("../conexion.php");
$maint_query = mysqli_query($conexion, "SELECT * FROM config_mantenimiento WHERE id = 1");
$data = mysqli_fetch_assoc($maint_query) ?: [
    'titulo' => 'Plataforma en Mantenimiento',
    'descripcion' => 'Estamos realizando mejoras técnicas para brindarte una mejor experiencia.',
    'hora_inicio' => '',
    'hora_fin' => ''
];
date_default_timezone_set('America/Caracas');

$is_active = (bool)($data['activo'] ?? false);
$fecha_maint = $data['fecha'] ?? null;
$hora_inicio = !empty($data['hora_inicio']) ? date('H:i', strtotime($data['hora_inicio'])) : '';
$hora_fin = !empty($data['hora_fin']) ? date('H:i', strtotime($data['hora_fin'])) : '';

if (!$is_active && !empty($hora_inicio) && !empty($hora_fin)) {
    $fecha_actual = date('Y-m-d');
    $hora_actual = date('H:i');
    if (empty($fecha_maint) || $fecha_maint === $fecha_actual) {
        if ($hora_inicio <= $hora_fin) {
            if ($hora_actual >= $hora_inicio && $hora_actual <= $hora_fin) $is_active = true;
        } else {
            if ($hora_actual >= $hora_inicio || $hora_actual <= $hora_fin) $is_active = true;
        }
    }
}

if (!$is_active) {
    header("Location: login.php");
    exit;
} else {
    // Si está activo, solo destruir sesión si NO es admin
    $user_role = strtolower($_SESSION['tipo'] ?? '');
    if ($user_role !== 'admin') {
        session_unset();
        session_destroy();
    }
}

// Determinamos si mostramos "Mantenimiento" o "Sistema Operativo"
$notice_title = $data['titulo'] ?? 'Plataforma en Mantenimiento';
$notice_desc = $data['descripcion'] ?? 'Estamos realizando mejoras técnicas para brindarte una mejor experiencia.';
$notice_icon = "fa-cog";
$notice_color = "var(--primary)";
$notice_subtitle = "TIEMPO REAL (VET)";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($notice_title); ?> - SDGBP</title>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        :root {
            --primary: #fb923c;
            --primary-rgb: 251, 146, 60;
            --secondary: #38bdf8;
            --bg-dark: #020617;
            --card-glass: rgba(15, 23, 42, 0.65);
            --border-glass: rgba(255, 255, 255, 0.08);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            position: relative;
        }

        /* Animated Background Mesh */
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 0% 0%, rgba(var(--primary-rgb), 0.12) 0%, transparent 40%),
                radial-gradient(circle at 100% 100%, rgba(56, 189, 248, 0.08) 0%, transparent 40%),
                radial-gradient(circle at 50% 50%, rgba(15, 23, 42, 1) 0%, rgba(2, 6, 23, 1) 100%);
            z-index: -1;
        }

        /* Floating particles effect (simple) */
        .decoration-blur {
            position: absolute;
            width: 300px;
            height: 300px;
            background: var(--primary);
            filter: blur(120px);
            opacity: 0.15;
            border-radius: 50%;
            z-index: -1;
            animation: floating 10s infinite alternate ease-in-out;
        }

        @keyframes floating {
            from { transform: translate(-10%, -10%); }
            to { transform: translate(10%, 10%); }
        }

        .container {
            width: 100%;
            max-width: 700px;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            z-index: 10;
        }

        .maintenance-card {
            background: var(--card-glass);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid var(--border-glass);
            border-radius: 2.5rem;
            padding: 4rem 2.5rem;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
            position: relative;
            overflow: hidden;
        }

        .maintenance-card::after {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, transparent, var(--primary), transparent);
            animation: progress-line 3s infinite linear;
        }

        @keyframes progress-line {
            to { left: 100%; }
        }

        .logo-section {
            margin-bottom: 2.5rem;
        }

        .company-logo {
            max-width: 160px;
            height: auto;
            filter: drop-shadow(0 0 20px rgba(var(--primary-rgb), 0.4));
            margin-bottom: 1.5rem;
        }

        .cog-icon {
            font-size: 3rem;
            color: var(--primary);
            animation: <?php echo $is_active ? 'spin 6s infinite linear' : 'heartbeat 2s infinite ease-in-out'; ?>;
            display: inline-block;
        }

        @keyframes spin {
            100% { transform: rotate(360deg); }
        }

        @keyframes heartbeat {
            0% { transform: scale(1); }
            14% { transform: scale(1.1); }
            28% { transform: scale(1); }
            42% { transform: scale(1.1); }
            70% { transform: scale(1); }
        }

        h1 {
            font-size: clamp(1.8rem, 5vw, 2.8rem);
            font-weight: 800;
            margin-bottom: 1rem;
            color: var(--primary);
            text-shadow: 0 4px 20px rgba(0,0,0,0.4);
            letter-spacing: -1px;
        }

        .description {
            font-size: clamp(1rem, 3vw, 1.15rem);
            line-height: 1.6;
            color: var(--text-muted);
            margin-bottom: 2.5rem;
            font-weight: 400;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .time-badge {
            background: rgba(var(--primary-rgb), 0.08);
            border: 1px solid rgba(var(--primary-rgb), 0.2);
            padding: 0.8rem 1.5rem;
            border-radius: 1rem;
            margin-bottom: 2rem;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            color: var(--primary);
            font-weight: 600;
            font-size: 0.95rem;
        }

        .clock-container {
            margin-bottom: 2.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        .real-time-clock {
            font-family: 'Monaco', 'Consolas', monospace;
            font-size: 1.8rem;
            font-weight: 700;
            color: #fff;
            background: rgba(255, 255, 255, 0.03);
            padding: 0.5rem 1.5rem;
            border-radius: 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
            letter-spacing: 2px;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.3);
        }

        .clock-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--primary);
            font-weight: 700;
        }

        .btn-refresh {
            background: linear-gradient(135deg, var(--primary) 0%, #f97316 100%);
            color: white;
            border: none;
            padding: 1.2rem 3rem;
            border-radius: 1rem;
            font-weight: 800;
            font-size: 1rem;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 10px 25px -5px rgba(var(--primary-rgb), 0.4);
            cursor: pointer;
        }

        .btn-refresh:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 35px -10px rgba(var(--primary-rgb), 0.6);
            filter: brightness(1.1);
            color: white;
        }

        .footer {
            margin-top: auto;
            padding: 2rem;
            width: 100%;
            text-align: center;
            color: var(--text-muted);
            font-size: 0.85rem;
            font-weight: 500;
        }

        .footer b {
            color: var(--primary);
        }

        /* Mobile specific adjustments */
        @media (max-width: 576px) {
            .maintenance-card {
                padding: 3rem 1.5rem;
                border-radius: 2rem;
            }
            .container {
                padding: 1rem;
            }
            .company-logo {
                max-width: 130px;
            }
            .real-time-clock {
                font-size: 1.5rem;
            }
        }
        /* Countdown Overlay Styles */
        .operational-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(var(--bg-dark), 0.9);
            backdrop-filter: blur(15px);
            z-index: 1000;
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem;
        }

        .operational-overlay.active {
            display: flex;
        }

        .countdown-number {
            font-size: 8rem;
            font-weight: 900;
            color: var(--primary);
            text-shadow: 0 0 40px rgba(var(--primary-rgb), 0.5);
            margin: 1rem 0;
        }

        .loader-bar-container {
            width: 100%;
            max-width: 300px;
            height: 6px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
            overflow: hidden;
            margin-top: 2rem;
        }

        .loader-progress {
            width: 100%;
            height: 100%;
            background: var(--primary);
            transition: width 1s linear;
        }
    </style>
</head>
<body class="<?php echo !$is_active ? 'is-operational' : ''; ?>">
    <!-- Countdown Overlay -->
    <div id="countdown-overlay" class="operational-overlay animate__animated animate__fadeIn">
        <i class="fas fa-check-circle cog-icon" style="font-size: 5rem; color: #10b981; margin-bottom: 2rem;"></i>
        <h1 style="color: #10b981; text-shadow: 0 4px 20px rgba(0,0,0,0.4);">¡Sistema Reestablecido!</h1>
        <p class="description">Todo está listo. Redirigiendo al login en...</p>
        <div id="countdown-num" class="countdown-number">5</div>
        <div class="loader-bar-container">
            <div id="loader-progress" class="loader-progress"></div>
        </div>
    </div>

    <div class="decoration-blur" style="top: 10%; left: 10%;"></div>
    <div class="decoration-blur" style="bottom: 10%; right: 10%; background: var(--secondary);"></div>

    <div class="container animate__animated animate__fadeIn">
        <div class="maintenance-card">
            <div class="logo-section">
                <img src="../img/Logo-OP2_V4.webp" alt="SDGBP Logo" class="company-logo">
                <div>
                    <i id="main-icon" class="fas <?php echo $notice_icon; ?> cog-icon"></i>
                </div>
            </div>

            <h1 id="main-title"><?php echo htmlspecialchars($notice_title); ?></h1>
            <p id="main-desc" class="description"><?php echo htmlspecialchars($notice_desc); ?></p>

            <div id="time-badge-container">
            <?php if ($is_active && !empty($data['hora_inicio']) && !empty($data['hora_fin'])): ?>
                <div class="time-badge animate__animated animate__pulse animate__infinite">
                    <i class="far fa-calendar-check"></i>
                    <span>Estimado: <?php echo date('g:i A', strtotime($data['hora_inicio'])); ?> — <?php echo date('g:i A', strtotime($data['hora_fin'])); ?></span>
                </div>
            <?php endif; ?>
            </div>

            <div class="clock-container">
                <span id="clock-label" class="clock-label"><?php echo $notice_subtitle; ?></span>
                <div id="clock" class="real-time-clock">00:00:00</div>
            </div>

            <a id="main-btn" href="login.php" class="btn-refresh">
                <i class="fas <?php echo $is_active ? 'fa-sync-alt' : 'fa-sign-in-alt'; ?>"></i> 
                <span><?php echo $is_active ? 'Verificar Conexión' : 'Regresar al Login'; ?></span>
            </a>
        </div>
    </div>

    <footer class="footer">
        &copy; 2026 <b>SDGBP</b> — Sistema de Gestión de Bienes y Pagos.
    </footer>

    <script>
        let isCurrentlyActive = <?php echo $is_active ? 'true' : 'false'; ?>;
        let countdownTimer = null;

        function updateClock() {
            const options = { timeZone: "America/Caracas", hour12: true, hour: '2-digit', minute: '2-digit', second: '2-digit' };
            const now = new Date();
            const timeString = now.toLocaleString("en-US", options);
            const clockElement = document.getElementById('clock');
            if (clockElement) clockElement.textContent = timeString;
        }

        async function checkMaintenanceStatus() {
            try {
                const response = await fetch('../acciones/check_mantenimiento.php');
                const state = await response.json();
                
                if (state.activo !== isCurrentlyActive) {
                    isCurrentlyActive = state.activo;
                    updateUI(state);
                }
            } catch (error) {
                console.error("Error al verificar estado:", error);
            }
        }

        function updateUI(state) {
            if (!state.activo) {
                startCountdown();
            } else {
                location.reload(); // Si vuelve a estar activo, mejor recargar para resetear todo
            }
        }

        function startCountdown() {
            const overlay = document.getElementById('countdown-overlay');
            const numElement = document.getElementById('countdown-num');
            const progress = document.getElementById('loader-progress');
            
            overlay.classList.add('active');
            let count = 5;
            
            countdownTimer = setInterval(() => {
                count--;
                numElement.textContent = count;
                progress.style.width = (count * 20) + '%';
                
                if (count <= 0) {
                    clearInterval(countdownTimer);
                    window.location.href = 'login.php';
                }
            }, 1000);
        }

        setInterval(updateClock, 1000);
        setInterval(checkMaintenanceStatus, 5000);
        updateClock();
    </script>
</body>
</html>
