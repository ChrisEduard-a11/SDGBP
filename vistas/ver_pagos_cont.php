<?php
include_once("../models/header.php");
include_once("../models/funciones.php");
require_once("../conexion.php");

// Verificar si el usuario tiene permisos de tipo "cont" o "admin"
if ($_SESSION["tipo"] != "cont" && $_SESSION["tipo"] != "admin") {
    header("Location: inicio.php");
    exit();
}

// Variables para los filtros
$estado = $_GET['estado'] ?? '';
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';
$referencia = $_GET['referencia'] ?? '';
$usuario_upu = $_GET['usuario_upu'] ?? '';

// --- Lógica PHP para la Consulta de Pagos ---
$where_clauses = ["1=1"];
$params = [];

if (!empty($estado)) {
    $where_clauses[] = "pagos.estado = ?";
    $params[] = $estado;
}
if (!empty($fecha_inicio) && !empty($fecha_fin)) {
    $where_clauses[] = "pagos.fecha_pago BETWEEN ? AND ?";
    $params[] = $fecha_inicio;
    $params[] = $fecha_fin;
}
if (!empty($referencia)) {
    $where_clauses[] = "pagos.referencia LIKE ?";
    $params[] = "%$referencia%";
}
if (!empty($usuario_upu)) {
    $where_clauses[] = "usuario.id_usuario = ?";
    $params[] = $usuario_upu;
}

$where_sql = implode(" AND ", $where_clauses);

// Consulta Principal
$sql = "SELECT pagos.*, usuario.nombre AS nombre_cliente FROM pagos 
         INNER JOIN usuario_pagos ON pagos.id = usuario_pagos.pago_id
         INNER JOIN usuario ON usuario.id_usuario = usuario_pagos.usuario_id
         WHERE $where_sql
         ORDER BY id DESC";

$stmt = $conexion->prepare($sql);
if ($params) {
    $types = str_repeat("s", count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Consulta de Métricas (Basada en los mismos filtros)
$sql_metrics = "SELECT 
    SUM(CASE WHEN tipo = 'Ingreso' AND estado = 'aprobado' THEN monto ELSE 0 END) as total_ingresos,
    SUM(CASE WHEN tipo = 'Egreso' AND estado = 'aprobado' THEN monto ELSE 0 END) as total_egresos,
    COUNT(*) as total_transacciones
    FROM pagos 
    INNER JOIN usuario_pagos ON pagos.id = usuario_pagos.pago_id
    INNER JOIN usuario ON usuario.id_usuario = usuario_pagos.usuario_id
    WHERE $where_sql";

$stmt_m = $conexion->prepare($sql_metrics);
if ($params) {
    $stmt_m->bind_param($types, ...$params);
}
$stmt_m->execute();
$metrics = $stmt_m->get_result()->fetch_assoc();

$total_ingresos = $metrics['total_ingresos'] ?? 0;
$total_egresos = $metrics['total_egresos'] ?? 0;
$pago_count = $metrics['total_transacciones'] ?? 0;
$balance_global = $total_ingresos - $total_egresos;

// Consultas para selects
$query_usuarios_upu = "SELECT id_usuario, nombre FROM usuario WHERE tipos = 'upu'";
$result_usuarios_upu_filter = $conexion->query($query_usuarios_upu);
$result_usuarios_upu_export = $conexion->query($query_usuarios_upu);
$query_upu_saldos = "SELECT nombre, correo, saldo FROM usuario WHERE tipos = 'upu'";
$result_upu_saldos = $conexion->query($query_upu_saldos);
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root {
        --glass-bg: rgba(255, 255, 255, 0.85);
        --glass-border: rgba(255, 255, 255, 0.4);
        --glass-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.1);
        --accent-primary: #4361ee;
        --accent-success: #2ec4b6;
        --accent-danger: #e71d36;
        --text-dark: #2b2d42;
    }

    [data-theme="dark"] {
        --glass-bg: rgba(30, 41, 59, 0.7);
        --glass-border: rgba(255, 255, 255, 0.1);
        --text-dark: #f8fafc;
        --glass-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
    }

    body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
    [data-theme="dark"] body { background-color: #0f172a; }

    /* Glassmorphism Containers */
    .glass-card {
        background: var(--glass-bg);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border);
        border-radius: 20px;
        box-shadow: var(--glass-shadow);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    /* Metrics Dashboard */
    .metric-card {
        border-radius: 24px;
        padding: 1.5rem;
        border: none;
        overflow: hidden;
        position: relative;
        z-index: 1;
        height: 100%;
    }
    .metric-card::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        z-index: -1;
    }
    .bg-gradient-blue { background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%); }
    .bg-gradient-teal { background: linear-gradient(135deg, #2ec4b6 0%, #0891b2 100%); }
    .bg-gradient-rose { background: linear-gradient(135deg, #e71d36 0%, #b91c1c 100%); }
    .bg-gradient-indigo { background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); }

    .metric-value { font-size: 1.8rem; font-weight: 800; letter-spacing: -1px; }
    .metric-label { font-size: 0.85rem; font-weight: 500; opacity: 0.85; text-transform: uppercase; }
    .metric-icon { 
        position: absolute;
        right: -10px;
        bottom: -10px;
        font-size: 5rem;
        opacity: 0.15;
        transform: rotate(-15deg);
    }

    /* Premium Table */
    .custom-table { border-collapse: separate; border-spacing: 0 8px; }
    .custom-table thead th {
        background: transparent;
        border: none;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        padding: 1rem;
    }
    .custom-table tbody tr {
        background: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        transition: all 0.2s ease;
    }
    [data-theme="dark"] .custom-table tbody tr {
        background: #1e293b;
        color: #f8fafc;
    }
    .custom-table tbody tr:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        background: #fdfdfd;
    }
    [data-theme="dark"] .custom-table tbody tr:hover {
        background: #232f45;
    }
    .custom-table td {
        padding: 1.25rem 1rem;
        border: none;
        vertical-align: middle;
    }
    [data-theme="dark"] .custom-table td {
        color: #f8fafc;
    }
    .custom-table td:first-child { border-radius: 12px 0 0 12px; }
    .custom-table td:last-child { border-radius: 0 12px 12px 0; }

    /* Badge Styling */
    .badge-premium {
        padding: 0.5em 1em;
        border-radius: 30px;
        font-weight: 600;
        font-size: 0.75rem;
    }

    /* Animations */
    .fade-in-up {
        animation: fadeInUp 0.6s ease-out forwards;
        opacity: 0;
        transform: translateY(20px);
    }
    @keyframes fadeInUp {
        to { opacity: 1; transform: translateY(0); }
    }

    .stagger-1 { animation-delay: 0.1s; }
    .stagger-2 { animation-delay: 0.2s; }
    .stagger-3 { animation-delay: 0.3s; }
    .stagger-4 { animation-delay: 0.4s; }

    /* Search/Filter Styling */
    .search-pill {
        border-radius: 50px;
        border: 1px solid #e2e8f0;
        padding: 0.6rem 1.2rem;
        transition: all 0.3s ease;
    }
    .search-pill:focus {
        border-color: var(--accent-primary);
        box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.1);
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .metric-value { font-size: 1.4rem; }
        .metric-card { padding: 1.2rem; }
        .custom-table { min-width: 900px; } /* Forzar scroll en tablas muy anchas */
        .custom-table td { font-size: 0.85rem; padding: 1rem 0.75rem; }
        .page-header-standard { flex-direction: column; align-items: flex-start !important; gap: 1rem; }
        header.page-header-standard h1 { font-size: 1.5rem; }
    }

    @media (max-width: 576px) {
        .container-fluid { padding-left: 1rem !important; padding-right: 1rem !important; }
        .glass-card { padding: 1.5rem !important; }
    }
</style>

<div id="layoutSidenav_content">
    <div class="container-fluid px-4">
        
        <header class="page-header-standard d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 animate__animated animate__fadeIn">
            <div>
                <h1 class="fw-bold mb-0 text-primary"><i class="fas fa-globe me-2"></i>Balance Financiero Global</h1>
                <p class="text-muted mb-0">Historial detallado de transacciones y estados de cuenta de todas las unidades</p>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigateTo('inicio.php')" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active">Reporte Global</li>
                </ol>
            </nav>
        </header>

        <!-- Metrics Dashboard -->
        <div class="row g-4 mb-5 stagger-1 fade-in-up">
            <div class="col-xl-3 col-md-6 col-12">
                <div class="metric-card bg-gradient-blue text-white shadow-lg">
                    <div class="metric-label">Ventas / Ingresos</div>
                    <div class="metric-value"><?php echo number_format($total_ingresos, 2, ',', '.'); ?> <small>Bs</small></div>
                    <i class="fas fa-arrow-down metric-icon"></i>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 col-12">
                <div class="metric-card bg-gradient-rose text-white shadow-lg stagger-3 fade-in-up">
                    <div class="metric-label">Compras / Egresos</div>
                    <div class="metric-value"><?php echo number_format($total_egresos, 2, ',', '.'); ?> <small>Bs</small></div>
                    <i class="fas fa-arrow-up metric-icon"></i>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 col-12">
                <div class="metric-card bg-gradient-teal text-white shadow-lg stagger-2 fade-in-up">
                    <div class="metric-label">Balance Global</div>
                    <div class="metric-value"><?php echo number_format($balance_global, 2, ',', '.'); ?> <small>Bs</small></div>
                    <i class="fas fa-wallet metric-icon"></i>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="metric-card bg-gradient-indigo text-white shadow-lg stagger-4 fade-in-up">
                    <div class="metric-label">Transacciones</div>
                    <div class="metric-value"><?php echo $pago_count; ?> <small>Regs</small></div>
                    <i class="fas fa-exchange-alt metric-icon"></i>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="glass-card p-4 mb-4 fade-in-up stagger-2">
            <div class="d-flex align-items-center mb-3">
                <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                    <i class="fas fa-filter text-primary"></i>
                </div>
                <h5 class="mb-0 fw-bold">Filtros Inteligentes</h5>
            </div>
            <form method="get" class="row g-3">
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-bold text-muted uppercase">Estado</label>
                    <select name="estado" class="form-select border-0 bg-light rounded-3">
                        <option value="">Todos los Estados</option>
                        <option value="pendiente" <?php echo $estado == 'pendiente' ? 'selected' : ''; ?>>⏳ Pendientes</option>
                        <option value="aprobado" <?php echo $estado == 'aprobado' ? 'selected' : ''; ?>>✅ Aprobados</option>
                        <option value="rechazado" <?php echo $estado == 'rechazado' ? 'selected' : ''; ?>>❌ Rechazados</option>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-bold text-muted uppercase">Desde</label>
                    <input type="text" name="fecha_inicio" class="form-control border-0 bg-light rounded-3 datepicker-flat" placeholder="YYYY-MM-DD" value="<?php echo $fecha_inicio; ?>">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-bold text-muted uppercase">Hasta</label>
                    <input type="text" name="fecha_fin" class="form-control border-0 bg-light rounded-3 datepicker-flat" placeholder="YYYY-MM-DD" value="<?php echo $fecha_fin; ?>">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-bold text-muted uppercase">Usuario UPU</label>
                    <select name="usuario_upu" class="form-select border-0 bg-light rounded-3">
                        <option value="">Todas las UPU</option>
                        <?php
if ($result_usuarios_upu_filter->num_rows > 0) {
    $result_usuarios_upu_filter->data_seek(0);
    while ($row_usuario = $result_usuarios_upu_filter->fetch_assoc()): ?>
                                <option value="<?php echo $row_usuario['id_usuario']; ?>" <?php echo $usuario_upu == $row_usuario['id_usuario'] ? 'selected' : ''; ?>>
                                    <?php echo $row_usuario['nombre']; ?>
                                </option>
                            <?php
    endwhile;
}?>
                    </select>
                </div>
                <div class="col-12 text-end mt-4">
                    <a href="ver_pagos_cont.php" class="btn btn-link text-muted text-decoration-none me-3">Limpiar</a>
                    <button type="submit" class="btn btn-primary px-4 rounded-pill shadow-sm fw-bold">
                        <i class="fas fa-search me-2"></i> Aplicar Filtros
                    </button>
                </div>
            </form>
        </div>

        <!-- Export Section -->
        <div class="glass-card p-4 mb-4 fade-in-up stagger-3" style="border-left: 5px solid #2ec4b6;">
            <div class="d-flex align-items-center mb-3">
                <div class="bg-success bg-opacity-10 p-2 rounded-circle me-3">
                    <i class="fas fa-file-pdf text-success"></i>
                </div>
                <h5 class="mb-0 fw-bold">Exportación de Reportes</h5>
            </div>
            <form method="POST" action="../dompdf/exportar_pdf_I-E.php" class="row g-3 w-100" onsubmit="return validateFormExportPDF()" data-no-preloader="true">
                <div class="col-6 col-md-3">
                    <input type="text" name="filtro_fecha_inicio" class="form-control border-0 bg-light rounded-3 datepicker-flat w-100" placeholder="YYYY-MM-DD" value="<?php echo $fecha_inicio; ?>">
                </div>
                <div class="col-6 col-md-3">
                    <input type="text" name="filtro_fecha_fin" class="form-control border-0 bg-light rounded-3 datepicker-flat w-100" placeholder="YYYY-MM-DD" value="<?php echo $fecha_fin; ?>">
                </div>
                <div class="col-12 col-md-4">
                    <select name="usuario_upu" class="form-select border-0 bg-light rounded-3 w-100">
                        <option value="">Seleccionar UPU...</option>
                        <option value="all">Todas las UPU</option>
                        <?php
if ($result_usuarios_upu_export->num_rows > 0) {
    $result_usuarios_upu_export->data_seek(0);
    while ($row_usuario = $result_usuarios_upu_export->fetch_assoc()): ?>
                                <option value="<?php echo $row_usuario['id_usuario']; ?>"><?php echo $row_usuario['nombre']; ?></option>
                            <?php
    endwhile;
}?>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-success w-100 rounded-pill shadow-sm fw-bold">
                        <i class="fas fa-download me-2"></i> Reporte PDF
                    </button>
                </div>
            </form>
        </div>

        <!-- Main Data Table Card -->
        <div class="glass-card p-4 mb-5 fade-in-up stagger-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-list-ul text-primary"></i>
                    </div>
                    <h5 class="mb-0 fw-bold">Historial de Transacciones</h5>
                </div>
                <a href="exportar_excel_pagos.php?<?php echo http_build_query($_GET); ?>" class="btn btn-outline-success rounded-pill px-4 fw-bold" style="background: linear-gradient(135deg, #2ec4b6 0%, #0891b2 100%);" data-no-preloader="true">
                    <i class="fas fa-file-excel me-2"></i> Excel Full
                </a>
            </div>

            <div class="table-responsive">
                <table id="datatablesSimple" class="table custom-table">
                    <thead>
                        <tr>
                            <th>UPU / Cliente</th>
                            <th>Info Pago</th>
                            <th class="text-center">Monto</th>
                            <th class="text-center">Saldo Resultante</th>
                            <th class="text-center">Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <?php if ($row['estado'] == 'rechazado' && $estado != 'rechazado')
            continue; ?>
                                <tr class="align-middle">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle bg-light text-primary fw-bold p-2 rounded-circle me-3 text-center" style="width: 40px; height: 40px; line-height: 24px;">
                                                <?php echo substr($row["nombre_cliente"], 0, 1); ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark"><?php echo ucfirst($row["nombre_cliente"]); ?></div>
                                                <div class="text-muted small"><?php echo $row['cliente']; ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold text-dark"><?php echo ucfirst($row["descripcion"]); ?></div>
                                        <div class="text-muted extra-small">
                                            <span class="me-2"><i class="fas fa-calendar-alt me-1"></i><?php echo date('d/m/Y', strtotime($row["fecha_pago"])); ?></span>
                                            <span><i class="fas fa-barcode me-1"></i><?php echo $row["referencia"]; ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($row["tipo"] == "Ingreso"): ?>
                                            <span class="text-success fw-bold">+<?php echo number_format($row["monto"], 2, ',', '.'); ?></span>
                                            <div class="extra-small text-muted">Ingreso</div>
                                        <?php
        else: ?>
                                            <span class="text-danger fw-bold">-<?php echo number_format($row["monto"], 2, ',', '.'); ?></span>
                                            <div class="extra-small text-muted">Egreso</div>
                                        <?php
        endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($row['estado'] == 'aprobado'): ?>
                                            <span class="fw-bold" style="font-size: 0.95rem; color: var(--accent-primary);">
                                                <?php echo isset($row['saldo_resultante']) ? number_format($row['saldo_resultante'], 2, ',', '.') . ' Bs' : '<span class="text-muted opacity-50">N/A</span>'; ?>
                                            </span>
                                        <?php
        else: ?>
                                            <span class="text-muted small opacity-50">Pendiente</span>
                                        <?php
        endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php
        $badge_class = 'bg-warning text-dark';
        $icon = 'fa-clock';
        $label = 'Pendiente';
        if ($row['estado'] == 'aprobado') {
            $badge_class = 'bg-success';
            $icon = 'fa-check';
            $label = 'Aprobado';
        }
        if ($row['estado'] == 'rechazado') {
            $badge_class = 'bg-danger';
            $icon = 'fa-ban';
            $label = 'Rechazado';
        }
?>
                                        <span class="badge badge-premium <?php echo $badge_class; ?>">
                                            <i class="fas <?php echo $icon; ?> me-1"></i> <?php echo $label; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <?php if (!empty($row['comprobante_archivo'])): ?>
                                                <button class="btn btn-action btn-sm bg-primary bg-opacity-10 text-primary border-primary border-opacity-25 rounded-pill px-3 shadow-sm" 
                                                        onclick="previewComprobante('<?php echo $row['comprobante_archivo']; ?>')" 
                                                        title="Ver Comprobante">
                                                    <i class="fas fa-eye me-1"></i> Ver
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if ($row['estado'] == 'rechazado'): ?>
                                                <button class="btn btn-action btn-sm bg-danger bg-opacity-10 text-danger border-danger border-opacity-25 rounded-pill px-3 shadow-sm" 
                                                        onclick="verMotivoRechazo('<?php echo addslashes($row['des_rechazo']); ?>')" 
                                                        title="Ver Motivo de Rechazo">
                                                    <i class="fas fa-info-circle me-1"></i> Motivo
                                                </button>
                                            <?php endif; ?>

                                            <?php if (empty($row['comprobante_archivo']) && $row['estado'] != 'rechazado'): ?>
                                                <span class="text-muted small italic opacity-50">Sin acciones</span>
                                            <?php endif; ?>
                                        </div>
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

        <!-- UPU Balances -->
        <div class="glass-card p-4 mb-5 fade-in-up stagger-5">
            <div class="d-flex align-items-center mb-4">
                <div class="bg-indigo bg-opacity-10 p-2 rounded-circle me-3">
                    <i class="fas fa-wallet text-indigo"></i>
                </div>
                <h5 class="mb-0 fw-bold">Saldos Disponibles por UPU</h5>
            </div>
            <div class="row g-3">
                <?php if ($result_upu_saldos->num_rows > 0): ?>
                    <?php while ($row_upu = $result_upu_saldos->fetch_assoc()): ?>
                        <div class="col-md-4">
                            <div class="bg-light p-3 rounded-4 d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold text-dark small"><?php echo $row_upu["nombre"]; ?></div>
                                    <div class="text-muted extra-small"><?php echo $row_upu["correo"]; ?></div>
                                </div>
                                <div class="text-end">
                                    <div class="text-primary fw-800"><?php echo number_format($row_upu["saldo"], 2, ',', '.'); ?></div>
                                    <div class="extra-small text-muted">Bs Disponible</div>
                                </div>
                            </div>
                        </div>
                    <?php
    endwhile; ?>
                <?php
endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Motivo Rechazo -->
    <div class="modal fade" id="motivoRechazoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card border-0">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-danger"><i class="fas fa-ban me-2"></i> Motivo del Rechazo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <p id="motivoRechazoTexto" class="text-dark bg-light p-3 rounded-3 mb-0"></p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Entendido</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Receipt Preview -->
    <div class="modal fade" id="modalPreview" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content glass-card border-0">
                <div class="modal-header bg-gradient-wallet text-white border-0 py-3 px-4 rounded-top-custom">
                    <h5 class="modal-title fw-bold"><i class="fas fa-file-invoice me-2 text-warning"></i> Vista Previa de Comprobante</h5>
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

<script>
    let modalPreview;
    
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

    function verMotivoRechazo(motivo) {
        Swal.fire({
            title: 'Motivo del Rechazo',
            text: motivo || 'No se especificó un motivo.',
            icon: 'error',
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Entendido',
            background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#fff',
            color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#f8fafc' : '#1e293b'
        });
    }
</script>

<style>
    .transition-all { transition: all 0.3s ease; }
    #previewContainer { overflow: auto; max-height: 75vh; }
    .zoomed { transform: scale(1.5); transform-origin: top center; margin-bottom: 50px; }
</style>

<?php
require_once("../models/footer.php");
?>
