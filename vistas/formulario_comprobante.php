<?php
// Se asume que estos archivos PHP existen y son necesarios para la estructura de la página
include_once("../models/header.php");
include_once("../models/funciones.php");
require_once("../conexion.php");

// Se asume que no hay lógica PHP adicional de consulta aquí, solo la vista.
?>

<style>
    :root {
        --sunset-red: #dc3545;
        --sunset-red-light: #ff4d5e;
        --premium-blue: #0d6efd;
        --glass-bg: rgba(255, 255, 255, 0.95);
        --glass-border: rgba(220, 38, 38, 0.1);
    }

    [data-theme="dark"] {
        --glass-bg: #000000;
        --glass-border: #333;
        --section-header-border: #222;
    }

    .page-title-icon { 
        color: var(--sunset-red);
        filter: drop-shadow(0 0 8px rgba(220, 53, 69, 0.2));
    }

    .glass-card {
        background: var(--glass-bg);
        backdrop-filter: blur(12px);
        border: 1px solid var(--glass-border);
        border-radius: 1.5rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        color: var(--text-main);
    }

    .card-header-main { 
        background: linear-gradient(135deg, var(--sunset-red) 0%, var(--sunset-red-light) 100%);
        color: white; 
        font-weight: 700; 
        padding: 1.5rem;
        border: none;
        letter-spacing: 0.02em;
    }

    .text-section-header { 
        color: var(--premium-blue);
        font-weight: 800;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid var(--section-header-border, #f1f5f9);
        margin: 2.5rem 0 1.5rem 0;
    }

    .form-label-premium {
        font-weight: 700;
        font-size: 0.85rem;
        color: #64748b;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-control-premium {
        border-radius: 0.75rem;
        padding: 0.75rem 1rem;
        border: 1.5px solid #e2e8f0;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        color: var(--text-main);
        background-color: var(--bs-body-bg);
        font-weight: 500;
    }

    [data-theme="dark"] .form-control-premium {
        border-color: rgba(255, 255, 255, 0.1);
        background-color: rgba(0, 0, 0, 0.2);
    }

    .form-control-premium:focus {
        border-color: var(--premium-blue);
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
        transform: translateY(-1px);
    }

    .premium-alert {
        border: none;
        border-radius: 1rem;
        padding: 1.5rem;
    }

    .btn-generate {
        background: linear-gradient(135deg, var(--sunset-red) 0%, var(--sunset-red-light) 100%);
        border: none;
        border-radius: 1rem;
        padding: 1rem 2.5rem;
        font-weight: 700;
        color: white;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
    }

    .btn-generate:hover {
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 8px 25px rgba(220, 53, 69, 0.4);
        color: white;
    }

    .info-group {
        padding: 1.5rem;
        border-radius: 1.25rem;
        border: 1px solid #e2e8f0;
    }

    [data-theme="dark"] .info-group {
        background: rgba(255, 255, 255, 0.02);
        border-color: rgba(255, 255, 255, 0.05);
    }
</style>

<div id="layoutSidenav_content">
    <div class="container-fluid px-4 py-4">
        
        <header class="page-header-standard d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
            <div>
                <h1 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-file-excel me-2"></i>Generar Comprobante</h1>
                <p class="text-muted">Generación de documentos oficiales de egreso en formato Excel</p>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigateTo('inicio.php')" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active">Comprobantes</li>
                </ol>
            </nav>
        </header>
        
        <div class="alert premium-alert alert-dismissible fade show shadow-sm mb-4" role="alert">
            <div class="d-flex gap-3 align-items-center mb-2">
                <div class="bg-primary text-white p-2 rounded-3">
                    <i class="fas fa-info-circle fa-lg"></i>
                </div>
                <h5 class="alert-heading text-primary fw-bold mb-0">Instrucciones de Generación</h5>
            </div>
            <p class="mb-2 text-slate-700">
                Complete el formulario para generar el documento oficial. Se descargará automáticamente un archivo <strong>.xlsm (Editable)</strong> listo para abrir en Microsorft Excel.
            </p>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        
        <div class="card glass-card border-0 animate__animated animate__fadeIn">
            <div class="card-header card-header-main">
                <i class="fas fa-clipboard-list me-2"></i> Formulario de Egreso
            </div>
            <div class="card-body p-4 p-md-5">
                
                <form action="../dompdf/generar_comprobante.php" method="post" class="row" onsubmit="return validateFormComprobante()">
                    
                    <!-- Datos Principales -->
                    <div class="row g-4">
                        <div class="col-md-4">
                            <label for="num_comprobante" class="form-label-premium"><i class="fas fa-hashtag"></i> N° de Comprobante</label>
                            <input type="number" class="form-control form-control-premium" id="num_comprobante" name="num_comprobante" placeholder="Ej: 00123">
                        </div>

                        <div class="col-md-4">
                            <label for="fecha" class="form-label-premium"><i class="fas fa-calendar-day"></i> Fecha de Emisión</label>
                            <input type="text" class="form-control form-control-premium datepicker-flat" id="fecha" name="fecha">
                        </div>
                        
                        <div class="col-md-4">
                            <label for="monto" class="form-label-premium"><i class="fas fa-money-bill-wave"></i> Monto Total (Bs)</label>
                            <input type="text" class="form-control form-control-premium campo-monto" id="monto" name="monto" placeholder="0,00">
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="text-section-header"><i class="fas fa-user-tie"></i> Información del Beneficiario</div>
                    </div>

                    <div class="info-group">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="nombre" class="form-label-premium"><i class="fas fa-user-circle"></i> Nombre o Razón Social</label>
                                <input type="text" class="form-control form-control-premium" id="nombre" name="nombre" placeholder="Nombre completo del cliente">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="rif" class="form-label-premium"><i class="fas fa-id-card-clip"></i> RIF / C.I.</label>
                                <input type="text" class="form-control form-control-premium" id="rif" name="rif" placeholder="V-00000000-0">
                            </div>
                            
                            <div class="col-md-4">
                                <label for="telefono" class="form-label-premium"><i class="fas fa-phone-volume"></i> Teléfono</label>
                                <input type="text" class="form-control form-control-premium" id="telefono" name="telefono" placeholder="+58 4XX-XXXXXXX">
                            </div>
                            
                            <div class="col-md-4">
                                <label for="correo" class="form-label-premium"><i class="fas fa-at"></i> Correo Electrónico</label>
                                <input type="email" class="form-control form-control-premium" id="correo" name="correo" placeholder="correo@ejemplo.com">
                            </div>
                            
                            <div class="col-md-4">
                                <label for="ciudad" class="form-label-premium"><i class="fas fa-location-dot"></i> Ciudad / Estado</label>
                                <input type="text" class="form-control form-control-premium" id="ciudad" name="ciudad" placeholder="Ej: Caracas">
                            </div>

                            <div class="col-12">
                                <label for="direccion" class="form-label-premium"><i class="fas fa-map-location-dot"></i> Dirección Fiscal</label>
                                <input type="text" class="form-control form-control-premium" id="direccion" name="direccion" placeholder="Ingrese la dirección completa">
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="text-section-header text-danger" style="border-color: #fee2e2;"><i class="fas fa-building-columns"></i> Datos Bancarios y Referencia</div>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="banco" class="form-label-premium"><i class="fas fa-bank"></i> Banco de Destino</label>
                            <select class="form-select form-control-premium form-select-select2" id="banco" name="banco">
                                <option value="">Seleccione una entidad...</option>
                                <optgroup label="Bancos Públicos">
                                    <option value="Banco de Venezuela">Banco de Venezuela (BDV)</option>
                                    <option value="Banco del Tesoro">Banco del Tesoro</option>
                                    <option value="BDT - Banco Digital de los Trabajadores">BDT - Banco Digital de los Trabajadores</option>
                                    <option value="Banco Agrícola de Venezuela">Banco Agrícola de Venezuela</option>
                                    <option value="BANFANB">BANFANB</option>
                                </optgroup>
                                <optgroup label="Bancos Privados">
                                    <option value="Banesco">Banesco</option>
                                    <option value="Banco Mercantil">Banco Mercantil</option>
                                    <option value="Provincial">Provincial (BBVA)</option>
                                    <option value="BNC - Banco Nacional de Crédito">BNC - Banco Nacional de Crédito</option>
                                    <option value="Bancamiga">Bancamiga</option>
                                    <option value="Banplus">Banplus</option>
                                    <option value="Banco Exterior">Banco Exterior</option>
                                    <option value="BFC Banco Fondo Común">BFC Banco Fondo Común</option>
                                    <option value="Banco Caroní">Banco Caroní</option>
                                    <option value="Banco Activo">Banco Activo</option>
                                    <option value="Banco Plaza">Banco Plaza</option>
                                    <option value="100% Banco">100% Banco</option>
                                    <option value="DelSur">DelSur</option>
                                    <option value="Bancentro">Bancentro</option>
                                    <option value="Mi Banco">Mi Banco</option>
                                    <option value="Banco Venezolano de Crédito">Banco Venezolano de Crédito</option>
                                </optgroup>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="tipo_pago" class="form-label-premium"><i class="fas fa-wallet"></i> Tipo de Cuenta</label>
                            <select class="form-select form-control-premium form-select-select2" id="tipo_pago" name="tipo_pago">
                                <option value="">Seleccione...</option>
                                <option value="Corriente">Cuenta Corriente</option>
                                <option value="Ahorro">Cuenta de Ahorro</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="cuenta" class="form-label-premium"><i class="fas fa-credit-card"></i> Número de Cuenta (20 dígitos)</label>
                            <input type="text" class="form-control form-control-premium" id="cuenta" name="cuenta" placeholder="01XXXXXXXXXXXXXX" maxlength="20">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="referencia" class="form-label-premium"><i class="fas fa-receipt"></i> N° de Referencia Bancaria</label>
                            <input type="number" class="form-control form-control-premium" id="referencia" name="referencia" placeholder="Número de confirmación">
                        </div>

                        <div class="col-12">
                            <label for="descripcion" class="form-label-premium"><i class="fas fa-comment-dots"></i> Concepto del Egreso</label>
                            <textarea class="form-control form-control-premium" id="descripcion" name="descripcion" rows="4" placeholder="Describa brevemente el motivo de este egreso..."></textarea>
                        </div>
                    </div>

                    <div class="col-12 text-center pt-5">
                        <button type="submit" class="btn btn-lg btn-generate px-5">
                            <i class="fas fa-file-download me-2"></i> Generar Comprobante Excel
                        </button>
                    </div>
                </form>
                
            </div>
        </div>
    </div>
<?php
require_once("../models/footer.php");
?>
