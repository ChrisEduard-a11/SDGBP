<?php
include_once("../models/header.php");
include_once("../models/funciones.php");

// Recibir el archivo por GET
$archivo = $_GET['archivo'] ?? null;
$rutaArchivo = realpath(__DIR__ . '/../comprobantes/' . $archivo);

if (!$archivo || !file_exists($rutaArchivo)) {
    die("Archivo no encontrado.");
}

/* ============================
   INCLUSIÓN DE CLASES
   ============================ */
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Cargar Excel
$spreadsheet = IOFactory::load($rutaArchivo);
$hoja = $spreadsheet->getActiveSheet();

// Extraer valores de celdas según tu mapa
$datos = [
    "num_comprobante" => $hoja->getCell('H4')->getValue(),
    "nombre"          => $hoja->getCell('B8')->getValue(),
    "rif"             => $hoja->getCell('B9')->getValue(),
    "telefono"        => $hoja->getCell('E9')->getValue(),
    "direccion"       => $hoja->getCell('B10')->getValue(),
    "ciudad"          => $hoja->getCell('E10')->getValue(),
    "correo"          => $hoja->getCell('B11')->getValue(),
    "monto"           => $hoja->getCell('H15')->getValue(),
    "descripcion"     => $hoja->getCell('C15')->getValue(),
    "fecha"           => $hoja->getCell('G9')->getValue(),
    "tipo_pago"       => $hoja->getCell('B28')->getValue(),
    "banco"           => $hoja->getCell('B29')->getValue(),
    "cuenta"          => $hoja->getCell('B30')->getValue(),
    "referencia"      => $hoja->getCell('B31')->getValue(),
];
?>

<style>
    :root {
        --sapphire-blue: #0d6efd;
        --sapphire-blue-light: #448aff;
        --sunset-red: #dc3545;
        --glass-bg: rgba(255, 255, 255, 0.95);
        --glass-border: rgba(13, 110, 253, 0.1);
    }

    [data-theme="dark"] {
        --glass-bg: rgba(30, 41, 59, 0.8);
        --glass-border: rgba(255, 255, 255, 0.1);
        --section-header-border: rgba(255, 255, 255, 0.1);
    }

    .page-title-icon { 
        color: var(--sapphire-blue);
        filter: drop-shadow(0 0 8px rgba(13, 110, 253, 0.2));
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
        background: linear-gradient(135deg, var(--sapphire-blue) 0%, var(--sapphire-blue-light) 100%);
        color: white; 
        font-weight: 700; 
        padding: 1.5rem;
        border: none;
        letter-spacing: 0.02em;
    }

    .text-section-header { 
        color: var(--sunset-red);
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
        border-color: var(--sapphire-blue);
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
        transform: translateY(-1px);
    }

    .btn-save {
        background: linear-gradient(135deg, var(--sapphire-blue) 0%, var(--sapphire-blue-light) 100%);
        border: none;
        border-radius: 1rem;
        padding: 1rem 2.5rem;
        font-weight: 700;
        color: white;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
    }

    .btn-save:hover {
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 8px 25px rgba(13, 110, 253, 0.4);
        color: white;
    }

    .info-group {
        background: #f8fafc;
        padding: 1.5rem;
        border-radius: 1.25rem;
        border: 1px solid #f1f5f9;
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
                <h1 class="fw-bold mb-0 text-primary"><i class="fas fa-file-excel me-2"></i>Editar Comprobante</h1>
                <p class="text-muted">Modificación de datos del comprobante de egreso y actualización del archivo fuente</p>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigateTo('inicio.php')" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigateTo('listar_comprobantes.php')" class="text-decoration-none">Comprobantes</a></li>
                    <li class="breadcrumb-item active">Editar</li>
                </ol>
            </nav>
        </header>
        
        <div class="card glass-card border-0 animate__animated animate__fadeIn">
            <div class="card-header card-header-main">
                <i class="fas fa-pen-to-square me-2"></i> Actualizar Datos del Archivo
            </div>
            <div class="card-body p-4 p-md-5">
                
                <form action="../dompdf/editar_comprobante.php" method="post" class="row">
                    
                    <input type="hidden" name="archivo" value="<?php echo htmlspecialchars($archivo); ?>">
                    
                    <!-- Datos Principales -->
                    <div class="row g-4">
                        <div class="col-md-4">
                            <label for="num_comprobante" class="form-label-premium"><i class="fas fa-hashtag"></i> N° de Comprobante</label>
                            <input type="number" class="form-control form-control-premium" id="num_comprobante" name="num_comprobante" 
                                   value="<?php echo $datos['num_comprobante']; ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label for="fecha" class="form-label-premium"><i class="fas fa-calendar-day"></i> Fecha</label>
                            <input type="text" class="form-control form-control-premium datepicker-flat" id="fecha" name="fecha" 
                                   value="<?php echo $datos['fecha']; ?>" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="monto" class="form-label-premium"><i class="fas fa-money-bill-wave"></i> Monto (Bs)</label>
                            <input type="text" class="form-control form-control-premium campo-monto" id="monto" name="monto" 
                                   value="<?php echo $datos['monto']; ?>" required>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="text-section-header"><i class="fas fa-user-tie"></i> Información del Beneficiario</div>
                    </div>

                    <div class="info-group">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="nombre" class="form-label-premium"><i class="fas fa-user-circle"></i> Nombre o Razón Social</label>
                                <input type="text" class="form-control form-control-premium" id="nombre" name="nombre" 
                                       value="<?php echo $datos['nombre']; ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="rif" class="form-label-premium"><i class="fas fa-id-card-clip"></i> RIF / C.I.</label>
                                <input type="text" class="form-control form-control-premium" id="rif" name="rif" 
                                       value="<?php echo $datos['rif']; ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="telefono" class="form-label-premium"><i class="fas fa-phone-volume"></i> Teléfono</label>
                                <input type="text" class="form-control form-control-premium" id="telefono" name="telefono" 
                                       value="<?php echo $datos['telefono']; ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="correo" class="form-label-premium"><i class="fas fa-at"></i> Correo Electrónico</label>
                                <input type="email" class="form-control form-control-premium" id="correo" name="correo" 
                                       value="<?php echo $datos['correo']; ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="ciudad" class="form-label-premium"><i class="fas fa-location-dot"></i> Ciudad</label>
                                <input type="text" class="form-control form-control-premium" id="ciudad" name="ciudad" 
                                       value="<?php echo $datos['ciudad']; ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label for="direccion" class="form-label-premium"><i class="fas fa-map-location-dot"></i> Dirección Fiscal</label>
                                <input type="text" class="form-control form-control-premium" id="direccion" name="direccion" 
                                       value="<?php echo $datos['direccion']; ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="text-section-header text-danger" style="border-color: #fee2e2;"><i class="fas fa-building-columns"></i> Datos Bancarios y Referencia</div>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="banco" class="form-label-premium"><i class="fas fa-bank"></i> Banco</label>
                            <select class="form-select form-control-premium form-select-select2" id="banco" name="banco" required>
                                <option value="">Seleccione un banco</option>
                                <optgroup label="Bancos Públicos">
                                    <option value="Banco de Venezuela" <?php if($datos['banco'] == 'Banco de Venezuela') echo 'selected'; ?>>Banco de Venezuela</option>
                                    <option value="Banco del Tesoro" <?php if($datos['banco'] == 'Banco del Tesoro') echo 'selected'; ?>>Banco del Tesoro</option>
                                    <option value="Banco Digital de los Trabajadores" <?php if($datos['banco'] == 'Banco Digital de los Trabajadores') echo 'selected'; ?>>Banco Digital de los Trabajadores</option>
                                    <option value="Banco Agrícola de Venezuela" <?php if($datos['banco'] == 'Banco Agrícola de Venezuela') echo 'selected'; ?>>Banco Agrícola de Venezuela</option>
                                    <option value="Banco de la Fuerza Armada Nacional Bolivariana (BANFANB)" <?php if($datos['banco'] == 'Banco de la Fuerza Armada Nacional Bolivariana (BANFANB)') echo 'selected'; ?>>BANFANB</option>
                                </optgroup>
                                <optgroup label="Bancos Privados">
                                    <option value="Banesco" <?php if($datos['banco'] == 'Banesco') echo 'selected'; ?>>Banesco</option>
                                    <option value="Banco Mercantil" <?php if($datos['banco'] == 'Banco Mercantil') echo 'selected'; ?>>Banco Mercantil</option>
                                    <option value="Banco Provincial" <?php if($datos['banco'] == 'Banco Provincial') echo 'selected'; ?>>Banco Provincial</option>
                                    <option value="Banco Nacional de Crédito (BNC)" <?php if($datos['banco'] == 'Banco Nacional de Crédito (BNC)') echo 'selected'; ?>>Banco Nacional de Crédito (BNC)</option>
                                    <option value="Bancamiga Banco Universal" <?php if($datos['banco'] == 'Bancamiga Banco Universal') echo 'selected'; ?>>Bancamiga Banco Universal</option>
                                    <option value="Banplus Banco Universal" <?php if($datos['banco'] == 'Banplus Banco Universal') echo 'selected'; ?>>Banplus Banco Universal</option>
                                </optgroup>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="tipo_pago" class="form-label-premium"><i class="fas fa-wallet"></i> Tipo de Cuenta</label>
                            <select class="form-select form-control-premium form-select-select2" id="tipo_pago" name="tipo_pago" required>
                                <option value="">Seleccione...</option>
                                <option value="Corriente" <?php if($datos['tipo_pago'] == 'Corriente') echo 'selected'; ?>>Corriente</option>
                                <option value="Ahorro" <?php if($datos['tipo_pago'] == 'Ahorro') echo 'selected'; ?>>Ahorro</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="cuenta" class="form-label-premium"><i class="fas fa-credit-card"></i> Número de Cuenta</label>
                            <input type="text" class="form-control form-control-premium" id="cuenta" name="cuenta" 
                                   value="<?php echo $datos['cuenta']; ?>" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="referencia" class="form-label-premium"><i class="fas fa-receipt"></i> N° de Referencia</label>
                            <input type="number" class="form-control form-control-premium" id="referencia" name="referencia" 
                                   value="<?php echo $datos['referencia']; ?>" required>
                        </div>

                        <div class="col-12">
                            <label for="descripcion" class="form-label-premium"><i class="fas fa-align-left"></i> Concepto / Descripción</label>
                            <textarea class="form-control form-control-premium" id="descripcion" name="descripcion" rows="4" required><?php echo $datos['descripcion']; ?></textarea>
                        </div>
                    </div>

                    <div class="col-12 text-center pt-5">
                        <button type="submit" class="btn btn-lg btn-save px-5">
                            <i class="fas fa-save me-2"></i> Guardar Cambios en Excel
                        </button>
                    </div>
                </form>
                
            </div>
        </div>
    </div>

<?php
require_once("../models/footer.php");
?>