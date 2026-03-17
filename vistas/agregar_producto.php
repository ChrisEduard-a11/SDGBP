<?php
// PHP Lógica de Backend (Mantenida sin cambios, solo para contexto)
require_once("../models/header.php");
require_once("../conexion.php");

// Obtener las categorías
$query = "SELECT * FROM categorias_productos";
$result = $conexion->query($query);
$categorias = $result->fetch_all(MYSQLI_ASSOC);
?>

<style>
    :root {
        --premium-emerald: #10b981;
        --premium-emerald-light: #34d399;
        --glass-bg: rgba(255, 255, 255, 0.95);
        --glass-border: rgba(16, 185, 129, 0.1);
    }

    [data-theme="dark"] {
        --glass-bg: rgba(30, 41, 59, 0.8);
        --glass-border: rgba(255, 255, 255, 0.1);
        --section-bg: rgba(255, 255, 255, 0.02);
        --section-border: rgba(255, 255, 255, 0.05);
    }

    .page-title-icon { 
        color: var(--premium-emerald);
        filter: drop-shadow(0 0 8px rgba(16, 185, 129, 0.2));
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
        background: linear-gradient(135deg, var(--premium-emerald) 0%, var(--premium-emerald-light) 100%);
        color: white; 
        font-weight: 700; 
        padding: 1.5rem;
        border: none;
        letter-spacing: 0.02em;
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
        border-color: var(--premium-emerald);
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        transform: translateY(-1px);
    }

    .btn-submit-premium {
        background: linear-gradient(135deg, var(--premium-emerald) 0%, var(--premium-emerald-light) 100%);
        border: none;
        border-radius: 1rem;
        padding: 1rem 2.5rem;
        font-weight: 700;
        color: white;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
    }

    .btn-submit-premium:hover {
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
        color: white;
    }

    .info-section {
        background: var(--section-bg, #f8fafc);
        padding: 1.5rem;
        border-radius: 1.25rem;
        border: 1px solid var(--section-border, #f1f5f9);
        height: 100%;
    }

    .section-title {
        color: var(--premium-emerald);
        font-weight: 800;
        font-size: 0.95rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
</style>

<div id="layoutSidenav_content">
    <div class="container-fluid px-4 py-4">
        
        <div class="mb-4 text-center">
            <h3 class="page-title fw-bold">
                <i class="fas fa-plus-circle page-title-icon me-2"></i> 
                Agregar Nuevo Producto
            </h3>
        </div>
        
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-white p-3 rounded-4 shadow-sm mb-4">
                <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigateTo('inicio.php')" class="text-decoration-none"><i class="fas fa-home me-1"></i> Inicio</a></li>
                <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigateTo('productos.php')" class="text-decoration-none"><i class="fas fa-boxes me-1"></i> Inventario</a></li>
                <li class="breadcrumb-item active text-secondary"><i class="fas fa-plus me-1"></i> Nueva Ficha</li>
            </ol>
        </nav>

        <div class="card glass-card border-0 animate__animated animate__fadeIn">
            <div class="card-header card-header-main">
                <i class="fas fa-box me-2"></i> Detalles del Producto
            </div>
            <div class="card-body p-4 p-md-5">
                
                <form action="../acciones/agregar_producto.php" method="POST" enctype="multipart/form-data" onsubmit="return validateFormAgregarProducto()" class="row g-4">
                    
                    <div class="col-lg-7">
                        <div class="info-section">
                            <div class="section-title"><i class="fas fa-info-circle"></i> Información Básica</div>
                            
                            <div class="mb-4">
                                <label for="nombre" class="form-label-premium"><i class="fas fa-signature"></i> Nombre del Producto</label>
                                <input type="text" class="form-control form-control-premium" id="nombre" name="nombre" placeholder="Ej: Laptop Gamer X9" required>
                            </div>
                            
                            <div class="mb-4">
                                <label for="categoria" class="form-label-premium"><i class="fas fa-tags"></i> Categoría Principal</label>
                                <select class="form-select form-control-premium form-select-select2" id="categoria" name="categoria_id" required>
                                    <option value="">Seleccione una categoría</option>
                                    <?php foreach ($categorias as $categoria): ?>
                                        <option value="<?= $categoria['id'] ?>"><?= htmlspecialchars($categoria['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-0">
                                <label for="descripcion" class="form-label-premium"><i class="fas fa-align-left"></i> Especificaciones Técnicas</label>
                                <textarea class="form-control form-control-premium" id="descripcion" name="descripcion" rows="5" placeholder="Describa el producto detalladamente..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="info-section">
                            <div class="section-title"><i class="fas fa-coins"></i> Valores y Disponibilidad</div>
                            
                            <div class="row g-3 mb-4">
                                <div class="col-6">
                                    <label for="precio" class="form-label-premium"><i class="fas fa-dollar-sign"></i> Precio Venta</label>
                                    <input type="number" step="0.01" class="form-control form-control-premium" id="precio" name="precio" placeholder="0.00" min="0" required>
                                </div>
                                
                                <div class="col-6">
                                    <label for="stock" class="form-label-premium"><i class="fas fa-warehouse"></i> Stock Inicial</label>
                                    <input type="number" class="form-control form-control-premium" id="stock" name="stock" placeholder="0" min="0" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="imagen" class="form-label-premium"><i class="fas fa-image"></i> Fotografía del Producto</label>
                                <div class="bg-section-inner border rounded-4 p-3 text-center">
                                    <input type="file" class="form-control form-control-premium mb-2" id="imagen" name="imagen" accept="image/*">
                                    <div class="text-muted x-small"><i class="fas fa-info-circle me-1"></i> Formatos JPG o PNG recomendados.</div>
                                </div>
                            </div>
                            
                            <div class="text-center pt-3 mt-auto">
                                <button type="submit" class="btn btn-submit-premium w-100">
                                    <i class="fas fa-save me-2"></i> Registrar Producto
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                
            </div>
        </div>
    </div>
<?php
require_once("../models/footer.php");
?>