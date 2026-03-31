<?php
    session_start();
    session_destroy();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Denegado - SDGBP</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../img/favicon.ico">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#f18000',
                        'primary-dark': '#d67100',
                        'brand-blue': '#0f172a',
                    },
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #f8fafc; overflow-x: hidden; }
        .denied-card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 20px 40px -10px rgba(220, 38, 38, 0.1), 0 10px 20px -5px rgba(0, 0, 0, 0.04);
            border: 1px solid rgba(220, 38, 38, 0.1);
            position: relative;
            overflow: hidden;
        }
        .denied-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 6px;
            background: linear-gradient(90deg, #ef4444, #f87171);
        }
        .icon-pulse {
            animation: pulse-ring 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse-ring {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.5); }
            70% { transform: scale(1); box-shadow: 0 0 0 20px rgba(239, 68, 68, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }
        .btn-primary-custom {
            background: linear-gradient(135deg, #f18000 0%, #d67100 100%);
            transition: all 0.3s ease;
        }
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px -6px rgba(241, 128, 0, 0.5);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center relative">
    
    <!-- Background Accents -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-red-100 rounded-full mix-blend-multiply filter blur-3xl opacity-50"></div>
        <div class="absolute top-40 -left-40 w-96 h-96 bg-orange-100 rounded-full mix-blend-multiply filter blur-3xl opacity-50"></div>
    </div>

    <!-- Main Content -->
    <div class="w-full max-w-md px-6 py-12 mt-4">
        <div class="denied-card p-10 text-center relative z-10">
            
            <!-- Logo area -->
            <div class="flex justify-center mb-6">
                <div class="bg-white p-3 rounded-2xl shadow-sm border border-slate-100">
                    <img src="../img/Logo-OP2_V4.webp" alt="SDGBP" class="w-20 h-20 object-contain">
                </div>
            </div>

            <!-- Icon -->
            <div class="mb-8 flex justify-center">
                <div class="w-20 h-20 bg-red-50 rounded-full flex items-center justify-center icon-pulse">
                    <i class="fa-solid fa-shield-cat text-4xl text-red-500 hidden"></i>
                    <i class="fa-solid fa-lock text-4xl text-red-500"></i>
                </div>
            </div>

            <h1 class="text-3xl font-bold text-brand-blue mb-2 tracking-tight">Acceso Denegado</h1>
            <p class="text-slate-500 font-medium mb-8 text-sm uppercase tracking-widest">Error de Autorización</p>
            
            <div class="bg-slate-50 rounded-xl p-5 mb-8 border border-slate-100 text-left">
                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-circle-info text-red-500 mt-1"></i>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        <strong>No tienes permisos</strong> para acceder a este módulo del sistema. Tu sesión ha sido cerrada preventivamente por seguridad. Si consideras que esto es un error, contacta inmediatamente al administrador central.
                    </p>
                </div>
            </div>

            <div class="space-y-4">
                <a href="login.php" class="btn-primary-custom w-full flex items-center justify-center gap-2 text-white font-semibold py-3.5 px-6 rounded-xl">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                    Volver al Inicio de Sesión
                </a>
                
                <a href="https://wa.me/584129796940?text=Hola%2C%20soy%20usuario%20del%20Sistema%20de%20Gesti%C3%B3n%20de%20Bienes%20y%20Pagos.%20Tengo%20acceso%20denegado%20y%20necesito%20ayuda%20para%20ingresar.%20Por%20favor%2C%20ind%C3%ADqueme%20los%20pasos%20a%20seguir." target="_blank" class="w-full flex items-center justify-center gap-2 text-emerald-600 bg-emerald-50 hover:bg-emerald-100 transition-colors font-semibold py-3.5 px-6 rounded-xl border border-emerald-100">
                    <i class="fa-brands fa-whatsapp text-lg"></i>
                    Contactar Soporte
                </a>
            </div>
            
        </div>
        
        <!-- Footer -->
        <div class="mt-12 text-center pb-8">
            <p class="text-sm text-slate-500 mb-2 font-medium">SDGBP - Sistema de Gestión de Bienes y Pagos</p>
            <p class="text-xs text-slate-400 font-medium tracking-wide">
                &copy; <?php echo date("Y"); ?> Todos los derechos reservados.
            </p>
        </div>
    </div>

</body>
</html>
