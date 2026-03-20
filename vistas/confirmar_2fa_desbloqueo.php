<?php
session_start();

// Verifica si la sesión de código 2FA existe para el desbloqueo, si no, es acceso no autorizado.
if (!isset($_SESSION['codigo_2fa_desbloqueo']) || !isset($_SESSION['correo_desbloqueo'])) {
    header("Location: denegado_a.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Confirmación 2FA (Desbloqueo) - SDGBP</title>

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

        .inst-input-wrapper { display: flex; align-items: center; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 0 1rem; margin-bottom: 1.25rem; transition: all 0.3s; }
        .inst-input-wrapper:focus-within { border-color: #f18000; background: #fff; box-shadow: 0 0 0 4px rgba(241, 128, 0, 0.1); }
        .inst-icon { color: #94a3b8; font-size: 1.1rem; transition: color 0.3s; display: flex; align-items: center; justify-content: center;}
        .inst-input-wrapper:focus-within .inst-icon { color: #f18000; }

        /* Estilo especial para el código de 6 dígitos */
        .code-input-wrapper { height: 80px; }
        .code-input {
            width: 100%; background: transparent; border: none; padding: 0.5rem 0;
            color: #0f172a; font-size: 2.5rem; outline: none; font-weight: 800; 
            letter-spacing: 12px; text-align: center;
        }
        .code-input::placeholder { color: #cbd5e1; font-weight: 500; font-size: 2rem; letter-spacing: 5px; }

        .inst-btn-submit { width: 100%; padding: 1.1rem; border: none; border-radius: 12px; background: #0f172a; color: #fff; font-size: 1rem; font-weight: 700; letter-spacing: 0.5px; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 0.5rem; margin-top: 1.5rem; }
        .inst-btn-submit:hover { background: #f18000; transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(241, 128, 0, 0.3); }

        .inst-links { display: flex; justify-content: center; margin-top: 1.5rem; }
        .inst-link { color: #64748b; font-size: 0.85rem; font-weight: 600; text-decoration: none; transition: color 0.3s; display: flex; align-items: center; gap: 0.4rem; }
        .inst-link:hover { color: #f18000; }

        .inst-footer { margin-top: auto; text-align: center; width: 100%; padding-top: 2rem; }
        .inst-footer p { font-size: 0.75rem; color: #94a3b8; }
        
        .recovery-icon { font-size: 2.5rem; color: #f18000; margin-bottom: 1.5rem; background: rgba(241, 128, 0, 0.1); width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; border-radius: 50%; border: 1px solid rgba(241, 128, 0, 0.2); box-shadow: 0 4px 15px rgba(241, 128, 0, 0.1); margin-left: auto; margin-right: auto; }

        .info-alert { background: rgba(14, 165, 233, 0.1); border-left: 4px solid #0ea5e9; padding: 1rem; border-radius: 0 8px 8px 0; margin-bottom: 1.5rem; }
        .info-alert-title { color: #0284c7; font-weight: 700; font-size: 0.85rem; margin-bottom: 0.25rem; display: flex; align-items: center; gap: 0.5rem; }
        .info-alert-text { color: #334155; font-size: 0.85rem; line-height: 1.5; }
        
        .masked-email { font-weight: 700; color: #0f172a; word-break: break-all; }
    </style>
</head>
<body>
    <div class="login-layout">
        <div class="login-image-side">
            <img src="../img/login_bg_premium.png" alt="Corporative Office" class="login-bg-img">
            <div class="login-overlay"></div>
            <div class="login-image-content">
                <div class="login-badge">Autorización Final</div>
                <h1 class="login-image-title">Verificación<br><span>2FA</span></h1>
                <p class="login-image-desc">Ingresa el código numérico confidencial enviado a tu bandeja de correo registrada para confirmar que eres tú y autorizar la liberación de la cuenta.</p>
            </div>
        </div>

        <div class="login-form-side">
            <div class="login-form-container">
                <div class="text-center md:text-left mb-6">
                    <img src="../img/Logo-OP2_V4.webp" alt="Logo" class="inst-logo mx-auto md:mx-0">
                    <h2 class="inst-title">Desbloqueo Seguro</h2>
                </div>

                <div class="info-alert">
                    <div class="info-alert-title"><i class="fas fa-shield-alt"></i> Doble Factor Requerido</div>
                    <div class="info-alert-text">
                        Hemos enviado un código numérico de 6 dígitos al correo asociado a su cuenta.
                        <?php
                            $email = $_SESSION['correo_desbloqueo'];
                            $partes = explode("@", $email);
                            $nombre = $partes[0];
                            $dominio = $partes[1];
                            $nombre_oculto = substr($nombre, 0, 3) . str_repeat("*", max(0, strlen($nombre) - 3));
                            $email_enmascarado = $nombre_oculto . "@" . $dominio;
                        ?>
                        <div class="mt-2 masked-email"><i class="fas fa-envelope mr-1"></i> <?php echo htmlspecialchars($email_enmascarado); ?></div>
                    </div>
                </div>

                <form action="../acciones/validar_2fa_desbloqueo.php" method="POST" onsubmit="return validateFormCodigo()" class="mt-4">
                    <div class="recovery-icon mx-auto md:mx-0"><i class="fas fa-lock"></i></div>

                    <div class="inst-input-wrapper code-input-wrapper">
                        <input id="codigo" type="text" name="codigo" class="code-input" placeholder="000000" maxlength="6" autocomplete="off" onkeypress="return soloNumeros(event)" />
                    </div>

                    <button class="inst-btn-submit" type="submit">
                        Verificar Código 2FA <i class="fas fa-arrow-right"></i>
                    </button>
                </form>

                <?php include("../models/sweetalert.php"); ?>

                <div class="inst-links flex-col items-center mt-6 space-y-3">
                    <a href="../acciones/enviar_desbloqueo.php?reenviar=1" class="inst-link text-primary"><i class="fas fa-redo-alt"></i> Obtener un nuevo código</a>
                    <a href="solicitar_desbloqueo.php" class="inst-link"><i class="fas fa-times"></i> Cancelar Desbloqueo</a>
                </div>
            </div>
            <div class="inst-footer"><p>&copy; <?php echo date("Y"); ?> SDGBP. Todos los derechos reservados.</p></div>
        </div>
    </div>
    
    <script src="../js/vali_login.js"></script>
    <script>
        function soloNumeros(e) {
            var key = window.Event ? e.which : e.keyCode
            return (key >= 48 && key <= 57)
        }
    </script>
</body>
</html>
