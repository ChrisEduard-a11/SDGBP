<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Recuperar Usuario - SDGBP</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../img/favicon.ico">

    <!-- Bootstrap core CSS-->
    <link href="../css/styles.css" rel="stylesheet" />
    <link href="../css/estilo_login.css" rel="stylesheet" />
    <!--Icons-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Sweetalert -->
    <link rel="stylesheet" type="text/css" href="../sweetalert/sweetalert2.min.css">
    <script src="../sweetalert/sweetalert2.js"></script>

    <!-- font Google -->
    <link href="./css/font_google.css" rel="stylesheet">
    <style>
        .recovery-icon {
            font-size: 3rem;
            color: #f18000;
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
        #inputCI {
            font-size: 1.4rem !important;
            font-weight: 700 !important;
            text-align: center;
            border: 2px solid #e2e8f0 !important;
            border-radius: 16px !important;
            height: 65px !important;
            letter-spacing: 2px;
        }
        #inputCI:focus {
            border-color: #f18000 !important;
            box-shadow: 0 10px 20px rgba(241, 128, 0, 0.1) !important;
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
        .step-label {
            font-size: 11px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .step-item.active .step-label {
            color: #f18000;
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
                        <h1 class="system-name">Recuperación de Usuario</h1>
                    </div>

                    <!-- Indicador de Pasos -->
                    <div class="stepper">
                        <div class="step-item active">
                            <div class="step-circle">1</div>
                            <div class="step-label">ID</div>
                        </div>
                        <div class="step-item">
                            <div class="step-circle">2</div>
                            <div class="step-label">Correo</div>
                        </div>
                        <div class="step-item">
                            <div class="step-circle">3</div>
                            <div class="step-label">2FA</div>
                        </div>
                    </div>

                    <div class="text-center mb-4">
                        <h4 class="fw-bold">Paso 1: Identificación</h4>
                        <p class="text-muted small">Valida tu Cédula de Identidad en el sistema.</p>
                    </div>

                    <div class="form-container">
                        <div class="recovery-icon mb-4">
                            <i class="fas fa-id-card"></i>
                        </div>

                        <form action="../acciones/validar_ci_recu_usuario.php" method="POST">
                            <div class="form-floating mb-4">
                                <input class="form-control" id="inputCI" type="text" placeholder="Cédula de Identidad" name="cedula" required oninput="this.value = this.value.replace(/[^0-9]/g, '');" />
                                <label for="inputCI" class="w-100 text-center">Cédula de Identidad(*)</label>
                            </div>

                            <div class="text-center mt-4 mb-4">
                                <button class="boton" type="submit">Siguiente Paso</button>
                            </div>
                        </form>

                        <?php include("../models/sweetalert.php"); ?>

                        <div class="text-center mt-4">
                            <a class="btn btn-secondary btn-sm" href='recuperar.php'>
                                <i class="fas fa-chevron-left"></i> Volver
                            </a>
                        </div>
                    </div>
                </div>
            </main>

            <footer class="footer_licencia text-center mt-auto pt-4">
                <b><small class="text-muted">&copy; <?php echo date("Y"); ?> SDGBP. Todos los derechos reservados.</small></b>
            </footer>
        </div>
    </div>
</body>
</html>
