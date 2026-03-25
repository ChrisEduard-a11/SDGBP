<?php
require_once("../models/header.php");
require_once("../conexion.php");


$id_cliente = $_GET['id'] ?? null;

if (!$id_cliente) {
    $_SESSION['estatus'] = 'error';
    $_SESSION['mensaje'] = 'Cliente no encontrado.';
    header("Location: ver_clientes.php");
    exit;
}

$sql = "SELECT nombre FROM cliente WHERE id_cliente = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_cliente);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['estatus'] = 'error';
    $_SESSION['mensaje'] = 'Cliente no encontrado.';
    header("Location: ver_clientes.php");
    exit;
}

$cliente = $result->fetch_assoc();
$nombre_cliente = htmlspecialchars($cliente['nombre']); // Nombre original para mostrar
?>

<style>
    /* =========================================
       SISTEMA SDGBP - DISEÑO ULTRA PREMIUM 2026
       EDICIÓN DE CLIENTES
       ========================================= */
    :root {
        --primary: #f18000;
        --primary-dark: #d67100;
        --primary-light: rgba(241, 128, 0, 0.1);
        --accent-warning: #f59e0b;
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
        background: linear-gradient(135deg, var(--accent-warning) 0%, #d97706 100%);
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
        border-color: var(--accent-warning) !important;
        box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1) !important;
    }

    .btn-save-premium {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
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

    .btn-save-premium:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 20px rgba(16, 185, 129, 0.3) !important;
    }

    .btn-cancel-premium {
        background: #f1f5f9 !important;
        border: 1.5px solid var(--border-color) !important;
        border-radius: 15px !important;
        padding: 0.85rem !important;
        font-weight: 600 !important;
        color: var(--text-muted) !important;
        text-align: center;
        text-decoration: none !important;
        transition: all 0.2s ease !important;
    }

    .btn-cancel-premium:hover {
        background: #e2e8f0 !important;
        color: var(--text-main) !important;
    }

    .input-icon-box {
        background: #fff7ed;
        color: var(--accent-warning);
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
                <h1 class="fw-bold mb-0 text-primary"><i class="fas fa-user-edit me-2"></i>Editar Cliente</h1>
                <p class="text-muted">Actualización de información del registro seleccionado en el directorio</p>
                <div class="mt-1"><span class="badge bg-light text-dark fw-bold border">Modificando: <?php echo $nombre_cliente; ?></span></div>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigateTo('inicio.php')" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigateTo('ver_clientes.php')" class="text-decoration-none">Clientes</a></li>
                    <li class="breadcrumb-item active">Editar</li>
                </ol>
            </nav>
        </header>
        
        <div class="card card-premium shadow-lg mx-auto" style="max-width: 600px;">
            <div class="card-premium-header">
                <h5><i class="fas fa-edit me-2"></i> Modificando: <span class="fw-normal text-white-50"><?php echo $nombre_cliente; ?></span></h5>
            </div>
            
            <div class="card-body p-4 p-md-5">
                <form method="post" action="../acciones/controlador_editar_cliente.php">
                    <input type="hidden" name="id_cliente" value="<?php echo $id_cliente; ?>">
                    
                    <div class="mb-5">
                        <div class="d-flex align-items-center mb-3">
                            <div class="input-icon-box">
                                <i class="fas fa-id-card"></i>
                            </div>
                            <label for="nombre" class="form-label fw-bold mb-0 text-dark">Nombre Actualizado</label>
                        </div>
                        <input 
                            type="text" 
                            class="form-control form-control-premium" 
                            id="nombre" 
                            name="nombre" 
                            value="<?php echo $nombre_cliente; ?>" 
                            placeholder="Ingrese el nuevo nombre del cliente" 
                            required
                        >
                    </div>

                    <div class="d-grid gap-3">
                        <button type="submit" class="btn btn-save-premium">
                            <i class="fas fa-save me-2"></i> Actualizar y Guardar
                        </button>
                        
                        <a onclick="navigateTo('ver_clientes.php')" class="btn btn-cancel-premium">
                            <i class="fas fa-times-circle me-2"></i> Descartar Cambios
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php
require_once("../models/footer.php");
?>
