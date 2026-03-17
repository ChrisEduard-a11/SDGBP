<?php
    session_start();
    session_destroy();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Acceso Denegado</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Roboto', Arial, sans-serif;
            background: linear-gradient(135deg, #f8d7da 0%, #fff3cd 100%);
            min-height: 100vh;
            color: #842029;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .container-denegado {
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card-denegado {
            border: none;
            border-radius: 18px;
            box-shadow: 0 12px 40px rgba(0,0,0,0.18), 0 1.5px 6px rgba(220,53,69,0.08);
            background: #fff;
            padding: 3rem 2rem 2.5rem 2rem;
            max-width: 470px;
            margin: auto;
            animation: fadeInDown 1s;
            position: relative;
        }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-40px);}
            to { opacity: 1; transform: translateY(0);}
        }
        .logo-empresa {
            width: 80px;
            height: 80px;
            object-fit: contain;
            margin-bottom: 0.5rem;
            margin-top: -2.5rem;
            border-radius: 50%;
            box-shadow: 0 2px 10px rgba(241,129,0,0.13);
            background: #fff;
            border: 2px solid #f18100;
        }
        .nombre-sistema {
            font-size: 1.25rem;
            font-weight: bold;
            color: #f18100;
            margin-bottom: 1.2rem;
            letter-spacing: 1px;
            text-shadow: 0 1px 0 #fff, 0 2px 6px #f1810033;
        }
        .icono-denegado {
            font-size: 4rem;
            color: #dc3545;
            margin-bottom: 1rem;
            animation: shake 0.7s;
        }
        @keyframes shake {
            10%, 90% { transform: translateX(-2px);}
            20%, 80% { transform: translateX(4px);}
            30%, 50%, 70% { transform: translateX(-8px);}
            40%, 60% { transform: translateX(8px);}
        }
        .btn-volver, .btn-contacto {
            border: none;
            font-weight: bold;
            transition: background 0.2s, color 0.2s;
        }
        .btn-volver {
            background-color: #f18000;
            color: #fff;
        }
        .btn-volver:hover {
            background-color: #d26900;
            color: #fff;
        }
        .btn-contacto {
            background-color: #fff;
            color: #dc3545;
            border: 2px solid #dc3545;
            margin-top: 10px;
        }
        .btn-contacto:hover {
            background-color: #dc3545;
            color: #fff;
        }
        .ayuda-box {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            border-radius: 8px;
            padding: 1rem 1.2rem;
            margin-bottom: 1.2rem;
            color: #842029;
            font-size: 1rem;
            text-align: left;
            box-shadow: 0 2px 8px rgba(220,53,69,0.07);
        }
        .footer_licencia {
            font-size: 0.9rem;
            color: #fff;
            text-align: center;
            background-color: #606060;
            padding: 1.5rem 1rem 0.5rem 1rem;
            border-top: 3px solid #f18100;
            width: 100%;
            margin-top: auto;
        }
        .footer_licencia a {
            color: #f18100;
            text-decoration: none;
            font-weight: bold;
        }
        .footer_licencia a:hover {
            text-decoration: underline;
        }
        .footer_licencia img {
            height: 22px !important;
            margin-left: 3px;
            vertical-align: text-bottom;
        }
        .watermark {
            position: absolute;
            bottom: 12px;
            right: 18px;
            opacity: 0.09;
            font-size: 2.5rem;
            pointer-events: none;
            user-select: none;
            font-weight: bold;
            letter-spacing: 2px;
        }
    </style>
</head>
<body>
    <div class="container-denegado">
        <div class="card-denegado text-center">
            <!-- LOGO DE LA EMPRESA -->
            <img src="../img/Logo-OP2_V4.webp" alt="Logo Empresa" class="logo-empresa">
            <!-- NOMBRE DEL SISTEMA -->
            <div class="nombre-sistema">Sistema de Gestión de Bienes y Pagos</div>
            <div>
                <i class="fas fa-ban icono-denegado"></i>
            </div>
            <h2 class="mb-3">Acceso Denegado</h2>
            <div class="ayuda-box mb-4">
                <i class="fas fa-info-circle"></i>
                <span>
                    No tienes permisos para acceder a esta página.<br>
                    Si crees que esto es un error, contacta al administrador o intenta iniciar sesión nuevamente.
                </span>
            </div>
            <!-- Botón principal grande centrado -->
            <div class="mb-3 d-grid gap-2">
                <a href="login.php" class="btn btn-volver btn-lg py-3">
                    <i class="fas fa-sign-in-alt fa-lg me-2"></i> Iniciar Sesión
                </a>
            </div>
            <!-- Botón de soporte como link de ayuda debajo -->
            <div class="mb-4">
                <a href="https://wa.me/584129796940?text=Hola%2C%20soy%20usuario%20del%20Sistema%20de%20Gesti%C3%B3n%20de%20Bienes%20y%20Pagos.%20Tengo%20acceso%20denegado%20y%20necesito%20ayuda%20para%20ingresar.%20Por%20favor%2C%20ind%C3%ADqueme%20los%20pasos%20a%20seguir." 
                target="_blank" 
                class="btn btn-link text-success fw-bold" 
                style="text-decoration: none;">
                    <i class="fab fa-whatsapp fa-lg me-1"></i>
                    ¿Necesitas ayuda? Contactar Soporte por WhatsApp
                </a>
            </div>
            <div class="watermark">SDGBP</div>
        </div>
    </div>
    <footer class="footer_licencia mt-4">
        <p class="mb-0 text-center">
            Este trabajo está licenciado bajo 
            <a href="https://creativecommons.org/licenses/by-nc/4.0/?ref=chooser-v1" target="_blank" rel="license noopener noreferrer">
                Creative Commons BY-NC 4.0
                <img src="https://mirrors.creativecommons.org/presskit/icons/cc.svg?ref=chooser-v1" alt="CC">
                <img src="https://mirrors.creativecommons.org/presskit/icons/by.svg?ref=chooser-v1" alt="BY">
                <img src="https://mirrors.creativecommons.org/presskit/icons/nc.svg?ref=chooser-v1" alt="NC">
            </a>
        </p>
        <div class="row mt-3">
            <div class="col text-center">
                <small style="color: #000;">&copy; <?php echo date("Y"); ?> Sistema de Gestión de Bienes y Pagos. Todos los derechos reservados.</small>
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>