<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>Validar - SDGBP</title>

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
            /* =========================================
   ESTILO PREMIUM VALIDACIÓN SDGBP 2026
   ========================================= */
:root {
    --primary-gradient: linear-gradient(135deg, #f18000 0%, #e67300 100%);
    --accent-color: #f18000;
    --text-main: #1e293b;
    --border-color: #e2e8f0;
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
/* Foco central: El input de usuario */
#inputUsuario {
    font-size: 1.2rem !important;
    font-weight: 600 !important;
    letter-spacing: 1px;
    text-align: center;
    border: 2px solid var(--border-color) !important;
    border-radius: 16px !important;
    height: 70px !important;
    transition: all 0.3s ease;
}

#inputUsuario:focus {
    border-color: var(--accent-color) !important;
    box-shadow: 0 10px 20px rgba(241, 128, 0, 0.1) !important;
    transform: translateY(-2px);
}

/* Estilo para el ícono decorativo */
.recovery-icon {
    font-size: 3rem;
    color: var(--accent-color);
    margin-bottom: 1.5rem;
    background: rgba(241, 128, 0, 0.1);
    width: 100px;
    height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    margin-left: auto;
    margin-right: auto;
}

/* Botón Validar */
.boton {
    width: 100%;
    max-width: 300px;
    height: 55px;
    background: var(--primary-gradient) !important;
    color: white !important;
    border: none !important;
    border-radius: 14px !important;
    font-size: 1.1rem !important;
    font-weight: 700 !important;
    text-transform: uppercase;
    box-shadow: 0 8px 15px rgba(241, 128, 0, 0.3) !important;
    transition: all 0.3s ease !important;
}

.boton:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 20px rgba(241, 128, 0, 0.4) !important;
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
        <div class="login-image-container d-none d-lg-block">
            <img src="../img/fondo_izq.webp" alt="Fondo" class="login-image">
        </div>

        <div class="login-form-container">
            <main>
                <div class="form-content">
                    <div class="text-center mb-4">
                        <img src="../img/Logo-OP2_V4.webp" alt="Logo" class="logo mb-2" style="max-width: 60px;">
                        <h1 class="system-name">Recuperación de Cuenta</h1>
                    </div>

                    <!-- Indicador de Pasos -->
                    <div class="stepper">
                        <div class="step-item active">
                            <div class="step-circle">1</div>
                            <div class="step-label">ID</div>
                        </div>
                        <div class="step-item">
                            <div class="step-circle">2</div>
                            <div class="step-label">Método</div>
                        </div>
                        <div class="step-item">
                            <div class="step-circle">3</div>
                            <div class="step-label">Validar</div>
                        </div>
                        <div class="step-item">
                            <div class="step-circle">4</div>
                            <div class="step-label">Clave</div>
                        </div>
                    </div>

                    <div class="text-center mb-4">
                        <h4 class="fw-bold">Paso 1: Identificación</h4>
                        <p class="text-muted small">Introduce tu usuario para validar tu identidad.</p>
                    </div>

                    <div class="form-container">
                        <div class="recovery-icon mb-4">
                            <i class="fas fa-user-shield"></i>
                        </div>

                        <form name="recuperarForm" action="../acciones/recuperar_clave.php" method="POST" onsubmit="return validateFormRCU()">
                            <div class="form-floating mb-4">
                                <input class="form-control" id="inputUsuario" type="text" placeholder="Usuario" name="usuario" autocomplete="off" />
                                <label for="inputUsuario" class="w-100 text-center">Nombre de Usuario(*)</label>
                            </div>

                            <div class="text-center mt-4 mb-4">
                                <button class="boton" type="submit">Validar Usuario</button>
                                <div class="mt-3">
                                    <a href="recuperar_usuario.php" class="text-decoration-none small text-primary fw-bold">¿Olvidaste tu nombre de usuario?</a>
                                </div>
                            </div>
                        </form>

                        <?php include("../models/sweetalert.php"); ?>

                        <div class="text-center mt-4">
                            <a class="btn btn-secondary btn-sm" href='login.php'>
                                <i class="fas fa-chevron-left"></i> Volver al Inicio
                            </a>
                        </div>
                    </div>
                </div>
            </main>

            <footer class="footer_licencia text-center mt-auto pt-4">
                <p class="small text-muted mb-1">
                    Licencia <a href="https://creativecommons.org/licenses/by-nc/4.0/" target="_blank" class="text-decoration-none">CC BY-NC 4.0</a>
                </p>
                <b><small class="text-muted">&copy; <?php echo date("Y"); ?> SDGBP. Todos los derechos reservados.</small></b>
            </footer>

            <script src="../js/vali_login.js"></script>
            <?php include("../models/footer_index.php"); ?>
            
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
        </div>
    </div>
</body>