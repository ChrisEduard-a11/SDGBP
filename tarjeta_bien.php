<?php
include('conexion.php');

$codigo = isset($_GET['c']) ? $_GET['c'] : '';
$bien = null;
$error = false;

if (!empty($codigo)) {
    $sql = "SELECT b.id, b.nombre, b.descripcion, c.nombre AS categoria, b.codigo, b.serial, b.fecha_adquisicion 
            FROM bienes b 
            JOIN categorias c ON b.categoria_id = c.id
            WHERE b.codigo = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $codigo);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $bien = $result->fetch_assoc();
    } else {
        $error = true;
    }
    $stmt->close();
} else {
    $error = true;
}

// Formato de fecha
$fechaStr = 'N/A';
if ($bien && !empty($bien['fecha_adquisicion'])) {
    $f = explode('-', $bien['fecha_adquisicion']);
    if (count($f) === 3) {
        $fechaStr = $f[2] . '/' . $f[1] . '/' . $f[0];
    }
}
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Activo Institucional <?php echo $bien ? ' - ' . htmlspecialchars($bien['codigo']) : ''; ?></title>
    
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#fff7ed',
                            100: '#ffedd5',
                            200: '#fed7aa',
                            300: '#fdba74',
                            400: '#fb923c',
                            500: '#f97316',
                            600: '#f18000',
                            700: '#c2410c',
                            800: '#9a3412',
                            900: '#7c2d12',
                        }
                    },
                    animation: {
                        'entry': 'entry 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards',
                        'spin-slow': 'spin 12s linear infinite',
                        'floatable': 'floatable 6s ease-in-out infinite',
                    },
                    keyframes: {
                        entry: {
                            '0%': { opacity: '0', transform: 'translateY(40px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        floatable: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' },
                        }
                    }
                }
            }
        }
    </script>
    <script>
        // Default to dark mode for the highly professional tech aesthetic, but allow system pref
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            // Forcing dark for the ultra premium look requested unless user strictly wants light
            document.documentElement.classList.add('dark');
        }
    </script>
    <style>
        .stagger-1 { animation-delay: 0.1s; opacity: 0; }
        .stagger-2 { animation-delay: 0.2s; opacity: 0; }
        .stagger-3 { animation-delay: 0.3s; opacity: 0; }
        .stagger-4 { animation-delay: 0.4s; opacity: 0; }
        
        .premium-shadow {
            box-shadow: 0 40px 80px -20px rgba(0,0,0,0.5), inset 0 1px 1px rgba(255,255,255,0.1);
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 dark:bg-slate-950 dark:text-slate-200 min-h-[100dvh] flex flex-col items-center justify-center relative py-12 px-4 selection:bg-brand-500 selection:text-white overflow-x-hidden">

    <!-- Professional SVG Background Ambient -->
    <div class="fixed inset-0 z-0 pointer-events-none flex justify-center items-center overflow-hidden">
        <!-- SVG Grid System -->
        <svg class="absolute inset-0 w-full h-full object-cover opacity-[0.03] dark:opacity-[0.02]" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="premium-grid" width="60" height="60" patternUnits="userSpaceOnUse">
                    <path d="M 60 0 L 0 0 0 60" fill="none" stroke="currentColor" stroke-width="1"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#premium-grid)" />
        </svg>

        <!-- SVG Top Right Blob -->
        <svg class="absolute top-0 right-0 w-[800px] h-[800px] text-brand-600/10 dark:text-brand-500/10 transform translate-x-1/3 -translate-y-1/3" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
            <path fill="currentColor" d="M47.7,-57.2C59.9,-46.8,66.6,-28.9,70.5,-9.7C74.4,9.6,75.4,30.3,66.1,47.1C56.9,63.9,37.3,76.9,16.5,80.7C-4.3,84.4,-26.4,78.9,-44.6,65.5C-62.8,52.2,-77.2,31,-79.8,8.2C-82.3,-14.7,-73,-39.2,-57.2,-49.7C-41.5,-60.2,-19.2,-56.7,-0.2,-56.5C18.9,-56.3,35.6,-67.7,47.7,-57.2Z" transform="translate(100 100)" />
        </svg>
        
        <!-- SVG Bottom Left Blob -->
        <svg class="absolute bottom-0 left-0 w-[600px] h-[600px] text-blue-600/10 dark:text-blue-500/10 transform -translate-x-1/3 translate-y-1/3" style="animation-direction: reverse;" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
            <path fill="currentColor" d="M51.1,-61C65.3,-48.9,75.3,-31.1,76.4,-12.4C77.4,6.4,69.4,26.1,55.9,41C42.5,56,23.5,66.2,2.3,63.5C-18.9,60.8,-42.2,45.2,-56.8,25.4C-71.3,5.6,-77,-18.4,-67.5,-35.5C-58.1,-52.7,-33.5,-63,-12.7,-65.1C8.1,-67.1,28.8,-60.8,51.1,-61Z" transform="translate(100 100)" />
        </svg>
    </div>

    <!-- Main Application Container -->
    <div class="relative z-10 w-full max-w-[32rem] mx-auto animate-entry">
        
        <?php if ($bien): ?>
            <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-2xl rounded-[2.5rem] premium-shadow border border-white/50 dark:border-slate-700/50 overflow-hidden relative">
                
                <!-- SVG Header Abstract Wave Art -->
                <div class="absolute top-0 left-0 right-0 h-48 z-0">
                    <svg viewBox="0 0 1440 320" class="w-full h-full object-cover" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="waveGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" stop-color="#f18000" stop-opacity="0.2" />
                                <stop offset="100%" stop-color="#fb923c" stop-opacity="0.05" />
                            </linearGradient>
                        </defs>
                        <path fill="url(#waveGrad)" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,112C672,96,768,96,864,112C960,128,1056,160,1152,165.3C1248,171,1344,149,1392,138.7L1440,128L1440,0L1392,0C1344,0,1248,0,1152,0C1056,0,960,0,864,0C768,0,672,0,576,0C480,0,384,0,288,0C192,0,96,0,48,0L0,0Z"></path>
                    </svg>
                    <!-- Superimposed elegant thin line -->
                    <svg viewBox="0 0 1440 320" class="absolute inset-0 w-full h-full object-cover" preserveAspectRatio="none">
                        <path fill="none" stroke="rgba(241,128,0,0.3)" stroke-width="2" d="M0,192L60,176C120,160,240,128,360,128C480,128,600,160,720,176C840,192,960,192,1080,170.7C1200,149,1320,107,1380,85.3L1440,64"></path>
                    </svg>
                </div>

                <!-- Status Badge Absolute -->
                <div class="absolute top-6 right-6 z-20">
                    <span class="inline-flex items-center px-4 py-1.5 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-xs font-bold border border-emerald-500/20 backdrop-blur-md shadow-lg uppercase tracking-widest">
                        <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Activo Operativo
                    </span>
                </div>

                <div class="relative z-10 pt-10 pb-6 px-8 text-center animate-entry stagger-1">
                    <div class="w-20 h-20 mx-auto bg-white dark:bg-slate-800 rounded-[1.25rem] shadow-xl border border-slate-100 dark:border-slate-700 flex items-center justify-center p-3 mb-6 transform rotate-3">
                        <img src="img/Logo-OP2_V4.webp" alt="Logo" class="w-full h-full object-contain drop-shadow" onerror="this.src=''; this.alt='EURIPYS'">
                    </div>
                    <p class="text-[0.65rem] font-bold text-slate-500 uppercase tracking-[0.2em] mb-2 dark:text-slate-400">EURIPYS 2024 C.A.</p>
                    <h2 class="text-3xl md:text-4xl font-display font-black text-slate-900 dark:text-white leading-tight tracking-tight mb-4">
                        <?php echo htmlspecialchars($bien['nombre']); ?>
                    </h2>
                    
                    <div class="inline-flex items-center text-sm font-semibold text-slate-600 dark:text-slate-300 bg-slate-100/50 dark:bg-slate-800/50 px-4 py-2 rounded-xl backdrop-blur-sm border border-slate-200/50 dark:border-slate-700/50">
                        <svg class="w-4 h-4 text-brand-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <?php echo htmlspecialchars($bien['categoria']); ?>
                    </div>
                </div>

                <!-- Technical Specs Layer -->
                <div class="px-8 pb-10 space-y-4 relative z-10">
                    
                    <?php if(!empty($bien['descripcion'])): ?>
                    <div class="bg-white/50 dark:bg-slate-800/40 rounded-2xl p-5 border border-white/60 dark:border-slate-700/40 animate-entry stagger-2">
                        <div class="flex items-center mb-3 text-slate-500 dark:text-slate-400">
                            <svg class="w-4 h-4 mr-2 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-[0.65rem] font-bold uppercase tracking-widest">Información Técnica</span>
                        </div>
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-300 leading-relaxed">
                            <?php echo nl2br(htmlspecialchars($bien['descripcion'])); ?>
                        </p>
                    </div>
                    <?php endif; ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 animate-entry stagger-3">
                        
                        <!-- Reference Vectors -->
                        <div class="group bg-white/50 dark:bg-slate-800/40 rounded-2xl p-5 border border-white/60 dark:border-slate-700/40 relative overflow-hidden transition-all hover:bg-white/80 dark:hover:bg-slate-800/80">
                            <!-- SVG Decorative Icon Absolute -->
                            <svg class="absolute -right-4 -bottom-4 w-24 h-24 text-slate-200 dark:text-slate-700/30 transform group-hover:scale-110 transition-transform duration-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                            
                            <div class="relative z-10 flex flex-col h-full">
                                <span class="text-[0.6rem] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-2">Código Interno</span>
                                <span class="text-base md:text-lg font-black font-mono text-brand-600 dark:text-brand-400 break-all leading-tight mt-auto">
                                    <?php echo htmlspecialchars($bien['codigo']); ?>
                                </span>
                            </div>
                        </div>

                        <div class="group bg-white/50 dark:bg-slate-800/40 rounded-2xl p-5 border border-white/60 dark:border-slate-700/40 relative overflow-hidden transition-all hover:bg-white/80 dark:hover:bg-slate-800/80">
                            <!-- SVG Decorative Icon Absolute -->
                            <svg class="absolute -right-4 -bottom-4 w-24 h-24 text-slate-200 dark:text-slate-700/30 transform group-hover:scale-110 transition-transform duration-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"></path></svg>
                            
                            <div class="relative z-10 flex flex-col h-full">
                                <span class="text-[0.6rem] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-2">Serial ID Fabricante</span>
                                <span class="text-base md:text-lg font-bold font-mono text-slate-800 dark:text-slate-200 break-all leading-tight mt-auto">
                                    <?php echo !empty($bien['serial']) ? htmlspecialchars($bien['serial']) : 'N/A'; ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Date Info Box -->
                    <div class="flex justify-between items-center bg-white/50 dark:bg-slate-800/40 rounded-2xl p-4 border border-white/60 dark:border-slate-700/40 animate-entry stagger-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-500 to-brand-400 flex justify-center items-center text-white mr-4 shadow-lg shadow-brand-500/20">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <div>
                                <span class="block text-[0.6rem] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Adquisición Reg.</span>
                                <span class="block text-sm font-black text-slate-800 dark:text-white"><?php echo $fechaStr; ?></span>
                            </div>
                        </div>

                        <!-- Mini SVG Shield -->
                        <div class="text-slate-300 dark:text-slate-600">
                             <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Sophisticated Footer -->
                <div class="bg-brand-50 dark:bg-slate-900/90 py-5 text-center flex items-center justify-center border-t border-white dark:border-slate-800">
                    <svg class="w-4 h-4 text-brand-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM12 2a1 1 0 01.967.744L14.146 7.2 17.5 9.134a1 1 0 010 1.732l-3.354 1.935-1.18 4.455a1 1 0 01-1.933 0L9.854 12.8 6.5 10.866a1 1 0 010-1.732l3.354-1.935 1.18-4.455A1 1 0 0112 2z" clip-rule="evenodd"></path></svg>
                    <span class="text-[0.65rem] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Verificado • SDGBP System</span>
                </div>
            </div>

        <?php else: ?>
            <!-- Ultra Premium Error Layout -->
            <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-2xl rounded-[2.5rem] premium-shadow border border-white/50 dark:border-slate-700/50 overflow-hidden relative text-center p-12">
                <!-- SVG Error Background Graphics -->
                <svg class="absolute top-0 right-0 w-64 h-64 text-red-500/5 transform translate-x-1/2 -translate-y-1/2 rotate-45" fill="currentColor" viewBox="0 0 100 100">
                    <rect width="100" height="100" />
                </svg>
                <svg class="absolute bottom-0 left-0 w-64 h-64 text-red-500/5 transform -translate-x-1/2 translate-y-1/2 rotate-12" fill="currentColor" viewBox="0 0 100 100">
                    <circle cx="50" cy="50" r="50" />
                </svg>

                <div class="relative z-10 pt-4">
                    <div class="w-24 h-24 mx-auto mb-6 bg-red-100 dark:bg-red-500/10 text-red-500 rounded-[1.5rem] flex items-center justify-center transform rotate-12 shadow-inner border border-red-200 dark:border-red-500/20">
                        <!-- Custom SVG Icon for Error -->
                        <svg class="w-12 h-12 transform -rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                
                    <h2 class="text-3xl font-display font-black text-slate-900 dark:text-white mb-3">Activo <span class="text-red-500">No Encontrado</span></h2>
                    
                    <p class="text-sm font-medium text-slate-600 dark:text-slate-400 mb-8 leading-relaxed">
                        El código de bien proporcionado no está registrado en el inventario activo de EURIPYS 2024.
                    </p>
                    
                    <div class="inline-flex flex-col text-left bg-slate-100 dark:bg-slate-800/80 rounded-2xl p-4 border border-slate-200 dark:border-slate-700 mb-10 min-w-[200px]">
                        <span class="text-[0.6rem] font-bold uppercase tracking-widest text-slate-500 mb-1">Parámetro Entrante</span>
                        <span class="font-mono text-sm text-red-500 font-bold break-all">
                            <?php echo !empty($_GET['c']) ? htmlspecialchars($_GET['c']) : 'NULL'; ?>
                        </span>
                    </div>

                    <div>
                        <a href="index.php" class="inline-flex items-center justify-center px-8 py-3.5 bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded-xl font-bold transition-all hover:bg-brand-600 dark:hover:bg-brand-500 hover:text-white shadow-xl hover:-translate-y-1">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                            Regresar Atrás
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
