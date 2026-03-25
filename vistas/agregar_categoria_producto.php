<?php
require_once("../models/header.php");
require_once("../conexion.php");

// Obtener las categorías existentes para la tabla de gestión
$query = "SELECT id, nombre FROM categorias_productos ORDER BY nombre ASC";
$result = $conexion->query($query);
$categorias = $result->fetch_all(MYSQLI_ASSOC);
?>

<style>
    :root {
        --premium-amber: #f59e0b;
        --premium-amber-light: #fbbf24;
        --glass-bg: rgba(255, 255, 255, 0.95);
        --glass-border: rgba(245, 158, 11, 0.1);
    }

    [data-theme="dark"] {
        --glass-bg: rgba(30, 41, 59, 0.8);
        --glass-border: rgba(255, 255, 255, 0.1);
        --table-header-bg: #111827;
    }

    .page-title-icon { 
        color: var(--premium-amber);
        filter: drop-shadow(0 0 8px rgba(245, 158, 11, 0.2));
    }

    .glass-card {
        background: var(--glass-bg);
        backdrop-filter: blur(12px);
        border: 1px solid var(--glass-border);
        border-radius: 1.5rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        color: var(--text-main);
    }

    .card-header-main { 
        background: linear-gradient(135deg, var(--premium-amber) 0%, var(--premium-amber-light) 100%);
        color: white; 
        font-weight: 700; 
        padding: 1.25rem 1.5rem;
        border: none;
    }

    .card-header-secondary {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        color: white;
        font-weight: 700;
        padding: 1.25rem 1.5rem;
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
    }

    [data-theme="dark"] .form-control-premium {
        border-color: rgba(255, 255, 255, 0.1);
        background-color: rgba(0, 0, 0, 0.2);
    }

    .form-control-premium:focus {
        border-color: var(--premium-amber);
        box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1);
    }

    .btn-category-add {
        background: linear-gradient(135deg, var(--premium-amber) 0%, var(--premium-amber-light) 100%);
        border: none;
        border-radius: 1rem;
        padding: 0.75rem 2rem;
        font-weight: 700;
        color: white;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
    }

    .btn-category-add:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4);
    }

    .category-badge {
        background: #fef3c7;
        color: #92400e;
        font-weight: 700;
        padding: 0.5rem 1rem;
        border-radius: 0.75rem;
        border: 1px solid #fde68a;
    }

    [data-theme="dark"] .category-badge {
        background: rgba(245, 158, 11, 0.1);
        color: #fbbf24;
        border-color: rgba(245, 158, 11, 0.2);
    }

    .action-btn-category {
        width: 35px;
        height: 35px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        transition: all 0.2s ease;
        border: none;
    }

    .action-btn-category:hover {
        transform: scale(1.1);
    }
</style>

<div id="layoutSidenav_content">
    <div class="container-fluid px-4 py-4">
        
        <header class="page-header-standard d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
            <div>
                <h1 class="fw-bold mb-0 text-primary"><i class="fas fa-tags me-2"></i>Categorías de Productos</h1>
                <p class="text-muted">Administración de categorías y clasificación del catálogo de ventas</p>
            </div>
            <nav aria-label="breadcrumb" class="d-none d-lg-block">
                <ol class="breadcrumb bg-transparent p-0 m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigateTo('inicio.php')" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigateTo('productos.php')" class="text-decoration-none">Productos</a></li>
                    <li class="breadcrumb-item active">Categorías</li>
                </ol>
            </nav>
        </header>

        <?php if (isset($mensaje)): ?>
            <div class="alert alert-info border-0 shadow-sm rounded-4 animate__animated animate__fadeInDown mb-4" role="alert">
                <i class="fas fa-info-circle me-2"></i> <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card glass-card border-0 animate__animated animate__fadeInLeft h-100">
                    <div class="card-header card-header-main">
                        <i class="fas fa-plus-circle me-2"></i> Nueva Categoría
                    </div>
                    <div class="card-body p-4">
                        <form action="../acciones/agregar_categoria_producto.php" method="POST" onsubmit="return validateFormAgregarCategoria()">
                            <div class="mb-4">
                                <label for="nombre" class="form-label-premium"><i class="fas fa-tag"></i> Nombre descriptivo</label>
                                <input type="text" class="form-control form-control-premium form-control-lg" id="nombre" name="nombre" placeholder="Ej: Electrónica, Ropa...">
                                <div class="form-text mt-2 small text-muted">Asegúrate de que el nombre sea único para evitar confusiones.</div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-category-add py-3">
                                    <i class="fas fa-plus me-2"></i> Crear Categoría
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card glass-card border-0 animate__animated animate__fadeInRight h-100">
                    <div class="card-header card-header-secondary">
                        <i class="fas fa-list me-2"></i> Directorio de Categorías
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table id="datatablesSimple" class="table table-hover align-middle border-0">
                                <thead class="bg-light-dynamic">
                                    <tr>
                                        <th class="border-0 px-4"># ID</th>
                                        <th class="border-0">Nombre de Categoría</th>
                                        <th class="border-0 text-center">Gestión</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categorias as $categoria): ?>
                                        <tr>
                                            <td class="px-4 fw-bold text-secondary">#<?php echo $categoria['id']; ?></td>
                                            <td>
                                                <span class="category-badge">
                                                    <?php echo htmlspecialchars($categoria['nombre']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-center gap-2">
                                                    <button onclick="navigateTo('editar_categoria_producto.php?id=<?php echo $categoria['id']; ?>')" class="action-btn-category btn-soft-primary" title="Renombrar">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="action-btn-category btn-soft-danger" 
                                                            onclick="confirmDeleteCategoria('../acciones/eliminar_categoria_producto.php?id=<?php echo $categoria['id']; ?>')" 
                                                            title="Dar de baja">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>  
<?php
require_once("../models/footer.php");
?>