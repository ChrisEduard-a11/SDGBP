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
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Confirmar Código 2FA - SDGBP</title>
    <link rel="icon" type="image/x-icon" href="../img/favicon.ico">
    <link rel="stylesheet" type="text/css" href="../sweetalert/sweetalert2.min.css">
    <script src="../sweetalert/sweetalert2.js"></script>
    <link href="../css/styles.css" rel="stylesheet" />
    <link href="../css/estilo_login.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        .code-input {
            font-size: 2.5rem;
            text-align: center;
            letter-spacing: 0.8rem;
            font-weight: 800;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            padding: 15px;
            width: 100%;
            margin-bottom: 25px;
            background: #f8fafc;
            color: #1e293b;
            transition: all 0.3s ease;
        }
        .code-input:focus {
            border-color: #f18000;
            box-shadow: 0 0 0 4px rgba(241, 128, 0, 0.1);
            outline: none;
            background: #fff;
        }
        #inputCodigo:focus {
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
        .alert-2fa {
            background: #fff8f1;
            border-left: 4px solid #f18000;
            color: #9a4d00;
            border-radius: 12px;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div id="layoutAuthentication">
        <div class="login-image-container d-none d-lg-block">
            <img src="../img/fondo_izq.webp" alt="Imagen de fondo" class="login-image">
        </div>
        <div class="login-form-container">
            <main>
                <div class="form-content animate__animated animate__fadeIn">
                    <div class="text-center mb-4">
                        <img src="../img/Logo-OP2_V4.webp" alt="Logo" class="logo mb-2" style="max-width: 60px;">
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
                        <h4 class="fw-bold">Paso 3: Verificación 2FA</h4>
                        <p class="text-muted small">Hemos enviado un código de 6 dígitos a tu correo.</p>
                    </div>
                    
                    <div class="alert alert-2fa shadow-sm mb-4">
                        <p class="mb-0">
                            <i class="fas fa-shield-alt me-2"></i> Por seguridad, ingresa el <b>código de 6 dígitos</b> que enviamos a tu correo electrónico.
                        </p>
                    </div>

                    <div class="form-container">
                        <form action="../acciones/validar_2fa_recu.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted small text-uppercase">Código de Seguridad</label>
                                <input type="text" name="codigo" class="code-input" maxlength="6" placeholder="000000" autocomplete="off" required>
                            </div>
                            
                            <div class="text-center mt-4">
                                <button class="boton w-100 py-3" type="submit">Validar Identidad</button>
                            </div>

                            <div class="text-center mt-4">
                                <a class="btn btn-link text-decoration-none small text-muted" href="seleccionar_meto_recu.php">
                                    <i class="fas fa-arrow-left me-1"></i> Usar otro método
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
            <?php include("../models/footer_index.php"); ?>
        </div>
    </div>
    <?php include("../models/sweetalert.php"); ?>
</body>
</html>
