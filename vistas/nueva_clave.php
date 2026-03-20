<?php
session_start();
if (empty($_SESSION["user"])) { header("Location: denegado_a.php"); exit(); }
$id = $_SESSION['id'];
$usuario = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Nueva Clave - SDGBP</title>

    <link rel="icon" type="image/x-icon" href="../img/favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="../js/all.js"></script>
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

        .inst-input-wrapper { display: flex; align-items: center; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 0 1rem; margin-bottom: 1.25rem; transition: all 0.3s; }
        .inst-input-wrapper:focus-within { border-color: #f18000; background: #fff; box-shadow: 0 0 0 4px rgba(241, 128, 0, 0.1); }
        .inst-icon { color: #94a3b8; font-size: 1.1rem; padding-right: 1rem; transition: color 0.3s; }
        .inst-input-wrapper:focus-within .inst-icon { color: #f18000; }
        .inst-input { width: 100%; background: transparent; border: none; padding: 1.1rem 0; color: #1e293b; font-size: 1rem; outline: none; font-weight: 500; }
        .inst-input::placeholder { color: #94a3b8; font-weight: 400; }
        
        .inst-btn-eye { cursor: pointer; color: #94a3b8; background: none; border: none; padding: 0.5rem; outline: none; transition: color 0.3s;}
        .inst-btn-eye:hover { color: #f18000; }

        .inst-btn-submit { width: 100%; padding: 1.1rem; border: none; border-radius: 12px; background: #0f172a; color: #fff; font-size: 1rem; font-weight: 700; letter-spacing: 0.5px; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 0.5rem; margin-top: 1rem;}
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
    <div class="login-layout">
        <div class="login-image-side">
            <img src="../img/login_bg_premium.png" alt="Corporative Office" class="login-bg-img">
            <div class="login-overlay"></div>
            <div class="login-image-content">
                <div class="login-badge">Restauración Efectiva</div>
                <h1 class="login-image-title">Nueva<br><span>Contraseña</span></h1>
                <p class="login-image-desc">Establece una contraseña fuerte y segura para proteger el acceso a tu plataforma administrativa. Nunca compartas tus credenciales corporativas.</p>
                <div class="flex items-center gap-4 mt-8">
                    <div class="flex -space-x-3">
                        <div class="w-10 h-10 rounded-full bg-slate-800 border-2 border-slate-700 flex items-center justify-center"><i class="fas fa-lock text-slate-300 text-sm"></i></div>
                        <div class="w-10 h-10 rounded-full bg-slate-800 border-2 border-slate-700 flex items-center justify-center"><i class="fas fa-check-shield text-slate-300 text-sm"></i></div>
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
                    <div class="step-item completed">
                        <div class="step-circle"><i class="fas fa-check"></i></div><div class="step-label">Validar</div>
                    </div>
                    <div class="step-item active">
                        <div class="step-circle">4</div><div class="step-label">Clave</div>
                    </div>
                </div>

                <div class="text-center md:text-left mb-6">
                    <h4 class="font-bold text-lg text-slate-800">Paso 4: Nueva Contraseña</h4>
                    <p class="text-slate-500 text-sm font-medium mt-1">Establece tu nueva clave de acceso segura.</p>
                </div>

                <?php if (isset($_GET['vencida'])): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6 shadow-sm">
                        <p class="font-bold mb-1"><i class="fas fa-clock mr-2"></i> Aviso de Seguridad</p>
                        <p class="text-sm">Tu contraseña ha superado los 180 días de vigencia y debe ser actualizada para acceder.</p>
                    </div>
                <?php endif; ?>

                <form action="../acciones/solicitar_cambio_clave.php" method="POST" onsubmit="return validateFormNC()" class="mt-4">
                    <div class="recovery-icon mx-auto md:mx-0"><i class="fas fa-key"></i></div>

                    <div class="inst-input-wrapper">
                        <i class="fas fa-lock inst-icon"></i>
                        <input type="password" id="inputPassword" name="clave" class="inst-input" placeholder="Nueva Contraseña" autocomplete="off" />
                        <button type="button" class="inst-btn-eye" onclick="togglePasswordVisibility('inputPassword', 'iconPass1')">
                            <i id="iconPass1" class="fas fa-eye"></i>
                        </button>
                    </div>

                    <div class="inst-input-wrapper">
                        <i class="fas fa-lock inst-icon"></i>
                        <input type="password" id="inputPasswordConfirm" name="clave1" class="inst-input" placeholder="Confirmar Contraseña" autocomplete="off" />
                        <button type="button" class="inst-btn-eye" onclick="togglePasswordVisibility('inputPasswordConfirm', 'iconPass2')">
                            <i id="iconPass2" class="fas fa-eye"></i>
                        </button>
                    </div>

                    <button class="inst-btn-submit" type="submit">
                        Actualizar Credenciales <i class="fas fa-save"></i>
                    </button>
                </form>

                <?php include("../models/sweetalert.php"); ?>

                <div class="inst-links">
                    <a href="login.php" class="inst-link"><i class="fas fa-home"></i> Cancelar e ir al Inicio</a>
                </div>
            </div>
            <div class="inst-footer"><p>&copy; <?php echo date("Y"); ?> SDGBP. Todos los derechos reservados.</p></div>
        </div>
    </div>
    
    <script>
        function togglePasswordVisibility(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }
    </script>
    <script src="../js/vali_login.js"></script>
</body>
</html>