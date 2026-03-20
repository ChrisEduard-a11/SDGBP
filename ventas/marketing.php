<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EURIPYS | Premium Store</title>
    
    <!-- Google Fonts: Inter and Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;800;900&display=swap" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
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
                            300: '#fdba74',
                            400: '#fb923c',
                            500: '#f97316',
                            600: '#f18000', /* EXACT BRAND ORANGE */
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
                            '0%': { opacity: '0', transform: 'translateY(15px)' },
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

    <style>
        /* Hide scrollbars but keep functionality */
        .hide-scroll::-webkit-scrollbar { display: none; }
        .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }

        .glass-nav {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .view-section { display: none; }
        .view-section.active { display: block; animation: fadeInUp 0.5s ease-out forwards; }

        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none; margin: 0;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased overflow-x-hidden pt-32">

    <!-- Background Ambient Glow -->
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none">
        <div class="absolute top-0 -left-4 w-72 h-72 bg-amber-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
        <div class="absolute top-0 -right-4 w-72 h-72 bg-brand-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
        <div class="absolute -bottom-8 left-20 w-72 h-72 bg-orange-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-4000"></div>
    </div>

    <!-- Topbar Oficial BCV -->
    <div class="fixed top-0 w-full bg-slate-900 text-slate-100 py-1.5 z-50 text-xs font-medium tracking-wide text-center shadow-md">
        <div class="max-w-7xl mx-auto px-4 flex justify-between items-center h-full">
            <div class="flex items-center space-x-2 text-emerald-400">
                <i class="fas fa-shield-alt text-sm"></i>
                <span class="hidden sm:inline">Transacción Segura Tasa Oficial BCV</span>
            </div>
            <div id="tasaBcvValue" class="flex items-center space-x-2">
                <i class="fas fa-circle-notch fa-spin text-brand-400"></i>
                <span>Enlazando API...</span>
            </div>
        </div>
    </div>

    <!-- Navbar Ultra Premium -->
    <nav class="glass-nav fixed top-[32px] w-full z-40 border-b border-slate-200 shadow-sm transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center cursor-pointer group" onclick="Navegar('catalogo')">
                    <img src="../img/Logo-OP2_V4.webp" alt="Logo" class="h-10 w-auto opacity-90 group-hover:opacity-100 transition-opacity drop-shadow-sm filter grayscale contrast-200">
                    <span class="ml-3 font-display font-black text-2xl tracking-tighter text-slate-900 group-hover:text-brand-600 transition-colors">EURIPYS</span>
                </div>

                <!-- Search -->
                <div class="flex-1 max-w-2xl px-8 hidden md:block relative group">
                    <div class="absolute inset-y-0 left-12 flex items-center pointer-events-none text-slate-400 group-focus-within:text-brand-500 transition-colors">
                        <i class="fas fa-search"></i>
                    </div>
                    <input type="text" id="busquedaProducto" oninput="FiltrarBusquedaGlobal()" class="block w-full w-full bg-slate-100/50 border border-slate-200 rounded-full py-3 pl-12 pr-6 text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/50 focus:border-brand-500 focus:bg-white transition-all shadow-inner" placeholder="Busca laptops, componentes, o herramientas...">
                </div>

                <!-- Actions -->
                <div class="flex items-center space-x-6">
                    <a href="../vistas/login.php" class="hidden lg:inline-flex items-center justify-center px-5 py-2.5 border border-slate-300 text-sm font-semibold rounded-full text-slate-700 bg-white hover:bg-slate-50 hover:text-brand-600 hover:border-brand-300 transition-all shadow-sm">
                        ERP Portal
                    </a>
                    
                    <button onclick="Navegar('carrito')" class="relative p-2 text-slate-600 hover:text-brand-600 transition-colors group">
                        <i class="fas fa-shopping-bag text-2xl group-hover:scale-110 transition-transform"></i>
                        <span id="carritoBadge" class="absolute top-0 right-0 inline-flex items-center justify-center h-5 w-5 rounded-full bg-gradient-to-r from-amber-500 to-brand-500 text-white text-[10px] font-bold shadow-sm hidden ring-2 ring-white">0</span>
                    </button>
                    
                    <!-- Mobile Search Trigger -->
                    <button class="md:hidden p-2 text-slate-600 hover:text-brand-600">
                        <i class="fas fa-search text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-24 relative z-10">

        <!-- ==========================================
             VISTA 1: CATÁLOGO
        =========================================== -->
        <div id="viewCatalogo" class="view-section active space-y-8">
            
            <!-- Hero Section -->
            <div class="relative rounded-3xl overflow-hidden bg-brand-950 shadow-[0_20px_50px_rgba(241,_128,_0,_0.1)]">
                <div class="absolute inset-0 bg-gradient-to-br from-brand-600/40 via-amber-600/20 to-transparent"></div>
                <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjEiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4wNSkiLz48L3N2Zz4=')] [mask-image:linear-gradient(to_bottom,white,transparent)]"></div>
                
                <div class="relative p-10 md:p-16 flex flex-col items-start justify-center text-left">
                    <span class="px-3 py-1 text-xs font-semibold tracking-wider text-brand-100 uppercase bg-white/10 border border-white/20 rounded-full mb-6 backdrop-blur-md">Hardware de Alto Rendimiento</span>
                    <h1 class="text-4xl md:text-6xl font-display font-black text-white leading-tight tracking-tight mb-4 max-w-3xl">
                        Acelerando el futuro de tu <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-300 to-amber-300">innovación.</span>
                    </h1>
                    <p class="text-lg text-slate-300 max-w-xl font-light">Catálogo exclusivo de componentes electrónicos y heramientas del Ecosistema de Producción de la UPTAG.</p>
                </div>
            </div>

            <!-- Categories Filter -->
            <div class="sticky top-[112px] z-30 pt-4 pb-2 bg-slate-50/95 backdrop-blur-sm -mx-4 px-4 sm:mx-0 sm:px-0">
                <div class="flex space-x-3 hide-scroll overflow-x-auto" id="pillsContainer"></div>
            </div>

            <!-- Products Grid -->
            <div id="productosGrid" class="space-y-16"></div>
        </div>


        <!-- ==========================================
             VISTA 2: CARRITO DE COMPRAS
        =========================================== -->
        <div id="viewCarrito" class="view-section pt-8">
            <button onclick="Navegar('catalogo')" class="text-brand-600 hover:text-brand-800 font-medium inline-flex items-center group transition-colors mb-8">
                <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i> Volver al Catálogo
            </button>
            
            <div class="mb-10">
                <h2 class="text-4xl font-display font-extrabold text-slate-900 tracking-tight">Tu Carrito</h2>
                <p class="text-slate-500 mt-2 text-lg">Revisa los artículos que has seleccionado antes de continuar al pago seguro.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                <!-- Cart Items -->
                <div class="lg:col-span-8 space-y-4" id="carritoItemsContainer">
                    <!-- Injected via JS -->
                </div>

                <!-- Order Summary Panel -->
                <div class="lg:col-span-4">
                    <div class="bg-white rounded-3xl p-8 border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] sticky top-[140px]">
                        <h3 class="text-lg font-bold text-slate-900 mb-6">Resumen Financiero</h3>
                        
                        <div class="space-y-4 text-sm">
                            <div class="flex justify-between items-center text-slate-600">
                                <span>Subtotal Original (USD)</span>
                                <span class="font-semibold text-slate-900" id="resumenSubtotal">...</span>
                            </div>
                            <div class="flex justify-between items-center text-slate-600">
                                <span>Tasa Especial BCV</span>
                                <span class="font-semibold text-brand-600 bg-brand-50 px-2 py-0.5 rounded" id="resumenTasa">...</span>
                            </div>
                            <div class="flex justify-between items-center text-slate-600 pb-2">
                                <span class="flex items-center"><i class="fas fa-truck-fast text-slate-400 mr-2"></i> Logística de Envío</span>
                                <span class="text-brand-600 font-bold bg-brand-50 px-2.5 py-1 rounded shadow-sm text-xs border border-brand-100">Cobro a Destino (C.O.D)</span>
                            </div>
                            <div class="pb-4 border-b border-dashed border-slate-200">
                                <p class="text-[11px] text-slate-400 leading-tight">El costo exacto del flete vehicular será liquidado y pagado por usted directamente a la agencia de paquetería (MRW, Zoom, etc.) al recibir su paquete.</p>
                            </div>
                        </div>

                        <div class="mt-4 flex justify-between items-end">
                            <div>
                                <p class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-1">Monto Estimado</p>
                                <p class="text-3xl font-display font-black text-slate-900" id="resumenTotalBs">...</p>
                            </div>
                        </div>

                        <button onclick="Navegar('checkout')" class="w-full mt-8 bg-slate-900 hover:bg-brand-600 text-white py-4 rounded-xl font-bold text-lg shadow-lg hover:shadow-brand-500/25 transition-all hover:-translate-y-0.5 flex justify-center items-center group">
                            Procesar Pago
                            <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                        </button>
                        
                        <p class="text-xs text-slate-400 text-center mt-4 flex items-center justify-center">
                            <i class="fas fa-lock mr-2"></i> Transacción cifrada con protocolo TLS
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==========================================
             VISTA 3: CHECKOUT (PAGO Y ENVÍO)
        =========================================== -->
        <div id="viewCheckout" class="view-section pt-8">
            <button onclick="Navegar('carrito')" class="text-brand-600 hover:text-brand-800 font-medium inline-flex items-center group transition-colors mb-8">
                <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i> Modificar Pedido
            </button>

            <div class="mb-10">
                <h2 class="text-4xl font-display font-extrabold text-slate-900 tracking-tight">Finalizar Compra</h2>
                <p class="text-slate-500 mt-2 text-lg">Ingresa comprobantes y valida la entrega segura de tus equipos.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                <!-- Data Form -->
                <div class="lg:col-span-7">
                    <form id="formCheckout" class="space-y-8">
                        
                        <!-- Block 1: Info -->
                        <div class="bg-white rounded-3xl p-8 border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] relative overflow-hidden">
                            <div class="absolute top-0 left-0 w-1 h-full bg-brand-500"></div>
                            <h3 class="text-xl font-bold text-slate-900 mb-6 flex items-center">
                                <span class="bg-brand-100 text-brand-700 w-8 h-8 rounded-full flex items-center justify-center text-sm mr-3">1</span>
                                Información de Cliente
                            </h3>
                            
                            <div class="space-y-5">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">Nombre y Apellido</label>
                                    <input type="text" id="coNombre" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500 focus:bg-white transition-colors" placeholder="Introduce tu nombre legal">
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-1">Correo Electrónico</label>
                                        <input type="email" id="coCorreo" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500 focus:bg-white transition-colors" placeholder="ejemplo@correo.com">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-1">Teléfono Principal</label>
                                        <input type="text" id="coTelefono" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500 focus:bg-white transition-colors" placeholder="04121234567">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Block 2: Logistics -->
                        <div class="bg-white rounded-3xl p-8 border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] relative overflow-hidden">
                            <div class="absolute top-0 left-0 w-1 h-full bg-indigo-500"></div>
                            <h3 class="text-xl font-bold text-slate-900 mb-6 flex items-center">
                                <span class="bg-indigo-100 text-indigo-700 w-8 h-8 rounded-full flex items-center justify-center text-sm mr-3">2</span>
                                Logística de Entrega
                            </h3>
                            
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-3">Modalidad de Despacho</label>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <label class="relative flex cursor-pointer rounded-2xl border border-slate-200 bg-white p-4 shadow-sm focus:outline-none hover:border-brand-500 has-[:checked]:border-brand-500 has-[:checked]:ring-2 has-[:checked]:ring-brand-500 has-[:checked]:bg-brand-50 transition-all group">
                                            <input type="radio" name="coTipoEntrega" value="Retiro" class="peer sr-only">
                                            <div class="flex items-center">
                                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-slate-600 peer-checked:bg-brand-100 peer-checked:text-brand-600 transition-colors group-hover:scale-105">
                                                    <i class="fas fa-store text-xl"></i>
                                                </div>
                                                <div class="ml-4 flex flex-col">
                                                    <span class="text-sm font-bold text-slate-900">Retiro Personal</span>
                                                    <span class="text-xs text-slate-500">Sede administrativa central</span>
                                                </div>
                                            </div>
                                        </label>
                                        <label class="relative flex cursor-pointer rounded-2xl border border-slate-200 bg-white p-4 shadow-sm focus:outline-none hover:border-brand-500 has-[:checked]:border-brand-500 has-[:checked]:ring-2 has-[:checked]:ring-brand-500 has-[:checked]:bg-brand-50 transition-all group">
                                            <input type="radio" name="coTipoEntrega" value="Envio" class="peer sr-only">
                                            <div class="flex items-center">
                                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-slate-600 peer-checked:bg-brand-100 peer-checked:text-brand-600 transition-colors group-hover:scale-105">
                                                    <i class="fas fa-truck-fast text-xl"></i>
                                                </div>
                                                <div class="ml-4 flex flex-col">
                                                    <span class="text-sm font-bold text-slate-900">Envío Nacional</span>
                                                    <span class="text-xs text-slate-500">Agencias de mensajería (COD)</span>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <div id="coEnvioDetalles" class="hidden space-y-5 pt-4 border-t border-slate-100 animate-fade-in-up">
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-2">Agencia Vehicular Preferida</label>
                                        <select id="coAgencia" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 text-slate-700 appearance-none font-medium">
                                            <option value="" disabled selected>Selecciona tu paquetería de confianza...</option>
                                            <option value="MRW">Agencia MRW</option>
                                            <option value="Zoom">Grupo Zoom</option>
                                            <option value="Tealca">Tealca</option>
                                            <option value="Domesa">Domesa</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-2">Dirección Exacta Destino (Sede o Residencia)</label>
                                        <textarea id="coDireccion" rows="2" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 text-slate-700 resize-none transition-colors leading-relaxed" placeholder="Ej: Estado Falcón, Ciudad de Coro. C.C. Las Virtudes. Entregar a nombre de..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Block 3: Payment -->
                        <div class="bg-white rounded-3xl p-8 border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] relative overflow-hidden">
                            <div class="absolute top-0 left-0 w-1 h-full bg-slate-900"></div>
                            <h3 class="text-xl font-bold text-slate-900 mb-6 flex items-center">
                                <span class="bg-slate-100 text-slate-700 w-8 h-8 rounded-full flex items-center justify-center text-sm mr-3">3</span>
                                Soporte Bancario
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">Modalidad</label>
                                    <select id="coMetodoPago" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 text-slate-700 appearance-none font-medium">
                                        <option value="" disabled selected>Elige un método...</option>
                                        <option value="Pago Móvil">Pago Móvil</option>
                                        <option value="Transferencia">Transferencia Bancaria</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">Banco Destino</label>
                                    <select id="coBanco" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 text-slate-700 appearance-none font-medium">
                                        <option value="BDV">Banco de Venezuela (BDV)</option>
                                        <option value="Banesco">Banesco</option>
                                        <option value="Mercantil">Mercantil</option>
                                        <option value="Provincial">Provincial</option>
                                        <option value="Otro">Otro Banco...</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Payment Instructions Box -->
                            <div id="bancaInstrucciones" class="hidden bg-brand-50/50 border border-brand-100 rounded-2xl p-6 mb-6"></div>

                            <div class="space-y-5">
                                <h4 class="text-sm font-bold text-slate-400 uppercase tracking-widest pt-2 border-t border-slate-100">Datos Emitidos Extraídos</h4>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div id="coDynamicWrapper1">
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Cédula del Titular</label>
                                        <input type="text" id="coCedula" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:bg-white">
                                    </div>
                                    <div id="coDynamicWrapper2">
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Teléfono Emisor</label>
                                        <input type="text" id="coTelefonoOrigen" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:bg-white">
                                    </div>
                                    <div id="coDynamicWrapper3" class="hidden md:col-span-2">
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Cuenta Corriente de Origen (20 dígitos)</label>
                                        <input type="text" id="coCuenta" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:bg-white font-mono">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Nro. Comprobante / Referencia <span class="text-red-500">*</span></label>
                                        <input type="text" id="coReferencia" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:bg-white font-mono text-brand-700 font-bold tracking-wider">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Fecha de Emisión <span class="text-red-500">*</span></label>
                                        <input type="date" id="coFecha" value="<?php echo date('Y-m-d'); ?>" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:bg-white">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Block 3: Action -->
                        <div class="bg-gradient-to-r from-slate-900 to-brand-950 rounded-3xl p-8 shadow-xl text-white">
                            <label class="block text-sm text-slate-300 mb-2 font-medium">Monto Exacto Vislumbrado en tu Recibo (Bs)</label>
                            <div class="relative mb-6">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 font-bold fs-5">Bs.</span>
                                <input type="number" step="0.01" id="coMontoPagado" class="w-full pl-12 pr-4 py-4 bg-white/10 border border-white/20 rounded-xl text-white text-2xl font-bold focus:ring-2 focus:ring-brand-400 focus:bg-white/20 transition-all outline-none" placeholder="0.00">
                            </div>
                            <button type="submit" id="btnConfirmarPedido" class="w-full bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-white font-bold py-4 rounded-xl shadow-[0_0_20px_rgba(16,185,129,0.3)] transition-all hover:scale-[1.01] flex justify-center items-center text-lg">
                                Someter Auditoría a Pago <i class="fas fa-arrow-circle-up ml-2"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Resume Column -->
                <div class="lg:col-span-5">
                    <div class="bg-slate-50 border border-slate-200 rounded-3xl p-8 sticky top-[140px]">
                        <h4 class="text-slate-900 font-bold text-lg mb-6 flex items-center">
                            <i class="fas fa-boxes text-slate-400 mr-2"></i> Orden Detallada
                        </h4>
                        
                        <div id="checkoutResumeList" class="space-y-4 mb-6 max-h-96 overflow-y-auto pr-2 hide-scroll"></div>
                        
                        <div class="pt-6 border-t border-slate-200">
                            <p class="text-sm font-semibold text-slate-500 mb-1">Deuda Conciliada Total</p>
                            <p class="text-3xl font-display font-black text-brand-600" id="checkoutTotalBs">...</p>
                            <p class="text-xs text-slate-400 mt-2"><i class="fas fa-shield-check mr-1"></i> Tasa asegurada por sistema interbancario.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <!-- Tailwind Toasts Container -->
    <div id="toastContainer" class="fixed bottom-6 right-6 z-[2000] flex flex-col gap-3 pointer-events-none"></div>

    <script src="marketing.js"></script>
</body>
</html>