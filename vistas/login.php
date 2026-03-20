<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Login Institucional - SDGBP</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../img/favicon.ico">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <script src="../js/all.js"></script>
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
                        'brand-blue': '#0f172a', /* Institucional Dark Blue */
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
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            font-family: 'Outfit', sans-serif;
            background-color: #f8fafc;
        }

        /* Layout Split */
        .login-layout {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        .login-image-side {
            display: none;
            position: relative;
            flex: 1;
            background-color: var(--brand-blue);
            overflow: hidden;
        }

        @media (min-width: 1024px) {
            .login-image-side { display: flex; flex-direction: column; justify-content: center; align-items: center; }
        }

        .login-bg-img {
            position: absolute; inset: 0;
            width: 100%; height: 100%;
            object-fit: cover;
            z-index: 0;
        }

        .login-overlay {
            position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.85) 0%, rgba(15, 23, 42, 0.4) 100%);
            z-index: 1;
        }

        .login-image-content {
            position: relative;
            z-index: 2;
            padding: 4rem;
            color: #fff;
            max-width: 650px;
        }

        .login-badge {
            display: inline-block;
            padding: 0.4rem 1rem;
            background: rgba(241, 128, 0, 0.2);
            border: 1px solid rgba(241, 128, 0, 0.3);
            color: #f18000;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            backdrop-filter: blur(4px);
            margin-bottom: 2rem;
        }

        .login-image-title { font-size: 4rem; font-weight: 800; line-height: 1.1; margin-bottom: 1.5rem; letter-spacing: -1px; }
        .login-image-title span { background: linear-gradient(135deg, #f18000 0%, #ffc107 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .login-image-desc { font-size: 1.15rem; color: rgba(255,255,255,0.8); line-height: 1.6; font-weight: 300; }

        .login-form-side {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            width: 100%;
            background-color: #ffffff;
            position: relative;
        }

        @media (min-width: 1024px) {
            .login-form-side { width: 500px; padding: 4rem; flex-shrink: 0; }
        }
        @media (min-width: 1280px) {
            .login-form-side { width: 550px; }
        }

        .login-form-container {
            width: 100%;
            max-width: 420px;
        }

        .inst-logo { width: 75px; margin-bottom: 1.5rem; }
        .inst-title { font-size: 2rem; font-weight: 800; color: #0f172a; margin-bottom: 0.5rem; letter-spacing: -0.5px; }
        .inst-subtitle { font-size: 0.95rem; color: #64748b; font-weight: 400; margin-bottom: 2.5rem; }

        .inst-input-wrapper {
            display: flex; align-items: center;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 0 1rem;
            margin-bottom: 1.25rem;
            transition: all 0.3s;
        }

        .inst-input-wrapper:focus-within {
            border-color: #f18000;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(241, 128, 0, 0.1);
        }

        .inst-icon { color: #94a3b8; font-size: 1.1rem; padding-right: 1rem; transition: color 0.3s; }
        .inst-input-wrapper:focus-within .inst-icon { color: #f18000; }
        
        .inst-input {
            width: 100%; background: transparent; border: none; padding: 1.1rem 0;
            color: #1e293b; font-size: 1rem; outline: none; font-weight: 500;
        }
        .inst-input::placeholder { color: #94a3b8; font-weight: 400; }
        .inst-btn-eye { background: transparent; border: none; color: #94a3b8; cursor: pointer; padding-left: 1rem; }
        .inst-btn-eye:hover { color: #f18000; }

        /* Captcha Area */
        .inst-captcha-container {
            background: #f8fafc;
            border: 1.5px dashed #cbd5e1;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .inst-captcha-label {
            font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.8rem;
        }

        .inst-captcha-flex {
            display: flex; gap: 0.8rem; align-items: stretch;
        }

        .inst-captcha-visual {
            position: relative; background: #fff; border-radius: 8px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            display: flex; align-items: center; justify-content: center;
        }

        #captchaCanvas { display: block; cursor: pointer; }

        .inst-captcha-refresh {
            position: absolute; right: -12px; top: -12px; background: #fff; border: 1px solid #e2e8f0; color: #64748b;
            border-radius: 50%; width: 26px; height: 26px; font-size: 0.7rem; cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: center; transition: all 0.2s;
        }
        .inst-captcha-refresh:hover { color: #f18000; transform: rotate(180deg); }

        .inst-captcha-input {
            flex: 1; background: #fff; border: 1.5px solid #e2e8f0; border-radius: 8px;
            padding: 0 1rem; text-align: center; font-size: 1.1rem; font-weight: 700; letter-spacing: 2px;
            outline: none; color: #1e293b; transition: all 0.3s; text-transform: uppercase;
        }
        .inst-captcha-input:focus { border-color: #f18000; box-shadow: 0 0 0 4px rgba(241, 128, 0, 0.1); }

        .inst-btn-submit {
            width: 100%; padding: 1.1rem; border: none; border-radius: 12px;
            background: #0f172a; color: #fff; font-size: 1rem; font-weight: 700; letter-spacing: 0.5px;
            cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 0.5rem;
        }
        .inst-btn-submit:hover:not(:disabled) { background: #f18000; transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(241, 128, 0, 0.3); }
        .inst-btn-submit:disabled { background: #cbd5e1; cursor: not-allowed; color: #f8fafc; }

        .inst-links { display: flex; flex-wrap: wrap; justify-content: center; gap: 1rem; margin-top: 2rem; }
        .inst-link { color: #64748b; font-size: 0.85rem; font-weight: 600; text-decoration: none; transition: color 0.3s; display: flex; align-items: center; gap: 0.4rem; }
        .inst-link:hover { color: #f18000; }

        .inst-footer { margin-top: auto; text-align: center; width: 100%; padding-top: 2rem; }
        .inst-footer p { font-size: 0.75rem; color: #94a3b8; }
        .inst-footer a { color: #64748b; font-weight: 500; text-decoration: none; }
        .inst-footer a:hover { color: #f18000; }
    </style>
</head>
<body>

    <div class="login-layout">

        <!-- Left: Corporate Image Side -->
        <div class="login-image-side">
            <img src="../img/login_bg_premium.png" alt="Corporative Office" class="login-bg-img">
            <div class="login-overlay"></div>
            <div class="login-image-content">
                <div class="login-badge">Sistema Institucional</div>
                <h1 class="login-image-title">Sistema de</h1>
                <h2 class="login-image-title"><span>Gestión</span> de Bienes y Pagos</h1>
                <p class="login-image-desc">
                    Una plataforma robusta, elegante y segura para la administración centralizada de bienes y pagos. Acceda al mejor entorno corporativo.
                </p>
                <div class="flex items-center gap-4 mt-8">
                    <div class="flex -space-x-3">
                        <div class="w-10 h-10 rounded-full bg-slate-800 border-2 border-slate-700 flex items-center justify-center"><i class="fas fa-shield-alt text-slate-300 text-sm"></i></div>
                        <div class="w-10 h-10 rounded-full bg-slate-800 border-2 border-slate-700 flex items-center justify-center"><i class="fas fa-lock text-slate-300 text-sm"></i></div>
                        <div class="w-10 h-10 rounded-full bg-slate-800 border-2 border-slate-700 flex items-center justify-center"><i class="fas fa-server text-slate-300 text-sm"></i></div>
                    </div>
                    <span class="text-sm font-medium text-slate-300">Infraestructura Segura</span>
                </div>
            </div>
        </div>

        <!-- Right: Form Side -->
        <div class="login-form-side">
            <div class="login-form-container">
                
                <div class="text-center md:text-left">
                    <img src="../img/Logo-OP2_V4.webp" alt="Logo" class="inst-logo mx-auto md:mx-0">
                    <h2 class="inst-title">Iniciar Sesión</h2>
                    <p class="inst-subtitle">Ingresa tus credenciales para continuar</p>
                </div>

                <form name="wowLoginForm" id="wowLoginForm" action="../acciones/login.php" method="POST" autocomplete="off">
                    
                    <div class="inst-input-wrapper">
                        <i class="fas fa-user inst-icon"></i>
                        <input type="text" id="inputEmail" name="usuario" class="inst-input" placeholder="Usuario">
                    </div>

                    <div class="inst-input-wrapper">
                        <i class="fas fa-lock inst-icon"></i>
                        <input type="password" id="inputPassword" name="clave" class="inst-input" placeholder="Contraseña">
                        <button type="button" class="inst-btn-eye" onclick="toggleWowPassword()">
                            <i id="wowEyeIcon" class="fas fa-eye"></i>
                        </button>
                    </div>

                    <?php include("../models/sweetalert.php"); ?>

                    <!-- Captcha Manual Premium -->
                    <div class="mb-5 bg-slate-50 p-4 rounded-xl border border-slate-100">
                        <label class="inst-label mb-2 block text-center">Código de Seguridad</label>
                        <div class="flex flex-col items-center gap-3">
                            <div class="flex flex-col sm:flex-row items-center gap-3">
                                <canvas id="captchaCanvas" width="240" height="70" class="rounded-lg shadow-sm border-2 border-slate-200 cursor-pointer bg-white" onclick="drawWowCaptcha()"></canvas>
                                <button type="button" onclick="drawWowCaptcha()" class="bg-white border border-slate-200 text-slate-500 hover:text-primary hover:border-primary transition-colors p-3 rounded-xl shadow-sm" title="Recargar imagen">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                            <div class="inst-input-wrapper mt-2 mb-0 w-full" style="max-width: 240px;">
                                <i class="fas fa-shield-alt inst-icon"></i>
                                <input type="text" id="captchaInput" autocomplete="off" class="inst-input text-center font-bold tracking-widest text-lg" placeholder="Escribe el código" maxlength="6" />
                            </div>
                        </div>
                    </div>

                    <button type="submit" id="btnEntrar" class="inst-btn-submit" disabled>
                        Acceder al Sistema <i class="fas fa-arrow-right"></i>
                    </button>

                </form>

                <div class="inst-links">
                    <a href="recuperar.php" class="inst-link"><i class="fas fa-unlock-alt"></i> Recuperar</a>
                    <a href="solicitar_desbloqueo.php" class="inst-link"><i class="fas fa-key"></i> Desbloquear</a>
                    <a href="register.php" class="inst-link"><i class="fas fa-user-plus"></i> Registrarse</a>
                </div>

            </div>

            <div class="inst-footer">
                <p>&copy; <?php echo date("Y"); ?> SDGBP. Todos los derechos reservados.</p>
                <p class="mt-1">Licencia <a href="https://creativecommons.org/licenses/by-nc/4.0/?ref=chooser-v1" target="_blank">Creative Commons BY-NC 4.0</a></p>
            </div>
        </div>

    </div>

    <!-- Scripts -->
    <script src="../js/vali_login.js?v=<?php echo time(); ?>"></script>
    
    <!-- Tawk.to Script -->
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
</body>
</html>
<?php
unset($_SESSION['old_usuario']);
unset($_SESSION['old_clave']);
?>