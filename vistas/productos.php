<?php
// PHP Lógica de Backend (Se mantiene la lógica original para obtener los totales)
require_once("../models/header.php");
include('../conexion.php');

// Total de categorías
$sql = "SELECT COUNT(*) AS total_categorias FROM categorias_productos";
$result = mysqli_query($conexion, $sql);
$row = mysqli_fetch_assoc($result);
$total_categorias = $row['total_categorias'];

// Total de productos
$sql = "SELECT COUNT(*) AS total_productos FROM productos";
$result = mysqli_query($conexion, $sql);
$row = mysqli_fetch_assoc($result);
$total_productos = $row['total_productos'];

// Total de productos sin stock
$sql = "SELECT COUNT(*) AS total_sin_stock FROM productos WHERE stock = 0";
$result = mysqli_query($conexion, $sql);
$row = mysqli_fetch_assoc($result);
$total_sin_stock = $row['total_sin_stock'];
?>

<style>
    :root {
        --premium-blue: #0d6efd;
        --premium-emerald: #10b981;
        --sunset-red: #dc3545;
        --glass-bg: rgba(255, 255, 255, 0.95);
        --glass-border: rgba(13, 110, 253, 0.1);
    }

    [data-theme="dark"] {
        --glass-bg: rgba(30, 41, 59, 0.8);
        --glass-border: rgba(255, 255, 255, 0.1);
    }

    .page-title-icon { 
        color: var(--premium-blue);
        filter: drop-shadow(0 0 8px rgba(13, 110, 253, 0.2));
    }

    /* Metric Cards */
    .metric-card {
        border-radius: 1.5rem;
        border: none;
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }

    .metric-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.12);
    }

    .metric-card::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
        transition: all 0.6s ease;
    }

    .metric-card:hover::before {
        transform: translate(10%, 10%);
    }

    .glass-card {
        background: var(--glass-bg);
        backdrop-filter: blur(12px);
        border: 1px solid var(--glass-border);
        border-radius: 1.5rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
        color: var(--text-main);
    }

    .card-header-main { 
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        color: white; 
        font-weight: 700; 
        padding: 1.5rem;
        border: none;
    }

    .btn-action-premium {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        transition: all 0.3s ease;
        border: none;
    }

    .btn-action-premium:hover {
        transform: scale(1.15) rotate(5deg);
    }

    .stock-badge {
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        font-weight: 700;
        font-size: 0.85rem;
    }

    .stock-ok { background: #ecfdf5; color: #059669; }
    .stock-low { background: #fff7ed; color: #d97706; }
    .stock-none { background: #fef2f2; color: #dc2626; }

    [data-theme="dark"] .stock-ok { background: rgba(16, 185, 129, 0.1); color: #4ade80; }
    [data-theme="dark"] .stock-low { background: rgba(245, 158, 11, 0.1); color: #fbbf24; }
    [data-theme="dark"] .stock-none { background: rgba(239, 68, 68, 0.1); color: #f87171; }

    .price-pill {
        background: #f1f5f9;
        color: #1e293b;
        font-weight: 800;
        padding: 0.4rem 0.8rem;
        border-radius: 0.75rem;
        border: 1px solid #e2e8f0;
    }

    [data-theme="dark"] .price-pill {
        background: rgba(255, 255, 255, 0.05);
        color: #f8fafc;
        border-color: rgba(255, 255, 255, 0.1);
    }
</style>

<div id="layoutSidenav_content">
    <div class="container-fluid px-4 py-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h3 class="fw-bold mb-0">
                    <i class="fas fa-cubes page-title-icon me-2"></i> 
                    Gestión de Inventario
                </h3>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-emerald-premium px-4 py-2" href="javascript:void(0);" onclick="navigateTo('agregar_producto.php')">
                    <i class="fa fa-plus-circle me-2"></i> Nuevo Producto
                </a>
                <a class="btn btn-outline-primary px-4 py-2 rounded-pill shadow-sm" href="javascript:void(0);" onclick="navigateTo('agregar_categoria_producto.php')">
                    <i class="fa fa-tags me-2"></i> Categorías
                </a>
            </div>
        </div>
        
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb bg-white p-3 rounded-4 shadow-sm">
                <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigateTo('inicio.php')" class="text-decoration-none text-primary"><i class="fas fa-home me-1"></i> Inicio</a></li>
                <li class="breadcrumb-item active text-secondary fw-medium"><i class="fas fa-box me-1"></i> Productos</li>
            </ol>
        </nav>
        
        <div class="row mb-5">
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card metric-card bg-primary text-white shadow-lg h-100">
                    <div class="card-body p-4 d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-uppercase fw-bold opacity-75 mb-2">Categorías Activas</h6>
                            <h2 class="display-5 fw-black mb-0"><?php echo $total_categorias; ?></h2>
                        </div>
                        <div class="bg-white bg-opacity-20 p-3 rounded-circle">
                            <i class="fas fa-tag fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card metric-card bg-success text-white shadow-lg h-100">
                    <div class="card-body p-4 d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-uppercase fw-bold opacity-75 mb-2">Total Productos</h6>
                            <h2 class="display-5 fw-black mb-0"><?php echo $total_productos; ?></h2>
                        </div>
                        <div class="bg-white bg-opacity-20 p-3 rounded-circle">
                            <i class="fas fa-boxes fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card metric-card bg-danger text-white shadow-lg h-100">
                    <div class="card-body p-4 d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-uppercase fw-bold opacity-75 mb-2">Sin Existencias</h6>
                            <h2 class="display-5 fw-black mb-0"><?php echo $total_sin_stock; ?></h2>
                        </div>
                        <div class="bg-white bg-opacity-20 p-3 rounded-circle">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card glass-card border-0 animate__animated animate__fadeIn">
            <div class="card-header card-header-main d-flex align-items-center rounded-top-4">
                <i class="fas fa-list-check me-2"></i> Listado Maestro de Productos
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table id="datatablesSimple" class="table table-hover align-middle border-0">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th class="border-0"><i class="fas fa-font me-2"></i> Producto</th>
                                <th class="border-0"><i class="fas fa-dollar-sign me-2"></i> Precio</th>
                                <th class="border-0"><i class="fas fa-warehouse me-2"></i> Stock</th>
                                <th class="border-0"><i class="fas fa-layer-group me-2"></i> Categoría</th>
                                <th class="border-0 text-center"><i class="fas fa-tools me-2"></i> Gestión</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT p.*, c.nombre AS categoria FROM productos p INNER JOIN categorias_productos c ON p.categoria_productos_id = c.id";
                            $result = mysqli_query($conexion, $sql);
                            while ($row = mysqli_fetch_assoc($result)) {
                                if ($row['stock'] == 0) {
                                    $stock_status = 'stock-none';
                                    $stock_text = 'Agotado';
                                } elseif ($row['stock'] <= 5) {
                                    $stock_status = 'stock-low';
                                    $stock_text = 'Bajo: ' . $row['stock'];
                                } else {
                                    $stock_status = 'stock-ok';
                                    $stock_text = $row['stock'] . ' Disp.';
                                }
                            ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-light p-2 rounded-3 border">
                                                <i class="fas fa-box text-primary"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-main"><?php echo htmlspecialchars($row['nombre']); ?></div>
                                                <div class="text-muted small"><?php echo htmlspecialchars(substr($row['descripcion'], 0, 40)) . '...'; ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="price-pill">$<?php echo number_format($row['precio'], 2); ?></span>
                                    </td>
                                    <td>
                                        <span class="stock-badge <?php echo $stock_status; ?>">
                                            <?php echo $stock_text; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border px-3 py-2 rounded-3">
                                            <?php echo htmlspecialchars($row['categoria']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-2">
                                            <button class="btn btn-action-premium btn-soft-primary" 
                                                    onclick="navigateTo('editar_producto.php?id=<?php echo $row['id']; ?>')" 
                                                    title="Editar Detalle">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-action-premium btn-soft-danger" 
                                                    onclick="confirmDeleteProducto('../acciones/eliminar_producto.php?id=<?php echo $row['id']; ?>')" 
                                                    title="Eliminar del Sistema">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php
// Asegúrate de cerrar la conexión si es necesario, aunque generalmente se hace en el footer/acciones.
// mysqli_close($conexion); 
require_once("../models/footer.php");
?>