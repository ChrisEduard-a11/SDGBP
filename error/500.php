<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Error 500 - Error Interno del Servidor</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            background: #606060;
            color: #fff;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .container {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .error-code {
            font-size: 8rem;
            font-weight: bold;
            color: #f18000;
            margin-bottom: 0.5rem;
            text-shadow: 2px 2px 10px #333;
        }
        .error-title {
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #f18000;
        }
        .error-message {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            color: #fff;
        }
        .btn-home {
            background: #f18000;
            color: #fff;
            padding: 0.8rem 2rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s;
        }
        .btn-home:hover {
            background: #ff9900;
        }
        footer {
            background: #333;
            color: #fff;
            text-align: center;
            padding: 1rem 0 0.5rem 0;
            font-size: 1rem;
            letter-spacing: 1px;
        }
        @media (max-width: 600px) {
            .error-code { font-size: 4rem; }
            .error-title { font-size: 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-code">500</div>
        <div class="error-title">Error Interno del Servidor</div>
        <div class="error-message">
            ¡Ups! Algo salió mal en el servidor.<br>
            Por favor, intenta nuevamente más tarde.<br>
            Si el problema persiste, contacta al administrador del sistema.
        </div>
        <a href="../vistas/login.php" class="btn-home">Volver al inicio</a>
    </div>
    <footer>
        &copy; <?php echo date('Y'); ?> Sistema de Gestión SGDBP &mdash; Desarrollado por EURIPYS 2024, C.A.
    </footer>
</body>
</html>
