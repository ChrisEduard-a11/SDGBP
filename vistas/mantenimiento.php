<?php
require_once("../conexion.php");
$maint_query = mysqli_query($conexion, "SELECT * FROM config_mantenimiento WHERE id = 1");
$data = mysqli_fetch_assoc($maint_query) ?: [
    'titulo' => 'Plataforma en Mantenimiento',
    'descripcion' => 'Estamos realizando mejoras técnicas.',
    'hora_inicio' => '',
    'hora_fin' => ''
];
date_default_timezone_set('America/Caracas');

$is_active = (bool)($data['activo'] ?? false);
$fecha_maint = $data['fecha'] ?? null;
$hora_inicio = substr($data['hora_inicio'], 0, 5);
$hora_fin = substr($data['hora_fin'], 0, 5);

// Verificar horario automático si no está activo manualmente
if (!$is_active && !empty($hora_inicio) && !empty($hora_fin)) {
    $fecha_actual = date('Y-m-d');
    $hora_actual = date('H:i');
    if (empty($fecha_maint) || $fecha_maint === $fecha_actual) {
        if ($hora_inicio <= $hora_fin) {
            if ($hora_actual >= $hora_inicio && $hora_actual < $hora_fin) $is_active = true;
        } else {
            if ($hora_actual >= $hora_inicio || $hora_actual < $hora_fin) $is_active = true;
        }
    }
}

// Si ya NO debe estar en mantenimiento, redirigir al inicio
if (!$is_active) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['titulo']); ?> - SDGBP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        :root {
            --primary: #fb923c;
            --primary-dark: #ea580c;
            --bg: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --accent: #38bdf8;
        }
        body {
            background-color: var(--bg);
            background-image: 
                radial-gradient(at 0% 0%, rgba(251, 146, 60, 0.05) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(56, 189, 248, 0.05) 0px, transparent 50%);
            color: white;
            font-family: 'Plus Jakarta Sans', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin: 0;
        }
        .maintenance-container {
            text-align: center;
            width: 90%;
            max-width: 650px;
            padding: 3.5rem 2.5rem;
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border-radius: 2.5rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            position: relative;
            z-index: 10;
        }
        
        .logo-box {
            margin-bottom: 2rem;
            position: relative;
        }
        .company-logo {
            max-width: 150px;
            height: auto;
            filter: drop-shadow(0 0 15px rgba(251, 146, 60, 0.3));
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .maintenance-title {
            background: linear-gradient(to right, #fb923c, #f97316);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .description {
            font-size: 1.1rem;
            line-height: 1.7;
            color: #cbd5e1;
            margin-bottom: 2.5rem;
            font-weight: 400;
        }

        .time-box {
            gap: 15px;
            background: rgba(251, 146, 60, 0.1);
            border: 1px solid rgba(251, 146, 60, 0.2);
            padding: 1rem 2rem;
            border-radius: 1.2rem;
            margin-bottom: 2.5rem;
            color: var(--primary);
            font-weight: 700;
        }
        
        .time-box i { font-size: 1.4rem; }

        .btn-refresh {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            padding: 1rem 2.5rem;
            border-radius: 1.2rem;
            font-weight: 800;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-decoration: none;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 10px 20px -5px rgba(234, 88, 12, 0.4);
        }
        
        .btn-refresh:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 20px 30px -10px rgba(234, 88, 12, 0.6);
            color: white;
        }
        
        .footer-text {
            position: absolute;
            bottom: 2rem;
            font-size: 0.85rem;
            color: #64748b;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="maintenance-container animate__animated animate__zoomIn">
        <div class="logo-box">
            <img src="../img/Logo-OP2_V4.webp" alt="Logo Empresa" class="company-logo">
            <div class="icon-overlay">
                <i class="fas fa-cog"></i>
            </div>
        </div>
        
        <h1><span class="maintenance-title"><?php echo htmlspecialchars($data['titulo']); ?></span></h1>
        
        <p class="description"><?php echo htmlspecialchars($data['descripcion']); ?></p>
        
        <?php if (!empty($data['hora_inicio']) && !empty($data['hora_fin'])): ?>
        <div class="time-box animate__animated animate__pulse animate__infinite">
            <i class="far fa-clock"></i>
            <span>Horario Estimado: <?php echo date('g:i A', strtotime($data['hora_inicio'])); ?> - <?php echo date('g:i A', strtotime($data['hora_fin'])); ?></span>
        </div>
        <?php endif; ?>

        <div class="mt-2 mb-4">
            <div class="badge bg-dark bg-opacity-50 border border-secondary rounded-pill px-3 py-2 shadow-sm">
                <i class="fas fa-clock text-primary me-2"></i>
                <span id="vargas-clock" class="fw-bold tracking-widest text-white" style="font-family: 'Courier New', Courier, monospace;">00:00:00</span>
                <span class="ms-1 text-primary opacity-75 small fw-bold">VET</span>
            </div>
        </div>
        
        <a href="login.php" class="btn-refresh">
            <i class="fas fa-sync-alt me-2"></i> Verificar Estado
        </a>
    </div>
    
    <div class="footer-text">
        &copy; 2026 SDGBP - Sistema de Gestión de Bienes y Pagos. Potenciado por Tecnología Ultra Premium.
    </div>

    <script>
        // Reloj en tiempo real (Venezuela)
        function updateClock() {
            const now = new Date(new Date().toLocaleString("en-US", {timeZone: "America/Caracas"}));
            let hours = now.getHours();
            const ampm = hours >= 12 ? 'PM' : 'AM';
            const militaryHours = hours;
            hours = hours % 12;
            hours = hours ? hours : 12; 
            
            const minutes = now.getMinutes();
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const strHours = String(hours).padStart(2, '0');
            const strMinutes = String(minutes).padStart(2, '0');
            
            const clockElement = document.getElementById('vargas-clock');
            if (clockElement) {
                clockElement.textContent = `${strHours}:${strMinutes}:${seconds} ${ampm}`;
            }

            // --- Lógica de Auto-Redirección en Tiempo Real ---
            const horaFinStr = "<?php echo $data['hora_fin']; ?>";
            const horaInicioStr = "<?php echo $data['hora_inicio']; ?>";
            const fechaProgramada = "<?php echo $data['fecha'] ?? ''; ?>";
            const mantenimientoManual = <?php echo $data['activo'] ? 'true' : 'false'; ?>;

            if (horaFinStr && !mantenimientoManual) {
                const today = new Date(new Date().toLocaleString("en-US", {timeZone: "America/Caracas"}));
                const currentDateStr = today.toISOString().split('T')[0];
                const currentTimeStr = `${String(militaryHours).padStart(2, '0')}:${strMinutes}`;
                
                // Si el fecha coincide o no hay fecha
                if (!fechaProgramada || fechaProgramada === currentDateStr) {
                    // Si el rango terminó (cambiado a >= para apertura inmediata)
                    if (horaInicioStr <= horaFinStr) {
                        if (currentTimeStr >= horaFinStr) window.location.href = 'login.php';
                    } else {
                        // Rangos nocturnos
                        if (currentTimeStr >= horaFinStr && currentTimeStr < horaInicioStr) window.location.href = 'login.php';
                    }
                }
            }
        }

        setInterval(updateClock, 1000);
        updateClock(); // Carga inicial

        setTimeout(function(){
            window.location.reload();
        }, 60000); // Recarga cada minuto
    </script>
</body>
</html>
