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

        <title>Desbloquear - SDGBP</title>

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
                            <form action="../acciones/enviar_desbloqueo.php" method="POST" onsubmit="return validateFormSD()">
                                <div class="form-floating mb-3">
                                    <input class="form-control" id="inputUsuario" type="text" placeholder="Nombre de Usuario" name="usuario" />
                                    <label for="inputUsuario">Nombre de Usuario(*):</label>
                                </div>
                                <div class="text-center justify-content-between mt-4 mb-4">
                                    <button class="boton" type="submit">Solicitar Desbloqueo</button>
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