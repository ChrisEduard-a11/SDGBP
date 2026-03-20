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
    ORDER BY usuario.nombre ASC, pagos.fecha_pago ASC, pagos.referencia ASC";
$result = $conexion->query($sql);
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
        background: var(--primary-gradient);
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
</style>

<div id="layoutSidenav_content">
    <div class="container-fluid dashboard-container px-4">
        
        <header class="mb-5 d-flex justify-content-between align-items-center fade-in-up">
            <div>
                <h1 class="fw-bold mb-0">Centro de Aprobaciones</h1>
                <p class="text-muted">Gestión inteligente de flujos financieros</p>
            </div>
            <div class="breadcrumb-container">
                <ol class="breadcrumb bg-transparent p-0 m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigateTo('inicio.php')" class="text-decoration-none"><i class="fas fa-home me-1"></i> Dashboard</a></li>
                    <li class="breadcrumb-item active">Aprobar Pagos</li>
                </ol>
            </div>
        </header>

        <!-- Metrics Section -->
        <div class="row g-4 mb-5 fade-in-up" style="animation-delay: 0.1s;">
            <div class="col-xl-4 col-md-6">
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
            <div class="col-xl-4 col-md-6">
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
            <div class="col-xl-4 col-md-12">
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
            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <h4 class="fw-bold mb-0"><i class="fas fa-tasks me-3 text-primary"></i> Cola de Procesamiento</h4>
                <div class="badge bg-soft-primary px-3 py-2 text-primary rounded-pill">
                    Monitor en Tiempo Real
                </div>
            </div>
            <div class="card-body table-container">
                <div class="table-responsive">
                    <table id="datatablesSimple" class="table custom-table w-100">
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
                                <?php $result->data_seek(0); ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr class="text-center">
                                        <td>
                                            <div class="d-flex align-items-center justify-content-center">
                                                <div class="avatar-sm bg-light text-primary rounded-circle p-2 me-2">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <span class="fw-bold"><?php echo htmlspecialchars($row['nombre_cliente'] ?? 'Sin UPU'); ?></span>
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
                                                    onclick="abrirModalAprobar(<?php echo $row['id']; ?>, '<?php echo $row['monto']; ?>', '<?php echo $row['referencia']; ?>')"
                                                    data-bs-toggle="tooltip" title="Aprobar Pago">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                
                                                <button type="button"
                                                    class="btn-action btn-reject"
                                                    onclick="rechazarPago(<?php echo $row['id']; ?>)"
                                                    data-bs-toggle="tooltip" title="Rechazar Pago">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                            <!-- Formularios ocultos -->
                                            <form id="form-aprobar-<?php echo $row['id']; ?>" method="post" action="../acciones/aprobar_pago.php" style="display:none;">
                                                <input type="hidden" name="id" value="<?php echo $row["id"]; ?>">
                                                <input type="hidden" name="accion" value="aprobar">
                                                <input type="hidden" name="comision" id="comision-<?php echo $row['id']; ?>">
                                            </form>
                                            <form id="form-rechazar-<?php echo $row['id']; ?>" method="post" action="../acciones/aprobar_pago.php" style="display:none;">
                                                <input type="hidden" name="id" value="<?php echo $row["id"]; ?>">
                                                <input type="hidden" name="accion" value="rechazar">
                                                <input type="hidden" name="descripcion" id="descripcion-<?php echo $row['id']; ?>">
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
    function abrirModalAprobar(id, monto, referencia) {
        if (!modalAprobar) {
            modalAprobar = new bootstrap.Modal(document.getElementById('modalAprobarPago'));
        }
        currentPagoId = id;
        document.getElementById('modal-monto').innerText = `${parseFloat(monto).toLocaleString('es-VE', {minimumFractionDigits: 2})} Bs`;
        document.getElementById('modal-referencia').innerText = referencia;
        document.getElementById('modal-comision').value = '0,00';
        modalAprobar.show();
    }

    document.getElementById('btn-confirmar-aprobar').addEventListener('click', function() {
        if (currentPagoId) {
            let comisionRaw = document.getElementById('modal-comision').value.trim();
            
            // Lógica robusta para parsear montos con comas o puntos
            if (comisionRaw.includes(',') && comisionRaw.includes('.')) {
                // Formato mixto: 1.000,50 -> elimina puntos y cambia coma a punto
                comisionRaw = comisionRaw.replace(/\./g, "").replace(",", ".");
            } else if (comisionRaw.includes(',')) {
                // Formato solo con coma: 1,50 o 1000,50 -> cambia coma a punto
                comisionRaw = comisionRaw.replace(",", ".");
            } else if (comisionRaw.includes('.')) {
                // Si tiene varios puntos (ej: 1.000.000), asumimos que son miles (eliminar)
                if ((comisionRaw.match(/\./g) || []).length > 1) {
                    comisionRaw = comisionRaw.replace(/\./g, "");
                }
                // Si tiene 1 solo punto (ej: 1.50) lo interpretamos como decimal y lo dejamos intacto.
            }
            
            const comision = parseFloat(comisionRaw);
            
            if (isNaN(comision) || comision < 0) {
                 Swal.fire({
                    icon: 'error',
                    title: 'Comisión No Válida',
                    text: 'Por favor, ingrese un monto correcto.',
                    confirmButtonColor: '#764ba2'
                 });
                 return;
            }
            
            modalAprobar.hide();
            document.getElementById(`comision-${currentPagoId}`).value = comision;
            
            Swal.fire({
                title: '¿Confirmar Aprobación?',
                text: 'El saldo del usuario será incrementado y este registro será marcado como procesado.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2af598',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, Confirmar',
                cancelButtonText: 'Cancelar',
                background: '#fff',
                backdrop: `rgba(0,0,123,0.1)`
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Procesando...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading() }
                    });
                    document.getElementById(`form-aprobar-${currentPagoId}`).submit();
                }
            });
        }
    });

    function rechazarPago(id) {
        Swal.fire({
            title: 'Motivo del Rechazo',
            text: 'Indique la razón técnica del rechazo del comprobante:',
            input: 'textarea',
            inputPlaceholder: 'Ej: Comprobante ilegible o referencia duplicada...',
            showCancelButton: true,
            confirmButtonText: 'Ejecutar Rechazo',
            cancelButtonText: 'Volver',
            confirmButtonColor: '#ff0844',
            preConfirm: (descripcion) => {
                if (!descripcion || descripcion.trim() === '') {
                    Swal.showValidationMessage('Es obligatorio proporcionar un motivo.');
                    return false;
                }
                return descripcion;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(`descripcion-${id}`).value = result.value;
                document.getElementById(`form-rechazar-${id}`).submit();
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>

<?php
require_once("../models/footer.php");
?>