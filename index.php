<?php
session_start();
if (isset($_SESSION['user'])) {
    header('Location: vistas/inicio.php'); 
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>SDGBP - EURIPYS</title>
    
    <!--<link rel="canonical" href="https://www.sdgbp.wuaze.com/" />-->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="sweetalert/sweetalert2.min.css">
    
    <link href="css/styles.css" rel="stylesheet" /> 
    <link rel="stylesheet" type="text/css" href="css/estilos_index.css"> 
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    
    <script src="sweetalert/sweetalert2.js"></script>

    <style>
        :root {
            --brand-orange: #f18000;
            --brand-dark: #1a1c1e;
            --soft-bg: #f8f9fa;
            --card-bg: #ffffff;
            --text-color: #444;
        }

        /* --- MODO OSCURO --- */
        body.dark-mode {
            background-color: #0f0f0f;
            color: #efefef;
            --brand-dark: #ffffff;
            --card-bg: #1e1e1e;
            --text-color: #d1d1d1;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text-color);
            background-color: #fff;
            scroll-behavior: smooth;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* Navbar */
        .navbar {
            background: rgba(255, 255, 255, 0.9) !important;
            backdrop-filter: blur(15px);
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 18px 0;
            transition: all 0.3s ease;
        }
        body.dark-mode .navbar {
            background: rgba(15, 15, 15, 0.9) !important;
            border-bottom: 1px solid #333;
        }
        .navbar-brand span {
            font-weight: 800;
            color: var(--brand-dark);
            letter-spacing: -0.5px;
        }
        .nav-link {
            color: var(--brand-dark) !important;
            font-weight: 600;
            font-size: 0.9rem;
            margin: 0 10px;
        }
        .nav-link:hover { color: var(--brand-orange) !important; }

        /* Hero */
        .hero {
            min-height: 100vh;
            padding-top: 120px;
            background: radial-gradient(circle at top right, rgba(241, 128, 0, 0.08), transparent),
                        radial-gradient(circle at bottom left, rgba(241, 128, 0, 0.05), transparent);
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .hero h1 {
            font-weight: 800;
            font-size: clamp(2.5rem, 5vw, 4rem);
            color: var(--brand-dark);
            line-height: 1.1;
            margin-bottom: 30px;
        }

        /* Icon Box */
        .icon-box {
            padding: 40px;
            border: 1px solid #f0f0f0;
            border-radius: 24px;
            background: var(--card-bg);
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            height: 100%;
            text-align: left;
        }
        body.dark-mode .icon-box { border-color: #333; }
        .icon-box:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.05);
            border-color: var(--brand-orange);
        }
        .icon-box i {
            font-size: 2.5rem;
            color: var(--brand-orange);
            margin-bottom: 25px;
            display: block;
        }
        .icon-box h5 { font-weight: 700; color: var(--brand-dark); }

        /* Botones */
        .btn-hero {
            background: var(--brand-orange);
            color: white;
            padding: 16px 40px;
            border-radius: 14px;
            font-weight: 700;
            box-shadow: 0 10px 20px rgba(241, 128, 0, 0.2);
            border: none;
            transition: 0.3s;
        }
        .btn-hero:hover {
            transform: translateY(-3px);
            background: #d97300;
            color: white;
        }
        body.dark-mode .btn-outline-dark { color: #fff !important; border-color: #fff !important; }

        .section-title {
            font-weight: 800;
            font-size: 2.5rem;
            margin-bottom: 50px;
            color: var(--brand-dark);
        }
        
        .marketing-section {
            background: #1a1c1e;
            color: white;
            border-radius: 40px;
            padding: 80px 40px;
        }

        .form-control {
            border-radius: 12px;
            padding: 14px;
            background: #fcfcfc;
            border: 1px solid #eee;
        }
        body.dark-mode .form-control {
            background: #252525;
            border-color: #444;
            color: #fff;
        }
        body.dark-mode .card { background-color: var(--card-bg); }

        /* Estilo para los iconos en el modal */
        .icon-circle {
            width: 45px;
            height: 45px;
            background: rgba(241, 128, 0, 0.1);
            color: var(--brand-orange);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        /* Efecto al pasar el mouse por las opciones */
        .shadow-sm-hover:hover {
            background-color: #fff4e6 !important;
            transform: translateX(5px);
            transition: all 0.3s ease;
        }

        /* Ajuste Modo Oscuro para el modal nuevo */
        body.dark-mode .list-group-item {
            background-color: #252525;
            color: white;
        }
        body.dark-mode .list-group-item:hover {
            background-color: #333 !important;
        }
        body.dark-mode .icon-circle {
            background: rgba(241, 128, 0, 0.2);
        }

        hr { opacity: 0.05; margin: 60px 0; }

        /* Toggles Flotantes */
        .floating-controls {
            position: fixed;
            bottom: 20px;
            right: 30px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            z-index: 999;
        }
        .btn-float {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 15px rgba(0,0,0,0.2);
            transition: 0.3s;
        }
        #darkModeToggle { background: #1a1c1e; color: white; }
        body.dark-mode #darkModeToggle { background: white; color: #1a1c1e; }
    </style>
</head>
<body>
    
    <nav class="navbar navbar-expand-lg fixed-top"> 
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="img/Logo-OP2_V4.webp" alt="Logo" width="45" class="me-2">
                <span>EURIPYS 2024, C.A.</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#marketing">Marketing</a></li>
                    <li class="nav-item"><a class="nav-link" href="#servicios">Servicios</a></li>
                    <li class="nav-item"><a class="nav-link" href="#funcionalidades">Funcionalidades</a></li>
                    <li class="nav-item"><a class="nav-link" href="#historia">Historia</a></li>
                    <li class="nav-item"><a class="nav-link" href="#quienes-somos">Nosotros</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contacto">Contacto</a></li>
                    <li class="nav-item ms-lg-3">
                        <a class="btn btn-hero py-2 px-4" href="vistas/login.php"><i class="fas fa-sign-in-alt me-2"></i>Acceder</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="hero">
        <img src="img/logo_animado3d.gif" alt="Fondo" style="position: absolute; width: 100%; height: 100%; object-fit: cover; z-index: 0; opacity: 0.05;">
        
        <div class="container" style="position: relative; z-index: 2;">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <span class="badge mb-3 px-3 py-2" style="background: rgba(241, 128, 0, 0.1); color: var(--brand-orange); border-radius: 50px; font-weight: 700;">PRODUCCIÓN E INVERSIÓN</span>
                    <h1 class="animate__animated animate__fadeInLeft">
                        Empresa Universitaria <span style="color: var(--brand-orange);">EURIPYS 2024</span>.
                    </h1>
                    <p class="lead mb-5 text-muted" style="font-size: 1.25rem;">Rental de inversión, producción y servicios de la Universidad Politécnica Territorial Alonso Gamero.</p>
                    <div class="d-flex gap-3 animate__animated animate__fadeInUp">
                        <a href="vistas/login.php" class="btn btn-hero">Comenzar Ahora</a>
                        <button class="btn btn-outline-dark btn-hero bg-transparent text-dark border-2" data-bs-toggle="modal" data-bs-target="#manualModal">Manual de Usuario</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalBienvenida" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 25px; overflow: hidden;">
                <div class="row g-0">
                    <div class="col-md-5 d-none d-md-flex align-items-center justify-content-center" style="background: #f8f9fa; border-right: 1px solid #eee;">
                        <img src="img/Logo-OP2_V4.webp" alt="Logo EURIPYS" class="img-fluid p-5 animate__animated animate__pulse animate__infinite">
                    </div>
                    
                    <div class="col-md-7 bg-white dark-mode-card">
                        <div class="modal-header border-0 pb-0">
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body px-4 pt-0 pb-4">
                            <div class="text-center text-md-start">
                                <h2 class="fw-800 mb-1" style="color: var(--brand-dark);">¡Bienvenido!</h2>
                                <p class="text-muted mb-4">Explora las secciones de nuestro portal corporativo:</p>
                            </div>

                            <div class="list-group list-group-flush mb-4">
                                <a href="#servicios" class="list-group-item list-group-item-action border-0 d-flex align-items-center rounded-3 mb-2 py-3 shadow-sm-hover" data-bs-dismiss="modal">
                                    <div class="icon-circle me-3"><i class="fas fa-concierge-bell"></i></div>
                                    <div><span class="fw-bold d-block">Servicios</span><small class="text-muted">Qué ofrecemos para ti.</small></div>
                                </a>
                                <a href="#funcionalidades" class="list-group-item list-group-item-action border-0 d-flex align-items-center rounded-3 mb-2 py-3 shadow-sm-hover" data-bs-dismiss="modal">
                                    <div class="icon-circle me-3"><i class="fas fa-cogs"></i></div>
                                    <div><span class="fw-bold d-block">Funcionalidades</span><small class="text-muted">De nuestra empresa.</small></div>
                                </a>
                                <a href="#historia" class="list-group-item list-group-item-action border-0 d-flex align-items-center rounded-3 mb-2 py-3 shadow-sm-hover" data-bs-dismiss="modal">
                                    <div class="icon-circle me-3"><i class="fas fa-book-open"></i></div>
                                    <div><span class="fw-bold d-block">Historia</span><small class="text-muted">Nuestra trayectoria.</small></div>
                                </a>
                                <a href="#contacto" class="list-group-item list-group-item-action border-0 d-flex align-items-center rounded-3 py-3 shadow-sm-hover" data-bs-dismiss="modal">
                                    <div class="icon-circle me-3"><i class="fas fa-envelope"></i></div>
                                    <div><span class="fw-bold d-block">Contacto</span><small class="text-muted">Atención personalizada.</small></div>
                                </a>
                            </div>

                            <div class="d-grid">
                                <button type="button" class="btn btn-hero py-3" data-bs-dismiss="modal">Comenzar Navegación</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="marketing" class="container my-5">
        <div class="marketing-section shadow-xl">
            <div class="row align-items-center">
                <div class="col-md-6 mb-4 mb-md-0">
                    <img src="img/marketing.webp" class="img-fluid rounded-4 shadow" alt="Marketing">
                </div>
                <div class="col-md-6 ps-lg-5">
                    <h2 class="text-white fw-bold mb-4">Marketing de la Empresa</h2>
                    <p class="text-white-50">En EURIPYS 2024, C.A., el marketing es una herramienta clave para promover nuestros productos y servicios. Nos enfocamos en estrategias innovadoras que destacan la calidad y el impacto de nuestras soluciones en el mercado nacional e internacional.</p>
                    <p class="text-white-50">Nuestro equipo de marketing trabaja para fortalecer la marca UPTAG, utilizando campañas digitales y eventos estratégicos.</p>
                    <div class="mt-4">
                        <a href="ventas/marketing.php" class="btn btn-warning fw-bold px-4 py-2 text-dark">Ver Más</a>
                        <a href="ventas/marketing.php#compras" class="btn btn-outline-light ms-2 px-4 py-2">Ir a Compras</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="servicios" class="container py-5">
        <div class="text-center"><h2 class="section-title">Nuestros Servicios</h2></div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="icon-box">
                    <i class="fas fa-laptop-code"></i>
                    <h5>Formación y Consultoría</h5>
                    <p class="text-muted small">Brindamos servicios de formación y consultoría en diversas áreas de las ciencias sociales.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="icon-box">
                    <i class="fas fa-network-wired"></i>
                    <h5>Emprendimiento Institucional</h5>
                    <p class="text-muted small">Promovemos el emprendimiento dentro de la Universidad Politécnica Territorial Alonso Gamero (UPTAG).</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="icon-box">
                    <i class="fas fa-shield-alt"></i>
                    <h5>Gestión de Unidades de Producción</h5>
                    <p class="text-muted small">Mejoramos la gestión administrativa de las unidades de producción universitarias.</p>
                </div>
            </div>
        </div>
    </div>

    <div id="funcionalidades" class="container py-5">
        <div class="text-center"><h2 class="section-title">Nuestras Funcionalidades</h2></div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="icon-box">
                    <i class="fas fa-laptop-code"></i>
                    <h5>Capacitación</h5>
                    <p class="text-muted small">Ofrecemos programas de capacitación en diversas áreas de las ciencias sociales.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="icon-box">
                    <i class="fas fa-network-wired"></i>
                    <h5>Asesoríal</h5>
                    <p class="text-muted small">Brindamos asesoría a instituciones y organizaciones en temas relacionados con las ciencias sociales.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="icon-box">
                    <i class="fas fa-shield-alt"></i>
                    <h5>Emprendimiento</h5>
                    <p class="text-muted small">Promovemos y apoyamos el emprendimiento institucional.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="icon-box">
                    <i class="fas fa-shield-alt"></i>
                    <h5>Gestión</h5>
                    <p class="text-muted small">Mejoramos la gestión administrativa de las unidades de producción universitarias.</p>
                </div>
            </div>
        </div>
    </div>

    <div id="historia" class="container py-5">
        <div class="text-center"><h2 class="section-title">Nuestra Historia</h2></div>
        <div class="row align-items-center">
            <div class="col-md-6 mb-4 mb-md-0 animate__animated animate__fadeInLeft">
                <img src="img/Logo-OP2_V4.webp" class="img-fluid rounded-4 shadow" alt="Historia de EURIPYS 2024, C.A.">
            </div>
            <div class="col-md-6 animate__animated animate__fadeInRight">
                <p class="text-justify" style="line-height: 1.8; font-size: 1.1rem;">
                    EURIPYS 2024, C.A nació como una iniciativa para fortalecer el patrimonio financiero de la Universidad Politécnica Territorial Alonso Gamero (UPTAG)...
                </p>
                <p class="text-justify" style="line-height: 1.8; font-size: 1.1rem;">
                    En 2016, se inició la formalización del proyecto con el apoyo del departamento jurídico de la universidad, culminando en un acta constitutiva... Liderada por la profesora Yohana Colina, la empresa fue constituida legalmente el 28 de octubre de 2024 bajo la denominación EURIPYS 2024, C.A.
                </p>
                <p class="text-justify" style="line-height: 1.8; font-size: 1.1rem;">
                    Desde diciembre de 2024, EURIPYS 2024, C.A ha trabajado para promover el emprendimiento institucional y mejorar la gestión administrativa de las unidades de producción universitarias...
                </p>
            </div>
        </div>
    </div>

    <div id="quienes-somos" class="container py-5">
        <div class="row g-5">
            <div class="col-md-4">
                <h2 class="fw-bold mb-4">Misión</h2>
                <p class="text-muted lead">Contribuir al sostenimiento financiero de la UPTAG en la comercialización de productos y servicios generados por la universidad, para facilitar la incorporación productiva a la sociedad a través de las unidades de producción universitarias.</p>
            </div>
            <div class="col-md-4">
                <h2 class="fw-bold mb-4">Visión</h2>
                <p class="text-muted lead">Convertirse en una reconocida empresa universitaria que reúne unidades teóricas, unidades técnicas, investigadores y trabajadores en actividades productivas desarrollando el campo científico.</p>
            </div>
            <div class="col-md-4">
                <h2 class="fw-bold mb-4">Valores</h2>
                <ul class="text-muted lead list-unstyled">
                    <li><i class="fas fa-check-circle text-warning me-2"></i> Institucionalidad.</li>
                    <li><i class="fas fa-check-circle text-warning me-2"></i> Idoneidad.</li>
                    <li><i class="fas fa-check-circle text-warning me-2"></i> Integridad.</li>
                    <li><i class="fas fa-check-circle text-warning me-2"></i> Calidad.</li>
                </ul>
            </div>
        </div>
    </div>

    <div id="contacto" class="container py-5">
        <div class="row g-5">
            <div class="col-md-5">
                <h2 class="fw-bold mb-4">Contáctanos</h2>
                <div class="d-flex mb-4">
                    <div class="me-3 text-warning"><i class="fas fa-map-marker-alt fa-2x"></i></div>
                    <div>
                        <h6>Dirección</h6>
                        <p class="small text-muted">Av. Libertador, Edif. UPTAG, planta baja, área de administración, sector los Orumos, Coro, Municipio Miranda del Estado Falcón.</p>
                    </div>
                </div>
                <div class="d-flex mb-4">
                    <div class="me-3 text-warning"><i class="fas fa-phone fa-2x"></i></div>
                    <div>
                        <h6>Telefonos</h6>
                        <p class="small text-muted">0268-2515165 - 0412-2916480.</p>
                    </div>
                </div>
                <div class="d-flex mb-4">
                    <div class="me-3 text-warning"><i class="fas fa-envelope fa-2x"></i></div>
                    <div>
                        <h6>Correo</h6>
                        <p class="small text-muted">euripys2024ca@gmail.com</p>
                    </div>
                </div>
                <div class="ratio ratio-16x9 mt-4">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2765.395539009471!2d-69.66365076613361!3d11.417714949859446!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8e842b3a0c5dcec9%3A0x2cb4d2c53b98bc27!2sEuripys%202024%20C.A!5e0!3m2!1ses-419!2sve!4v1770157784253!5m2!1ses-419!2sve" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>                </div>
                </div>
            <div class="col-md-7">
                <div class="card border-0 shadow-sm p-4 rounded-4">
                    <form id="contactForm" action="acciones/procesar_contacto.php" method="POST" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Nombre</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Tu nombre" required>
                                <div class="invalid-feedback">Por favor ingresa tu nombre.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Correo Electrónico</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Tu correo electrónico" required>
                                <div class="invalid-feedback">Por favor ingresa un correo con un dominio válido.</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Mensaje</label>
                                <textarea class="form-control" id="mensaje" name="mensaje" rows="4" placeholder="Escribe tu mensaje aquí" required></textarea>
                                <div class="invalid-feedback">Por favor escribe tu mensaje.</div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-hero w-100">Enviar Mensaje</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="manualModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content" style="border-radius:1.5rem; overflow:hidden;">
                <div class="modal-header bg-dark text-white border-0">
                    <h5 class="modal-title"><i class="fa-solid fa-book me-2"></i> Manual de Usuario</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0" style="height:80vh;">
                    <iframe src="manuales/Manual_del_Usuario.pdf" width="100%" height="100%" style="border:none;"></iframe>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer_licencia mt-4 py-4 bg-dark text-white">
        <div class="container ">
            <p class="mb-2 text-center">
                Este trabajo está licenciado bajo 
                <a href="https://creativecommons.org/licenses/by-nc/4.0/?ref=chooser-v1" target="_blank" rel="license noopener noreferrer" class="text-warning">
                    Creative Commons BY-NC 4.0
                </a>
            </p>
            <b><small>© <?php echo date("Y"); ?> Sistema de Gestión de Bienes y Pagos. Todos los derechos reservados.</small></b>
        </div>
    </footer>

    <div class="floating-controls">
        <button id="darkModeToggle" class="btn-float" title="Cambiar Tema">
            <i class="fas fa-moon"></i>
        </button>
        <button class="btn-float text-white" style="background:var(--brand-orange);" onclick="scrollToTop()">
            <i class="fas fa-arrow-up"></i>
        </button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // --- LÓGICA MODO OSCURO ---
    const btnDark = document.getElementById('darkModeToggle');
    const iconDark = btnDark.querySelector('i');

    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-mode');
        iconDark.classList.replace('fa-moon', 'fa-sun');
    }

    btnDark.addEventListener('click', () => {
        document.body.classList.toggle('dark-mode');
        const isDark = document.body.classList.contains('dark-mode');
        iconDark.classList.replace(isDark ? 'fa-moon' : 'fa-sun', isDark ? 'fa-sun' : 'fa-moon');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
    });

    // Validación de formulario
    document.querySelectorAll('#contactForm input, #contactForm textarea').forEach((field) => {
        field.addEventListener('input', () => {
            if (field.id === 'email') {
                const regex = /^[a-zA-Z0-9._%+-]+@(claro\.com|claro\.com\.ve|gmail\.com|hotmail\.com)$/; 
                if (regex.test(field.value)) {
                    field.classList.remove('is-invalid');
                    field.classList.add('is-valid');
                } else {
                    field.classList.remove('is-valid');
                    field.classList.add('is-invalid');
                }
            } else {
                if (field.checkValidity()) {
                    field.classList.remove('is-invalid');
                    field.classList.add('is-valid');
                } else {
                    field.classList.remove('is-valid');
                    field.classList.add('is-invalid');
                }
            }
        });
    });

    document.getElementById('contactForm').addEventListener('submit', function(event) {
        if (!this.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        this.classList.add('was-validated');
    });

    // Seguridad
    document.addEventListener('keydown', function(e) {
        if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && e.key.toLowerCase() === 'i') || (e.ctrlKey && e.key.toLowerCase() === 'u')) {
            e.preventDefault();
            return false;
        }
    });
    document.addEventListener('contextmenu', function(e) { e.preventDefault(); });
    
    function scrollToTop() { window.scrollTo({ top: 0, behavior: 'smooth' }); }
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
        if (!sessionStorage.getItem('modalMostrado')) {
            var miModal = new bootstrap.Modal(document.getElementById('modalBienvenida'));
            setTimeout(function() {
                miModal.show();
                sessionStorage.setItem('modalMostrado', 'true');
            }, 100);
        }
    });
    </script>
    <?php if (isset($_SESSION["estatus"]) && isset($_SESSION["mensaje"])): ?>
        <script>
            Swal.fire({
                icon: '<?php echo htmlspecialchars($_SESSION["estatus"]); ?>',
                title: '<?php echo htmlspecialchars($_SESSION["mensaje"]); ?>', 
                showConfirmButton: true,
                confirmButtonText: 'OK',
                showConfirmButton: true,
                confirmButtonText: 'OK',
                confirmButtonColor: '#f18000'
            });
        </script>
    <?php
        unset($_SESSION["estatus"]);
        unset($_SESSION["mensaje"]);
    ?>              
    <?php endif; ?>
    
</body>
</html>