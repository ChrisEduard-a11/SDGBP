<?php
session_start();
require_once("conexion.php");

?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>SDGBP - EURIPYS</title>
    
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">

    <!-- Fonts: Plus Jakarta Sans / Inter / Outfit for Premium feel -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;800;900&display=swap" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" type="text/css" href="sweetalert/sweetalert2.min.css">
    <script src="sweetalert/sweetalert2.js"></script>

    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class', // Enables dark mode toggling via 'dark' class
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
                            600: '#f18000', // EXACT BRAND ORANGE
                            700: '#c2410c',
                            800: '#9a3412',
                            900: '#7c2d12',
                            950: '#431407',
                        }
                    },
                    animation: {
                        'fade-in-up': 'fadeInUp 0.5s ease-out forwards',
                        'blob': 'blob 7s infinite',
                    },
                    keyframes: {
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        blob: {
                            '0%': { transform: 'translate(0px, 0px) scale(1)' },
                            '33%': { transform: 'translate(30px, -50px) scale(1.1)' },
                            '66%': { transform: 'translate(-20px, 20px) scale(0.9)' },
                            '100%': { transform: 'translate(0px, 0px) scale(1)' },
                        }
                    }
                }
            }
        }
    </script>

    <!-- Script to prevent FOUC for Dark Mode -->
    <script>
        if (localStorage.theme === 'dark') {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
    </script>

    <style>
        .glass-nav {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }
        .dark .glass-nav {
            background: rgba(15, 23, 42, 0.85); /* slate-900 w/ opacity */
            border-bottom-color: rgba(255,255,255,0.05);
        }

        .gradient-text {
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Modals */
        .modal-overlay {
            background-color: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            transition: opacity 0.3s ease;
        }
        
        /* Iframe scroll fix */
        iframe {
            display: block;
            border: none;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-700 dark:bg-slate-900 dark:text-slate-300 font-sans antialiased transition-colors duration-300 overflow-x-hidden pt-20">

    <!-- Navbar Ultra Premium -->
    <nav class="glass-nav fixed top-0 w-full z-50 border-b border-slate-200 dark:border-slate-800 transition-all duration-300 h-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full">
            <div class="flex justify-between items-center h-full">
                <!-- Logo -->
                <a href="#" class="flex-shrink-0 flex items-center group">
                    <img src="img/Logo-OP2_V4.webp" alt="Logo" class="h-10 w-auto opacity-90 group-hover:opacity-100 transition-opacity drop-shadow-sm">
                    <span class="ml-3 font-display font-black text-2xl tracking-tighter text-slate-900 dark:text-white group-hover:text-brand-600 dark:group-hover:text-brand-500 transition-colors">EURIPYS</span>
                </a>

                <!-- Desktop Menu -->
                <div class="hidden lg:flex items-center space-x-1 xl:space-x-4">
                    <?php if($marketingActivo): ?>
                    <a href="#marketing" class="px-3 py-2 text-sm font-semibold text-slate-800 dark:text-slate-200 hover:text-brand-600 dark:hover:text-brand-400 transition-colors">Marketing</a>
                    <?php endif; ?>
                    <a href="#servicios" class="px-3 py-2 text-sm font-semibold text-slate-800 dark:text-slate-200 hover:text-brand-600 dark:hover:text-brand-400 transition-colors">Servicios</a>
                    <a href="#funcionalidades" class="px-3 py-2 text-sm font-semibold text-slate-800 dark:text-slate-200 hover:text-brand-600 dark:hover:text-brand-400 transition-colors">Funcionalidades</a>
                    <a href="#historia" class="px-3 py-2 text-sm font-semibold text-slate-800 dark:text-slate-200 hover:text-brand-600 dark:hover:text-brand-400 transition-colors">Historia</a>
                    <a href="#quienes-somos" class="px-3 py-2 text-sm font-semibold text-slate-800 dark:text-slate-200 hover:text-brand-600 dark:hover:text-brand-400 transition-colors">Nosotros</a>
                    <a href="#contacto" class="px-3 py-2 text-sm font-semibold text-slate-800 dark:text-slate-200 hover:text-brand-600 dark:hover:text-brand-400 transition-colors">Contacto</a>
                </div>

                <!-- Actions -->
                <div class="hidden lg:flex items-center space-x-4">
                    <a href="vistas/login.php" class="inline-flex items-center justify-center px-6 py-2.5 bg-brand-600 hover:bg-brand-500 text-white text-sm font-bold rounded-full shadow-[0_8px_20px_rgba(241,128,0,0.3)] hover:-translate-y-0.5 hover:shadow-[0_12px_25px_rgba(241,128,0,0.4)] transition-all">
                        <i class="fas fa-sign-in-alt me-2"></i> Acceder
                    </a>
                </div>

                <!-- Mobile menu button -->
                <div class="flex lg:hidden items-center">
                    <button id="mobileMenuBtn" class="p-2 text-slate-600 dark:text-slate-300 hover:text-brand-600 dark:hover:text-brand-400 focus:outline-none">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile Dropdown -->
        <div id="mobileMenu" class="hidden lg:hidden bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 absolute w-full shadow-lg">
            <div class="flex flex-col px-4 pt-2 pb-6 space-y-2">
                <?php if($marketingActivo): ?>
                <a href="#marketing" class="block px-4 py-3 rounded-xl text-base font-semibold text-slate-800 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800">Marketing</a>
                <?php endif; ?>
                <a href="#servicios" class="block px-4 py-3 rounded-xl text-base font-semibold text-slate-800 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800">Servicios</a>
                <a href="#funcionalidades" class="block px-4 py-3 rounded-xl text-base font-semibold text-slate-800 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800">Funcionalidades</a>
                <a href="#historia" class="block px-4 py-3 rounded-xl text-base font-semibold text-slate-800 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800">Historia</a>
                <a href="#contacto" class="block px-4 py-3 rounded-xl text-base font-semibold text-slate-800 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800">Contacto</a>
                <a href="vistas/login.php" class="block mt-4 text-center px-4 py-3 bg-brand-600 text-white rounded-xl font-bold flex items-center justify-center">
                    <i class="fas fa-sign-in-alt mr-2"></i> Acceder
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative min-h-[90vh] flex items-center justify-center overflow-hidden">
        <!-- Animated Background Image -->
        <div class="absolute inset-0 z-0">
            <img src="img/logo_animado3d.gif" alt="Background Animation" class="w-full h-full object-cover opacity-5 dark:opacity-[0.03]">
        </div>

        <!-- Abstract Blobs -->
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-brand-300/20 dark:bg-brand-600/10 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob"></div>
        <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-orange-400/20 dark:bg-orange-600/10 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob animation-delay-2000"></div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 w-full">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="text-left">
                    <div class="inline-flex items-center px-3 py-1.5 rounded-full border border-brand-200 dark:border-brand-900 bg-brand-50 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 text-sm font-bold tracking-wide uppercase mb-8 shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-brand-600 dark:bg-brand-400 mr-2 animate-pulse"></span> Producción e Inversión
                    </div>
                    
                    <h1 class="text-5xl sm:text-6xl md:text-7xl font-display font-black tracking-tight text-slate-900 dark:text-white leading-[1.1] mb-6">
                        Empresa Universitaria <br/>
                        <span class="bg-gradient-to-r from-brand-500 to-amber-500 gradient-text">EURIPYS 2024.</span>
                    </h1>
                    
                    <p class="text-xl md:text-2xl text-slate-500 dark:text-slate-400 font-light mb-10 max-w-2xl leading-relaxed">
                        Rental corporativo de inversión, producción comercial y de servicios de la <strong class="text-slate-700 dark:text-slate-200 font-semibold">Universidad Politécnica Territorial Alonso Gamero</strong>.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="vistas/login.php" class="inline-flex justify-center items-center px-8 py-4 bg-brand-600 hover:bg-brand-500 text-white rounded-2xl font-bold text-lg shadow-[0_10px_25px_rgba(241,128,0,0.3)] hover:-translate-y-1 hover:shadow-[0_15px_35px_rgba(241,128,0,0.4)] transition-all">
                            Comenzar Ahora <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                        <button onclick="document.getElementById('manualModal').classList.remove('hidden')" class="inline-flex justify-center items-center px-8 py-4 bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:border-slate-300 dark:hover:border-slate-600 rounded-2xl font-bold text-lg hover:-translate-y-1 shadow-sm transition-all focus:outline-none">
                            <i class="fa-solid fa-book mr-2"></i> Manual de Usuario
                        </button>
                    </div>
                </div>
                
                <div class="hidden lg:block relative">
                    <div class="absolute inset-0 bg-gradient-to-tr from-brand-600/20 to-transparent rounded-[3rem] transform rotate-3 scale-105 filter blur-xl"></div>
                    <img src="img/Logo-OP2_V4.webp" alt="3D Presentation" class="relative z-10 w-full max-w-lg mx-auto drop-shadow-2xl animate-fade-in-up">
                </div>
            </div>
        </div>
    </section>

    <?php if($marketingActivo): ?>
    <!-- Marketing Call to Action Area -->
    <section id="marketing" class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-slate-900 dark:bg-slate-950 rounded-[3rem] p-10 md:p-16 lg:p-20 shadow-2xl relative overflow-hidden">
                <!-- BG Decor -->
                <div class="absolute top-0 right-0 w-1/2 h-full bg-gradient-to-l from-brand-600/20 to-transparent"></div>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center relative z-10">
                    <div>
                        <img src="img/marketing.webp" alt="Marketing" class="rounded-3xl shadow-[0_20px_40px_rgba(0,0,0,0.4)] border border-slate-800 w-full object-cover">
                    </div>
                    <div>
                        <div class="inline-flex items-center justify-center p-3 bg-brand-500/10 rounded-2xl mb-6">
                            <i class="fas fa-bullhorn text-3xl text-brand-500"></i>
                        </div>
                        <h2 class="text-4xl font-display font-black text-white mb-6">Tienda de Compras & Negocios</h2>
                        <p class="text-slate-400 text-lg mb-6 leading-relaxed">
                            En EURIPYS 2024, C.A., hemos construido un catálogo espectacular donde el marketing es nuestra herramienta clave para promover productos y herramientas producidos por la UPTAG. Destacando la calidad y el impacto de nuestras soluciones en el mercado.
                        </p>
                        <p class="text-slate-400 text-lg mb-10 leading-relaxed">
                            Acércate y mira nuestro catálogo Ultra Premium, revisa los equipos, agrégalos al carrito y reporta tus compras formalmente.
                        </p>
                        <div class="flex flex-wrap gap-4">
                            <a href="ventas/marketing.php" class="inline-flex items-center px-8 py-4 bg-brand-600 hover:bg-brand-500 text-white font-bold rounded-xl shadow-lg shadow-brand-500/25 transition-all">
                                Ingresar al Catálogo
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Global Grid Standard Design -->
    <section id="servicios" class="py-24 bg-white dark:bg-slate-900 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-4xl md:text-5xl font-display font-extrabold text-slate-900 dark:text-white mb-4">Nuestros Servicios</h2>
                <p class="text-xl text-slate-500 dark:text-slate-400">Soluciones universitarias directas hacia la sociedad y sus requerimientos institucionales.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Card 1 -->
                <div class="bg-slate-50 dark:bg-slate-800 rounded-3xl p-10 border border-slate-100 dark:border-slate-700 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 group">
                    <div class="w-16 h-16 bg-white dark:bg-slate-700 rounded-2xl flex items-center justify-center shadow-md mb-8 group-hover:scale-110 transition-transform">
                        <i class="fas fa-laptop-code text-3xl text-brand-600 dark:text-brand-400"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-4">Formación y Consultoría</h3>
                    <p class="text-slate-600 dark:text-slate-400 leading-relaxed">Brindamos servicios de alta formación y consultoría profesional en diversas áreas de las ciencias sociales, administrativas y de red.</p>
                </div>
                
                <!-- Card 2 -->
                <div class="bg-slate-50 dark:bg-slate-800 rounded-3xl p-10 border border-slate-100 dark:border-slate-700 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 group">
                    <div class="w-16 h-16 bg-white dark:bg-slate-700 rounded-2xl flex items-center justify-center shadow-md mb-8 group-hover:scale-110 transition-transform">
                        <i class="fas fa-network-wired text-3xl text-brand-600 dark:text-brand-400"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-4">Emprendimiento Int.</h3>
                    <p class="text-slate-600 dark:text-slate-400 leading-relaxed">Promovemos e incubamos los emprendimientos surgidos directamente desde el cuerpo de ingenieros y licenciados de la universidad.</p>
                </div>

                <!-- Card 3 -->
                <div class="bg-slate-50 dark:bg-slate-800 rounded-3xl p-10 border border-slate-100 dark:border-slate-700 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 group">
                    <div class="w-16 h-16 bg-white dark:bg-slate-700 rounded-2xl flex items-center justify-center shadow-md mb-8 group-hover:scale-110 transition-transform">
                        <i class="fas fa-shield-alt text-3xl text-brand-600 dark:text-brand-400"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-4">Gestión de Producción</h3>
                    <p class="text-slate-600 dark:text-slate-400 leading-relaxed">Desarrollamos e iteramos sobre la gestión administrativa profunda de todas las unidades de producción físicas con las que contamos.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="funcionalidades" class="py-24 bg-slate-50 dark:bg-slate-800/50 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-4xl md:text-5xl font-display font-extrabold text-slate-900 dark:text-white mb-4">Ejes Operativos</h2>
                <p class="text-xl text-slate-500 dark:text-slate-400">Las cuatro aristas elementales del ecosistema EURIPYS.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white dark:bg-slate-800 p-8 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-sm hover:border-brand-500 dark:hover:border-brand-500 transition-colors text-center">
                    <i class="fas fa-graduation-cap text-4xl text-brand-500 mb-6"></i>
                    <h4 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Capacitación</h4>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Seminarios y congresos que elevan la competencia interna.</p>
                </div>
                <div class="bg-white dark:bg-slate-800 p-8 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-sm hover:border-brand-500 dark:hover:border-brand-500 transition-colors text-center">
                    <i class="fas fa-comments-dollar text-4xl text-brand-500 mb-6"></i>
                    <h4 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Asesoría</h4>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Apoyo integral a nivel macro para entidades externas.</p>
                </div>
                <div class="bg-white dark:bg-slate-800 p-8 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-sm hover:border-brand-500 dark:hover:border-brand-500 transition-colors text-center">
                    <i class="fas fa-lightbulb text-4xl text-brand-500 mb-6"></i>
                    <h4 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Innovación</h4>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Incubación de startups en diferentes ramales tecnológicos.</p>
                </div>
                <div class="bg-white dark:bg-slate-800 p-8 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-sm hover:border-brand-500 dark:hover:border-brand-500 transition-colors text-center">
                    <i class="fas fa-chart-line text-4xl text-brand-500 mb-6"></i>
                    <h4 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Inversión</h4>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Manejo y optimización del retorno financiero corporativo.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- History / Story -->
    <section id="historia" class="py-24 bg-white dark:bg-slate-900 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                <div class="order-2 lg:order-1 relative">
                    <div class="absolute inset-0 bg-brand-500 rounded-3xl transform -rotate-3 scale-105 opacity-20 dark:opacity-40"></div>
                    <img src="img/Logo-OP2_V4.webp" alt="Historia" class="relative z-10 w-full rounded-3xl bg-slate-100 dark:bg-slate-800 shadow-2xl p-10">
                </div>
                <div class="order-1 lg:order-2">
                    <h2 class="text-4xl md:text-5xl font-display font-extrabold text-slate-900 dark:text-white mb-8">Nuestra Historia</h2>
                    
                    <div class="space-y-6 text-lg text-slate-600 dark:text-slate-400 leading-relaxed font-light">
                        <p>
                            EURIPYS 2024, C.A. nació orgánicamente como una iniciativa soberana para fortalecer transversalmente el patrimonio financiero de la Universidad Politécnica Territorial Alonso Gamero (UPTAG).
                        </p>
                        <p>
                            La piedra angular se sentó en 2016, tras una ardua formalización apoyada por el departamento jurídico y el rectorado en pleno, materializando nuestras operaciones de élite. Liderada por la profesora y administradora Yohana Colina, la entidad fue refrendada legalmente y potenciada para el libre mercado el 28 de octubre de 2024 bajo la denominación EURIPYS.
                        </p>
                        <p>
                            A partir de ese diciembre, nuestro ecosistema ha impulsado sistemáticamente y sin descanso la inyección de capital a las Unidades de Producción a Nivel Nacional.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mision Vision Mod -->
    <section id="quienes-somos" class="py-24 border-t border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                <div>
                    <h2 class="text-3xl font-display font-black text-slate-900 dark:text-white mb-6">Misión</h2>
                    <p class="text-slate-500 dark:text-slate-400 leading-relaxed">
                        Contribuir al absoluto sostenimiento financiero de la UPTAG en la comercialización de productos, sistemas y servicios generados en el recinto, facilitando la incorporación productiva de nuestros egresados a la sociedad real.
                    </p>
                </div>
                <div>
                    <h2 class="text-3xl font-display font-black text-slate-900 dark:text-white mb-6">Visión</h2>
                    <p class="text-slate-500 dark:text-slate-400 leading-relaxed">
                        Convertirnos en el estándar de oro nacional como empresa universitaria que amalgama investigadores, trabajadores tecnológicos y hardware en actividades lucrativas de alto impacto científico-social.
                    </p>
                </div>
                <div class="bg-slate-50 dark:bg-slate-800 p-8 rounded-3xl border border-slate-200 dark:border-slate-700">
                    <h2 class="text-2xl font-display font-black text-slate-900 dark:text-white mb-6">Nuestros Valores</h2>
                    <ul class="space-y-4">
                        <li class="flex items-center text-slate-700 dark:text-slate-300 font-medium">
                            <i class="fas fa-check-circle text-brand-500 text-xl mr-3"></i> Institucionalidad Suprema.
                        </li>
                        <li class="flex items-center text-slate-700 dark:text-slate-300 font-medium">
                            <i class="fas fa-check-circle text-brand-500 text-xl mr-3"></i> Idoneidad y Transparencia.
                        </li>
                        <li class="flex items-center text-slate-700 dark:text-slate-300 font-medium">
                            <i class="fas fa-check-circle text-brand-500 text-xl mr-3"></i> Integridad Corporativa.
                        </li>
                        <li class="flex items-center text-slate-700 dark:text-slate-300 font-medium">
                            <i class="fas fa-check-circle text-brand-500 text-xl mr-3"></i> Alta Calidad Mundial.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact & Maps -->
    <section id="contacto" class="py-24 bg-slate-50 dark:bg-slate-950 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16">
                <!-- Info & Map -->
                <div>
                    <h2 class="text-4xl font-display font-extrabold text-slate-900 dark:text-white mb-8">Canales de Atención</h2>
                    
                    <div class="space-y-8 mb-10">
                        <div class="flex items-start">
                            <div class="w-12 h-12 bg-white dark:bg-slate-800 rounded-full flex items-center justify-center shadow-md shrink-0">
                                <i class="fas fa-map-marker-alt text-brand-500 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-bold text-slate-900 dark:text-white mb-1">Directorio Principal</h4>
                                <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed">Av. Libertador, Edificio Administrativo UPTAG pb, Sector los Orumos.<br>Coro, Municipio Miranda del Estado Falcón.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="w-12 h-12 bg-white dark:bg-slate-800 rounded-full flex items-center justify-center shadow-md shrink-0">
                                <i class="fas fa-phone-alt text-brand-500 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-bold text-slate-900 dark:text-white mb-1">Central Telefónica</h4>
                                <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed">0268-2515165<br>0412-2916480 (Mensajería)</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="w-12 h-12 bg-white dark:bg-slate-800 rounded-full flex items-center justify-center shadow-md shrink-0">
                                <i class="fas fa-envelope text-brand-500 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-bold text-slate-900 dark:text-white mb-1">Buzón Corporativo</h4>
                                <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed">euripys2024ca@gmail.com</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl overflow-hidden shadow-lg h-64 relative border border-slate-200 dark:border-slate-800">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2765.395539009471!2d-69.66365076613361!3d11.417714949859446!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8e842b3a0c5dcec9%3A0x2cb4d2c53b98bc27!2sEuripys%202024%20C.A!5e0!3m2!1ses-419!2sve!4v1770157784253!5m2!1ses-419!2sve" width="100%" height="100%" class="absolute inset-0" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>

                <!-- Form -->
                <div>
                    <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] p-10 border border-slate-100 dark:border-slate-800 shadow-[0_20px_40px_rgba(0,0,0,0.05)] h-full">
                        <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-8">Envíanos un Mensaje</h3>
                        
                        <form id="contactForm" action="acciones/procesar_contacto.php" method="POST" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Nombre y Apellido</label>
                                    <input type="text" id="nombre" name="nombre" placeholder="Ej: Juan Pérez" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white dark:focus:bg-slate-900 transition-all shadow-sm" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Correo Electrónico</label>
                                    <input type="email" id="email" name="email" placeholder="tucorreo@empresa.com" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white dark:focus:bg-slate-900 transition-all shadow-sm" required>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Mensaje o Solicitud</label>
                                <textarea id="mensaje" name="mensaje" rows="5" placeholder="Describe brevemente en qué podemos ayudarte..." class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white dark:focus:bg-slate-900 transition-all shadow-sm resize-none" required></textarea>
                            </div>
                            
                            <button type="submit" class="w-full py-4 bg-slate-900 dark:bg-white text-white dark:text-slate-900 hover:bg-brand-600 dark:hover:bg-brand-500 hover:text-white rounded-xl font-bold text-lg shadow-lg hover:shadow-brand-500/30 transition-all hover:-translate-y-1">
                                Enviar Requisición Oficial <i class="fas fa-paper-plane ml-2"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-950 pt-16 pb-8 border-t border-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center mb-10">
                <div class="flex items-center mb-6 md:mb-0">
                    <img src="img/Logo-OP2_V4.webp" alt="Logo" class="h-10 w-auto opacity-50">
                    <span class="ml-3 font-display font-bold text-xl text-slate-400">EURIPYS</span>
                </div>
                <div class="flex space-x-6">
                    <a href="#" class="text-slate-500 hover:text-brand-500 text-2xl transition-colors"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-slate-500 hover:text-brand-500 text-2xl transition-colors"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-slate-500 hover:text-brand-500 text-2xl transition-colors"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            
            <div class="border-t border-slate-900 pt-8 flex flex-col md:flex-row justify-between items-center text-slate-500 text-sm">
                <p class="mb-4 md:mb-0">
                    Licenciado bajo <a href="https://creativecommons.org/licenses/by-nc/4.0/?ref=chooser-v1" target="_blank" class="text-brand-500 hover:underline">Creative Commons BY-NC 4.0</a>
                </p>
                <div class="flex flex-col md:flex-row items-center gap-4">
                    <p class="font-medium text-slate-400">© <?php echo date("Y"); ?> Sistema de Gestión. Todos los derechos reservados.</p>
                    <span class="bg-slate-900 text-slate-500 px-3 py-1 rounded-full text-xs border border-slate-800">SDGBP v2.0</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Floating Theme Toggles -->
    <div class="fixed bottom-6 right-6 z-40 flex flex-col gap-3">
        <button id="themeToggle" class="w-12 h-12 rounded-full bg-white dark:bg-slate-800 text-slate-800 dark:text-white shadow-xl flex items-center justify-center hover:scale-110 transition-transform border border-slate-100 dark:border-slate-700">
            <i class="fas fa-moon dark:hidden"></i>
            <i class="fas fa-sun hidden dark:block text-amber-400"></i>
        </button>
        <button onclick="window.scrollTo({top:0, behavior:'smooth'})" class="w-12 h-12 rounded-full bg-brand-600 text-white shadow-lg shadow-brand-500/30 flex items-center justify-center hover:scale-110 transition-transform">
            <i class="fas fa-arrow-up"></i>
        </button>
    </div>

    <!-- Modal de Bienvenida (Flyers) -->
    <?php
    $flyers = [];
    if (is_dir('img/flyers')) {
        $files = glob('img/flyers/*.{jpg,jpeg,png,webp,gif}', GLOB_BRACE);
        if ($files) {
            $flyers = $files;
        }
    }
    
    if (!empty($flyers)):
    ?>
    <div id="modalBienvenida" class="fixed inset-0 z-[100] hidden">
        <div class="fixed inset-0 modal-overlay opacity-0" id="modalBienvenidaBackdrop" onclick="closeBienvenida()"></div>
        <div class="fixed inset-0 overflow-y-auto w-full h-full flex items-center justify-center p-4">
            <div id="modalBienvenidaPanel" class="bg-transparent w-full max-w-4xl lg:max-w-5xl relative transition-all duration-500 transform scale-95 opacity-0 flex flex-col items-center justify-center pointer-events-none">
                
                <!-- Contenedor del Carrusel -->
                <div class="w-full relative overflow-hidden shadow-2xl rounded-[1.5rem] md:rounded-[2rem] border-[3px] border-white/20 pointer-events-auto bg-slate-900">
                    
                    <button onclick="closeBienvenida()" class="absolute top-4 right-4 text-white hover:text-brand-500 bg-black/50 hover:bg-black/80 rounded-full w-10 h-10 flex items-center justify-center transition-all z-50 backdrop-blur-md shadow-lg border border-white/20">
                        <i class="fas fa-times text-xl"></i>
                    </button>

                    <div id="flyerCarousel" class="relative w-full h-[50vh] sm:h-[60vh] md:h-[70vh] flex bg-slate-950/90 items-center justify-center backdrop-blur-3xl">
                        <?php foreach($flyers as $index => $flyer): ?>
                            <div class="flyer-slide absolute inset-0 w-full h-full transition-opacity duration-700 ease-in-out <?php echo $index === 0 ? 'opacity-100 relative' : 'opacity-0 pointer-events-none'; ?> flex items-center justify-center p-2 md:p-4">
                                <img src="<?php echo $flyer; ?>" alt="Flyer Promocional" class="max-w-full max-h-full object-contain drop-shadow-2xl rounded-xl">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if(count($flyers) > 1): ?>
                    <!-- Controles -->
                    <button id="prevFlyer" class="absolute left-2 md:left-6 top-1/2 -translate-y-1/2 w-10 h-10 md:w-12 md:h-12 rounded-full bg-black/40 hover:bg-brand-600 border border-white/20 text-white flex items-center justify-center backdrop-blur-md transition-all shadow-xl hover:scale-110 z-40">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button id="nextFlyer" class="absolute right-2 md:right-6 top-1/2 -translate-y-1/2 w-10 h-10 md:w-12 md:h-12 rounded-full bg-black/40 hover:bg-brand-600 border border-white/20 text-white flex items-center justify-center backdrop-blur-md transition-all shadow-xl hover:scale-110 z-40">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    
                    <!-- Indicadores -->
                    <div class="absolute bottom-6 left-1/2 -translate-x-1/2 flex space-x-3 z-40 bg-black/40 px-4 py-2 rounded-full backdrop-blur-md border border-white/10">
                        <?php foreach($flyers as $index => $flyer): ?>
                            <button class="flyer-indicator w-2.5 h-2.5 rounded-full transition-all <?php echo $index === 0 ? 'bg-brand-500 scale-125 shadow-[0_0_10px_rgba(241,128,0,0.8)]' : 'bg-white/60 hover:bg-white'; ?>" data-index="<?php echo $index; ?>"></button>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Funcionalidad del Carrusel
        document.addEventListener('DOMContentLoaded', () => {
            const slides = document.querySelectorAll('.flyer-slide');
            const indicators = document.querySelectorAll('.flyer-indicator');
            if(slides.length <= 1) return;
            
            let currentSlide = 0;
            let slideInterval;
            
            function showSlide(index) {
                slides[currentSlide].classList.replace('opacity-100', 'opacity-0');
                slides[currentSlide].classList.remove('relative');
                slides[currentSlide].classList.add('pointer-events-none');
                
                if(indicators.length) {
                    indicators[currentSlide].classList.replace('bg-brand-500', 'bg-white/60');
                    indicators[currentSlide].classList.remove('scale-125', 'shadow-[0_0_10px_rgba(241,128,0,0.8)]');
                }
                
                currentSlide = (index + slides.length) % slides.length;
                
                slides[currentSlide].classList.replace('opacity-0', 'opacity-100');
                slides[currentSlide].classList.add('relative');
                slides[currentSlide].classList.remove('pointer-events-none');
                
                if(indicators.length) {
                    indicators[currentSlide].classList.replace('bg-white/60', 'bg-brand-500');
                    indicators[currentSlide].classList.add('scale-125', 'shadow-[0_0_10px_rgba(241,128,0,0.8)]');
                }
            }
            
            function nextSlide() { showSlide(currentSlide + 1); }
            function prevSlide() { showSlide(currentSlide - 1); }
            
            function startSlideShow() {
                slideInterval = setInterval(nextSlide, 5000);
            }
            
            document.getElementById('nextFlyer')?.addEventListener('click', () => { clearInterval(slideInterval); nextSlide(); startSlideShow(); });
            document.getElementById('prevFlyer')?.addEventListener('click', () => { clearInterval(slideInterval); prevSlide(); startSlideShow(); });
            
            indicators.forEach((indicator, i) => {
                indicator.addEventListener('click', () => {
                    clearInterval(slideInterval);
                    showSlide(i);
                    startSlideShow();
                });
            });
            
            startSlideShow();
        });
    </script>
    <?php endif; ?>

    <!-- Manual PDF Modal -->
    <div id="manualModal" class="fixed inset-0 z-[100] hidden">
        <div class="fixed inset-0 modal-overlay" onclick="document.getElementById('manualModal').classList.add('hidden')"></div>
        <div class="fixed inset-0 p-4 sm:p-6 lg:p-10 flex items-center justify-center pointer-events-none">
            <div class="bg-white dark:bg-slate-900 rounded-[2rem] w-full max-w-6xl h-full pb-8 shadow-2xl overflow-hidden flex flex-col pointer-events-auto border border-slate-200 dark:border-slate-800">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-900">
                    <h3 class="font-bold text-lg text-slate-800 dark:text-white"><i class="fas fa-book text-brand-500 mr-2"></i> Documentación de Referencia</h3>
                    <button onclick="document.getElementById('manualModal').classList.add('hidden')" class="text-slate-400 hover:text-red-500 w-8 h-8 rounded-full bg-white dark:bg-slate-800 flex items-center justify-center transition-colors shadow-sm">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="flex-grow w-full bg-slate-100 dark:bg-slate-950">
                    <iframe src="manuales/Manual_del_Usuario.pdf" class="w-full h-full"></iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- Javascript Handlers -->
    <script>
        // DOM Listeners
        document.addEventListener('DOMContentLoaded', () => {
            
            // Welcome Modal Logic
            const modal = document.getElementById('modalBienvenida');
            if (modal && !sessionStorage.getItem('modalMostrado')) {
                const backdrop = document.getElementById('modalBienvenidaBackdrop');
                const panel = document.getElementById('modalBienvenidaPanel');
                
                modal.classList.remove('hidden');
                // Trigger transitions after render
                requestAnimationFrame(() => {
                    backdrop.classList.remove('opacity-0');
                    panel.classList.remove('scale-95', 'opacity-0');
                    panel.classList.add('scale-100', 'opacity-100');
                });
                sessionStorage.setItem('modalMostrado', 'true');
            }

            // Dark Mode Toggle
            const themeBtn = document.getElementById('themeToggle');
            themeBtn.addEventListener('click', () => {
                const isDark = document.documentElement.classList.contains('dark');
                if (isDark) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                }
            });

            // Mobile Menu
            const btnMenu = document.getElementById('mobileMenuBtn');
            const menuDropdown = document.getElementById('mobileMenu');
            btnMenu.addEventListener('click', () => {
                menuDropdown.classList.toggle('hidden');
            });
            // Auto close mobile menu on click
            document.querySelectorAll('#mobileMenu a').forEach(a => {
                a.addEventListener('click', () => menuDropdown.classList.add('hidden'));
            });

            // Form Validation Style Fallback (Tailwind handles invalid pseudo-classes internally, but we can prevent default)
            const form = document.getElementById('contactForm');
            form.addEventListener('submit', (e) => {
                const email = document.getElementById('email').value;
                const regex = /^[a-zA-Z0-9._%+-]+@(claro\.com|claro\.com\.ve|gmail\.com|hotmail\.com)$/;
                if(!regex.test(email)) {
                    e.preventDefault();
                    Swal.fire({icon: 'error', title: 'Atención', text: 'Por favor usa un correo electrónico corporativo o válido (Gmail, Hotmail).', confirmButtonColor: '#f18000'});
                }
            });
        });

        function closeBienvenida() {
            const modal = document.getElementById('modalBienvenida');
            if(!modal) return;
            
            const panel = document.getElementById('modalBienvenidaPanel');
            const backdrop = document.getElementById('modalBienvenidaBackdrop');
            // Hide transitions
            panel.classList.remove('scale-100', 'opacity-100');
            panel.classList.add('scale-95', 'opacity-0');
            backdrop.classList.add('opacity-0');
            
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        // Security Context Restrictions
        document.addEventListener('keydown', function(e) {
            if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && e.key.toLowerCase() === 'i') || (e.ctrlKey && e.key.toLowerCase() === 'u')) {
                e.preventDefault();
            }
        });
        document.addEventListener('contextmenu', e => e.preventDefault());
    </script>

    <!-- Sweet Alert Notifications via PHP -->
    <?php if (isset($_SESSION["estatus"]) && isset($_SESSION["mensaje"])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    icon: '<?php echo htmlspecialchars($_SESSION["estatus"]); ?>',
                    title: '<?php echo htmlspecialchars($_SESSION["mensaje"]); ?>', 
                    showConfirmButton: true,
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#f18000',
                    background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#ffffff',
                    color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#0f172a',
                });
            });
        </script>
    <?php
        unset($_SESSION["estatus"]);
        unset($_SESSION["mensaje"]);
    endif; 
    ?>
</body>
</html>