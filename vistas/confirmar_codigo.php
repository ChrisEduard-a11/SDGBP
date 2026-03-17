<?php
session_start();
if (empty($_SESSION["user"])) {
    header("Location: denegado_a.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Verificar Código - SDGBP</title>
    <link rel="icon" type="image/x-icon" href="../img/favicon.ico">
    <link rel="stylesheet" type="text/css" href="../sweetalert/sweetalert2.min.css">
    <script src="../sweetalert/sweetalert2.js"></script>
    <link href="../css/styles.css" rel="stylesheet" />
    <link href="../css/estilo_login.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        .code-input {
            font-size: 2rem;
            text-align: center;
            letter-spacing: 0.5rem;
            font-weight: bold;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            padding: 10px;
            width: 100%;
            margin-bottom: 20px;
        }
        .code-input:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
            outline: none;
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
                        <img src="../img/Logo-OP2_V4.webp" alt="Logo Empresa" class="logo mb-3">
                        <h1 class="system-name">Verificación de Seguridad</h1>
                    </div>
                    
                    <div class="alert alert-info border-0 shadow-sm mb-4">
                        <i class="fas fa-paper-plane me-2"></i> Hemos enviado un <b>código de 6 dígitos</b> a tu correo electrónico. Por favor, ingrésalo para confirmar el cambio de contraseña.
                    </div>

                    <div class="form-container">
                        <form action="../acciones/verificar_y_actualizar.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted small text-uppercase">Código de Verificación</label>
                                <input type="text" name="codigo" class="code-input" maxlength="6" placeholder="000000" autocomplete="off" required>
                            </div>
                            
                            <div class="text-center mt-4">
                                <button class="boton w-100" type="submit">Verificar y Activar</button>
                            </div>

                            <div class="text-center mt-4">
                                <a class="btn btn-link text-decoration-none small" href="nueva_clave.php">
                                    <i class="fas fa-arrow-left me-1"></i> Volver a intentar
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
