<?php
include_once("../models/header.php");
include_once("../models/funciones.php");
require_once("../conexion.php");

// Verificar si el usuario tiene permisos de tipo "cont"
if ($_SESSION["tipo"] != "cont" && $_SESSION["tipo"] != "admin") {
    header("Location: inicio.php");
    exit();
}

// 1. Obtener métricas para el Dashboard
$sql_metrics = "SELECT 
    COUNT(*) as total_count, 
    SUM(monto) as total_amount,
    AVG(monto) as avg_amount
    FROM pagos 
    WHERE estado = 'pendiente'";
$metrics_result = $conexion->query($sql_metrics);
$metrics = $metrics_result->fetch_assoc();

// 2. Obtener pagos pendientes con UPU (usuario)
$sql = "SELECT pagos.*, usuario_pagos.usuario_id AS usuario_id, usuario.nombre AS nombre_cliente
    FROM pagos
    LEFT JOIN usuario_pagos ON pagos.id = usuario_pagos.pago_id
    LEFT JOIN usuario ON usuario_pagos.usuario_id = usuario.id_usuario
    WHERE pagos.estado = 'pendiente'
    ORDER BY usuario.nombre ASC, pagos.id ASC";
$result = $conexion->query($sql);

// Inicializar tokens en sesión
if (!isset($_SESSION['form_tokens'])) {
    $_SESSION['form_tokens'] = [];
}
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
    [data-theme="dark"] {
        --glass-bg: rgba(30, 41, 59, 0.7);
        --glass-border: rgba(255, 255, 255, 0.1);
    }

    body {
        font-family: 'Inter', sans-serif;
    }

    #layoutSidenav_content {
        background: transparent;
    }

    .dashboard-container {
        padding-top: 2rem;
        padding-bottom: 2rem;
    }

    /* Glassmorphism Classes */
    .glass-card {
        background: var(--glass-bg);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border);
        border-radius: 1.5rem;
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
    }

    /* Metrics Dashboard */
    .metric-card {
        border: none;
        border-radius: 1.25rem;
        color: white;
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        overflow: hidden;
        position: relative;
    }
    .metric-card:hover {
        transform: translateY(-8px);
    }
    .metric-card::after {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: rgba(255,255,255,0.1);
        transform: rotate(30deg);
        pointer-events: none;
    }
    .bg-gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .bg-gradient-success { background: linear-gradient(135deg, #2af598 0%, #009efd 100%); }
    .bg-gradient-warning { background: linear-gradient(135deg, #f6d365 0%, #fda085 100%); }

    .metric-icon {
        font-size: 3rem;
        opacity: 0.3;
        position: absolute;
        right: 1.5rem;
        bottom: 1rem;
    }

    /* Table Styles */
    .table-container {
        padding: 2rem;
        margin-top: 2rem;
    }
    .custom-table thead th {
        background-color: transparent;
        color: var(--text-muted);
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        border-bottom: 2px solid var(--glass-border);
        padding: 1rem;
    }
    .custom-table tbody tr {
        transition: all 0.2s;
        border-radius: 1rem;
    }
    [data-theme="dark"] .custom-table tbody tr {
        color: #f8fafc;
    }
    .custom-table tbody tr:hover {
        background-color: rgba(66, 153, 225, 0.05);
        transform: scale(1.005);
    }
    [data-theme="dark"] .custom-table tbody tr:hover {
        background-color: rgba(255, 255, 255, 0.05);
    }
    .custom-table td {
        padding: 1.25rem 1rem;
        color: inherit;
        border-bottom: 1px solid var(--glass-border);
    }

    /* Status & Badges */
    .status-badge {
        font-weight: 600;
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        font-size: 0.85rem;
    }
    .amount-badge {
        font-size: 1.1rem;
        font-weight: 700;
        color: #2b6cb0;
        background: rgba(66, 153, 225, 0.1);
        padding: 0.4rem 0.8rem;
        border-radius: 0.75rem;
        display: inline-block;
    }
    [data-theme="dark"] .amount-badge {
        color: #60a5fa;
        background: rgba(96, 165, 250, 0.1);
    }

    /* Actions */
    .btn-action {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
        border: none;
        margin: 0 4px;
    }
    .btn-approve {
        background-color: #c6f6d5;
        color: #22543d;
    }
    .btn-approve:hover {
        background-color: #2f855a;
        color: white;
        box-shadow: 0 4px 12px rgba(48, 133, 90, 0.3);
    }
    .btn-reject {
        background-color: #fed7d7;
        color: #742a2a;
    }
    .btn-reject:hover {
        background-color: #c53030;
        color: white;
        box-shadow: 0 4px 12px rgba(197, 48, 48, 0.3);
    }

    /* UPU Group Header */
    .upu-group-header td {
        background: rgba(66, 153, 225, 0.05) !important;
        border-left: 5px solid #4299e1;
        text-align: left !important;
        padding: 1.5rem 2rem !important;
    }
    [data-theme="dark"] .upu-group-header td {
        background: rgba(255, 255, 255, 0.05) !important;
        border-left: 5px solid #60a5fa;
    }
    .bg-soft-primary {
        background-color: rgba(66, 153, 225, 0.1);
        color: #2b6cb0;
    }
    [data-theme="dark"] .bg-soft-primary {
        background-color: rgba(96, 165, 250, 0.1);
        color: #93c5fd;
    }
    .avatar-sm.bg-primary-premium {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        box-shadow: 0 4px 10px rgba(118, 75, 162, 0.3);
    }

    /* Modal Styling */
    .modal-content-glass {
        background: #ffffff;
        border: none;
        border-radius: 2rem;
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    }
    [data-theme="dark"] .modal-content-glass {
        background: rgba(30, 41, 59, 0.95);
        backdrop-filter: blur(15px);
        border: 1px solid var(--glass-border);
        box-shadow: 0 15px 35px rgba(0,0,0,0.4);
    }
    .modal-header-premium {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 2rem 2rem 0 0;
        padding: 1.5rem;
    }

    /* Animations */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .fade-in-up {
        animation: fadeIn 0.6s ease-out forwards;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .dashboard-container {
            padding-top: 1rem;
            padding-bottom: 1rem;
        }
        .table-container {
            padding: 1rem;
        }
        .metric-card {
            padding: 1.5rem !important;
        }
        .metric-icon {
            font-size: 2.5rem;
        }
        header.page-header-standard h1 {
            font-size: 1.5rem;
        }
        .upu-group-header td {
            padding: 1rem !important;
        }
    }

    @media (max-width: 576px) {
        .btn-action {
            width: 36px;
            height: 36px;
        }
        .amount-badge {
            font-size: 0.9rem;
            padding: 0.3rem 0.6rem;
        }
    }
</style>

<div id="layoutSidenav_content">
    <div class="container-fluid dashboard-container px-4">
        
        <header class="page-header-standard d-flex justify-content-between align-items-center flex-wrap gap-3 animate__animated animate__fadeIn">
            <div>
                <h1 class="fw-bold mb-0 text-primary"><i class="fas fa-check-double me-2"></i>Centro de Aprobaciones</h1>
                <p class="text-muted mb-0">Gestión inteligente de flujos financieros y validación de pagos</p>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigateTo('inicio.php')" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active">Aprobar Pagos</li>
                </ol>
            </nav>
        </header>

        <!-- Metrics Section -->
        <div class="row g-4 mb-5 fade-in-up" style="animation-delay: 0.1s;">
            <div class="col-xl-4 col-md-6 col-12">
                <div class="card metric-card bg-gradient-primary shadow-lg p-4">
                    <div class="card-body p-0">
                        <h6 class="text-uppercase mb-2 opacity-75 fw-bold">Volumen Pendiente</h6>
                        <h2 class="display-6 fw-bold mb-0">
                            <?php echo number_format($metrics['total_amount'] ?? 0, 2, ',', '.'); ?> <small class="fs-4">Bs</small>
                        </h2>
                        <i class="fas fa-wallet metric-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 col-12">
                <div class="card metric-card bg-gradient-warning shadow-lg p-4">
                    <div class="card-body p-0">
                        <h6 class="text-uppercase mb-2 opacity-75 fw-bold">Pagos en Cola</h6>
                        <h2 class="display-6 fw-bold mb-0">
                            <?php echo $metrics['total_count'] ?? 0; ?> <small class="fs-4">Items</small>
                        </h2>
                        <i class="fas fa-history metric-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-12 col-12">
                <div class="card metric-card bg-gradient-success shadow-lg p-4">
                    <div class="card-body p-0">
                        <h6 class="text-uppercase mb-2 opacity-75 fw-bold">Ticket Promedio</h6>
                        <h2 class="display-6 fw-bold mb-0">
                            <?php echo number_format($metrics['avg_amount'] ?? 0, 2, ',', '.'); ?> <small class="fs-4">Bs</small>
                        </h2>
                        <i class="fas fa-chart-line metric-icon"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="card glass-card fade-in-up" style="animation-delay: 0.2s;">
            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <h4 class="fw-bold mb-0"><i class="fas fa-tasks me-3 text-primary"></i> Cola de Procesamiento</h4>
                <div class="ms-md-auto me-md-4 w-100" style="max-width: 350px;">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="upuSearchInput" class="form-control bg-light border-start-0" placeholder="Buscar pagos...">
                    </div>
                </div>
                <div class="badge bg-soft-primary px-3 py-2 text-primary rounded-pill">
                    Monitor en Tiempo Real
                </div>
            </div>
            <div class="card-body table-container">
                <div class="table-responsive">
                    <table id="tbl-aprobaciones" class="table custom-table w-100">
                        <thead class="text-center">
                            <tr>
                                <th>Origen (UPU)</th>
                                <th>Transacción</th>
                                <th>Monto</th>
                                <th>Método / Ref</th>
                                <th>Fecha</th>
                                <th>Entidad</th>
                                <th>Recibo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php 
                                    $result->data_seek(0); 
                                    $current_upu = null;
                                ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <?php 
                                        $upu_name = htmlspecialchars($row['nombre_cliente'] ?? 'Sin UPU'); 
                                        if ($current_upu !== $upu_name):
                                            $current_upu = $upu_name;
                                    ?>
                                        <tr class="upu-group-header">
                                            <td colspan="8">
                                                <div class="d-flex align-items-center flex-wrap gap-3">
                                                    <div class="avatar-sm bg-primary-premium text-white rounded-circle p-2 me-md-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                        <i class="fas fa-university"></i>
                                                    </div>
                                                    <div class="me-auto">
                                                        <h5 class="mb-0 fw-bold text-primary">Unidad: <?php echo $upu_name; ?></h5>
                                                        <small class="text-muted text-uppercase tracking-wider">Cola de aprobación por entidad</small>
                                                    </div>
                                                    <span class="badge bg-soft-primary px-3 py-2 rounded-pill fw-bold">
                                                        <i class="fas fa-layer-group me-1"></i> Agrupado por UPU
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                    <tr class="text-center">
                                        <td>
                                            <div class="d-flex align-items-center justify-content-center">
                                                <div class="avatar-sm bg-light text-primary rounded-circle p-2 me-2">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <span class="fw-bold"><?php echo $upu_name; ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-dark fw-semibold"><?php echo htmlspecialchars($row["tipo"]); ?></span>
                                            <br><small class="text-muted"><?php echo htmlspecialchars(substr($row["descripcion"], 0, 20)) . '...'; ?></small>
                                        </td>
                                        <td>
                                            <span class="amount-badge">
                                                <?php echo number_format($row["monto"], 2, ',', '.'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-dark"><?php echo htmlspecialchars($row["metodo_pago"]); ?></span>
                                            <br><span class="badge bg-light text-secondary border"><?php echo htmlspecialchars($row["referencia"]); ?></span>
                                        </td>
                                        <td class="text-muted"><?php echo date('d/M/Y', strtotime($row["fecha_pago"])); ?></td>
                                        <td class="fw-semibold"><?php echo htmlspecialchars($row["cliente"]); ?></td>
                                        <td>
                                            <?php if (!empty($row["comprobante_archivo"])): ?>
                                                <button onclick="previewComprobante('<?php echo $row['comprobante_archivo']; ?>')" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm border-0 bg-light">
                                                    <i class="fas fa-image me-1"></i> Ver
                                                </button>
                                            <?php
        else: ?>
                                                <span class="text-muted small">N/A</span>
                                            <?php
        endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center">
                                                <button type="button"
                                                    class="btn-action btn-approve"
                                                    onclick="abrirModalAprobar(<?php echo $row['id']; ?>, '<?php echo $row['monto']; ?>', '<?php echo $row['referencia']; ?>', '<?php echo $row['tipo']; ?>', '<?php echo $upu_name; ?>', '<?php echo date('d/m/Y', strtotime($row['fecha_pago'])); ?>', '<?php echo htmlspecialchars($row['cliente']); ?>')"
                                                    data-bs-toggle="tooltip" title="Aprobar Pago">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                
                                                <button type="button"
                                                    class="btn-action btn-reject"
                                                    onclick="rechazarPago(<?php echo $row['id']; ?>, '<?php echo $row['monto']; ?>', '<?php echo $row['referencia']; ?>', '<?php echo $upu_name; ?>', '<?php echo date('d/m/Y', strtotime($row['fecha_pago'])); ?>', '<?php echo htmlspecialchars($row['cliente']); ?>')"
                                                    data-bs-toggle="tooltip" title="Rechazar Pago">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                            <!-- Formularios ocultos -->
                                             <?php
                                                $idempotency_token = bin2hex(random_bytes(16));
                                                $_SESSION['form_tokens'][$idempotency_token] = time();
                                             ?>
                                             <form id="form-aprobar-<?php echo $row['id']; ?>" method="post" action="../acciones/aprobar_pago.php" style="display:none;">
                                                 <input type="hidden" name="id" value="<?php echo $row["id"]; ?>">
                                                 <input type="hidden" name="accion" value="aprobar">
                                                 <input type="hidden" name="comision" id="comision-<?php echo $row['id']; ?>">
                                                 <input type="hidden" name="idempotency_token" value="<?php echo $idempotency_token; ?>">
                                             </form>
                                             <form id="form-rechazar-<?php echo $row['id']; ?>" method="post" action="../acciones/aprobar_pago.php" style="display:none;">
                                                 <input type="hidden" name="id" value="<?php echo $row["id"]; ?>">
                                                 <input type="hidden" name="accion" value="rechazar">
                                                 <input type="hidden" name="descripcion" id="descripcion-<?php echo $row['id']; ?>">
                                                 <input type="hidden" name="idempotency_token" value="<?php echo $idempotency_token; ?>">
                                             </form>
                                        </td>
                                    </tr>
                                <?php
    endwhile; ?>
                            <?php
endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Aprobar -->
    <div class="modal fade" id="modalAprobarPago" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-glass shadow-lg">
                <div class="modal-header modal-header-premium border-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-check-double me-2"></i> Confirmar Operación</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted">Revise los detalles antes de liberar los fondos:</p>
                    
                    <div class="p-4 rounded-4 mb-4 border border-info" style="background: rgba(0, 158, 253, 0.05);">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Unidad / UPU:</span>
                            <span id="modal-upu" class="fw-bold text-dark"></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Entidad / Cliente:</span>
                            <span id="modal-entidad" class="fw-bold text-dark"></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Fecha del Pago:</span>
                            <span id="modal-fecha" class="fw-bold text-dark"></span>
                        </div>
                        <hr class="my-3 opacity-10">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Monto a Procesar:</span>
                            <span id="modal-monto" class="fw-bold fs-5"></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Referencia bancaria:</span>
                            <span id="modal-referencia" class="fw-bold text-primary"></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="modal-comision" class="form-label fw-bold">Comisión por Gestión (Opcional)</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-tag text-muted"></i></span>
                            <input type="text" class="form-control border-start-0 ps-0 text-end fw-bold campo-monto" id="modal-comision" value="0,00">
                            <span class="input-group-text bg-white">Bs.</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-link text-muted text-decoration-none me-auto" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary px-5 py-3 rounded-pill fw-bold shadow" id="btn-confirmar-aprobar">
                        Liberar Pago <i class="fas fa-paper-plane ms-2"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Rechazar -->
    <div class="modal fade" id="modalRechazarPago" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-glass shadow-lg">
                <div class="modal-header border-0" style="background: linear-gradient(135deg, #e71d36 0%, #ff6b6b 100%); color: white; border-radius: 2rem 2rem 0 0; padding: 1.5rem;">
                    <h5 class="modal-title fw-bold text-white"><i class="fas fa-times-circle me-2"></i> Declinar Transacción</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted">Estás a punto de rechazar este movimiento. Por favor, detalla la razón técnica para el registro de auditoría:</p>
                    
                    <div class="p-4 rounded-4 mb-4 border border-danger" style="background: rgba(231, 29, 54, 0.05);">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Unidad / UPU:</span>
                            <span id="modal-upu-rechazo" class="fw-bold text-dark"></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Entidad / Cliente:</span>
                            <span id="modal-entidad-rechazo" class="fw-bold text-dark"></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Fecha del Pago:</span>
                            <span id="modal-fecha-rechazo" class="fw-bold text-dark"></span>
                        </div>
                        <hr class="my-3 opacity-10">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Monto de Operación:</span>
                            <span id="modal-monto-rechazo" class="fw-bold fs-5 text-dark"></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Referencia bancaria:</span>
                            <span id="modal-referencia-rechazo" class="fw-bold text-danger"></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="modal-motivo-rechazo" class="form-label fw-bold">Motivo del Rechazo <span class="text-danger">*</span></label>
                        <textarea class="form-control bg-light" id="modal-motivo-rechazo" rows="3" placeholder="Ej. Comprobante ilegible, referencia duplicada o monto incorrecto..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-link text-muted text-decoration-none me-auto" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger px-5 py-3 rounded-pill fw-bold shadow" id="btn-confirmar-rechazo">
                        Revisar Rechazo <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Receipt Preview -->
    <div class="modal fade" id="modalPreview" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content glass-card border-0">
                <div class="modal-header bg-gradient-wallet text-white border-0 py-3 px-4 rounded-top-custom">
                    <h5 class="modal-title fw-bold"><i class="fas fa-file-invoice me-2 text-warning"></i> Comprobante</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0 text-center bg-dark bg-opacity-10 d-flex flex-column" style="min-height: 400px;">
                    <div id="previewContainer" class="flex-grow-1 d-flex align-items-center justify-content-center p-2">
                        <!-- Content injected by JS -->
                    </div>
                    <div class="p-3 bg-white bg-opacity-50 backdrop-blur d-flex justify-content-center gap-2">
                        <button id="zoomBtn" class="btn btn-outline-dark rounded-pill px-3 shadow-sm fw-bold d-none" onclick="toggleZoom()">
                            <i class="fas fa-search-plus me-1"></i> Zoom
                        </button>
                        <a id="downloadLink" href="" download class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">
                            <i class="fas fa-download me-2"></i> Descargar Original
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> 
<script>
    let modalPreview;
    let modalAprobar;
    let currentPagoId = null;

    function previewComprobante(archivo) {
        if (!modalPreview) {
            modalPreview = new bootstrap.Modal(document.getElementById('modalPreview'));
        }
        
        const path = `../uploads/comprobantes/${archivo}`;
        const container = document.getElementById('previewContainer');
        const zoomBtn = document.getElementById('zoomBtn');
        const extension = archivo.split('.').pop().toLowerCase();
        
        container.innerHTML = ''; // Limpiar anterior
        zoomBtn.classList.add('d-none');
        
        if (['jpg', 'jpeg', 'png', 'gif'].includes(extension)) {
            const img = document.createElement('img');
            img.src = path;
            img.id = 'imgPreview';
            img.className = 'img-fluid rounded shadow transition-all';
            img.style.maxHeight = '70vh';
            img.style.cursor = 'zoom-in';
            img.onclick = toggleZoom;
            container.appendChild(img);
            zoomBtn.classList.remove('d-none');
        } else if (extension === 'pdf') {
            const iframe = document.createElement('iframe');
            iframe.src = path;
            iframe.style.width = '100%';
            iframe.style.height = '70vh';
            iframe.style.border = 'none';
            container.appendChild(iframe);
        } else {
            container.innerHTML = `<div class="p-5 text-muted">
                <i class="fas fa-file-alt fa-4x mb-3"></i>
                <p>Vista previa no disponible para este formato (.${extension})</p>
            </div>`;
        }
        
        document.getElementById('downloadLink').href = path;
        modalPreview.show();
    }

    function toggleZoom() {
        const img = document.getElementById('imgPreview');
        const btn = document.getElementById('zoomBtn');
        if (!img) return;

        if (img.classList.contains('zoomed')) {
            img.classList.remove('zoomed');
            img.style.maxHeight = '70vh';
            img.style.maxWidth = '100%';
            img.style.cursor = 'zoom-in';
            btn.innerHTML = '<i class="fas fa-search-plus me-1"></i> Zoom';
        } else {
            img.classList.add('zoomed');
            img.style.maxHeight = 'none';
            img.style.maxWidth = 'none';
            img.style.cursor = 'zoom-out';
            btn.innerHTML = '<i class="fas fa-search-minus me-1"></i> Alejar';
        }
    }
</script>

</style>

<script>
    // La función abrirModalAprobar y rechazarPago se manejan globalmente en models/funciones.php

    // El evento click para btn-confirmar-aprobar es manejado globalmente en models/funciones.php

    // La función rechazarPago(id) se maneja globalmente desde models/funciones.php

    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Búsqueda personalizada compatible con agrupamiento
        const searchInput = document.getElementById('upuSearchInput');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const value = this.value.toLowerCase();
                const table = document.getElementById('tbl-aprobaciones');
                const tbody = table.querySelector('tbody');
                const rows = tbody.querySelectorAll('tr');
                
                rows.forEach(row => {
                    if (row.classList.contains('upu-group-header')) return;
                    
                    const text = row.innerText.toLowerCase();
                    if (text.includes(value)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Mostrar/Ocultar encabezados de grupo si no tienen hijos visibles
                const groupHeaders = tbody.querySelectorAll('.upu-group-header');
                groupHeaders.forEach(header => {
                    let nextRow = header.nextElementSibling;
                    let hasVisibleChildren = false;
                    
                    while (nextRow && !nextRow.classList.contains('upu-group-header')) {
                        if (nextRow.style.display !== 'none') {
                            hasVisibleChildren = true;
                            break;
                        }
                        nextRow = nextRow.nextElementSibling;
                    }
                    
                    header.style.display = hasVisibleChildren || value === '' ? '' : 'none';
                });
            });
        }
    });
</script>

<?php
require_once("../models/footer.php");
?>
