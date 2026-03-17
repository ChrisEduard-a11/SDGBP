<?php
require_once("../models/header.php");
require_once("../conexion.php");

// Obtener la categoría a editar
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sql = "SELECT * FROM categorias_productos WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$categoria = $res->fetch_assoc();
$stmt->close();

if (!$categoria) {
    $_SESSION['estatus'] = "error";
    $_SESSION['mensaje'] = "Categoría no encontrada.";
    header("Location: agregar_categoria_producto.php");
    exit;
}
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
    }

    .page-title-icon { 
        color: var(--premium-amber);
        filter: drop-shadow(0 0 8px rgba(245, 158, 11, 0.2));
    }

    .glass-card {
        background: var(--glass-bg);
        backdrop-filter: blur(12px);
        border: 1px solid var(--glass-border);
        border-radius: 2rem;
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        color: var(--text-main);
    }

    .card-header-main { 
        background: linear-gradient(135deg, var(--premium-amber) 0%, var(--premium-amber-light) 100%);
        color: white; 
        font-weight: 800; 
        padding: 2rem;
        border: none;
        text-align: center;
    }

    .form-label-premium {
        font-weight: 700;
        font-size: 0.9rem;
        color: #475569;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-control-premium {
        border-radius: 1rem;
        padding: 1rem 1.25rem;
        border: 2px solid #e2e8f0;
        transition: all 0.3s ease;
        font-size: 1.1rem;
        font-weight: 500;
        color: var(--text-main);
        background-color: var(--bs-body-bg);
    }

    [data-theme="dark"] .form-control-premium {
        border-color: rgba(255, 255, 255, 0.1);
        background-color: rgba(0, 0, 0, 0.2);
    }

    .form-control-premium:focus {
        border-color: var(--premium-amber);
        box-shadow: 0 0 0 5px rgba(245, 158, 11, 0.1);
        transform: translateY(-2px);
    }

    .btn-update-premium {
        background: linear-gradient(135deg, var(--premium-amber) 0%, var(--premium-amber-light) 100%);
        border: none;
        border-radius: 1.25rem;
        padding: 1.25rem 3rem;
        font-weight: 800;
        color: white;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 10px 30px rgba(245, 158, 11, 0.3);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .btn-update-premium:hover {
        transform: translateY(-4px) scale(1.02);
        box-shadow: 0 15px 40px rgba(245, 158, 11, 0.4);
        color: white;
    }

    .form-container {
        max-width: 600px;
        margin: 0 auto;
    }
</style>

<div id="layoutSidenav_content">
    <div class="container-fluid px-4 py-5">
        
        <nav aria-label="breadcrumb" class="mb-5">
            <ol class="breadcrumb bg-white p-3 rounded-4 shadow-sm justify-content-center">
                <li class="breadcrumb-item"><a href="inicio.php" class="text-decoration-none text-primary"><i class="fas fa-home me-1"></i> Dashboard</a></li>
                <li class="breadcrumb-item"><a href="agregar_categoria_producto.php" class="text-decoration-none text-primary"><i class="fas fa-tags me-1"></i> Categorías</a></li>
                <li class="breadcrumb-item active text-secondary fw-bold"><i class="fas fa-pen-nib me-1"></i> Editar Etiqueta</li>
            </ol>
        </nav>

        <div class="form-container">
            <div class="card glass-card border-0 animate__animated animate__zoomIn">
                <div class="card-header card-header-main">
                    <div class="mb-3">
                        <i class="fas fa-folder-open fa-3x"></i>
                    </div>
                    <h3 class="mb-0">Actualizar Categoría</h3>
                </div>
                <div class="card-body p-4 p-md-5">
                    <form action="../acciones/editar_categoria_producto.php" method="POST" class="row g-4 text-center">
                        <input type="hidden" name="id" value="<?= $categoria['id'] ?>">
                        <div class="col-12 text-start">
                            <div class="mb-4">
                                <label for="nombre" class="form-label-premium"><i class="fas fa-edit"></i> Nuevo Nombre de Categoría</label>
                                <input type="text" class="form-control form-control-premium" id="nombre" name="nombre" value="<?= htmlspecialchars($categoria['nombre']) ?>" required placeholder="Nombre de la categoría">
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-update-premium w-100 mb-3">
                                <i class="fas fa-save me-2"></i> Guardar Cambios
                            </button>
                            <a href="javascript:void(0);" onclick="navigateTo('agregar_categoria_producto.php')" class="btn btn-link text-muted text-decoration-none small">
                                <i class="fas fa-times me-1"></i> Cancelar operación
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php
require_once("../models/footer.php");
?>