<?php
// PHP Lógica de Backend (Mantenida sin cambios, solo para contexto)
require_once("../models/header.php");
require_once("../conexion.php");

// Verificar si se recibió el ID del producto
if (!isset($_GET['id'])) {
    header("Location: productos.php");
    exit;
}

$id = $_GET['id'];

// Obtener los datos del producto
$sql = "SELECT * FROM productos WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$producto = $result->fetch_assoc();

if (!$producto) {
    header("Location: productos.php");
    exit;
}

// Obtener las categorías
$sql = "SELECT * FROM categorias_productos";
$result = $conexion->query($sql);
$categorias = $result->fetch_all(MYSQLI_ASSOC);
?>

<style>
    :root {
        --premium-sapphire: #0d6efd;
        --premium-sapphire-light: #60a5fa;
        --glass-bg: rgba(255, 255, 255, 0.95);
        --glass-border: rgba(13, 110, 253, 0.1);
    }

    [data-theme="dark"] {
        --glass-bg: rgba(30, 41, 59, 0.8);
        --glass-border: rgba(255, 255, 255, 0.1);
        --info-group-bg: rgba(255, 255, 255, 0.02);
        --info-group-border: rgba(255, 255, 255, 0.05);
    }

    .page-title-icon { 
        color: var(--premium-sapphire);
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
        background: linear-gradient(135deg, var(--premium-sapphire) 0%, var(--premium-sapphire-light) 100%);
        color: white; 
        font-weight: 700; 
        padding: 1.5rem;
        border: none;
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
        transition: all 0.3s ease;
        color: var(--text-main);
        background-color: var(--bs-body-bg);
        font-weight: 500;
    }

    [data-theme="dark"] .form-control-premium {
        border-color: rgba(255, 255, 255, 0.1);
        background-color: rgba(0, 0, 0, 0.2);
    }

    .form-control-premium:focus {
        border-color: var(--premium-sapphire);
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
    }

    .btn-save-premium {
        background: linear-gradient(135deg, var(--premium-sapphire) 0%, var(--premium-sapphire-light) 100%);
        border: none;
        border-radius: 1rem;
        padding: 1rem 2.5rem;
        font-weight: 700;
        color: white;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
    }

    .btn-save-premium:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(13, 110, 253, 0.4);
    }

    .preview-container {
        background: var(--info-group-bg, #f8fafc);
        border-radius: 1.25rem;
        border: 2px dashed var(--info-group-border, #e2e8f0);
        padding: 2rem;
        text-align: center;
        transition: all 0.3s ease;
    }

    .img-thumbnail-premium {
        max-width: 100%;
        height: 200px;
        object-fit: contain;
        border-radius: 1rem;
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        background: white;
    }

    .info-group {
        background: var(--info-group-bg, #f8fafc);
        padding: 1.5rem;
        border-radius: 1.25rem;
        border: 1px solid var(--info-group-border, #f1f5f9);
        height: 100%;
    }
</style>

<div id="layoutSidenav_content">
    <div class="container-fluid px-4 py-4">
        
        <header class="page-header-standard d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
            <div>
                <h1 class="fw-bold mb-0 text-primary"><i class="fas fa-edit me-2"></i>Editar Producto</h1>
                <p class="text-muted">Actualización de información, precios y existencias del producto</p>
                <div class="mt-1"><span class="badge bg-light text-dark fw-bold border">ID: #<?php echo $producto['id']; ?></span></div>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigateTo('inicio.php')" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigateTo('productos.php')" class="text-decoration-none">Productos</a></li>
                    <li class="breadcrumb-item active">Editar</li>
                </ol>
            </nav>
        </header>

        <div class="card glass-card border-0 animate__animated animate__fadeIn">
            <div class="card-header card-header-main">
                <i class="fas fa-file-invoice-dollar me-2"></i> Formulario de Modificación
            </div>
            <div class="card-body p-4 p-md-5">
                
                <form action="../acciones/editar_producto.php" method="POST" enctype="multipart/form-data" class="row g-4">
                    
                    <input type="hidden" name="id" value="<?php echo $producto['id']; ?>">
                    
                    <div class="col-lg-7">
                        <div class="info-group">
                            <h6 class="fw-bold text-primary mb-4 text-uppercase small letter-spacing-1"><i class="fas fa-info-circle me-2"></i> Información General</h6>
                            
                            <div class="mb-4">
                                <label for="nombre" class="form-label-premium"><i class="fas fa-signature"></i> Nombre Comercial</label>
                                <input type="text" class="form-control form-control-premium" id="nombre" name="nombre" value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>
                            </div>
                            
                            <div class="row g-3">
                                <div class="col-md-6 mb-4">
                                    <label for="precio" class="form-label-premium"><i class="fas fa-tag"></i> Precio de Venta ($)</label>
                                    <input type="number" step="0.01" class="form-control form-control-premium" id="precio" name="precio" value="<?php echo htmlspecialchars($producto['precio']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label for="stock" class="form-label-premium"><i class="fas fa-warehouse"></i> Existencias en Stock</label>
                                    <input type="number" class="form-control form-control-premium" id="stock" name="stock" value="<?php echo htmlspecialchars($producto['stock']); ?>" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="categoria" class="form-label-premium"><i class="fas fa-layer-group"></i> Clasificación</label>
                                <select class="form-select form-control-premium form-select-select2" id="categoria" name="categoria_id" required>
                                    <option value="">Seleccione una categoría</option>
                                    <?php foreach ($categorias as $categoria): ?>
                                        <option 
                                            value="<?= $categoria['id'] ?>" 
                                            <?= $categoria['id'] == $producto['categoria_productos_id'] ? 'selected' : '' ?>
                                        >
                                            <?= htmlspecialchars($categoria['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-0">
                                <label for="descripcion" class="form-label-premium"><i class="fas fa-align-left"></i> Descripción Técnica</label>
                                <textarea class="form-control form-control-premium" id="descripcion" name="descripcion" rows="4"><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="info-group">
                            <h6 class="fw-bold text-secondary mb-4 text-uppercase small letter-spacing-1"><i class="fas fa-image me-2"></i> Identidad Visual</h6>
                            
                            <div class="preview-container mb-4">
                                <?php if (!empty($producto['imagen'])): ?>
                                    <img src="<?php echo htmlspecialchars($producto['imagen']); ?>" class="img-thumbnail-premium mb-3" alt="Foto actual">
                                    <p class="text-muted x-small mb-0">Imagen actual en el sistema.</p>
                                <?php else: ?>
                                    <div class="py-4 text-muted opacity-50">
                                        <i class="fas fa-camera fa-4x mb-3"></i>
                                        <p class="mb-0 fw-bold">Sin imagen asignada</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-4">
                                <label for="imagen" class="form-label-premium"><i class="fas fa-upload"></i> Reemplazar Fotografía</label>
                                <input type="file" class="form-control form-control-premium" id="imagen" name="imagen" accept="image/*">
                                <div class="form-text x-small">Elige un archivo nuevo para actualizar la imagen (PNG, JPG).</div>
                            </div>

                            <div class="d-grid gap-2 pt-3">
                                <button type="submit" class="btn btn-save-premium">
                                    <i class="fas fa-check-circle me-2"></i> Confirmar Cambios
                                </button>
                                <a href="javascript:void(0);" onclick="navigateTo('productos.php')" class="btn btn-outline-secondary border-0 rounded-4 py-2 fw-bold small">
                                    <i class="fas fa-arrow-left me-1"></i> Volver sin cambios
                                </a>
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