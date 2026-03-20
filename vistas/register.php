<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Registro Institucional - SDGBP</title>

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

    <!-- hcaptcha -->
    <?php 
    $host = $_SERVER['HTTP_HOST'];
    if (strpos($host, 'localhost') === false && strpos($host, '127.0.0.1') === false): 
    ?>
    <script src="https://js.hcaptcha.com/1/api.js" async defer></script>
    <?php endif; ?>

    <style>
        body, html {
            margin: 0; padding: 0; width: 100%; height: 100%;
            font-family: 'Outfit', sans-serif; background-color: #f8fafc;
        }

        .login-layout {
            display: flex; min-height: 100vh; width: 100%;
        }

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
        .login-image-title span { background: linear-gradient(135deg, #f18000 0%, #ffc107 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .login-image-desc { font-size: 1.1rem; color: rgba(255,255,255,0.8); line-height: 1.6; font-weight: 300; }

        .login-form-side {
            display: flex; flex-direction: column; justify-content: flex-start; align-items: center;
            padding: 2rem; width: 100%; background-color: #ffffff;
            overflow-y: auto; overflow-x: hidden;
        }

        @media (min-width: 1024px) {
            .login-form-side { width: 600px; padding: 3rem 4rem; justify-content: center; }
        }

        .login-form-container { width: 100%; max-width: 500px; padding-top: 1rem; padding-bottom: 2rem;}

        .inst-logo { width: 65px; margin-bottom: 1.2rem; }
        .inst-title { font-size: 1.8rem; font-weight: 800; color: #0f172a; margin-bottom: 0.5rem; letter-spacing: -0.5px; }
        .inst-subtitle { font-size: 0.9rem; color: #64748b; font-weight: 400; margin-bottom: 2rem; }

        .inst-input-wrapper {
            display: flex; align-items: center;
            background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px;
            padding: 0 1rem; margin-bottom: 1rem; transition: all 0.3s;
        }
        .inst-input-wrapper:focus-within {
            border-color: #f18000; background: #fff; box-shadow: 0 0 0 3px rgba(241, 128, 0, 0.1);
        }

        .inst-icon { color: #94a3b8; font-size: 1rem; padding-right: 0.8rem; transition: color 0.3s; }
        .inst-input-wrapper:focus-within .inst-icon { color: #f18000; }
        
        .inst-input {
            width: 100%; background: transparent; border: none; padding: 0.9rem 0;
            color: #1e293b; font-size: 0.95rem; outline: none; font-weight: 500;
        }
        .inst-input::placeholder { color: #94a3b8; font-weight: 400; }
        
        .inst-select {
            width: 100%; background: transparent; border: none; padding: 0.9rem 0;
            color: #1e293b; font-size: 0.95rem; outline: none; font-weight: 500; cursor: pointer;
            appearance: none;
        }
        .inst-select option { color: #1e293b; background: #fff; }

        .inst-label { font-size: 0.75rem; font-weight: 700; color: #64748b; margin-bottom: 0.3rem; margin-left: 0.2rem; text-transform: uppercase; letter-spacing: 0.5px; display: block;}

        .inst-btn-submit {
            width: 100%; padding: 1.1rem; border: none; border-radius: 12px; margin-top: 1rem;
            background: #0f172a; color: #fff; font-size: 1rem; font-weight: 700; letter-spacing: 0.5px;
            cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 0.5rem;
        }
        .inst-btn-submit:hover { background: #f18000; transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(241, 128, 0, 0.3); }

        .inst-links { display: flex; justify-content: center; margin-top: 1.5rem; }
        .inst-link { color: #64748b; font-size: 0.85rem; font-weight: 600; text-decoration: none; transition: color 0.3s; display: flex; align-items: center; gap: 0.4rem; }
        .inst-link:hover { color: #f18000; }

        .inst-footer { margin-top: auto; text-align: center; width: 100%; padding-top: 2rem; }
        .inst-footer p { font-size: 0.75rem; color: #94a3b8; }
        .inst-footer a { color: #64748b; font-weight: 500; text-decoration: none; }
        .inst-footer a:hover { color: #f18000; }

        /* Profile Pic Upload */
        .profile-pic-container {
            position: relative; width: 90px; height: 90px; margin: 0 auto 1.5rem;
            border-radius: 50%; border: 3px solid #fff; box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            background: #f8fafc;
        }
        #profilePicPreview { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
        #inputFoto { display: none; }
        .upload-icon {
            position: absolute; bottom: 0; right: 0; background: #f18000; color: white;
            width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
            cursor: pointer; border: 2px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: all 0.3s;
        }
        .upload-icon:hover { transform: scale(1.1); background: #0f172a; }

        /* Two columns grid for inputs */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0 1rem; }
        @media (max-width: 640px) { .form-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

    <div class="login-layout">

        <!-- Left: Corporate Image Side -->
        <div class="login-image-side">
            <img src="../img/login_bg_premium.png" alt="Corporative Office" class="login-bg-img">
            <div class="login-overlay"></div>
            <div class="login-image-content">
                <div class="login-badge">Acceso Exclusivo UPU</div>
                <h1 class="login-image-title">Registro <span>UPU EURIPYS</span></h1>
                <p class="login-image-desc">
                    Este registro está destinado única y exclusivamente a las Unidades de Producción Universitaria (UPU) de EURIPYS.
                    <br><br>
                    <span style="color: #f18000; font-weight: 600;">Importante:</span> Toda solicitud de registro pasará por un proceso de auditoría y <strong>aprobación por parte del administrador</strong> antes de que se conceda el acceso al sistema.
                </p>
                <div class="flex items-center gap-4 mt-8">
                    <div class="flex -space-x-3">
                        <div class="w-10 h-10 rounded-full bg-slate-800 border-2 border-slate-700 flex items-center justify-center"><i class="fas fa-users text-slate-300 text-sm"></i></div>
                        <div class="w-10 h-10 rounded-full bg-slate-800 border-2 border-slate-700 flex items-center justify-center"><i class="fas fa-chart-line text-slate-300 text-sm"></i></div>
                        <div class="w-10 h-10 rounded-full bg-slate-800 border-2 border-slate-700 flex items-center justify-center"><i class="fas fa-shield-alt text-slate-300 text-sm"></i></div>
                    </div>
                    <span class="text-sm font-medium text-slate-300">Entorno Confiable</span>
                </div>
            </div>
        </div>

        <!-- Right: Form Side -->
        <div class="login-form-side">
            <div class="login-form-container">
                
                <div class="text-center md:text-left">
                    <img src="../img/Logo-OP2_V4.webp" alt="Logo" class="inst-logo mx-auto md:mx-0">
                    <h2 class="inst-title">Crear Cuenta</h2>
                    <p class="inst-subtitle">Rellena los campos para solicitar acceso al sistema.</p>
                </div>

                <form name="registerForm" id="registroForm" method="POST" action="../acciones/guardar_u_login.php" onsubmit="return validateFormRL()" autocomplete="off" enctype="multipart/form-data">
                    
                    <!-- Foto -->
                    <div class="text-center">
                        <div class="profile-pic-container">
                            <img id="profilePicPreview" src="../img/default_profile.png" alt="Foto de Perfil" />
                            <label for="inputFoto" class="upload-icon">
                                <i class="fas fa-camera text-xs"></i>
                            </label>
                            <input id="inputFoto" type="file" name="foto" accept="image/*" onchange="previewProfilePic(event)" />
                        </div>
                    </div>

                    <div class="form-grid">
                        <!-- Usuario -->
                        <div>
                            <label class="inst-label" for="inputFirstName">Usuario (*)</label>
                            <div class="inst-input-wrapper">
                                <i class="fas fa-user-tag inst-icon"></i>
                                <input id="inputFirstName" type="text" name="usuario" maxlength="15" class="inst-input" placeholder="Ej. juan123" />
                            </div>
                        </div>
                        <!-- Nombre -->
                        <div>
                            <label class="inst-label" for="inputLastName">Nombre Completo (*)</label>
                            <div class="inst-input-wrapper">
                                <i class="fas fa-signature inst-icon"></i>
                                <input id="inputLastName" type="text" name="nombre" maxlength="255" class="inst-input" placeholder="Nombres y Apellidos" />
                            </div>
                        </div>
                    </div>

                    <div class="form-grid">
                        <!-- Nacionalidad -->
                        <div>
                            <label class="inst-label" for="inputNACI">Nac. (*)</label>
                            <div class="inst-input-wrapper pr-2">
                                <i class="fas fa-flag inst-icon"></i>
                                <select name="nacionalidad" id="inputNACI" class="inst-select">
                                    <option value="" selected disabled>Seleccionar</option>    
                                    <option value="V-">V - Venezolano</option>
                                    <option value="E-">E - Extranjero</option>
                                    <option value="G-">G - Gubernamental</option>
                                    <option value="J-">J - Jurídico</option>
                                </select>
                            </div>
                        </div>
                        <!-- Cédula -->
                        <div>
                            <label class="inst-label" for="inputDNI">Cédula (*)</label>
                            <div class="inst-input-wrapper">
                                <i class="fas fa-id-card inst-icon"></i>
                                <input id="inputDNI" type="number" name="cedula" maxlength="20" class="inst-input" placeholder="Ej. 12345678" />
                            </div>
                        </div>
                    </div>

                    <!-- Correo -->
                    <div>
                        <label class="inst-label" for="inputEmail">Correo Electrónico (*)</label>
                        <div class="inst-input-wrapper">
                            <i class="fas fa-envelope inst-icon"></i>
                            <input id="inputEmail" type="text" name="correo" class="inst-input" placeholder="correo@ejemplo.com" />
                        </div>
                    </div>

                    <div class="form-grid">
                        <!-- Clave -->
                        <div>
                            <label class="inst-label" for="inputPassword">Contraseña (*)</label>
                            <div class="inst-input-wrapper flex">
                                <i class="fas fa-lock inst-icon"></i>
                                <input id="inputPassword" type="password" name="clave" onkeyup="checkPasswordStrength()" class="inst-input" placeholder="Contraseña" />
                                <button class="inst-btn-eye" type="button" onclick="togglePasswordVisibility('inputPassword', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small id="passwordStrength" class="text-xs text-slate-500 mt-1 ml-1 block mb-3"></small>
                        </div>
                        <!-- Confirmar Clave -->
                        <div>
                            <label class="inst-label" for="inputPasswordConfirm">Confirmar (*)</label>
                            <div class="inst-input-wrapper flex">
                                <i class="fas fa-check-double inst-icon"></i>
                                <input id="inputPasswordConfirm" type="password" name="confirmar_clave" class="inst-input" placeholder="Repite contraseña" />
                                <button class="inst-btn-eye" type="button" onclick="togglePasswordVisibility('inputPasswordConfirm', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="form-grid">
                        <!-- Pregunta 1 -->
                        <div>
                            <label class="inst-label" for="inputPregunta1">Pregunta 1 (*)</label>
                            <div class="inst-input-wrapper pr-2">
                                <i class="fas fa-question-circle inst-icon"></i>
                                <select name="pregunta" id="inputPregunta1" class="inst-select">
                                    <option value="" disabled selected>Seleccione</option>
                                    <option value="¿Comida favorita?">¿Comida favorita?</option>
                                    <option value="¿Color Preferido?">¿Color Preferido?</option>
                                    <option value="¿Nombre de mi mascota?">¿Nombre de mi mascota?</option>
                                    <option value="¿Deporte Favorito?">¿Deporte Favorito?</option>
                                    <option value="¿Lugar de nacimiento?">¿Lugar de nacimiento?</option>
                                    <option value="¿Nombre de mi mejor amigo de la infancia?">¿Nombre amigo infancia?</option>
                                    <option value="¿Película favorita?">¿Película favorita?</option>
                                    <option value="¿Nombre de mi primer maestro?">¿Primer maestro?</option>
                                    <option value="¿Marca de mi primer automóvil?">¿Primer auto?</option>
                                    <option value="¿Nombre de mi primer jefe?">¿Primer jefe?</option>
                                </select>
                            </div>
                        </div>
                        <!-- Respuesta 1 -->
                        <div>
                            <label class="inst-label" for="inputRespuesta">Respuesta 1 (*)</label>
                            <div class="inst-input-wrapper">
                                <i class="fas fa-key inst-icon"></i>
                                <input id="inputRespuesta" type="text" name="respuesta" maxlength="255" class="inst-input" placeholder="Respuesta secreta" />
                            </div>
                        </div>
                    </div>

                    <div class="form-grid">
                        <!-- Pregunta 2 -->
                        <div>
                            <label class="inst-label" for="inputPregunta2">Pregunta 2 (*)</label>
                            <div class="inst-input-wrapper pr-2">
                                <i class="fas fa-question-circle inst-icon"></i>
                                <select name="pregunta2" id="inputPregunta2" class="inst-select">
                                    <option value="" disabled selected>Seleccione</option>
                                    <option value="¿Comida favorita?">¿Comida favorita?</option>
                                    <option value="¿Color Preferido?">¿Color Preferido?</option>
                                    <option value="¿Nombre de mi mascota?">¿Nombre de mi mascota?</option>
                                    <option value="¿Deporte Favorito?">¿Deporte Favorito?</option>
                                    <option value="¿Lugar de nacimiento?">¿Lugar de nacimiento?</option>
                                    <option value="¿Nombre de mi mejor amigo de la infancia?">¿Nombre amigo infancia?</option>
                                    <option value="¿Película favorita?">¿Película favorita?</option>
                                    <option value="¿Nombre de mi primer maestro?">¿Primer maestro?</option>
                                    <option value="¿Marca de mi primer automóvil?">¿Primer auto?</option>
                                    <option value="¿Nombre de mi primer jefe?">¿Primer jefe?</option>
                                </select>
                            </div>
                        </div>
                        <!-- Respuesta 2 -->
                        <div>
                            <label class="inst-label" for="inputRespuesta2">Respuesta 2 (*)</label>
                            <div class="inst-input-wrapper">
                                <i class="fas fa-key inst-icon"></i>
                                <input id="inputRespuesta2" type="text" name="respuesta2" maxlength="255" class="inst-input" placeholder="Respuesta secreta" />
                            </div>
                        </div>
                    </div>

                    <button class="inst-btn-submit" type="submit">
                        <i class="fas fa-user-check"></i> Registrar Cuenta
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
                <p class="mt-1">Licencia <a href="https://creativecommons.org/licenses/by-nc/4.0/?ref=chooser-v1" target="_blank">Creative Commons BY-NC 4.0</a></p>
            </div>
        </div>

    </div>

    <!-- Scripts -->
    <script src="../js/vali_login.js"></script>
    
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