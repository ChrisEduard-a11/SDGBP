<?php
require_once("../models/header.php");
// Se incluye el controlador para obtener los datos
include('../acciones/controlador_categoria.php');

// Asumimos que esta función existe y obtiene las categorías
// Nota: 'obtenerCategorias' requiere la conexión, que no se ve en este archivo pero se asume que está disponible globalmente o inyectada.
// Si el archivo 'controlador_categoria.php' no define $conexion, esto podría fallar. Se mantendrá la estructura original.
// $categorias = obtenerCategorias($conexion); 
// Dado que $conexion no está aquí, asumimos que se inicializa en el controlador o es global.
$categorias = obtenerCategorias($conexion);
?>
<style>
    /* =========================================
       SISTEMA SDGBP - DISEÑO ULTRA PREMIUM 2026
       GESTIÓN DE CATEGORÍAS
       ========================================= */
    :root {
        --primary: #f18000;
        --primary-dark: #d67100;
        --primary-light: rgba(241, 128, 0, 0.1);
        --accent-blue: #3b82f6;
        --accent-green: #10b981;
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
        padding: 1.5rem 2rem;
        border: none !important;
        color: white;
    }

    .header-register { background: linear-gradient(135deg, var(--accent-green) 0%, #059669 100%); }
    .header-list { background: linear-gradient(135deg, var(--accent-blue) 0%, #1d4ed8 100%); }

    .card-premium-header h5 {
        margin: 0;
        font-weight: 700;
        letter-spacing: 0.5px;
    }

    .form-control-premium {
        border: 1.5px solid var(--border-color) !important;
        border-radius: 12px !important;
        padding: 0.75rem 1rem !important;
        font-weight: 500 !important;
        transition: all 0.3s ease !important;
    }

    .form-control-premium:focus {
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 4px var(--primary-light) !important;
    }

    #datatablesSimple {
        border-collapse: separate !important;
        border-spacing: 0 8px !important;
    }

    #datatablesSimple thead th {
        background: #f8fafc !important;
        color: var(--text-muted) !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        font-size: 0.75rem !important;
        padding: 1.25rem 1rem !important;
        border: none !important;
    }

    #datatablesSimple td {
        padding: 1rem !important;
        vertical-align: middle !important;
        border: none !important;
    }

    .btn-delete-premium {
        background: #fef2f2;
        color: #dc2626;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .btn-delete-premium:hover {
        background: #dc2626;
        color: white;
        transform: scale(1.05);
    }

    .btn-save-premium {
        background: linear-gradient(135deg, var(--accent-green) 0%, #059669 100%);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        font-weight: 700;
        box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.4);
        transition: all 0.3s ease;
    }

    .btn-save-premium:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.5);
        color: white;
    }
</style>

<div id="layoutSidenav_content">
    <div class="container-fluid px-4 py-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h1 class="mt-2 mb-1 fw-bold text-dark">Gestión de Categorías</h1>
                <p class="text-muted mb-0">Organice y clasifique los bienes nacionales</p>
            </div>
            <div class="d-none d-md-block text-end">
                <span class="badge bg-white text-dark border rounded-pill px-3 py-2 shadow-sm">
                    <i class="fas fa-tags text-primary me-2"></i> Bienes y Suministros
                </span>
            </div>
        </div>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-premium p-3 mb-4">
                <li class="breadcrumb-item"><a href="inicio.php" class="text-primary fw-600 text-decoration-none"><i class="fas fa-home me-1"></i> Inicio</a></li>
                <li class="breadcrumb-item active text-muted"><i class="fas fa-folder-open me-1"></i> Categorías</li>
            </ol>
        </nav>

        <div class="row g-4">
            <!-- REGISTRO -->
            <div class="col-lg-4">
                <div class="card card-premium shadow-lg border-0 h-100">
                    <div class="card-premium-header header-register">
                        <h5><i class="fas fa-plus-circle me-2"></i> Nueva Categoría</h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="post" action="../acciones/controlador_categoria_r.php">
                            <div class="form-group mb-4">
                                <label for="nombre" class="form-label small fw-bold text-muted mb-2">Nombre descriptivo</label>
                                <input type="text" class="form-control form-control-premium" id="nombre" name="nombre" placeholder="Ej: Equipos Médicos, Mobiliario..." required>
                                <div class="form-text small mt-2"><i class="fas fa-info-circle me-1"></i> El nombre de la categoría debe ser único en el sistema.</div>
                            </div>
                            <button type="submit" class="btn btn-save-premium w-100 py-3">
                                <i class="fas fa-check-circle me-2"></i> Guardar Clasificación
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- LISTADO -->
            <div class="col-lg-8">
                <div class="card card-premium shadow-lg border-0 h-100">
                    <div class="card-premium-header header-list d-flex justify-content-between align-items-center">
                        <h5><i class="fas fa-list-ul me-2"></i> Clasificaciones Activas</h5>
                        <span class="badge bg-white text-dark rounded-pill px-3 py-2 fw-bold">
                            Total: <?php echo count($categorias); ?>
                        </span>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table id="datatablesSimple" class="table">
                                <thead>
                                    <tr>
                                        <th style="width: 80px;" class="text-center">ID</th> 
                                        <th>Nombre de Categoría</th>
                                        <th style="width: 150px;" class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $contador = 1; 
                                    foreach ($categorias as $categoria) { ?>
                                        <tr class="align-middle">
                                            <td class="text-center">
                                                <span class="badge bg-light text-muted border px-2 py-1"><?php echo str_pad($contador++, 2, "0", STR_PAD_LEFT); ?></span>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark fs-6"><?php echo htmlspecialchars($categoria['nombre']); ?></div>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-delete-premium shadow-sm w-100" onclick="confirmDelete2(<?php echo $categoria['id']; ?>)" title="Eliminar Categoría">
                                                    <i class="fas fa-trash-alt me-1"></i> Quitar
                                                </button>
                                            </td>
                                        </tr>
                                    <?php } ?>
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