<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <link rel="canonical" href="https://sdgbp.wuaze.com/<?php echo basename($_SERVER['REQUEST_URI']); ?>" />
        <title>Registro - SDGBP</title>

        <!-- Favicon -->
        <link rel="icon" type="image/x-icon" href="../img/favicon.ico">

        <!-- Bootstrap core CSS-->
        <link href="../css/styles.css" rel="stylesheet" />
        <link href="../css/estilo_login.css" rel="stylesheet" />
        <!--Icons-->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <!-- Toastr -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        
        <!--Sweetalert-->
        <link rel="stylesheet" type="text/css" href="../sweetalert/sweetalert2.min.css">
        <script src="../sweetalert/sweetalert2.js"></script>

        <!--font Google-->
        <link href="./css/font_google.css" rel="stylesheet">

        <!--hcaptcha-->
        <?php 
        $host = $_SERVER['HTTP_HOST'];
        if (strpos($host, 'localhost') === false && strpos($host, '127.0.0.1') === false): 
        ?>
        <script src="https://js.hcaptcha.com/1/api.js" async defer></script>
        <?php endif; ?>

        <style >
/* =========================================
   ESTILO ULTRA-PREMIUM REGISTRO SDGBP 2026
   ========================================= */
:root {
    --primary: #f18000;
    --primary-gradient: linear-gradient(135deg, #f18000 0%, #e67300 100%);
    --bg-body: #f8fafc;
    --text-main: #1e293b;
    --text-muted: #64748b;
    --border-color: #e2e8f0;
    --radius-premium: 16px;
}

body {
    background-color: var(--bg-body);
    font-family: 'Inter', sans-serif;
    margin: 0;
    overflow: hidden; /* Evita doble scroll */
}

#layoutAuthentication {
    display: flex;
    height: 100vh;
}

/* Mitad Izquierda */
.login-image-container {
    flex: 1.2; /* Un poco más ancho para registro */
    position: relative;
}

.login-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Mitad Derecha - Scrollable */
.login-form-container {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: white;
    overflow-y: auto; /* Permite scroll si el form es largo */
    padding: 40px;
    z-index: 2;
}

.form-content {
    width: 100%;
    max-width: 550px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    min-height: 100%;
}

.system-name {
    font-size: 1.4rem;
    font-weight: 800;
    color: var(--text-main);
    margin-bottom: 1.5rem;
}

/* --- FOTO DE PERFIL --- */
.profile-pic-container {
    position: relative;
    width: 120px;
    height: 120px;
    margin: 0 auto 10px;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 0 0 2px var(--border-color), 0 10px 15px rgba(0,0,0,0.1);
}

#profilePicPreview {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

#inputFoto { display: none; }

.upload-icon {
    position: absolute;
    bottom: 5px;
    right: 5px;
    background: var(--primary-gradient);
    color: white;
    width: 32px;
    height: 32px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border: 2px solid #fff;
}

/* --- FORMULARIO --- */
.form-control, .form-select {
    border: 1.5px solid var(--border-color) !important;
    border-radius: 12px !important;
    padding: 0.8rem 1rem !important;
}

.form-control:focus {
    border-color: var(--primary) !important;
    box-shadow: 0 0 0 4px rgba(241, 128, 0, 0.1) !important;
}

.input-group .btn {
    border: 1.5px solid var(--border-color);
    border-left: none;
    background: white;
    border-radius: 0 12px 12px 0 !important;
}

/* --- BOTONES --- */
.boton {
    width: 100%;
    height: 55px;
    background: var(--primary-gradient) !important;
    color: white !important;
    border: none !important;
    border-radius: 14px !important;
    font-weight: 700 !important;
    box-shadow: 0 8px 20px rgba(241, 128, 0, 0.2) !important;
    transition: 0.3s;
}

.boton:hover { transform: translateY(-2px); filter: brightness(1.1); }

/* --- FOOTER SIEMPRE ABAJO --- */
.footer_licencia {
    margin-top: auto !important;
    padding: 30px 0 10px;
    border-top: 1px solid var(--border-color);
}
        </style>
    </head>
    <body>
        <div id="layoutAuthentication">
            <!-- Mitad izquierda: Imagen de fondo -->
            <div class="login-image-container d-none d-lg-block">
                <img src="../img/fondo_izq.webp" alt="Imagen de fondo" class="login-image">
            </div>

            <!-- Mitad derecha: Formulario -->
            <div class="login-form-container">
                <main>
                    <div class="form-content">
                        <!-- Nombre del sistema y logo -->
                        <div class="text-center">
                            <img src="../img/Logo-OP2_V4.webp" alt="Logo Empresa" class="logo mb-3">
                            <h1 class="system-name">Sistema de Gestión Bienes y Pagos</h1>
                        </div>

                        <!-- Formulario sin caja -->
                        <div class="form-container">
                            <form name="registerForm" id="registroForm" method="POST" action="../acciones/guardar_u_login.php" onsubmit="return validateFormRL()" autocomplete="off" enctype="multipart/form-data">
                                <div class="row mb-3">
                                    <div class="text-center mb-4">
                                        <div class="profile-pic-container">
                                            <img id="profilePicPreview" src="../img/default_profile.png" alt="Foto de Perfil" />
                                            <label for="inputFoto" class="upload-icon">
                                                <i class="fas fa-plus"></i>
                                            </label>
                                            <input id="inputFoto" type="file" name="foto" accept="image/*" onchange="previewProfilePic(event)" />
                                        </div>
                                        <small class="text-muted">Haz clic en el ícono para subir una foto (opcional).</small>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3 mb-md-0">
                                            <input class="form-control" id="inputFirstName" type="text" name="usuario" maxlength="15" placeholder="ej.juan" />
                                            <label for="inputFirstName">Usuario(*):</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input class="form-control" id="inputLastName" type="text" name="nombre" maxlength="255" placeholder="Nombres y Apellidos" />
                                            <label for="inputLastName">Nombre(*):</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3 mb-md-0">
                                            <select name="nacionalidad" id="inputNACI" class="form-control select-picker">
                                                <option value="" selected>Seleccionar</option>    
                                                <option value="V-">V</option>
                                                <option value="E-">E</option>
                                                <option value="G-">G</option>
                                                <option value="J-">J</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input class="form-control" id="inputDNI" type="number" name="cedula" maxlength="20" placeholder="Cedula (DNI)" />
                                            <label for="inputDNI">Cedula(*):</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-floating mb-3">
                                    <input class="form-control" id="inputEmail" type="text" name="correo" />
                                    <label for="inputEmail">Correo Electrónico(*)</label>
                                </div>
                                <div class="form-floating mb-3">
                                    <div class="input-group">
                                        <input 
                                            class="form-control" 
                                            id="inputPassword" 
                                            type="password" 
                                            name="clave" 
                                            onkeyup="checkPasswordStrength()" 
                                            placeholder="Contraseña(*):" 
                                        />
                                        <button class="btn btn-outline-secondary toggle-password" type="button" onclick="togglePasswordVisibility('inputPassword', this)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <small id="passwordStrength" class="form-text text-muted"></small>
                                </div>

                                <div class="form-floating mb-3">
                                    <div class="input-group">
                                        <input 
                                            class="form-control" 
                                            id="inputPasswordConfirm" 
                                            type="password" 
                                            name="confirmar_clave" 
                                            placeholder="Confirmar Contraseña(*):" 
                                        />
                                        <button class="btn btn-outline-secondary toggle-password" type="button" onclick="togglePasswordVisibility('inputPasswordConfirm', this)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3 mb-md-0">
                                            <select name="pregunta" id="inputPregunta1" class="form-control select-picker">
                                                <option value="">Seleccione una pregunta</option>
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
                                            <label for="inputPregunta1"></label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input class="form-control" id="inputRespuesta" type="text" name="respuesta" maxlength="255" placeholder="Respuesta" />
                                            <label for="inputRespuesta">Respuesta 1(*):</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3 mb-md-0">
                                            <select name="pregunta2" id="inputPregunta2" class="form-control select-picker">
                                                <option value="">Seleccione una pregunta</option>
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
                                            <label for="inputPregunta2"></label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input class="form-control" id="inputRespuesta2" type="text" name="respuesta2" maxlength="255" placeholder="Respuesta" />
                                            <label for="inputRespuesta2">Respuesta 2(*):</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center mt-4 mb-4">
                                    <button type="submit" class="boton">Crear Cuenta</button>
                                </div>
                                <!-- Botón para volver a la página principal -->
                                <div class="text-center mt-3">
                                    <a class="btn btn-secondary btn-sm" href='login.php'>
                                        <i class="fas fa-home"></i> Atras
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </main>
                <footer class="footer_licencia text-center mt-4">
                    <p>
                        Este trabajo está licenciado bajo 
                        <a href="https://creativecommons.org/licenses/by-nc/4.0/?ref=chooser-v1" target="_blank" rel="license noopener noreferrer">
                            Creative Commons BY-NC 4.0
                            <img src="https://mirrors.creativecommons.org/presskit/icons/cc.svg?ref=chooser-v1" alt="CC">
                            <img src="https://mirrors.creativecommons.org/presskit/icons/by.svg?ref=chooser-v1" alt="BY">
                            <img src="https://mirrors.creativecommons.org/presskit/icons/nc.svg?ref=chooser-v1" alt="NC">
                        </a>
                    </p>
                    <b><small>&copy; <?php echo date("Y"); ?> Sistema de Gestión de Bienes y Pagos. Todos los derechos reservados.</small></b>
                </footer>
                
                <script src="../js/vali_login.js"></script>
                <?php include("../models/footer_index.php"); ?>
                <!--Start of Tawk.to Script-->
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
                <!--End of Tawk.to Script-->
            </div>
        </div>
    </body>
</html>