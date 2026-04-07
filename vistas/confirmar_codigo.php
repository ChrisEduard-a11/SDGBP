<?php
session_start();
if (empty($_SESSION["user"]) && empty($_SESSION["usuario"])) { header("Location: denegado_a.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Código de Seguridad - SDGBP</title>

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
        .login-image-title span { background: linear-gradient(135deg, #f18000 0%, #ffc107 100%); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
        .login-image-desc { font-size: 1.1rem; color: rgba(255,255,255,0.8); line-height: 1.6; font-weight: 300; }

        .login-form-side { display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 2rem; width: 100%; background-color: #ffffff; overflow-y: auto; overflow-x: hidden; }
        @media (min-width: 1024px) { .login-form-side { width: 500px; padding: 3rem 4rem; flex-shrink: 0; } }

        .login-form-container { width: 100%; max-width: 420px; }
        .inst-logo { width: 65px; margin-bottom: 1.2rem; }
        .inst-title { font-size: 1.8rem; font-weight: 800; color: #0f172a; margin-bottom: 0.5rem; letter-spacing: -0.5px; }

        .inst-input-wrapper { display: flex; flex-direction: column; background: #fff; border: 2px solid #e2e8f0; border-radius: 12px; padding: 1rem; margin-bottom: 1.5rem; transition: all 0.3s; position: relative; text-align: center;}
        .inst-input-wrapper:focus-within { border-color: #f18000; box-shadow: 0 0 0 4px rgba(241, 128, 0, 0.1); }
        .inst-input-wrapper label { font-size: 0.8rem; font-weight: 800; color: #64748b; margin-bottom: 0.8rem; text-transform: uppercase; letter-spacing: 1px;}
        
        .inst-input { width: 100%; background: transparent; border: none; padding: 0.5rem 0; color: #0f172a; font-size: 2.5rem; outline: none; font-weight: 800; letter-spacing: 15px; text-align: center;}
        .inst-input::placeholder { color: #cbd5e1; font-weight: 400; letter-spacing: 15px;}

        .alert-info-premium { background-color: #eff6ff; border-left: 4px solid #3b82f6; border-radius: 12px; color: #1e40af; font-size: 0.85rem; padding: 15px 20px; display: flex; align-items: center; gap: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); margin-bottom: 1.5rem; }
        .alert-info-premium i { font-size: 1.5rem; color: #3b82f6; }

        .inst-btn-submit { width: 100%; padding: 1.1rem; border: none; border-radius: 12px; background: #0f172a; color: #fff; font-size: 1rem; font-weight: 700; letter-spacing: 0.5px; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 0.5rem; }
        .inst-btn-submit:hover { background: #f18000; transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(241, 128, 0, 0.3); }

        .inst-links { display: flex; justify-content: center; margin-top: 1.5rem; }
        .inst-link { color: #64748b; font-size: 0.85rem; font-weight: 600; text-decoration: none; transition: color 0.3s; display: flex; align-items: center; gap: 0.4rem; }
        .inst-link:hover { color: #f18000; }

        .inst-footer { margin-top: auto; text-align: center; width: 100%; padding-top: 2rem; }
        .inst-footer p { font-size: 0.75rem; color: #94a3b8; }
        
        .recovery-icon { font-size: 2.5rem; color: #f18000; margin-bottom: 1.5rem; background: rgba(241, 128, 0, 0.1); width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; border-radius: 50%; border: 1px solid rgba(241, 128, 0, 0.2); box-shadow: 0 4px 15px rgba(241, 128, 0, 0.1); margin-left: auto; margin-right: auto; }
    </style>
</head>
<body>
<!-- GLOBAL PRELOADER -->
<?php include("../models/preloader.php"); ?>
<!-- END GLOBAL PRELOADER -->
    <div class="login-layout">
        <div class="login-image-side">
            <img src="../img/login_bg_premium.png" alt="Corporative Office" class="login-bg-img">
            <div class="login-overlay"></div>
            <div class="login-image-content">
                <div class="login-badge">Protección de Cuenta</div>
                <h1 class="login-image-title">Verificación de<br><span>Seguridad</span></h1>
                <p class="login-image-desc">Ingresa el código único que acabamos de enviar a tu dirección de correo electrónico para confirmar tu identidad corporativa.</p>
                <div class="flex items-center gap-4 mt-8">
                    <div class="flex -space-x-3">
                        <div class="w-10 h-10 rounded-full bg-slate-800 border-2 border-slate-700 flex items-center justify-center"><i class="fas fa-key text-slate-300 text-sm"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="login-form-side">
            <div class="login-form-container">
                <div class="text-center md:text-left mb-6">
                    <img src="../img/Logo-OP2_V4.webp" alt="Logo" class="inst-logo mx-auto md:mx-0">
                    <h2 class="inst-title">Validación 2FA</h2>
                </div>

                <div class="alert-info-premium">
                    <i class="fas fa-paper-plane"></i>
                    <span>Hemos enviado un <b>código de 6 dígitos</b> a tu correo electrónico. Por favor revisa tu bandeja principal o de SPAM.</span>
                </div>

                <form action="../acciones/verificar_y_actualizar.php" method="POST" onsubmit="return validateFormCodigo()" class="mt-4">
                    <div class="recovery-icon mx-auto md:mx-0"><i class="fas fa-hashtag"></i></div>

                    <div class="inst-input-wrapper">
                        <label>Código de Seis Dígitos</label>
                        <input type="text" name="codigo" class="inst-input" maxlength="6" placeholder="000000" autocomplete="off" oninput="this.value = this.value.replace(/[^0-9]/g, '');" />
                    </div>

                    <button class="inst-btn-submit" type="submit">
                        Verificar Autenticidad <i class="fas fa-check-circle"></i>
                    </button>
                </form>

                <?php include("../models/sweetalert.php"); ?>

                <div class="inst-links">
                    <a href="nueva_clave.php" class="inst-link"><i class="fas fa-arrow-left"></i> Volver a intentar</a>
                </div>
            </div>
            <div class="inst-footer"><p>&copy; <?php echo date("Y"); ?> SDGBP. Todos los derechos reservados.</p></div>
        </div>
    </div>
    <script src="../js/vali_login.js"></script>
</body>
</html>

