<?php
global $conexion;
require_once("../models/header.php");
require_once("../conexion.php");

if (!isset($_SESSION['id'])) {
    header("Location: inicio.php");
    exit();
}

include_once("../models/funciones.php");

$is_ajax = isset($_GET['ajax']);

// --- Lógica de Paginación y Filtros ---
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

if ($_SESSION['tipo'] == 'admin') {
    $where = [];
    $where[] = "(b.accion NOT LIKE 'Comisión Aplicada - Aprobado por:%' AND b.accion NOT LIKE 'Pago Aprobado - Aprobado por:%' AND b.accion NOT LIKE 'Pago Rechazado - Rechazado por:%')";
    if (!empty($_GET['filtro_usuario'])) $where[] = "u.id_usuario = " . intval($_GET['filtro_usuario']);
    if (!empty($_GET['filtro_tipo'])) $where[] = "u.tipos = '" . mysqli_real_escape_string($conexion, $_GET['filtro_tipo']) . "'";
    if (!empty($_GET['filtro_accion'])) $where[] = "b.accion LIKE '%" . mysqli_real_escape_string($conexion, $_GET['filtro_accion']) . "%'";
    if (!empty($_GET['filtro_fecha'])) $where[] = "DATE(b.fecha) = '" . mysqli_real_escape_string($conexion, $_GET['filtro_fecha']) . "'";
    $where_sql = count($where) ? "WHERE " . implode(" AND ", $where) : "";
} else {
    $where = ["u.id_usuario = " . $_SESSION['id']];
    if (!empty($_GET['filtro_accion'])) $where[] = "b.accion LIKE '%" . mysqli_real_escape_string($conexion, $_GET['filtro_accion']) . "%'";
    if (!empty($_GET['filtro_fecha'])) $where[] = "DATE(b.fecha) = '" . mysqli_real_escape_string($conexion, $_GET['filtro_fecha']) . "'";
    $where_sql = count($where) ? "WHERE " . implode(" AND ", $where) : "";
}

// Conteo total
$sql_count = "SELECT COUNT(DISTINCT b.id) as total FROM usuario_pagos AS up INNER JOIN usuario AS u ON up.usuario_id = u.id_usuario INNER JOIN bitacora AS b ON up.bitacora_id = b.id $where_sql";
$count_res = mysqli_query($conexion, $sql_count);
$total_filtered = mysqli_fetch_assoc($count_res)['total'];
$total_pages = ceil($total_filtered / $records_per_page);

// Consulta principal
if ($_SESSION['tipo'] == 'admin') {
    $sql = "SELECT u.nombre, u.tipos, b.ip, b.fecha, b.system_info, b.accion FROM usuario_pagos AS up INNER JOIN usuario AS u ON up.usuario_id = u.id_usuario INNER JOIN bitacora AS b ON up.bitacora_id = b.id $where_sql GROUP BY b.id ORDER BY b.id DESC LIMIT $records_per_page OFFSET $offset";
} else {
    $sql = "SELECT b.ip, b.fecha, b.system_info, b.accion FROM usuario_pagos AS up INNER JOIN usuario AS u ON up.usuario_id = u.id_usuario INNER JOIN bitacora AS b ON up.bitacora_id = b.id $where_sql GROUP BY b.id ORDER BY b.id DESC LIMIT $records_per_page OFFSET $offset";
}
$result = mysqli_query($conexion, $sql);

if ($is_ajax):
    ob_start();
?>
<div id="ajax-response">
    <div id="total-count-update"><?php echo $total_filtered; ?></div>
    <table-body>
<?php else: ?>
<style>
    :root {
        --primary: #f18000;
        --primary-dark: #d67100;
        --primary-light: rgba(241, 128, 0, 0.1);
        --accent-dark: #1e293b;
        --bg-body: #f8fafc;
        --text-main: #1e293b;
        --text-muted: #64748b;
        --border-color: #e2e8f0;
        --radius-premium: 20px;
        --shadow-premium: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
        --glass: rgba(255, 255, 255, 0.8);
        --glass-border: rgba(255, 255, 255, 0.3);
    }
    body { background-color: var(--bg-body); color: var(--text-main); }
    .card-premium { background: transparent; border: none !important; border-radius: var(--radius-premium) !important; overflow: hidden; }
    .card-premium-header { background: linear-gradient(135deg, var(--accent-dark) 0%, #0f172a 100%); padding: 1.5rem 2rem; border: none !important; }
    .card-premium-header h5 { color: white; margin: 0; font-weight: 700; letter-spacing: 0.5px; }
    .filter-section-premium { background: #f1f5f9; border-radius: 15px; padding: 1.5rem; margin-bottom: 2rem; border: 1px solid var(--border-color); }
    .form-select-premium, .form-control-premium { border: 1.5px solid var(--border-color) !important; border-radius: 10px !important; transition: all 0.3s ease !important; }
    /* Premium Table */
    .custom-table { border-collapse: separate; border-spacing: 0 8px; }
    .custom-table thead th {
        background: transparent !important;
        border: none !important;
        color: var(--text-muted) !important;
        font-weight: 600 !important;
        text-transform: uppercase !important;
        font-size: 0.75rem !important;
        letter-spacing: 0.05em !important;
        padding: 1rem !important;
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
        padding: 1.25rem 1rem !important;
        border: none !important;
        vertical-align: middle !important;
        font-size: 0.9rem;
    }
    [data-theme="dark"] .custom-table td {
        color: #f8fafc;
    }
    .custom-table td:first-child { border-radius: 12px 0 0 12px; }
    .custom-table td:last-child { border-radius: 0 12px 12px 0; }
    
    .btn-apply-premium { background: linear-gradient(135deg, var(--primary) 0%, #ff9800 100%) !important; color: white !important; border: none !important; padding: 0.6rem 1.75rem !important; border-radius: 12px !important; font-weight: 700 !important; }
</style>

<div id="layoutSidenav_content">
    <div class="container-fluid px-4 py-4">
        <header class="page-header-standard d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
            <div>
                <h1 class="fw-bold mb-0 text-primary"><i class="fas fa-history me-2"></i>Bitácora Global</h1>
                <p class="text-muted">Seguimiento detallado de todas las operaciones y eventos del sistema</p>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 m-0 breadcrumb-premium px-3 py-2">
                    <li class="breadcrumb-item"><a href="inicio.php" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active">Bitácora</li>
                </ol>
            </nav>
        </header>

        <div class="card card-premium shadow mb-5">
            <div class="card-premium-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-history me-2"></i> Historial de Actividades</h5>
                <span class="badge bg-white text-dark rounded-pill px-3 py-2 fw-bold">
                    Registros: <span id="records-total-display"><?php echo $total_filtered; ?></span>
                </span>
            </div>
            
            <div class="card-body p-4 bg-white">
                <div class="filter-section-premium">
                    <h6 class="fw-bold mb-3 text-dark"><i class="fas fa-sliders-h me-2 text-primary"></i> Filtrar Historial</h6>
                    <form method="GET" class="row g-3" id="filter-form-bitacora">
                        <?php if ($_SESSION['tipo'] == 'admin') { ?>
                            <div class="col-md-3">
                                <label class="form-label small fw-600 text-muted">Usuario Responsable</label>
                                <select name="filtro_usuario" class="form-select form-select-premium">
                                    <option value="">Todos los usuarios</option>
                                    <?php
                                    $usuarios = mysqli_query($conexion, "SELECT id_usuario, nombre FROM usuario ORDER BY nombre");
                                    $selected_u = isset($_GET['filtro_usuario']) ? $_GET['filtro_usuario'] : '';
                                    while ($u = mysqli_fetch_assoc($usuarios)) {
                                        $sel = ($selected_u == $u['id_usuario']) ? 'selected' : '';
                                        echo "<option value='{$u['id_usuario']}' {$sel}>{$u['nombre']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-600 text-muted">Tipo de Cuenta</label>
                                <?php $selected_t = isset($_GET['filtro_tipo']) ? $_GET['filtro_tipo'] : ''; ?>
                                <select name="filtro_tipo" class="form-select form-select-premium">
                                    <option value="">Todos</option>
                                    <option value="admin" <?php echo ($selected_t == 'admin') ? 'selected' : ''; ?>>Super Usuario</option>
                                    <option value="cont" <?php echo ($selected_t == 'cont') ? 'selected' : ''; ?>>Administrador</option>
                                    <option value="check" <?php echo ($selected_t == 'check') ? 'selected' : ''; ?>>Checkeador</option>
                                    <option value="upu" <?php echo ($selected_t == 'upu') ? 'selected' : ''; ?>>UPU</option>
                                </select>
                            </div>
                        <?php } ?>
                        <div class="<?php echo ($_SESSION['tipo'] == 'admin') ? 'col-md-3' : 'col-md-6'; ?>">
                            <label class="form-label small fw-600 text-muted">Búsqueda por Acción</label>
                            <input type="text" name="filtro_accion" class="form-control form-control-premium" placeholder="Ej: Pago, Edición..." value="<?php echo isset($_GET['filtro_accion']) ? htmlspecialchars($_GET['filtro_accion']) : ''; ?>">
                        </div>
                        <div class="<?php echo ($_SESSION['tipo'] == 'admin') ? 'col-md-2' : 'col-md-4'; ?>">
                            <label class="form-label small fw-600 text-muted">Fecha del Evento</label>
                            <input type="text" name="filtro_fecha" class="form-control form-control-premium datepicker-flat" placeholder="YYYY-MM-DD" value="<?php echo isset($_GET['filtro_fecha']) ? htmlspecialchars($_GET['filtro_fecha']) : ''; ?>">
                        </div>
                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-apply-premium w-100"><i class="fas fa-filter"></i></button>
                            <a href="bitacora.php" class="btn btn-clean-premium"><i class="fas fa-undo"></i></a>
                        </div>
                    </form>
                </div>
<?php endif; ?>

                <div class="table-responsive" id="table-container">
                    <table id="datatablesSimple" class="table custom-table mb-0" data-paging="false" data-searching="false">
                        <thead>
                            <tr>
                                <?php if ($_SESSION['tipo'] == 'admin') { ?>
                                    <th>Usuario</th>
                                    <th>Rol</th>
                                <?php } ?>
                                <th>IP</th>
                                <th>Fecha/Hora</th>
                                <th>Sistema</th>
                                <th>Actividad</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)): 
                                $parts = explode(' - ', $row['accion'], 2);
                                $main_action = trim($parts[0]);
                                $details = isset($parts[1]) ? trim($parts[1]) : '';
                                $icon = 'fa-info-circle'; $color = 'text-secondary';
                                if (strpos($main_action, 'Inicio') !== false) { $icon = 'fa-sign-in-alt'; $color = 'text-success'; }
                                elseif (strpos($main_action, 'Agregado') !== false) { $icon = 'fa-plus-circle'; $color = 'text-primary'; }
                                elseif (strpos($main_action, 'Edit') !== false) { $icon = 'fa-edit'; $color = 'text-warning'; }
                            ?>
                                <tr>
                                    <?php if ($_SESSION['tipo'] == 'admin'): ?>
                                        <td><b><?php echo $row['nombre']; ?></b></td>
                                        <td><span class="badge bg-light text-dark border"><?php echo $row['tipos']; ?></span></td>
                                    <?php endif; ?>
                                    <td><code><?php echo $row['ip']; ?></code></td>
                                    <td><?php echo date('d/m/Y h:i A', strtotime($row['fecha'])); ?></td>
                                    <td><small><?php echo htmlspecialchars($row['system_info']); ?></small></td>
                                    <td><i class="fas <?php echo $icon; ?> <?php echo $color; ?> me-2"></i><b><?php echo $main_action; ?></b><br><small><?php echo $details; ?></small></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <div id="pagination-container">
                    <?php if ($total_pages > 1): ?>
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div class="text-muted small">Página <?php echo $page; ?> de <?php echo $total_pages; ?> &mdash; <?php echo $total_filtered; ?> registros</div>
                            <nav><ul class="pagination pagination-sm m-0">
                                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">&laquo;</a>
                                </li>
                                <?php
                                $start = max(1, $page - 2);
                                $end   = min($total_pages, $page + 2);
                                if ($start > 1): ?>
                                    <li class="page-item"><a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>">1</a></li>
                                    <?php if ($start > 2): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
                                <?php endif; ?>
                                <?php for ($i = $start; $i <= $end; $i++): ?>
                                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <?php if ($end < $total_pages): ?>
                                    <?php if ($end < $total_pages - 1): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
                                    <li class="page-item"><a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $total_pages])); ?>"><?php echo $total_pages; ?></a></li>
                                <?php endif; ?>
                                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">&raquo;</a>
                                </li>
                            </ul></nav>
                        </div>
                    <?php endif; ?>
                </div>

<?php if (!$is_ajax): ?>
            </div>
        </div>
    </div>


<script>
// (sin AJAX en paginación - recarga normal de página)
</script>
<?php endif; ?>
<?php 
if ($is_ajax):
    $html = ob_get_clean();
    echo $html;
    echo "</table-body></div>";
    exit();
endif;
?>
<?php
require_once("../models/footer.php");
?>
