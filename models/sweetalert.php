<?php include("../models/toastr_css.php"); ?>
<?php if (isset($_SESSION["estatus"]) && isset($_SESSION["mensaje"])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const preloader = document.getElementById('custom-global-preloader');
            const delay = 0;
            setTimeout(() => {
                Swal.fire({
                    icon: '<?php echo $_SESSION["estatus"]; ?>',
                    title: '<?php echo $_SESSION["mensaje"]; ?>',
                    showConfirmButton: true,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#007bff'
                });
            }, delay);
        });
    </script>
 <?php
// Limpiar las variables de sesión después de mostrar la alerta
    unset($_SESSION["estatus"]);
    unset($_SESSION["mensaje"]);
?>                
<?php endif; ?>

<style>
    .swal2-container { z-index: 20000000 !important; }
</style>

<?php if (isset($_SESSION['type']) && isset($_SESSION['alert'])): ?>
    <style>
        /* Revolutionary Welcome Modal Styles */
        .premium-welcome-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            z-index: 999999;
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .premium-welcome-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }

        .premium-welcome-modal {
            position: relative;
            width: 92%;
            max-width: 500px;
            background: rgba(255, 255, 255, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            transform: scale(0.95) translateY(20px);
            opacity: 0;
            transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
            overflow: hidden;
            text-align: center;
            padding: 2.5rem 2rem;
        }

        [data-theme="dark"] .premium-welcome-modal, body.dark-mode .premium-welcome-modal {
            background: rgba(15, 23, 42, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .premium-welcome-overlay.active .premium-welcome-modal {
            transform: scale(1) translateY(0);
            opacity: 1;
        }

        /* Animated Top Border */
        .welcome-top-glow {
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 5px;
            background: linear-gradient(90deg, #3b82f6, #f18000, #3b82f6, #f18000);
            background-size: 300% 100%;
            animation: gradientShift 3s linear infinite;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            100% { background-position: 100% 50%; }
        }

        /* Avatar Container */
        .welcome-avatar-wrapper {
            position: relative;
            display: inline-block;
            margin-bottom: 1.5rem;
        }

        .welcome-avatar-glow {
            position: absolute;
            top: -5px; left: -5px; right: -5px; bottom: -5px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #f18000);
            filter: blur(12px);
            opacity: 0.6;
            animation: pulseGlow 2s infinite alternate;
        }

        @keyframes pulseGlow {
            0% { opacity: 0.4; transform: scale(0.95); }
            100% { opacity: 0.8; transform: scale(1.05); }
        }

        .welcome-avatar {
            position: relative;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid #ffffff;
            object-fit: cover;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            z-index: 2;
        }

        [data-theme="dark"] .welcome-avatar { border-color: #0f172a; }

        /* Text Styles */
        .welcome-heading {
            font-size: 2rem;
            font-weight: 900;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #1e293b, #64748b);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        [data-theme="dark"] .welcome-heading { background: linear-gradient(135deg, #ffffff, #cbd5e1); -webkit-background-clip: text; background-clip: text; }

        .welcome-subheading {
            font-size: 1.1rem;
            font-weight: 500;
            color: #64748b;
            margin-bottom: 2rem;
        }

        [data-theme="dark"] .welcome-subheading { color: #94a3b8; }

        .welcome-grid-title {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 800;
            color: #94a3b8;
            margin-bottom: 1rem;
        }

        /* Grid Actions */
        .welcome-actions-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .welcome-action-card {
            background: #ffffff;
            border: 1px solid rgba(0,0,0,0.05);
            border-radius: 1.25rem;
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            text-decoration: none;
            color: inherit;
        }

        [data-theme="dark"] .welcome-action-card {
            background: rgba(30, 41, 59, 0.6);
            border-color: rgba(255,255,255,0.05);
        }

        .welcome-action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(241, 128, 0, 0.15);
            border-color: rgba(f,128,0,0.3);
        }

        .welcome-icon-box {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: rgba(241, 128, 0, 0.08); /* Brand clear orange */
            color: #f18000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-bottom: 0.75rem;
            transition: all 0.3s ease;
        }

        .welcome-action-card:hover .welcome-icon-box {
            background: #f18000;
            color: #ffffff;
            transform: scale(1.1);
        }

        .welcome-action-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: #334155;
            text-align: center;
        }

        [data-theme="dark"] .welcome-action-title { color: #f8fafc; }

        /* Dismiss Button */
        .welcome-dismiss-btn {
            width: 100%;
            padding: 1rem;
            border-radius: 1rem;
            border: none;
            background: #0f172a;
            color: #ffffff;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.4);
        }

        [data-theme="dark"] .welcome-dismiss-btn {
            background: #f18000;
            box-shadow: 0 10px 25px -5px rgba(241, 128, 0, 0.4);
        }

        .welcome-dismiss-btn:hover {
            transform: translateY(-2px);
            background: #f18000;
            box-shadow: 0 15px 30px -5px rgba(241, 128, 0, 0.5);
        }
        
        [data-theme="dark"] .welcome-dismiss-btn:hover { background: #ea580c; }

        .welcome-dismiss-btn i { transition: transform 0.3s; }
        .welcome-dismiss-btn:hover i { transform: translateX(4px); }

        /* Floating background elements */
        .welcome-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            z-index: 0;
            opacity: 0.3;
            animation: floatOrb 10s infinite ease-in-out alternate;
        }
        
        .orb-1 { width: 300px; height: 300px; background: #3b82f6; top: -100px; left: -100px; }
        .orb-2 { width: 400px; height: 400px; background: #f18000; bottom: -150px; right: -150px; animation-delay: -5s; }

        @keyframes floatOrb {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(30px, 40px) scale(1.2); }
        }
    </style>

    <!-- Custom HTML Structure -->
    <div id="premiumWelcomeOverlay" class="premium-welcome-overlay">
        
        <div class="welcome-orb orb-1"></div>
        <div class="welcome-orb orb-2"></div>
        
        <div class="premium-welcome-modal">
            <div class="welcome-top-glow"></div>
            
            <div class="welcome-avatar-wrapper">
                <div class="welcome-avatar-glow"></div>
                <?php $foto_src = (isset($_SESSION["foto"]) && !empty($_SESSION["foto"])) ? $_SESSION["foto"] : "../img/default-user.png"; ?>
                <img src="<?php echo htmlspecialchars($foto_src); ?>" alt="Avatar" class="welcome-avatar">
            </div>

            <h2 class="welcome-heading">¡Bienvenido!</h2>
            <p class="welcome-subheading"><?php echo htmlspecialchars($_SESSION["alert"]); ?></p>

            <p class="welcome-grid-title">¿Qué te gustaría hacer ahora?</p>

            <div class="welcome-actions-grid">
                <?php 
                $tipo = $_SESSION['tipo'] ?? '';
                if ($tipo == 'admin') {
                    echo '<div onclick="closeWelcomeGo(\'registro_u.php\')" class="welcome-action-card">
                            <div class="welcome-icon-box"><i class="fas fa-user-plus"></i></div>
                            <span class="welcome-action-title">Nuevo Usuario</span>
                          </div>';
                    echo '<div onclick="closeWelcomeGo(\'registro_bien.php\')" class="welcome-action-card">
                            <div class="welcome-icon-box" style="color:#0ea5e9; background:rgba(14,165,233,0.1);"><i class="fas fa-box-open"></i></div>
                            <span class="welcome-action-title">Nuevo Bien</span>
                          </div>';
                } elseif ($tipo == 'cont') {
                    echo '<div onclick="closeWelcomeGo(\'registro_pagos_egresos.php\')" class="welcome-action-card">
                            <div class="welcome-icon-box"><i class="fas fa-file-invoice-dollar"></i></div>
                            <span class="welcome-action-title">Nueva Comisión</span>
                          </div>';
                    echo '<div onclick="closeWelcomeGo(\'ver_pagos_cont.php\')" class="welcome-action-card">
                            <div class="welcome-icon-box" style="color:#0ea5e9; background:rgba(14,165,233,0.1);"><i class="fas fa-search-dollar"></i></div>
                            <span class="welcome-action-title">Revisar Pagos</span>
                          </div>';
                } elseif ($tipo == 'inv') {
                    echo '<div onclick="closeWelcomeGo(\'registro_bien.php\')" class="welcome-action-card">
                            <div class="welcome-icon-box"><i class="fas fa-plus-circle"></i></div>
                            <span class="welcome-action-title">Registrar Bien</span>
                          </div>';
                    echo '<div onclick="closeWelcomeGo(\'lista_bienes.php\')" class="welcome-action-card">
                            <div class="welcome-icon-box" style="color:#0ea5e9; background:rgba(14,165,233,0.1);"><i class="fas fa-list"></i></div>
                            <span class="welcome-action-title">Ver Inventario</span>
                          </div>';
                } elseif ($tipo == 'upu') {
                    echo '<div onclick="closeWelcomeGo(\'registro_pagos.php\')" class="welcome-action-card">
                            <div class="welcome-icon-box" style="color:#10b981; background:rgba(16,185,129,0.1);"><i class="fas fa-download"></i></div>
                            <span class="welcome-action-title">Ingreso</span>
                          </div>';
                    echo '<div onclick="closeWelcomeGo(\'registro_pagos_egresos.php\')" class="welcome-action-card">
                            <div class="welcome-icon-box" style="color:#ef4444; background:rgba(239,68,68,0.1);"><i class="fas fa-upload"></i></div>
                            <span class="welcome-action-title">Egreso</span>
                          </div>';
                    echo '<div onclick="closeWelcomeGo(\'ver_pagos.php\')" class="welcome-action-card" style="grid-column: span 2; display: flex; flex-direction: row; gap: 10px;">
                            <div class="welcome-icon-box" style="width:35px; height:35px; margin:0;"><i class="fas fa-history" style="font-size:1rem;"></i></div>
                            <span class="welcome-action-title">Ver Mi Historial</span>
                          </div>';
                }
                ?>
            </div>

            <button class="welcome-dismiss-btn" onclick="closePremiumWelcome()">
                Ir al Panel Principal <i class="fas fa-arrow-right"></i>
            </button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const overlay = document.getElementById('premiumWelcomeOverlay');
            // Retraso para que la animación de entrada se vea elegante tras la carga
            setTimeout(() => {
                if(overlay) overlay.classList.add('active');
            }, 100);
        });

        function closePremiumWelcome() {
            const overlay = document.getElementById('premiumWelcomeOverlay');
            if(overlay) {
                overlay.classList.remove('active');
                setTimeout(() => {
                    overlay.remove();
                }, 600); // Esperar que termine la transición CSS
            }
        }

        function closeWelcomeGo(url) {
            closePremiumWelcome();
            setTimeout(() => {
                if (typeof navigateTo === 'function') {
                    navigateTo(url);
                } else {
                    window.location.href = url;
                }
            }, 300);
        }
    </script>

    <?php
    // Limpiar las variables de sesión después de mostrar la alerta
    unset($_SESSION['type']);
    unset($_SESSION['alert']);
    ?>
<?php endif; ?>
