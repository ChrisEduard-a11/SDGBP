<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link rel="canonical" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" />

    <title>Desbloquear - SDGBP</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../img/favicon.ico">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="../sweetalert/sweetalert2.min.css">
    <script src="../sweetalert/sweetalert2.js"></script>

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
        body, html {
            margin: 0; padding: 0; width: 100%; height: 100%;
            font-family: 'Outfit', sans-serif; background-color: #f8fafc;
        }

        .login-layout { display: flex; min-height: 100vh; width: 100%; }

        .login-image-side {
            display: none; position: relative; flex: 1; background-color: var(--brand-blue); overflow: hidden;
            position: sticky; top: 0; height: 100vh;
        }
        @media (min-width: 1024px) {
            .login-image-side { display: flex; flex-direction: column; justify-content: center; align-items: center; }
        }

        .login-bg-img {
            position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; z-index: 0;
        }
        .login-overlay {
            position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.85) 0%, rgba(15, 23, 42, 0.6) 100%);
            z-index: 1;
        }

        .login-image-content {
            position: relative; z-index: 2; padding: 4rem; color: #fff; max-width: 650px;
        }

        .login-badge {
            display: inline-block; padding: 0.4rem 1rem; background: rgba(241, 128, 0, 0.2);
            border: 1px solid rgba(241, 128, 0, 0.3); color: #f18000; border-radius: 50px;
            font-size: 0.75rem; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase;
            backdrop-filter: blur(4px); margin-bottom: 2rem;
        }

        .login-image-title { font-size: 3.5rem; font-weight: 800; line-height: 1.1; margin-bottom: 1.5rem; letter-spacing: -1px; }
        .login-image-title span { background: linear-gradient(135deg, #f18000 0%, #ffc107 100%); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
        .login-image-desc { font-size: 1.1rem; color: rgba(255,255,255,0.8); line-height: 1.6; font-weight: 300; }

        .login-form-side {
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            padding: 2rem; width: 100%; background-color: #ffffff;
            overflow-y: auto; overflow-x: hidden;
        }

        @media (min-width: 1024px) {
            .login-form-side { width: 500px; padding: 3rem 4rem; flex-shrink: 0; }
        }
        @media (min-width: 1280px) {
            .login-form-side { width: 550px; }
        }

        .login-form-container { width: 100%; max-width: 420px; }

        .inst-logo { width: 65px; margin-bottom: 1.2rem; }
        .inst-title { font-size: 1.8rem; font-weight: 800; color: #0f172a; margin-bottom: 0.5rem; letter-spacing: -0.5px; }
        .inst-subtitle { font-size: 0.9rem; color: #64748b; font-weight: 400; margin-bottom: 2rem; }

        .inst-input-wrapper {
            display: flex; align-items: center;
            background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px;
            padding: 0 1rem; margin-bottom: 1.25rem; transition: all 0.3s;
        }
        .inst-input-wrapper:focus-within {
            border-color: #f18000; background: #fff; box-shadow: 0 0 0 4px rgba(241, 128, 0, 0.1);
        }

        .inst-icon { color: #94a3b8; font-size: 1.1rem; padding-right: 1rem; transition: color 0.3s; }
        .inst-input-wrapper:focus-within .inst-icon { color: #f18000; }
        
        .inst-input {
            width: 100%; background: transparent; border: none; padding: 1.1rem 0;
            color: #1e293b; font-size: 1rem; outline: none; font-weight: 500;
        }
        .inst-input::placeholder { color: #94a3b8; font-weight: 400; }

        .inst-label { font-size: 0.75rem; font-weight: 700; color: #64748b; margin-bottom: 0.3rem; margin-left: 0.2rem; text-transform: uppercase; letter-spacing: 0.5px; display: block;}

        .inst-btn-submit {
            width: 100%; padding: 1.1rem; border: none; border-radius: 12px;
            background: #0f172a; color: #fff; font-size: 1rem; font-weight: 700; letter-spacing: 0.5px;
            cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 0.5rem;
        }
        .inst-btn-submit:hover { background: #f18000; transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(241, 128, 0, 0.3); }

        .inst-links { display: flex; justify-content: center; margin-top: 1.5rem; }
        .inst-link { color: #64748b; font-size: 0.85rem; font-weight: 600; text-decoration: none; transition: color 0.3s; display: flex; align-items: center; gap: 0.4rem; }
        .inst-link:hover { color: #f18000; }

        .inst-footer { margin-top: auto; text-align: center; width: 100%; padding-top: 2rem; }
        .inst-footer p { font-size: 0.75rem; color: #94a3b8; }

        .recovery-icon {
            font-size: 2.5rem; color: #f18000; margin-bottom: 1.5rem;
            background: rgba(241, 128, 0, 0.1); width: 80px; height: 80px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 50%; margin-left: auto; margin-right: auto;
            border: 1px solid rgba(241, 128, 0, 0.2); box-shadow: 0 4px 15px rgba(241, 128, 0, 0.1);
        }
    </style>
</head>
<body>
<!-- GLOBAL PRELOADER -->
<style>.swal2-container { z-index: 9999999 !important; }</style>
<div id="global-preloader" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); z-index: 999999; display: flex; align-items: center; justify-content: center; transition: opacity 0.4s ease, visibility 0.4s ease;">
    <div style="color: #f18000; text-align: center; padding: 20px;">
        <i class="fas fa-circle-notch fa-spin" style="font-size: 4rem; filter: drop-shadow(0 0 10px rgba(255,255,255,0.3)); margin-bottom: 20px;"></i>
        <h5 style="font-family: 'Outfit', sans-serif; font-weight: 600; color: #ffffff; letter-spacing: 1px; margin: 0;">Cargando...</h5>
    </div>
</div>
<script>
    window.addEventListener('load', function() {
        const preloader = document.getElementById('global-preloader');
        if (preloader) {
            preloader.style.opacity = '0';
            preloader.style.visibility = 'hidden';
            setTimeout(() => preloader.remove(), 400);
        }
    });
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('a:not([target="_blank"]):not([href^="#"]):not([href^="javascript:"])').forEach(link => {
            link.addEventListener('click', function(e) {
                if (!e.ctrlKey && !e.shiftKey && !e.metaKey && this.href) {
                    const preloader = document.getElementById('global-preloader');
                    if (preloader) {
                        preloader.style.visibility = 'visible';
                        preloader.style.opacity = '1';
                    }
                }
            });
        });
    });
</script>
<!-- END GLOBAL PRELOADER -->

    <div class="login-layout">

        <!-- Left: Corporate Image Side -->
        <div class="login-image-side">
            <img src="../img/login_bg_premium.png" alt="Corporative Office" class="login-bg-img">
            <div class="login-overlay"></div>
            <div class="login-image-content">
                <div class="login-badge">Protección de Cuentas</div>
                <h1 class="login-image-title">Desbloquea tu <span>Usuario</span></h1>
                <p class="login-image-desc">
                    El sistema protege tus datos bloqueando los intentos fallidos reiterados. Solicita tu desbloqueo aquí de forma segura para recuperar el mando de tu gestión.
                </p>
                <div class="flex items-center gap-4 mt-8">
                    <div class="flex -space-x-3">
                        <div class="w-10 h-10 rounded-full bg-slate-800 border-2 border-slate-700 flex items-center justify-center"><i class="fas fa-lock-open text-slate-300 text-sm"></i></div>
                        <div class="w-10 h-10 rounded-full bg-slate-800 border-2 border-slate-700 flex items-center justify-center"><i class="fas fa-shield-alt text-slate-300 text-sm"></i></div>
                    </div>
                    <span class="text-sm font-medium text-slate-300">Auditoría Estricta</span>
                </div>
            </div>
        </div>

        <!-- Right: Form Side -->
        <div class="login-form-side">
            <div class="login-form-container">
                
                <div class="text-center md:text-left">
                    <img src="../img/Logo-OP2_V4.webp" alt="Logo" class="inst-logo mx-auto md:mx-0">
                    <h2 class="inst-title">Desbloquear Cuenta</h2>
                    <p class="inst-subtitle">Ingresa tu usuario para que un administrador evalúe tu solicitud.</p>
                </div>

                <form action="../acciones/enviar_desbloqueo.php" method="POST" onsubmit="return validateFormSD()" class="mt-8">
                    
                    <div class="recovery-icon mx-auto md:mx-0">
                        <i class="fas fa-user-lock"></i>
                    </div>

                    <div>
                        <label class="inst-label text-center md:text-left" for="inputUsuario">Nombre de Usuario (*)</label>
                        <div class="inst-input-wrapper">
                            <i class="fas fa-user-tag inst-icon"></i>
                            <input id="inputUsuario" type="text" name="usuario" class="inst-input" style="text-align: center; font-size: 1.1rem; letter-spacing: 1px;" placeholder="Ingresa tu usuario" autocomplete="off" />
                        </div>
                    </div>

                    <button class="inst-btn-submit mt-4" type="submit">
                        <i class="fas fa-paper-plane"></i> Enviar Solicitud
                    </button>

                </form>

                <?php include("../models/sweetalert.php"); ?>

                <div class="inst-links">
                    <a href="login.php" class="inst-link">
                        <i class="fas fa-arrow-left"></i> Volver al Login
                    </a>
                </div>

            </div>

            <div class="inst-footer">
                <p>&copy; <?php echo date("Y"); ?> SDGBP. Todos los derechos reservados.</p>
            </div>
        </div>

    </div>

    <!-- Scripts -->
    <script src="../js/vali_login.js"></script>
    
</body>
</html>
