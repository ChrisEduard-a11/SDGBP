<?php
require_once("../models/header.php");
require_once("../conexion.php");

?>

<style>
    /* =========================================
       SISTEMA SDGBP - DISEÑO ULTRA PREMIUM 2026
       REGISTRO DE CLIENTES
       ========================================= */
    :root {
        --primary: #f18000;
        --primary-dark: #d67100;
        --primary-light: rgba(241, 128, 0, 0.1);
        --success-premium: #10b981;
        --bg-body: #f8fafc;
        --text-main: #1e293b;
        --text-muted: #64748b;
        --border-color: #e2e8f0;
        --radius-premium: 20px;
        --shadow-premium: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
        --glass: rgba(255, 255, 255, 0.8);
        --glass-border: rgba(255, 255, 255, 0.3);
    }

    body {
        background-color: var(--bg-body);
        color: var(--text-main);
    }

    .breadcrumb-premium {
        background: var(--glass) !important;
        backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border) !important;
        border-radius: 12px !important;
        box-shadow: var(--shadow-premium);
    }

    .card-premium {
        background: #ffffff;
        border: none !important;
        border-radius: var(--radius-premium) !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
        overflow: hidden;
    }

    .card-premium-header {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        padding: 1.5rem 2rem;
        border: none !important;
    }

    .card-premium-header h5 {
        color: white;
        margin: 0;
        font-weight: 700;
        letter-spacing: 0.5px;
    }

    .form-control-premium {
        border: 1.5px solid var(--border-color) !important;
        border-radius: 12px !important;
        padding: 0.85rem 1.25rem !important;
        font-weight: 500 !important;
        transition: all 0.3s ease !important;
    }

    .form-control-premium:focus {
        border-color: var(--success-premium) !important;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1) !important;
    }

    .btn-confirm-premium {
        background: linear-gradient(135deg, var(--success-premium) 0%, #059669 100%) !important;
        border: none !important;
        padding: 1rem !important;
        border-radius: 15px !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: white !important;
        box-shadow: 0 8px 15px rgba(16, 185, 129, 0.2) !important;
        transition: all 0.3s ease !important;
    }

    .btn-confirm-premium:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 20px rgba(16, 185, 129, 0.3) !important;
    }

    .btn-outline-premium {
        border: 1.5px solid var(--border-color) !important;
        border-radius: 15px !important;
        padding: 0.85rem !important;
        font-weight: 600 !important;
        color: var(--text-muted) !important;
        transition: all 0.2s ease !important;
    }

    .btn-outline-premium:hover {
        background: #f1f5f9 !important;
        color: var(--text-main) !important;
        border-color: var(--text-muted) !important;
    }

    .input-icon-box {
        background: #f1f5f9;
        color: var(--success-premium);
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        margin-right: 12px;
    }
</style>

<div id="layoutSidenav_content">
    <div class="container-fluid px-4 py-4">
        
        <header class="page-header-standard d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
            <div>
                <h1 class="fw-bold mb-0 text-primary"><i class="fas fa-user-plus me-2"></i>Registrar Cliente</h1>
                <p class="text-muted">Añade un nuevo miembro a tu directorio de gestión y seguimiento</p>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigateTo('inicio.php')" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigateTo('ver_clientes.php')" class="text-decoration-none">Clientes</a></li>
                    <li class="breadcrumb-item active">Nuevo</li>
                </ol>
            </nav>
        </header>
        
        <div class="card card-premium shadow-lg mx-auto" style="max-width: 600px;">
            <div class="card-premium-header text-center">
                <h5><i class="fas fa-user-plus me-2"></i> Formulario de Inscripción</h5>
            </div>
            
            <div class="card-body p-4 p-md-5">
                <form method="post" action="../acciones/agregar_cliente.php" onsubmit="return validateFormAgregarCliente()">
                    
                    <div class="mb-5">
                        <div class="d-flex align-items-center mb-3">
                            <div class="input-icon-box">
                                <i class="fas fa-tag"></i>
                            </div>
                            <label for="nombre" class="form-label fw-bold mb-0 text-dark">Nombre Completo o Razón Social</label>
                        </div>
                        <input 
                            type="text" 
                            class="form-control form-control-premium" 
                            id="nombre" 
                            name="nombre" 
                            placeholder="Ej. Juan Pérez / Corporación Tech" 
                        >
                        <div class="mt-3 py-2 px-3 rounded-3" style="background: #eff6ff; color: #1d4ed8; font-size: 0.85rem;">
                            <i class="fas fa-info-circle me-1"></i> Este cliente se vinculará a tu cuenta automáticamente.
                        </div>
                    </div>

                    <div class="d-grid gap-3">
                        <button type="submit" class="btn btn-confirm-premium">
                            <i class="fas fa-check-circle me-2"></i> Guardar y Confirmar Cliente
                        </button>
                        
                        <a onclick="navigateTo('ver_clientes.php')" class="btn btn-outline-premium text-center">
                            <i class="fas fa-arrow-left me-2"></i> Cancelar y Volver
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php
require_once("../models/footer.php");
?>
