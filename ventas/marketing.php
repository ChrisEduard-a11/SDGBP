<?php
session_start();
if (isset($_SESSION['carrito'])) {
    unset($_SESSION['carrito']); 
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>EURIPYS 2024 - Tienda</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="icon" type="image/x-icon" href="../img/favicon.ico">

    <style>
        :root {
            --brand-orange: #f18000;
            --brand-dark: #1a1c1e;
            --brand-light: #f8f9fa;
            --card-bg: #ffffff;
            --text-main: #333333;
            --text-muted: #6c757d;
        }

        /* --- MODO OSCURO (REPARADO PARA VISIBILIDAD) --- */
        body.dark-mode {
            background-color: #0f0f0f !important;
            color: #ffffff !important;
            --card-bg: #1e1e1e;
        }

        /* Forzar textos blancos en modo oscuro */
        body.dark-mode .card-title, 
        body.dark-mode .category-title, 
        body.dark-mode h1, body.dark-mode h3, body.dark-mode h5, body.dark-mode h6,
        body.dark-mode .nav-link,
        body.dark-mode .form-label,
        body.dark-mode .modal-title {
            color: #ffffff !important;
        }

        body.dark-mode .card-text, 
        body.dark-mode .text-muted, 
        body.dark-mode small {
            color: #d1d1d1 !important;
        }

        /* Inputs en modo oscuro */
        body.dark-mode .form-control {
            background-color: #2d2d2d !important;
            border-color: #444 !important;
            color: #ffffff !important;
        }
        body.dark-mode .input-group-text {
            background-color: #3d3d3d !important;
            color: #ffffff !important;
            border-color: #444 !important;
        }

        /* Tarjetas en modo oscuro */
        body.dark-mode .card {
            background-color: var(--card-bg) !important;
            border: 1px solid #3d3d3d !important;
            box-shadow: 0 10px 20px rgba(0,0,0,0.4);
        }

        /* Navbar en modo oscuro */
        body.dark-mode .navbar {
            background: rgba(15, 15, 15, 0.98) !important;
            border-bottom: 1px solid #333;
        }
        body.dark-mode .navbar-brand span { color: #ffffff !important; }

        /* Estilos Generales */
        body {
            background-color: #fdfdfd;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text-main);
            transition: all 0.3s ease;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 12px 0;
            z-index: 1050;
        }

        .hero {
            background: linear-gradient(135deg, var(--brand-dark) 0%, #2c2f33 100%);
            color: #ffffff;
            padding: 80px 0;
            text-align: center;
        }

        .search-container {
            background: var(--card-bg);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            margin-top: -50px;
            position: relative;
            z-index: 10;
        }

        .pill-item {
            white-space: nowrap;
            padding: 8px 20px;
            border-radius: 50px;
            background: #f0f0f0;
            color: #555;
            cursor: pointer;
            transition: 0.3s;
            font-weight: 600;
        }
        body.dark-mode .pill-item { background: #333; color: #eee; }
        .pill-item.active { background: var(--brand-orange) !important; color: white !important; }

        .card {
            border: none;
            border-radius: 20px;
            transition: all 0.3s ease;
            background: var(--card-bg);
            height: 100%;
        }
        .card:hover { transform: translateY(-10px); }

        /* Botones Flotantes */
        #btnCarritoFlotante, #darkModeToggle {
            width: 60px; height: 60px;
            border-radius: 50%;
            position: fixed;
            right: 30px;
            z-index: 1000;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }
        #btnCarritoFlotante { bottom: 30px; background: var(--brand-orange); color: white; }
        #darkModeToggle { bottom: 105px; background: var(--brand-dark); color: white; }
        body.dark-mode #darkModeToggle { background: #ffffff; color: #1a1c1e; }

        .footer_licencia { background: #1a1c1e; padding: 40px 0; color: white; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="../index.php">
                <img src="../img/Logo-OP2_V4.webp" alt="Logo" width="45" class="me-2">
                <span>EURIPYS 2024</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#compras">Productos</a></li>
                    <li class="nav-item"><a class="nav-link" href="../index.php#contacto">Contacto</a></li>
                    <li class="nav-item ms-lg-3"><a class="btn btn-warning btn-sm px-4 py-2 text-white fw-bold" href="../vistas/login.php">Iniciar Sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="hero mb-5 pt-5 mt-5">
        <div class="container mt-4">
            <img src="../img/Logo-OP2_V4.webp" alt="Logo" class="img-fluid mb-4 animate__animated animate__zoomIn" style="max-width: 120px;">
            <h1 class="animate__animated animate__fadeInUp">Tienda EURIPYS</h1>
            <p class="animate__animated animate__fadeInUp">Innovación y producción desde la UPTAG.</p>
        </div>
    </div>

    <div class="container">
        <div class="search-container mb-5">
            <div class="row g-3">
                <div class="col-md-12">
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-transparent border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="busquedaProducto" class="form-control border-start-0" placeholder="¿Qué estás buscando hoy?" oninput="filtrarProductosPorNombre()">
                    </div>
                    <div class="filter-pills d-flex gap-2 overflow-auto pb-2" id="pillsContainer">
                        <div class="pill-item active" onclick="filtrarPorCategoria('')">Todos</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="compras" class="container mb-5">
        <div id="productosGrid">
            <div class="text-center py-5">
                <div class="spinner-border text-warning" role="status"></div>
                <p class="mt-2">Cargando catálogo...</p>
            </div>
        </div>
    </div>

    <button type="button" id="darkModeToggle" title="Cambiar Tema">
        <i class="fas fa-moon fs-5"></i>
    </button>

    <button type="button" id="btnCarritoFlotante" data-bs-toggle="modal" data-bs-target="#carritoModal">
        <i class="fas fa-shopping-cart fs-4"></i>
        <span id="carritoBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">0</span>
    </button>

    <div class="modal fade" id="carritoModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="fw-bold mb-0">Mi Carrito</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="carritoContenido">
                    <p class="text-center py-5">Tu carrito está vacío.</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Seguir Comprando</button>
                    <button type="button" class="btn btn-warning px-4 text-white fw-bold" id="btnAbrirPago">Proceder al Pago</button>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer_licencia mt-5">
        <div class="container text-center">
            <p class="mb-3">Licencia Creative Commons BY-NC 4.0</p>
            <small>© <?php echo date("Y"); ?> EURIPYS 2024. UPTAG.</small>
        </div>
    </footer>

    <script src="../js/productos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const btnDark = document.getElementById('darkModeToggle');
        const iconDark = btnDark.querySelector('i');

        function aplicarTema(tema) {
            if (tema === 'dark') {
                document.body.classList.add('dark-mode');
                iconDark.classList.replace('fa-moon', 'fa-sun');
            } else {
                document.body.classList.remove('dark-mode');
                iconDark.classList.replace('fa-sun', 'fa-moon');
            }
        }

        // Leer preferencia al cargar
        aplicarTema(localStorage.getItem('theme'));

        btnDark.addEventListener('click', () => {
            const nuevoTema = document.body.classList.contains('dark-mode') ? 'light' : 'dark';
            aplicarTema(nuevoTema);
            localStorage.setItem('theme', nuevoTema);
        });

        function actualizarCarritoBadge() {
            fetch('obtener_carrito.php')
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('carritoBadge');
                    const totalItems = data.carrito ? data.carrito.length : 0;
                    badge.textContent = totalItems;
                    badge.style.display = totalItems > 0 ? 'block' : 'none';
                });
        }
        document.addEventListener('DOMContentLoaded', actualizarCarritoBadge);
    </script>
</body>
</html>