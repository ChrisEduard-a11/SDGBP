<?php
session_start();
require_once("../conexion.php");

if (empty($_SESSION["usuario"])) {
    header("Location: denegado_a.php");
    exit();
}

$usuario_id = $_SESSION['id_usuario'];

$sql = "SELECT pregunta, pregunta2 FROM usuario WHERE id_usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$pregunta1 = $row['pregunta'];
$pregunta2 = $row['pregunta2'];
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Actualizar Preguntas - SDGBP</title>

    <link rel="icon" type="image/x-icon" href="../img/favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="../js/all.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Toastr & SweetAlert2 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <link rel="stylesheet" href="../sweetalert/sweetalert2.min.css">
    <script src="../sweetalert/sweetalert2.js"></script>

    <!-- Select2 - Global Standard -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

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

        .inst-input-wrapper { display: flex; align-items: center; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 0 1rem; margin-bottom: 1rem; transition: all 0.3s; }
        .inst-input-wrapper:focus-within { border-color: #f18000; background: #fff; box-shadow: 0 0 0 4px rgba(241, 128, 0, 0.1); }
        .inst-icon { color: #94a3b8; font-size: 1.1rem; padding-right: 1rem; transition: color 0.3s; }
        .inst-input-wrapper:focus-within .inst-icon { color: #f18000; }
        .inst-input, .inst-select { width: 100%; background: transparent; border: none; padding: 1.1rem 0; color: #1e293b; font-size: 0.95rem; outline: none; font-weight: 500; }
        .inst-input::placeholder { color: #94a3b8; font-weight: 400; }
        
        .inst-select { appearance: none; cursor: pointer; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23f18000' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e"); background-repeat: no-repeat; background-position: right 0.5rem center; background-size: 14px; }
        .inst-label { font-size: 0.75rem; font-weight: 700; color: #64748b; margin-bottom: 0.3rem; margin-left: 0.2rem; text-transform: uppercase; letter-spacing: 0.5px; display: block;}

        .inst-btn-submit { width: 100%; padding: 1.1rem; border: none; border-radius: 12px; background: #0f172a; color: #fff; font-size: 1rem; font-weight: 700; letter-spacing: 0.5px; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 0.5rem; margin-top: 1rem;}
        .inst-btn-submit:hover { background: #f18000; transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(241, 128, 0, 0.3); }

        .inst-links { display: flex; justify-content: center; margin-top: 1.5rem; }
        .inst-link { color: #64748b; font-size: 0.85rem; font-weight: 600; text-decoration: none; transition: color 0.3s; display: flex; align-items: center; gap: 0.4rem; }
        .inst-link:hover { color: #f18000; }

        .inst-footer { margin-top: auto; text-align: center; width: 100%; padding-top: 2rem; }
        .inst-footer p { font-size: 0.75rem; color: #94a3b8; }
        
        .recovery-icon { font-size: 2.5rem; color: #f18000; margin-bottom: 1.5rem; background: rgba(241, 128, 0, 0.1); width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; border-radius: 50%; border: 1px solid rgba(241, 128, 0, 0.2); box-shadow: 0 4px 15px rgba(241, 128, 0, 0.1); margin-left: auto; margin-right: auto; }

        .question-group { margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px dashed #e2e8f0; }
        .question-group:last-of-type { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }

        /* Select2 Premium Theme Overrides for Tailwind Design */
        .select2-container--default .select2-selection--single {
            background-color: #f8fafc !important;
            border: 1.5px solid #e2e8f0 !important;
            border-radius: 12px !important;
            height: 48px !important;
            display: flex !important;
            align-items: center !important;
            transition: all 0.3s !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #1e293b !important;
            font-size: 0.95rem !important;
            font-weight: 500 !important;
            padding-left: 0 !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 46px !important;
        }
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #f18000 !important;
            background-color: #ffffff !important;
            box-shadow: 0 0 0 4px rgba(241, 128, 0, 0.1) !important;
        }
        .select2-dropdown {
            border-radius: 12px !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
            overflow: hidden !important;
        }
        .select2-results__option--highlighted[aria-selected] {
            background-color: #f18000 !important;
        }
    </style>
</head>
<body>
    <div class="login-layout">
        <div class="login-image-side">
            <img src="../img/login_bg_premium.png" alt="Corporative Office" class="login-bg-img">
            <div class="login-overlay"></div>
            <div class="login-image-content">
                <div class="login-badge">Configuración de Seguridad</div>
                <h1 class="login-image-title">Actualiza tus<br><span>Preguntas</span></h1>
                <p class="login-image-desc">Modifica tus preguntas de seguridad para mantener tu cuenta blindada y garantizar tu acceso exclusivo al sistema en todo momento.</p>
            </div>
        </div>

        <div class="login-form-side">
            <div class="login-form-container">
                <div class="text-center md:text-left mb-6">
                    <img src="../img/Logo-OP2_V4.webp" alt="Logo" class="inst-logo mx-auto md:mx-0">
                    <h2 class="inst-title">Seguridad</h2>
                </div>

                <div class="text-center md:text-left mb-6">
                    <h4 class="font-bold text-lg text-slate-800">Preguntas de Seguridad</h4>
                    <p class="text-slate-500 text-sm font-medium mt-1">Configura nuevas preguntas para proteger tu cuenta.</p>
                </div>

                <!-- Oculto por defecto hasta validar la contraseña (resolviendo bug de ID) -->
                <div id="form-container" style="display: none;">
                    <form name="preguntaForm" action="../acciones/actualizar_preguntas.php" method="POST" onsubmit="return validateFormCPS()" class="mt-4">
                        
                        <div class="question-group">
                            <label class="inst-label">Pregunta de Seguridad 1</label>
                            <div class="inst-input-wrapper">
                                <i class="fas fa-question-circle inst-icon"></i>
                                <select name="pregunta1" id="inputPregunta1" class="inst-select">
                                    <option value="<?php echo htmlspecialchars($pregunta1); ?>" selected><?php echo htmlspecialchars($pregunta1); ?></option>
                                    <option value="¿Comida favorita?">¿Comida favorita?</option>
                                    <option value="¿Color Preferido?">¿Color Preferido?</option>
                                    <option value="¿Nombre de mi mascota?">¿Nombre de mi mascota?</option>
                                    <option value="¿Deporte Favorito?">¿Deporte Favorito?</option>
                                    <option value="¿Lugar de nacimiento?">¿Lugar de nacimiento?</option>
                                    <option value="¿Nombre de mi mejor amigo de la infancia?">¿Nombre de mi mejor amigo de la infancia?</option>
                                    <option value="¿Película favorita?">¿Película favorita?</option>
                                    <option value="¿Nombre de mi primer maestro?">¿Nombre de mi primer maestro?</option>
                                    <option value="¿Marca de mi primer automóvil?">¿Marca de mi primer automóvil?</option>
                                    <option value="¿Nombre de mi primer jefe?">¿Nombre de mi primer jefe?</option>
                                </select>
                            </div>
                            
                            <div class="inst-input-wrapper">
                                <i class="fas fa-pen inst-icon"></i>
                                <input id="inputRespuesta1" type="text" name="respuesta1" class="inst-input" placeholder="Ingresa tu respuesta" autocomplete="off" />
                            </div>
                        </div>

                        <div class="question-group">
                            <label class="inst-label">Pregunta de Seguridad 2</label>
                            <div class="inst-input-wrapper">
                                <i class="fas fa-question-circle inst-icon"></i>
                                <select name="pregunta2" id="inputPregunta2" class="inst-select">
                                    <option value="<?php echo htmlspecialchars($pregunta2); ?>" selected><?php echo htmlspecialchars($pregunta2); ?></option>
                                    <option value="¿Comida favorita?">¿Comida favorita?</option>
                                    <option value="¿Color Preferido?">¿Color Preferido?</option>
                                    <option value="¿Nombre de mi mascota?">¿Nombre de mi mascota?</option>
                                    <option value="¿Deporte Favorito?">¿Deporte Favorito?</option>
                                    <option value="¿Lugar de nacimiento?">¿Lugar de nacimiento?</option>
                                    <option value="¿Nombre de mi mejor amigo de la infancia?">¿Nombre de mi mejor amigo de la infancia?</option>
                                    <option value="¿Película favorita?">¿Película favorita?</option>
                                    <option value="¿Nombre de mi primer maestro?">¿Nombre de mi primer maestro?</option>
                                    <option value="¿Marca de mi primer automóvil?">¿Marca de mi primer automóvil?</option>
                                    <option value="¿Nombre de mi primer jefe?">¿Nombre de mi primer jefe?</option>
                                </select>
                            </div>

                            <div class="inst-input-wrapper">
                                <i class="fas fa-pen inst-icon"></i>
                                <input id="inputRespuesta2" type="text" name="respuesta2" class="inst-input" placeholder="Ingresa tu respuesta" autocomplete="off" />
                            </div>
                        </div>

                        <button class="inst-btn-submit" type="submit">
                            Actualizar Preguntas <i class="fas fa-save"></i>
                        </button>
                    </form>
                </div>

                <?php include("../models/sweetalert.php"); ?>

                <div class="inst-links">
                    <a href="pregunta.php" class="inst-link"><i class="fas fa-arrow-left"></i> Volver</a>
                </div>
            </div>
            <div class="inst-footer"><p>&copy; <?php echo date("Y"); ?> SDGBP. Todos los derechos reservados.</p></div>
        </div>
    </div>
    
    <script>
        function validateFormCPS() {
            const pregunta1 = document.getElementById("inputPregunta1").value.trim();
            const respuesta1 = document.getElementById("inputRespuesta1").value.trim();
            const pregunta2 = document.getElementById("inputPregunta2").value.trim();
            const respuesta2 = document.getElementById("inputRespuesta2").value.trim();
            const audio = new Audio('../error/validation_error.mp3');

            if (pregunta1 === "" || respuesta1 === "" || pregunta2 === "" || respuesta2 === "") {
                audio.play();
                toastr.error("Todos los campos son obligatorios", "Error de Validación");
                return false;
            }

            if (pregunta1 === pregunta2) {
                audio.play();
                toastr.error("Las preguntas de seguridad deben ser diferentes", "Error de Validación");
                return false;
            }
            return true;
        }

        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": true,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };

        document.addEventListener('DOMContentLoaded', function () {
            // Initialize Select2
            $('.inst-select').select2({
                width: '100%',
                placeholder: "Seleccione una opción",
                allowClear: false
            });

            // Eliminar estilos y modales previos de SWAL puramente cosméticos que bloquean
            Swal.fire({
                title: 'Verificación requerida',
                text: 'Por favor, ingresa tu contraseña para acceder a esta vista de seguridad.',
                input: 'password',
                inputPlaceholder: 'Ingresa tu contraseña actual',
                showCancelButton: true,
                confirmButtonText: 'Verificar',
                cancelButtonText: 'Cancelar',
                allowOutsideClick: false,
                confirmButtonColor: '#0f172a',
                cancelButtonColor: '#e2e8f0',
                customClass: {
                    cancelButton: 'text-slate-800'
                },
                preConfirm: (password) => {
                    return fetch('../acciones/verificar_contraseña.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ password: password })
                    })
                    .then(response => response.json().then(data => ({ status: response.status, body: data })))
                    .then(({ status, body }) => {
                        if (status === 200) {
                            return body.message; 
                        } else if (status === 403) {
                            Swal.fire({
                                title: 'Procesando...',
                                html: 'Serás redirigido al login en <b>3</b> segundos.',
                                icon: 'info',
                                allowOutsideClick: false,
                                showConfirmButton: false,
                                didOpen: () => {
                                    const b = Swal.getHtmlContainer().querySelector('b');
                                    let timer = 3; 
                                    const interval = setInterval(() => {
                                        timer--;
                                        b.textContent = timer;
                                        if (timer === 0) {
                                            clearInterval(interval);
                                            window.location.href = body.redirect; 
                                        }
                                    }, 1000); 
                                }
                            });
                            throw new Error('Usuario bloqueado'); 
                        } else if (status === 401) {
                            throw new Error(body.message); 
                        } else {
                            throw new Error('Ocurrió un error inesperado.');
                        }
                    })
                    .catch(error => {
                        Swal.showValidationMessage(error.message);
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: result.value, 
                        confirmButtonColor: '#f18000',
                        confirmButtonText: 'Continuar'
                    }).then(() => {
                        document.getElementById('form-container').style.display = 'block';
                    });
                } else {
                    window.location.href = 'pregunta.php';
                }
            });
        });
    </script>
</body>
</html>