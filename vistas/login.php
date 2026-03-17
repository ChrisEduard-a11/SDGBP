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
        <title>Login - SDGBP</title>

        <!-- Favicon -->
        <link rel="icon" type="image/x-icon" href="../img/favicon.ico">

        <!-- Bootstrap core CSS-->
        <link href="../css/styles.css" rel="stylesheet" />
        <link href="../css/estilo_login.css" rel="stylesheet" />

        <!--Icons-->
        <script src="../js/all.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <!-- Toastr -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        
        <!--Sweetalert-->
        <link rel="stylesheet" type="text/css" href="../sweetalert/sweetalert2.min.css">
        <script src="../sweetalert/sweetalert2.js"></script>

        <!--font Google-->
        <link href="./css/font_google.css" rel="stylesheet">

        <!-- reCAPTCHA -->
        <?php 
        $host = $_SERVER['HTTP_HOST'];
        if (strpos($host, 'localhost') === false && strpos($host, '127.0.0.1') === false): 
        ?>
        <script src="https://www.google.com/recaptcha/api.js?render=6LdOo14rAAAAALmCONvTluM7hVcBK3i5O688C8pq"></script>
        <?php endif; ?>

        <style >
/* =========================================
   SISTEMA SDGBP - DISEÑO ULTRA PREMIUM 2026
   ========================================= */

:root {
    --primary: #f18000;
    --primary-dark: #d67100;
    --primary-light: rgba(241, 128, 0, 0.1);
    --bg-body: #f1f5f9;
    --text-main: #1e293b;
    --text-muted: #64748b;
    --border-color: #e2e8f0;
    --radius-premium: 16px;
    --shadow-premium: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
}

/* --- ESTILO DE LOS BOTONES DE ROL (TARJETAS) --- */
.gap-2 {
    gap: 12px !important;
    margin-bottom: 20px;
}

.boton-rol {
    all: unset;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 80px; 
    height: 85px;
    background: #ffffff;
    border: 2px solid #f1f5f9;
    border-radius: var(--radius-premium);
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: var(--shadow-premium);
}

.boton-rol i {
    font-size: 1.5rem !important;
    margin-bottom: 6px;
    color: var(--text-muted);
    transition: all 0.3s ease;
}

.boton-rol span, .boton-rol {
    font-size: 0.65rem;
    font-weight: 800;
    letter-spacing: 0.5px;
    color: var(--text-muted);
    text-transform: uppercase;
}

.boton-rol:hover {
    border-color: var(--primary);
    transform: translateY(-4px);
    background: var(--primary-light);
}

.boton-rol:hover i {
    color: var(--primary);
}

/* Clase activa cuando se selecciona un rol */
.boton-rol.active {
    background: var(--primary) !important;
    border-color: var(--primary) !important;
    box-shadow: 0 12px 20px rgba(241, 128, 0, 0.3) !important;
    transform: scale(1.05);
}

.boton-rol.active i, 
.boton-rol.active span,
.boton-rol.active {
    color: #ffffff !important;
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
/* --- INPUTS Y FORMULARIOS --- */
.form-control, .form-select {
    border: 1.5px solid var(--border-color) !important;
    border-radius: 12px !important;
    background-color: #ffffff !important;
    font-size: 0.95rem !important;
    font-weight: 500 !important;
    color: var(--text-main) !important;
    transition: all 0.25s ease !important;
}

.form-control:focus {
    border-color: var(--primary) !important;
    box-shadow: 0 0 0 4px var(--primary-light) !important;
    background: #fff !important;
}

.form-floating > label {
    color: var(--text-muted) !important;
    font-weight: 500;
}

/* --- FOTO DE PERFIL (REGISTRO) --- */
.profile-pic-container {
    position: relative;
    width: 120px;
    height: 120px;
    margin: 0 auto 15px;
    border-radius: 50%;
    background: #fff;
    padding: 3px;
    box-shadow: 0 0 0 2px var(--border-color), var(--shadow-premium);
}

#profilePicPreview {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.upload-icon {
    position: absolute;
    bottom: 5px;
    right: 5px;
    background: var(--primary);
    color: white;
    width: 35px;
    height: 35px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border: 3px solid #fff;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* --- BOTÓN PRINCIPAL (SUBMIT) --- */
.boton {
    width: 100%;
    max-width: 320px;
    height: 55px;
    background: linear-gradient(135deg, #f18000 0%, #ff9800 100%) !important;
    color: white !important;
    border: none !important;
    border-radius: 14px !important;
    font-weight: 700 !important;
    text-transform: uppercase;
    letter-spacing: 1px;
    box-shadow: 0 8px 20px rgba(241, 128, 0, 0.25) !important;
    cursor: pointer;
    transition: all 0.3s ease !important;
}

.boton:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 12px 25px rgba(241, 128, 0, 0.4) !important;
}

.boton:disabled {
    background: #cbd5e1 !important;
    box-shadow: none !important;
    cursor: not-allowed;
}

/* --- BOTONES SECUNDARIOS (LINKS) --- */
.btn-outline-secondary.btn-sm, .btn-secondary.btn-sm {
    border: none !important;
    background: #f1f5f9 !important;
    color: var(--text-muted) !important;
    font-weight: 600 !important;
    padding: 10px 18px !important;
    border-radius: 10px !important;
    transition: 0.2s;
    text-decoration: none;
}

.btn-outline-secondary.btn-sm:hover {
    background: #e2e8f0 !important;
    color: var(--primary) !important;
}

/* Ojo de contraseña */
.input-group .btn-outline-secondary {
    border: 1.5px solid var(--border-color) !important;
    border-left: none !important;
    background: #fff !important;
    border-radius: 0 12px 12px 0 !important;
}

.input-group .form-control {
    border-top-right-radius: 0 !important;
    border-bottom-right-radius: 0 !important;
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
                        <div class="text-center mb-4">
                            <img src="../img/Logo-OP2_V4.webp" alt="Logo Empresa" class="logo mb-3">
                            <h1 class="system-name">Sistema de Gestión Bienes y Pagos</h1>
                        </div>
                        <!-- Formulario sin caja -->
                        <div class="form-container">
                            <form name="loginForm" action="../acciones/login.php" method="POST" onsubmit="return validateFormLOGIN()">
                                <div class="form-floating mb-3">
                                    <input 
                                        class="form-control" 
                                        id="inputEmail" 
                                        type="text" 
                                        placeholder="Usuario" 
                                        name="usuario" 
                                        disabled
                                    />
                                    <label for="inputEmail">Usuario(*):</label>
                                </div>
                                <div class="form-floating mb-3">
                                    <div class="input-group">
                                        <input 
                                            class="form-control" 
                                            id="inputPassword" 
                                            type="password" 
                                            placeholder="Contraseña(*):" 
                                            name="clave" 
                                            disabled
                                        />
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group mb-3 text-center">
                                    <label class="mb-2">Para habilitar los campos selecciona tu rol:</label>
                                    <div class="d-flex justify-content-center gap-2">
                                        <button type="button" class=" boton-rol" onclick="selectRole1(this, 'admin')">
                                            <i class="fas fa-user-shield"></i> S U
                                        </button>
                                        <button type="button" class=" boton-rol" onclick="selectRole1(this, 'cont')">
                                            <i class="fas fa-calculator"></i> ADMIN
                                        </button>
                                        <button type="button" class=" boton-rol" onclick="selectRole1(this, 'inv')">
                                            <i class="fas fa-check-circle"></i> CHECK
                                        </button>
                                        <button type="button" class=" boton-rol" onclick="selectRole1(this, 'upu')">
                                            <i class="fas fa-users"></i> UPU
                                        </button>
                                    </div>
                                    <input type="hidden" id="inputRole" name="rol" value="" required />
                                </div>
                                <!-- Mensaje de error -->
                                <?php include("../models/sweetalert.php"); ?>
                                <!-- Botones estilizados -->
                                <div class="d-flex justify-content-center align-items-center mt-4 gap-3 flex-wrap">
                                    <a class="btn btn-outline-secondary btn-sm" href='recuperar.php'>
                                        <i class="fas fa-unlock-alt"></i> Recuperar
                                    </a>
                                    <a class="btn btn-outline-secondary btn-sm" href='solicitar_desbloqueo.php'>
                                        <i class="fas fa-unlock-alt"></i> Desbloquear
                                    </a>
                                    <a class="btn btn-outline-secondary btn-sm" href='register.php'>
                                        <i class="fas fa-user-plus"></i> Registrarse
                                    </a>
                                </div>
                                <!-- Botón de envío -->
                                <div class="text-center mt-4">
                                    <button class="boton" type="submit" id="btnEntrar" disabled>Entrar</button>
                                </div>
                            </form>
                            <?php include("../models/sweetalert.php"); ?>
                            <!-- Botón para volver a la página principal -->
                            <div class="text-center mt-4">
                                <a class="btn btn-secondary btn-sm" href='../index.php'>
                                    <i class="fas fa-home"></i> Volver al Inicio
                                </a>
                            </div>
                        </div>
                    </div>
                </main>
                 <!-- Footer -->
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
<?php
// Limpiar las variables de sesión después de usarlas
unset($_SESSION['old_usuario']);
unset($_SESSION['old_clave']);
?>