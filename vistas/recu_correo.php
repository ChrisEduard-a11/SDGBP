<?php
session_start();
if (!isset($_SESSION['correo'])) {
    // Redirigir al usuario a la página de inicio de sesión si no está autenticado
    $_SESSION['estatus'] = 'error';
    $_SESSION['mensaje'] = "No tienes permisos o Ningun Correo Registrado, Consulte al Soporte de Usuarios.";
    header("Location: login.php");
    exit();
}

// Función para enmascarar el correo
function enmascararCorreo($correo) {
    $partes = explode("@", $correo);
    $parteLocal = substr($partes[0], 0, 3) . str_repeat('*', strlen($partes[0]) - 6) . substr($partes[0], -3);
    return $parteLocal . "@" . $partes[1];
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <link rel="canonical" href="https://sdgbp.wuaze.com/<?php echo basename($_SERVER['REQUEST_URI']); ?>" />

        <title>Recuoeracion Email - SDGBP</title>

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
/* --- VISTA DE ENVÍO DE CORREO PREMIUM --- */
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
.btn-secondary.btn-sm {
    background: transparent !important;
    color: #64748b !important;
    border: none !important;
    font-weight: 600;
}
.btn-secondary.btn-sm:hover {
    color: var(--text-main) !important;
}
/* Estilo para el campo de correo Readonly */
input[readonly].form-control {
    background-color: #f8fafc !important; /* Un gris muy tenue, casi blanco */
    border: 1.5px dashed #cbd5e1 !important; /* Borde punteado para indicar que es informativo */
    color: #475569 !important;
    cursor: not-allowed;
    font-weight: 600;
    opacity: 0.8;
}

/* Rediseño de la Alerta (Info) */
.alert-info {
    background-color: #eff6ff !important; /* Azul suave */
    border: none !important;
    border-left: 4px solid #3b82f6 !important; /* Barra lateral para dar seriedad */
    border-radius: 12px !important;
    color: #1e40af !important;
    font-size: 0.85rem;
    padding: 15px 20px !important;
    display: flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
}

/* Icono para la alerta (si quieres agregarlo manualmente o vía CSS) */
.alert-info::before {
    content: '\f05a'; /* Icono de Info de FontAwesome */
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    font-size: 1.1rem;
}

/* Ajuste del botón para que destaque la acción de envío */
.boton {
    background: linear-gradient(135deg, #f18000 0%, #ff9800 100%) !important;
    position: relative;
    overflow: hidden;
}

.boton::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: 0.5s;
}

.boton:hover::after {
    left: 100%;
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
                            <div class="step-item active">
                                <div class="step-circle">3</div>
                                <div class="step-label">Validar</div>
                            </div>
                            <div class="step-item">
                                <div class="step-circle">4</div>
                                <div class="step-label">Clave</div>
                            </div>
                        </div>

                        <div class="text-center mb-4">
                            <h4 class="fw-bold">Paso 3: Confirmación Email</h4>
                            <p class="text-muted small">Presiona el botón para recibir el enlace seguro.</p>
                        </div>
                        <!-- Formulario sin caja -->
                        <div class="form-container">
                            <form action="../acciones/enviar_recuperacion.php" method="POST">
                                <div class="form-floating mb-3">
                                    <input class="form-control" id="correo" type="email" name="correo" value="<?php echo $_SESSION['correo']; ?>" readonly required />
                                    <label for="correo">Correo Electrónico</label>
                                </div>
                                <div class="alert alert-info" role="alert">
                                    Se enviará un correo de recuperación al correo registrado.
                                </div>
                                <div class="text-center mt-4 mb-4">
                                    <button type="submit" class="boton">Enviar Correo</button>
                                </div>
                            </form>
                            <?php include("../models/sweetalert.php"); ?>
                            <!-- Botón para volver a la página principal -->
                            <div class="text-center mt-4">
                                <a class="btn btn-secondary btn-sm" href='seleccionar_meto_recu.php'>
                                    <i class="fas fa-chevron-left"></i> Volver al Inicio
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
                <?php include("../models/footer_index.php"); ?>
                <!--Start of Tawk.to Script-->
                <script type="text/javascript">
                    var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
                    (function(){
                    var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
                    s1.async=true;
                    s1.src='https://embed.tawk.to/6908e0b8b0b4221952b32bb8/1j95arkos';
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