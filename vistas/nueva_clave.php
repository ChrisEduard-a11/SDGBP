<?php
session_start();
if (empty($_SESSION["user"])) {
    header("Location: denegado_a.php");
    exit();
}

/*SECIONES*/
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
        <style>
/* --- VISTA CAMBIO DE CONTRASEÑA PREMIUM --- */
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
/* Contenedor del grupo de entrada para que no pierda el estilo premium */
.input-group {
    position: relative;
    display: flex;
    align-items: stretch;
    width: 100%;
}
.btn-secondary.btn-sm {
    background: transparent !important;
    color: #64748b !important;
    border: none !important;
    font-weight: 600;
}
.btn-secondary.btn-sm:hover {
    color: var(--text-main) !important;
}
/* Ajuste del botón de "ojo" para que encaje perfecto con los bordes redondeados */
.input-group .btn-outline-secondary {
    border: 1.5px solid var(--border-color) !important;
    border-left: none !important;
    background-color: #ffffff !important;
    color: var(--text-muted) !important;
    border-radius: 0 14px 14px 0 !important;
    padding: 0 15px !important;
    z-index: 4;
    transition: all 0.2s ease;
}

.input-group .btn-outline-secondary:hover {
    color: var(--primary) !important;
    background-color: var(--primary-light) !important;
}

/* El input dentro del grupo */
.input-group .form-control {
    border-top-right-radius: 0 !important;
    border-bottom-right-radius: 0 !important;
    z-index: 3;
}

/* INDICADOR DE FORTALEZA DE CONTRASEÑA (SÚPER MODERNO) */
#passwordStrength {
    display: block;
    margin-top: 8px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
}

/* Colores dinámicos para la fortaleza (puedes aplicarlos con JS) */
.strength-weak { color: #ef4444; }     /* Rojo */
.strength-medium { color: #f59e0b; }   /* Ámbar */
.strength-strong { color: #10b981; }   /* Esmeralda */

/* Animación sutil al escribir */
#inputPassword, #inputPasswordConfirm {
    letter-spacing: 0.15em;
}

#inputPassword::placeholder, #inputPasswordConfirm::placeholder {
    letter-spacing: normal;
}

/* Estilos del Stepper */
.stepper {
    display: flex;
    justify-content: space-between;
    margin-bottom: 30px;
    position: relative;
}
.stepper::before {
    content: '';
    position: absolute;
    top: 15px;
    left: 0;
    right: 0;
    height: 2px;
    background: #e2e8f0;
    z-index: 1;
}
.step-item {
    position: relative;
    z-index: 2;
    text-align: center;
    flex: 1;
}
.step-circle {
    width: 32px;
    height: 32px;
    background: #fff;
    border: 2px solid #e2e8f0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 8px;
    font-weight: bold;
    color: #64748b;
    transition: all 0.3s ease;
}
.step-item.active .step-circle {
    background: #f18000;
    border-color: #f18000;
    color: #fff;
    box-shadow: 0 0 0 5px rgba(241, 128, 0, 0.2);
}
.step-item.completed .step-circle {
    background: #28a745;
    border-color: #28a745;
    color: #fff;
}
.step-label {
    font-size: 10px;
    font-weight: bold;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.step-item.active .step-label {
    color: #f18000;
}
.step-item.completed .step-label {
    color: #28a745;
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
                            <img src="../img/Logo-OP2_V4.webp" alt="Logo Empresa" class="logo mb-2" style="max-width: 60px;">
                            <h1 class="system-name">Recuperación de Cuenta</h1>
                        </div>

                        <!-- Indicador de Pasos -->
                        <div class="stepper">
                            <div class="step-item completed">
                                <div class="step-circle"><i class="fas fa-check"></i></div>
                                <div class="step-label">ID</div>
                            </div>
                            <div class="step-item completed">
                                <div class="step-circle"><i class="fas fa-check"></i></div>
                                <div class="step-label">Método</div>
                            </div>
                            <div class="step-item completed">
                                <div class="step-circle"><i class="fas fa-check"></i></div>
                                <div class="step-label">Validar</div>
                            </div>
                            <div class="step-item active">
                                <div class="step-circle">4</div>
                                <div class="step-label">Clave</div>
                            </div>
                        </div>

                        <div class="text-center mb-4">
                            <h4 class="fw-bold">Paso 4: Nueva Contraseña</h4>
                            <p class="text-muted small">Establece tu nueva clave de acceso segura.</p>
                        </div>

                        <?php if (isset($_GET['vencida'])): ?>
                            <div class="alert alert-danger shadow-sm border-0 mb-4 animate__animated animate__shakeX">
                                <i class="fas fa-clock me-2"></i> <b>Seguridad:</b> Tu contraseña ha superado los 180 días de vigencia y debe ser actualizada para continuar.
                            </div>
                        <?php endif; ?>

                        <!-- Formulario sin caja -->
                        <div class="form-container">
                            <form name="claveForm" action="../acciones/solicitar_cambio_clave.php" method="POST" onsubmit="return validateFormNC()">
                                <div class="form-floating mb-3">
                                    <div class="input-group">
                                        <input 
                                            class="form-control" 
                                            id="inputPassword" 
                                            type="password" 
                                            placeholder="Nueva Contraseña" 
                                            name="clave" 
                                            oninput="checkPasswordStrength()" 
                                        />
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('inputPassword', this)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <small id="passwordStrength" class="form-text text-muted"></small> <!-- Indicador de fortaleza -->
                                </div>
                                <div class="form-floating mb-3">
                                    <div class="input-group">
                                        <input 
                                            class="form-control" 
                                            id="inputPasswordConfirm" 
                                            type="password" 
                                            placeholder="Confirmar Contraseña" 
                                            name="clave1" 
                                        />
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('inputPasswordConfirm', this)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="text-center mb-4 mt-4">
                                    <button class="boton" type="submit">Cambiar</button>
                                </div>
                            </form>
                            <?php include("../models/sweetalert.php"); ?>
                            <!-- Botón para volver a la página principal -->
                            <div class="text-center mt-4">
                                <a class="btn btn-secondary btn-sm" href='login.php'>
                                    <i class="fas fa-home"></i> Volver
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
            </div>
        </div>
    </body>
</html>