<?php
session_start();
if (empty($_SESSION["usuario"])) {
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
        <link rel="canonical" href="https://sdgbp.wuaze.com/<?php echo basename($_SERVER['REQUEST_URI']); ?>" />

        <title>Selecionar Método de Recuperación - SDGBP</title>

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
            /* --- ESTILO ESPECÍFICO PARA RECUPERACIÓN --- */
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
            .form-group label {
                display: block;
                font-weight: 700;
                color: var(--text-main);
                margin-bottom: 12px;
                font-size: 0.9rem;
                text-align: center; /* Centramos el texto para esta vista */
            }

            /* Selector Premium con flecha personalizada */
            select.form-control {
                appearance: none; /* Quita el diseño por defecto del navegador */
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
                background-repeat: no-repeat;
                background-position: right 1.2rem center;
                background-size: 16px 12px;
                padding-right: 3rem !important;
                cursor: pointer;
                height: 55px !important; /* Un poco más alto para que sea fácil tocar en móviles */
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
            select.form-control:hover {
                border-color: var(--primary) !important;
                background-color: var(--primary-light) !important;
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
                        <div class="step-item active">
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
                        <!-- Formulario sin caja -->
                        <div class="form-container">
                            <form action="../acciones/procesar_seleccion_recu.php" method="POST" onsubmit="return validateFormMC()">
                                <div class="form-group">
                                    <label for="metodo">Seleccione el método de recuperación:</label>
                                    <select class="form-control" id="metodo" name="metodo" >
                                        <option value="">Seleccione un método</option>
                                        <option value="correo">Enlace por Correo</option>
                                        <option value="2fa">Verificación 2FA (Código al Correo)</option>
                                        <option value="preguntas">Preguntas de Seguridad</option>
                                    </select>
                                </div>
                                <div class="text-center form-group mt-4 mb-4">  
                                    <button type="submit" class="boton">Continuar</button>
                                </div>
                            </form>
                            <?php include("../models/sweetalert.php"); ?>
                            <!-- Botón para volver a la página principal -->
                            <div class="text-center mt-4">
                                <a class="btn btn-secondary btn-sm" href='recuperar.php'>
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