<?php
// PHP SCRIPT START - NO CHANGES MADE TO BACKEND LOGIC
require_once("../models/header.php");
require_once("../conexion.php");

// Verificar si el usuario tiene permisos de tipo "upu"
if ($_SESSION["tipo"] != "upu") {
    header("Location: inicio.php"); // Redirige a una página de inicio
    exit();
}

// Variables para los filtros
$estado = $_GET['estado'] ?? '';
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';
$referencia = $_GET['referencia'] ?? '';
$cliente = $_GET['cliente'] ?? '';

// Construir la consulta SQL con filtros
$sql = "SELECT pagos.*, pagos.des_rechazo 
        FROM pagos
        INNER JOIN usuario_pagos ON pagos.id = usuario_pagos.pago_id
        WHERE usuario_pagos.usuario_id = ?";
$params = [$_SESSION['id']];

if (!empty($estado)) {
    $sql .= " AND estado = ?";
    $params[] = $estado;
}

if (!empty($fecha_inicio) && !empty($fecha_fin)) {
    $sql .= " AND fecha_pago BETWEEN ? AND ?";
    $params[] = $fecha_inicio;
    $params[] = $fecha_fin;
}

if (!empty($referencia)) {
    $sql .= " AND referencia LIKE ?";
    $params[] = "%$referencia%";
}

if (!empty($cliente)) {
    $sql .= " AND cliente = ?";
    $params[] = $cliente; // Comparar con el nombre del cliente
}

$sql .= " ORDER BY pagos.id DESC";

// Preparar y ejecutar la consulta
$stmt = $conexion->prepare($sql);
$types = str_repeat("s", count($params));

if ($stmt === false) {
// Manejar el error de preparación si es necesario
// die('Error de preparación: ' . $conexion->error);
}
// Ajuste para el primer parámetro que es integer (usuario_id) si los demás son strings
// El código original asume todos como 's', se mantiene por compatibilidad del código dado.
$types = 'i' . substr($types, 1);

if (!empty($params)) {
    // Usamos 'i' para el primer parámetro (id) y 's' para los demás.
    // Asumiendo que el ID es un entero y los demás (estado, fechas, referencia, cliente) son strings.
    // PHP >= 5.6 allows $stmt->bind_param($types, ...$params); 
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Obtener el saldo del usuario si es de tipo "UPU"
$saldo = 0;
if ($_SESSION["tipo"] == "upu") {
    $query_saldo = "SELECT saldo FROM usuario WHERE id_usuario = ?";
    $stmt_saldo = $conexion->prepare($query_saldo);
    $stmt_saldo->bind_param("i", $_SESSION['id']);
    $stmt_saldo->execute();
    $result_saldo = $stmt_saldo->get_result();
    if ($result_saldo->num_rows > 0) {
        $saldo = $result_saldo->fetch_assoc()['saldo'];
    }
}

// 1. Métricas: Pagos Pendientes (Específicos del UPU)
$sql_pendientes = "SELECT COUNT(*) as count, SUM(monto) as total FROM pagos 
                  INNER JOIN usuario_pagos ON pagos.id = usuario_pagos.pago_id 
                  WHERE usuario_pagos.usuario_id = ? AND pagos.estado = 'pendiente'";
$stmt_p = $conexion->prepare($sql_pendientes);
$stmt_p->bind_param("i", $_SESSION['id']);
$stmt_p->execute();
$metrics_p = $stmt_p->get_result()->fetch_assoc();

// 2. Métricas: Actividad en el rango filtrado (Aprobados)
// Reutilizamos la lógica de filtros para las métricas de actividad
$sql_actividad = "SELECT 
    SUM(CASE WHEN tipo = 'Ingreso' THEN monto ELSE 0 END) as ingresos,
    SUM(CASE WHEN tipo = 'Egreso' THEN monto ELSE 0 END) as egresos,
    COUNT(*) as transacciones
    FROM pagos 
    INNER JOIN usuario_pagos ON pagos.id = usuario_pagos.pago_id 
    WHERE usuario_pagos.usuario_id = ? AND estado = 'aprobado'";

$act_params = [$_SESSION['id']];
if (!empty($fecha_inicio) && !empty($fecha_fin)) {
    $sql_actividad .= " AND fecha_pago BETWEEN ? AND ?";
    $act_params[] = $fecha_inicio;
    $act_params[] = $fecha_fin;
}

$stmt_a = $conexion->prepare($sql_actividad);
$a_types = str_repeat("s", count($act_params));
$a_types[0] = 'i';
$stmt_a->bind_param($a_types, ...$act_params);
$stmt_a->execute();
$metrics_act = $stmt_a->get_result()->fetch_assoc();
?>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --glass-bg: rgba(255, 255, 255, 0.7);
        --glass-border: rgba(255, 255, 255, 0.3);
        --glass-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.1);
        --accent-primary: #4361ee;
        --accent-success: #2ec4b6;
        --accent-warning: #f7b731;
        --accent-danger: #e71d36;
        --text-main: #1e293b;
        --text-muted: #64748b;
    }

    [data-theme="dark"] {
        --glass-bg: rgba(30, 41, 59, 0.7);
        --glass-border: rgba(255, 255, 255, 0.1);
        --glass-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
        --text-main: #f8fafc;
        --text-muted: #94a3b8;
    }

    body { font-family: 'Inter', sans-serif; }

    #layoutSidenav_content {
        background: transparent;
    }

    /* Glassmorphism Containers */
    .glass-card {
        background: var(--glass-bg);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border);
        border-radius: 20px;
        box-shadow: var(--glass-shadow);
        transition: transform 0.3s ease;
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
        color: white;
    }
    .metric-card::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
        z-index: -1;
    }
    .bg-gradient-wallet { background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%); }
    .bg-gradient-pending { background: linear-gradient(135deg, #f7b731 0%, #ff9f43 100%); }
    .bg-gradient-activity { background: linear-gradient(135deg, #2ec4b6 0%, #0891b2 100%); }

    .metric-value { font-size: 1.8rem; font-weight: 800; letter-spacing: -1px; }
    .metric-label { font-size: 0.85rem; font-weight: 500; opacity: 0.9; text-transform: uppercase; }
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
        color: var(--text-muted);
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
        color: var(--text-main);
    }
    .custom-table td:first-child { border-radius: 12px 0 0 12px; }
    .custom-table td:last-child { border-radius: 0 12px 12px 0; }

    /* Badges & Status */
    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.8rem;
    }
    .ingreso-cell-pro { color: #2ec4b6; font-weight: 700; }
    .egreso-cell-pro { color: #e71d36; font-weight: 700; }
    
    .badge-soft-success { background: rgba(46, 196, 182, 0.1); color: #2ec4b6; }
    .badge-soft-warning { background: rgba(247, 183, 49, 0.1); color: #f7b731; }
    .badge-soft-danger { background: rgba(231, 29, 54, 0.1); color: #e71d36; }

    /* Animations */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-up { animation: fadeInUp 0.5s ease forwards; }
</style>

<div id="layoutSidenav_content">
    <div class="container-fluid px-4 py-4">
        
        <header class="d-flex justify-content-between align-items-center mb-4 animate-up">
            <div>
                <h1 class="fw-bold mb-0">Gestión de Mis Pagos</h1>
                <p class="text-muted small">Control total de tus transacciones y saldo disponible</p>
            </div>
            <div class="breadcrumb-container d-none d-md-block">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigateTo('inicio.php')" class="text-decoration-none"><i class="fas fa-home me-1"></i> Inicio</a></li>
                    <li class="breadcrumb-item active">Mis Pagos</li>
                </ol>
            </div>
        </header>

        <!-- Dynamic Metrics Dashboard -->
        <div class="row g-4 mb-5 animate-up" style="animation-delay: 0.1s;">
            <!-- Saldo Card -->
            <div class="col-xl-4 col-md-6">
                <div class="card metric-card bg-gradient-wallet shadow-lg">
                    <div class="card-body p-0 d-flex flex-column justify-content-between">
                        <div>
                            <div class="metric-label">Saldo Disponible</div>
                            <div class="metric-value mt-1"><?php echo number_format($saldo, 2, ',', '.'); ?> <small class="fs-6">Bs</small></div>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-light btn-sm rounded-pill fw-bold text-primary px-3" 
                                    data-bs-toggle="modal" data-bs-target="#modalSaldoUPU" 
                                    onclick="cargarSaldoUPUPorMes(<?php echo $_SESSION['id']; ?>)">
                                <i class="fas fa-calendar-alt me-1"></i> Detalle Mensual
                            </button>
                        </div>
                        <i class="fas fa-wallet metric-icon"></i>
                    </div>
                </div>
            </div>

            <!-- Pending Card -->
            <div class="col-xl-4 col-md-6">
                <div class="card metric-card bg-gradient-pending shadow-lg">
                    <div class="card-body p-0">
                        <div class="metric-label">Pagos en Verificación</div>
                        <div class="metric-value mt-1"><?php echo number_format($metrics_p['total'] ?? 0, 2, ',', '.'); ?> <small class="fs-6">Bs</small></div>
                        <div class="mt-2 small opacity-90">
                            <strong><?php echo $metrics_p['count'] ?? 0; ?></strong> transacciones pendientes
                        </div>
                        <i class="fas fa-clock metric-icon"></i>
                    </div>
                </div>
            </div>

            <!-- Filtered Activity Card -->
            <div class="col-xl-4 col-md-12">
                <div class="card metric-card bg-gradient-activity shadow-lg">
                    <div class="card-body p-0">
                        <div class="metric-label">Actividad Registrada</div>
                        <div class="metric-value mt-1">
                            <?php echo $metrics_act['transacciones'] ?? 0; ?> <small class="fs-6">Items</small>
                        </div>
                        <div class="d-flex gap-3 mt-2 small opacity-90">
                            <span><i class="fas fa-arrow-up me-1"></i> In: <?php echo number_format($metrics_act['ingresos'] ?? 0, 0, ',', '.'); ?></span>
                            <span><i class="fas fa-arrow-down me-1"></i> Out: <?php echo number_format($metrics_act['egresos'] ?? 0, 0, ',', '.'); ?></span>
                        </div>
                        <i class="fas fa-exchange-alt metric-icon"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5 animate-up" style="animation-delay: 0.2s;">
            <!-- Filters -->
            <div class="col-lg-12">
                <div class="card glass-card border-0">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0 text-primary"><i class="fas fa-search me-2"></i> Filtros de Búsqueda</h5>
                    </div>
                    <div class="card-body px-4">
                        <form method="get" class="row g-3">
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Estado</label>
                                <select name="estado" class="form-select form-select-sm rounded-3">
                                    <option value="">Todos</option>
                                    <option value="pendiente" <?php echo $estado == 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                    <option value="aprobado" <?php echo $estado == 'aprobado' ? 'selected' : ''; ?>>Aprobado</option>
                                    <option value="rechazado" <?php echo $estado == 'rechazado' ? 'selected' : ''; ?>>Rechazado</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Desde</label>
                                <input type="date" name="fecha_inicio" class="form-control form-control-sm rounded-3" value="<?php echo htmlspecialchars($fecha_inicio); ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Hasta</label>
                                <input type="date" name="fecha_fin" class="form-control form-control-sm rounded-3" value="<?php echo htmlspecialchars($fecha_fin); ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Nro Referencia</label>
                                <input type="text" name="referencia" class="form-control form-control-sm rounded-3" placeholder="Buscar ref..." value="<?php echo htmlspecialchars($referencia); ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Entidad/Cliente</label>
                                <select name="cliente" class="form-select form-select-sm rounded-3">
                                    <option value="">Todos</option>
                                    <?php
$usuario_id = $_SESSION['id'];
$sqlClientes = "SELECT DISTINCT c.nombre FROM cliente c INNER JOIN usuario_pagos up ON c.id_cliente = up.cliente_id WHERE up.usuario_id = ?";
$stmtClientes = $conexion->prepare($sqlClientes);
$stmtClientes->bind_param("i", $usuario_id);
$stmtClientes->execute();
$resultClientes = $stmtClientes->get_result();
while ($rowCliente = $resultClientes->fetch_assoc()) {
    $selected = ($rowCliente['nombre'] == $cliente) ? 'selected' : '';
    echo "<option value='" . htmlspecialchars($rowCliente['nombre']) . "' $selected>" . htmlspecialchars($rowCliente['nombre']) . "</option>";
}
?>
                                </select>
                            </div>
                            <div class="col-md-12 text-end">
                                <a href="ver_pagos.php" class="btn btn-light btn-sm rounded-pill px-3 me-2">Limpiar</a>
                                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm">Aplicar Filtros</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Export Section -->
        <div class="row g-4 mb-5 animate-up" style="animation-delay: 0.3s;">
            <div class="col-lg-12">
                <div class="card glass-card border-0">
                    <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-center">
                        <div class="mb-3 mb-md-0">
                            <h5 class="fw-bold mb-0 text-success"><i class="fas fa-file-export me-2"></i> Reportes Contables</h5>
                            <p class="text-muted small mb-0">Generar balance de ingresos y egresos en formato PDF</p>
                        </div>
                        <form method="POST" action="../dompdf/exportar_pdf_I-E_UPU.php" id="form-exportar" class="d-flex gap-2" target="_blank">
                            <input type="date" name="filtro_fecha_inicio" class="form-control form-control-sm rounded-3 w-auto" value="<?php echo $fecha_inicio ?: date('Y-m-01'); ?>" required title="Fecha Inicio">
                            <input type="date" name="filtro_fecha_fin" class="form-control form-control-sm rounded-3 w-auto" value="<?php echo $fecha_fin ?: date('Y-m-d'); ?>" required title="Fecha Fin">
                            <input type="hidden" name="usuario_upu" value="<?php echo $_SESSION['id']; ?>">
                            <button type="submit" class="btn btn-success btn-sm rounded-pill px-4 shadow-sm">
                                <i class="fas fa-file-pdf me-1"></i> Exportar PDF
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="motivoRechazoModal" tabindex="-1" aria-labelledby="motivoRechazoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="motivoRechazoModalLabel"><i class="fas fa-exclamation-triangle me-1"></i> Motivo del Rechazo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light">
                <p id="motivoRechazoTexto" class="lead text-danger fw-bold border p-3 rounded shadow-sm"></p>
                <p class="text-muted small mt-3">Este pago no afectó su saldo.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-close" data-bs-dismiss="modal">Cerrar</button>
            </div>
            </div>
        </div>
        </div>

        <div class="card glass-card border-0 animate-up" style="animation-delay: 0.4s;">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-list-ul me-2 text-primary"></i> Historial de Movimientos</h5>
            </div>
            <div class="card-body table-responsive px-4">
                <table id="datatablesSimple" class="table custom-table w-100"> 
                    <thead>
                        <tr>
                            <th>Concepto / Referencia</th>
                            <th>Fecha</th>
                            <th class="text-center">Monto</th>
                            <th class="text-center">Saldo Resultante</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Comprobante</th>
                        </tr>
                    </thead>
                    <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr class="align-middle">
                                        <td>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['descripcion']); ?></div>
                                            <div class="text-muted small">
                                                <i class="fas fa-barcode me-1"></i> <?php echo htmlspecialchars($row['referencia']); ?>
                                                <span class="mx-1">|</span>
                                                <i class="fas fa-credit-card me-1"></i> <?php echo htmlspecialchars($row['metodo_pago']); ?>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="small fw-bold"><?php echo date('d/m/Y', strtotime($row['fecha_pago'])); ?></div>
                                            <div class="extra-small text-muted"><?php echo ucfirst($row['tipo']); ?></div>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($row["tipo"] == "Ingreso"): ?>
                                                <span class="ingreso-cell-pro">+<?php echo number_format($row["monto"], 2, ',', '.'); ?></span>
                                            <?php
        else: ?>
                                                <span class="egreso-cell-pro">-<?php echo number_format($row["monto"], 2, ',', '.'); ?></span>
                                            <?php
        endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($row['estado'] == 'aprobado'): ?>
                                                <span class="fw-bold" style="color: var(--accent-primary);">
                                                    <?php echo number_format($row['saldo_resultante'], 2, ',', '.'); ?> Bs
                                                </span>
                                            <?php
        else: ?>
                                                <span class="text-muted opacity-50 small italic">Pendiente</span>
                                            <?php
        endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php
        $badge_class = 'badge-soft-warning';
        $text = 'Pendiente';
        $icon = 'fa-clock';

        if ($row['estado'] == 'aprobado') {
            $badge_class = 'badge-soft-success';
            $text = 'Aprobado';
            $icon = 'fa-check-circle';
        }
        elseif ($row['estado'] == 'rechazado') {
            $badge_class = 'badge-soft-danger';
            $text = 'Rechazado';
            $icon = 'fa-times-circle';
        }
?>
                                            <div class="status-badge <?php echo $badge_class; ?> d-inline-flex align-items-center gap-2">
                                                <i class="fas <?php echo $icon; ?>"></i> <?php echo $text; ?>
                                                <?php if ($row['estado'] == 'rechazado'): ?>
                                                    <button class="btn btn-sm btn-link p-0 text-danger border-0 ms-1" 
                                                            onclick="verMotivoRechazo('<?php echo addslashes($row['des_rechazo']); ?>')"
                                                            title="Ver Motivo">
                                                        <i class="fas fa-info-circle"></i>
                                                    </button>
                                                <?php
        endif; ?>
                                            </div>
                                        </td>
                                        <td class="text-center">
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

    <!-- Modal Detalle Mensual (Restaurado y Mejorado) -->
    <div class="modal fade" id="modalSaldoUPU" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card border-0">
                <div class="modal-header bg-gradient-wallet text-white border-0 py-4 px-4 rounded-top-custom">
                    <h5 class="modal-title fw-bold"><i class="fas fa-calendar-check me-2 text-warning"></i> Saldo por Mes</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="formFiltroMes" class="mb-4">
                        <label class="form-label small fw-bold">Selecciona el mes (YYYY-MM):</label>
                        <div class="input-group">
                            <input type="text" class="form-control rounded-start-pill ps-3" id="mesFiltro" name="mesFiltro" placeholder="Ej: 2024-03" required>
                            <button type="submit" class="btn btn-primary rounded-end-pill px-4 fw-bold">Filtrar</button>
                        </div>
                    </form>
                    <div id="resultadoSaldoMes" class="text-center py-5 border rounded-4 bg-light bg-opacity-50">
                        <i class="fas fa-chart-pie fa-3x text-muted opacity-25 mb-3"></i>
                        <p class="text-muted mb-0">Selecciona un periodo para analizar</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Receipt Preview -->
    <div class="modal fade" id="modalPreview" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content glass-card border-0">
                <div class="modal-header bg-gradient-wallet text-white border-0 py-3 px-4 rounded-top-custom">
                    <h5 class="modal-title fw-bold"><i class="fas fa-file-invoice me-2"></i> Vista Previa de Comprobante</h5>
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
<script>
    let modalPreview;
    
    function cargarSaldoUPUPorMes(id) {
        document.getElementById('mesFiltro').value = '';
        document.getElementById('resultadoSaldoMes').innerHTML = '<i class="fas fa-chart-pie fa-3x text-muted opacity-25 mb-3"></i><p class="text-muted mb-0">Selecciona un periodo para analizar</p>';
    }

    document.addEventListener('DOMContentLoaded', function() {
        const formFiltroMes = document.getElementById('formFiltroMes');
        if (formFiltroMes) {
            formFiltroMes.addEventListener('submit', function(e) {
                e.preventDefault();
                const mes = document.getElementById('mesFiltro').value;
                const resultadoDiv = document.getElementById('resultadoSaldoMes');
                
                resultadoDiv.innerHTML = '<div class="spinner-border text-primary my-4" role="status"><span class="visually-hidden">Cargando...</span></div><p class="text-muted small">Consultando...</p>';
                
                fetch(`../acciones/obtener_saldo_mes.php?mes=${mes}`)
                    .then(response => response.text())
                    .then(data => {
                        resultadoDiv.innerHTML = `<div class="p-4 bg-white rounded-4 shadow-sm border border-primary border-opacity-25">${data}</div>`;
                    })
                    .catch(error => {
                        resultadoDiv.innerHTML = `<div class="text-danger py-4"><i class="fas fa-exclamation-circle fa-2x mb-2"></i><br>Error al consultar el saldo. Intente de nuevo.</div>`;
                    });
            });
        }
    });

    function previewComprobante(archivo) {
        if (!modalPreview) {
            modalPreview = new bootstrap.Modal(document.getElementById('modalPreview'));
        }
        
        const path = `../uploads/comprobantes/${archivo}`;
        const container = document.getElementById('previewContainer');
        const zoomBtn = document.getElementById('zoomBtn');
        const extension = archivo.split('.').pop().toLowerCase();
        
        container.innerHTML = ''; // Limpiar anterior
        zoomBtn.classList.add('d-none'); // Ocultar zoom por defecto
        
        if (['jpg', 'jpeg', 'png', 'gif'].includes(extension)) {
            const img = document.createElement('img');
            img.src = path;
            img.id = 'imgPreview';
            img.className = 'img-fluid rounded shadow transition-all';
            img.style.maxHeight = '70vh';
            img.style.cursor = 'zoom-in';
            img.onclick = toggleZoom;
            container.appendChild(img);
            zoomBtn.classList.remove('d-none'); // Mostrar zoom para fotos
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
            confirmButtonColor: '#e71d36',
            confirmButtonText: 'Entendido',
            background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#fff',
            color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#f8fafc' : '#1e293b'
        });
    }
</script>

<style>
    .rounded-top-custom { border-radius: 20px 20px 0 0 !important; }
    .border-pro-white { border: 6px solid white; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
    [data-theme="dark"] .border-pro-white { border-color: #334155; }
    
    /* Input mes rounded */
    #mesFiltro { border-radius: 50px 0 0 50px !important; border: 1px solid #dee2e6; }
    [data-theme="dark"] #mesFiltro { background: #1a2235; border-color: #334155; color: white; }

    /* Zoom Support */
    .transition-all { transition: all 0.3s ease; }
    #previewContainer { overflow: auto; max-height: 75vh; }
    .zoomed { transform: scale(1.5); transform-origin: top center; margin-bottom: 50px; }
</style>

<?php
require_once("../models/footer.php");
?>