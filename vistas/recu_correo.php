<?php
session_start();
if (!isset($_SESSION['correo'])) {
    $_SESSION['estatus'] = 'error';
    $_SESSION['mensaje'] = "No tienes permisos o Ningun Correo Registrado, Consulte al Soporte de Usuarios.";
    header("Location: login.php");
    exit();
}
function enmascararCorreo($correo) {
    if (!$correo) return '';
    $partes = explode("@", $correo);
    if (count($partes) < 2) return $correo;
    $parteLocal = strlen($partes[0]) > 6 ? substr($partes[0], 0, 3) . str_repeat('*', strlen($partes[0]) - 6) . substr($partes[0], -3) : substr($partes[0], 0, 1) . '***';
    return $parteLocal . "@" . $partes[1];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Recuperación Email - SDGBP</title>

    <link rel="icon" type="image/x-icon" href="../img/favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Toastr & SweetAlert2 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <link rel="stylesheet" href="../sweetalert/sweetalert2.min.css">
    <script src="../sweetalert/sweetalert2.js"></script>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { primary: '#f18000', 'primary-dark': '#d67100', 'brand-blue': '#0f172a' },
                    fontFamily: { sans: ['Outfit', 'sans-serif'], }
                }
            }
        }
    </script>

    <style>
        body, html { margin: 0; padding: 0; width: 100%; height: 100%; font-family: 'Outfit', sans-serif; background-color: #f8fafc; }
        .login-layout { display: flex; min-height: 100vh; width: 100%; }

        .login-image-side { display: none; position: relative; flex: 1; background-color: var(--brand-blue); overflow: hidden; position: sticky; top: 0; height: 100vh; }
        @media (min-width: 1024px) { .login-image-side { display: flex; flex-direction: column; justify-content: center; align-items: center; } }

        .login-bg-img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; z-index: 0; }
        .login-overlay { position: absolute; inset: 0; background: linear-gradient(135deg, rgba(15, 23, 42, 0.85) 0%, rgba(15, 23, 42, 0.6) 100%); z-index: 1; }
        .login-image-content { position: relative; z-index: 2; padding: 4rem; color: #fff; max-width: 650px; }
        .login-badge { display: inline-block; padding: 0.4rem 1rem; background: rgba(241, 128, 0, 0.2); border: 1px solid rgba(241, 128, 0, 0.3); color: #f18000; border-radius: 50px; font-size: 0.75rem; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; backdrop-filter: blur(4px); margin-bottom: 2rem; }
        .login-image-title { font-size: 3.5rem; font-weight: 800; line-height: 1.1; margin-bottom: 1.5rem; letter-spacing: -1px; }
        .login-image-title span { background: linear-gradient(135deg, #f18000 0%, #ffc107 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .login-image-desc { font-size: 1.1rem; color: rgba(255,255,255,0.8); line-height: 1.6; font-weight: 300; }

        .login-form-side { display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 2rem; width: 100%; background-color: #ffffff; overflow-y: auto; overflow-x: hidden; }
        @media (min-width: 1024px) { .login-form-side { width: 500px; padding: 3rem 4rem; flex-shrink: 0; } }

        .login-form-container { width: 100%; max-width: 420px; }
        .inst-logo { width: 65px; margin-bottom: 1.2rem; }
        .inst-title { font-size: 1.8rem; font-weight: 800; color: #0f172a; margin-bottom: 0.5rem; letter-spacing: -0.5px; }

        .inst-input-wrapper { display: flex; flex-direction: column; background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 12px; padding: 1rem; margin-bottom: 1.25rem; transition: all 0.3s; position: relative;}
        .inst-input-wrapper label { font-size: 0.75rem; font-weight: 700; color: #64748b; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.5px; display: block;}
        
        .inst-input-inner { display: flex; align-items: center; width: 100%; }
        .inst-icon { color: #64748b; font-size: 1.1rem; padding-right: 1rem; }
        
        .inst-input { width: 100%; background: transparent; border: none; padding: 0.5rem 0; color: #475569; font-size: 1rem; outline: none; font-weight: 600; cursor: not-allowed;}

        .alert-info-premium { background-color: #eff6ff; border-left: 4px solid #3b82f6; border-radius: 12px; color: #1e40af; font-size: 0.85rem; padding: 15px 20px; display: flex; align-items: center; gap: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); margin-bottom: 1.5rem; }
        .alert-info-premium i { font-size: 1.2rem; color: #3b82f6; }

        .inst-btn-submit { width: 100%; padding: 1.1rem; border: none; border-radius: 12px; background: #0f172a; color: #fff; font-size: 1rem; font-weight: 700; letter-spacing: 0.5px; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 0.5rem; }
        .inst-btn-submit:hover { background: #f18000; transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(241, 128, 0, 0.3); }

        .inst-links { display: flex; justify-content: center; margin-top: 1.5rem; }
        .inst-link { color: #64748b; font-size: 0.85rem; font-weight: 600; text-decoration: none; transition: color 0.3s; display: flex; align-items: center; gap: 0.4rem; }
        .inst-link:hover { color: #f18000; }

        .inst-footer { margin-top: auto; text-align: center; width: 100%; padding-top: 2rem; }
        .inst-footer p { font-size: 0.75rem; color: #94a3b8; }

        .recovery-icon { font-size: 2.5rem; color: #f18000; margin-bottom: 1.5rem; background: rgba(241, 128, 0, 0.1); width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; border-radius: 50%; border: 1px solid rgba(241, 128, 0, 0.2); box-shadow: 0 4px 15px rgba(241, 128, 0, 0.1); margin-left: auto; margin-right: auto; }

        /* Stepper */
        .stepper { display: flex; justify-content: space-between; margin-bottom: 30px; position: relative; }
        .stepper::before { content: ''; position: absolute; top: 15px; left: 15%; right: 15%; height: 2px; background: #e2e8f0; z-index: 1; }
        .step-item { position: relative; z-index: 2; text-align: center; flex: 1; }
        .step-circle { width: 32px; height: 32px; background: #fff; border: 2px solid #e2e8f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px; font-weight: bold; color: #64748b; transition: all 0.3s ease; }
        .step-item.active .step-circle { background: #f18000; border-color: #f18000; color: #fff; box-shadow: 0 0 0 4px rgba(241, 128, 0, 0.2); }
        .step-item.completed .step-circle { background: #10b981; border-color: #10b981; color: #fff; }
        .step-label { font-size: 10px; font-weight: bold; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
        .step-item.active .step-label { color: #f18000; }
        .step-item.completed .step-label { color: #10b981; }
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
        <div class="login-image-side">
            <img src="../img/login_bg_premium.png" alt="Corporative Office" class="login-bg-img">
            <div class="login-overlay"></div>
            <div class="login-image-content">
                <div class="login-badge">Canal Seguro</div>
                <h1 class="login-image-title">Enlace de<br><span>Restauración</span></h1>
                <p class="login-image-desc">Generaremos un enlace encriptado de uso único y te lo enviaremos por correo electrónico para garantizar que solo tú puedas restablecer el acceso.</p>
                <div class="flex items-center gap-4 mt-8">
                    <div class="flex -space-x-3">
                        <div class="w-10 h-10 rounded-full bg-slate-800 border-2 border-slate-700 flex items-center justify-center"><i class="fas fa-paper-plane text-slate-300 text-sm"></i></div>
                        <div class="w-10 h-10 rounded-full bg-slate-800 border-2 border-slate-700 flex items-center justify-center"><i class="fas fa-shield-alt text-slate-300 text-sm"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="login-form-side">
            <div class="login-form-container">
                <div class="text-center md:text-left mb-6">
                    <img src="../img/Logo-OP2_V4.webp" alt="Logo" class="inst-logo mx-auto md:mx-0">
                    <h2 class="inst-title">Recuperación de Cuenta</h2>
                </div>

                <div class="stepper">
                    <div class="step-item completed">
                        <div class="step-circle"><i class="fas fa-check"></i></div><div class="step-label">ID</div>
                    </div>
                    <div class="step-item completed">
                        <div class="step-circle"><i class="fas fa-check"></i></div><div class="step-label">Método</div>
                    </div>
                    <div class="step-item active">
                        <div class="step-circle">3</div><div class="step-label">Validar</div>
                    </div>
                    <div class="step-item">
                        <div class="step-circle">4</div><div class="step-label">Clave</div>
                    </div>
                </div>

                <div class="text-center md:text-left mb-6">
                    <h4 class="font-bold text-lg text-slate-800">Paso 3: Confirmación Email</h4>
                    <p class="text-slate-500 text-sm font-medium mt-1">Presiona enviar para recibir el enlace seguro.</p>
                </div>

                <form action="../acciones/enviar_recuperacion.php" method="POST" class="mt-4">
                    <div class="recovery-icon mx-auto md:mx-0"><i class="fas fa-envelope-open-text"></i></div>

                    <div class="inst-input-wrapper">
                        <label>Correo Electrónico de Destino</label>
                        <div class="inst-input-inner">
                            <i class="fas fa-at inst-icon"></i>
                            <input type="hidden" name="correo" value="<?php echo $_SESSION['correo']; ?>" />
                            <input type="email" class="inst-input" value="<?php echo enmascararCorreo($_SESSION['correo']); ?>" disabled />
                        </div>
                    </div>

                    <div class="alert-info-premium">
                        <i class="fas fa-info-circle"></i>
                        <span>Te enviaremos un correo electrónico con instrucciones a la dirección registrada.</span>
                    </div>

                    <button class="inst-btn-submit" type="submit">
                        Enviar Enlace <i class="fas fa-paper-plane"></i>
                    </button>
                </form>

                <?php include("../models/sweetalert.php"); ?>

                <div class="inst-links">
                    <a href="seleccionar_meto_recu.php" class="inst-link"><i class="fas fa-arrow-left"></i> Cambiar Método</a>
                </div>
            </div>
            <div class="inst-footer"><p>&copy; <?php echo date("Y"); ?> SDGBP. Todos los derechos reservados.</p></div>
        </div>
    </div>
    <script src="../js/vali_login.js"></script>
</body>
</html>

