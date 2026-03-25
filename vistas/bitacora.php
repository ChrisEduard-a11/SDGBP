<?php
require_once("../models/header.php");
include('../conexion.php');

// ====================================================================
// !!! IMPORTANTE: TODO EL CÓDIGO PHP DE BACKEND SE MANTIENE INTACTO !!!
// ====================================================================
?>

<style>
    /* =========================================
       SISTEMA SDGBP - DISEÑO ULTRA PREMIUM 2026
       BITÁCORA DE ACTIVIDADES
       ========================================= */
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

    body {
        background-color: var(--bg-body);
        color: var(--text-main);
    }

    .breadcrumb-premium {
        background: var(--glass) !important;
        backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border) !important;
        border-radius: 12px !important;
    }

    .card-premium {
        background: transparent;
        border: none !important;
        border-radius: var(--radius-premium) !important;
        overflow: hidden;
    }

    .card-premium-header {
        background: linear-gradient(135deg, var(--accent-dark) 0%, #0f172a 100%);
        padding: 1.5rem 2rem;
        border: none !important;
    }

    .card-premium-header h5 {
        color: white;
        margin: 0;
        font-weight: 700;
        letter-spacing: 0.5px;
    }

    /* --- FILTER SECTION --- */
    .filter-section-premium {
        background: #f1f5f9;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border: 1px solid var(--border-color);
    }

    .form-select-premium, .form-control-premium {
        border: 1.5px solid var(--border-color) !important;
        border-radius: 10px !important;
        padding: 0.5rem 0.75rem !important;
        font-weight: 500 !important;
        transition: all 0.3s ease !important;
    }

    .form-select-premium:focus, .form-control-premium:focus {
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 4px var(--primary-light) !important;
    }

    /* --- TABLE CUSTOMIZATION --- */
    #datatablesSimple {
        border-collapse: separate !important;
        border-spacing: 0 5px !important;
    }

    #datatablesSimple thead th {
        background: #f8fafc !important;
        color: var(--text-muted) !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        font-size: 0.7rem !important;
        padding: 1rem !important;
        border: none !important;
        letter-spacing: 1px;
    }

    #datatablesSimple tbody tr {
        transition: all 0.2s ease;
    }

    #datatablesSimple tbody tr:hover {
        transform: scale(1.001);
    }

    #datatablesSimple td {
        padding: 0.75rem 1rem !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f1f5f9 !important;
        font-size: 0.9rem;
    }

    /* --- BUTTONS --- */
    .btn-apply-premium {
        background: linear-gradient(135deg, var(--primary) 0%, #ff9800 100%) !important;
        color: white !important;
        border: none !important;
        padding: 0.6rem 1.75rem !important;
        border-radius: 12px !important;
        font-weight: 700 !important;
        box-shadow: 0 4px 10px rgba(241, 128, 0, 0.2) !important;
        transition: all 0.3s ease !important;
    }

    .btn-apply-premium:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(241, 128, 0, 0.3) !important;
    }

    .btn-clean-premium {
        background: white !important;
        border: 1.5px solid var(--border-color) !important;
        color: var(--text-muted) !important;
        padding: 0.6rem 1.5rem !important;
        border-radius: 12px !important;
        font-weight: 600 !important;
        transition: all 0.2s ease !important;
    }

    .btn-clean-premium:hover {
        background: #f1f5f9 !important;
        color: var(--text-dark) !important;
    }

    /* --- BADGES --- */
    .badge-premium {
        padding: 0.5rem 0.75rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.75rem;
    }
</style>

<div id="layoutSidenav_content">
    <div class="container-fluid px-4 py-4">
        
        <header class="page-header-standard d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
            <div>
                <h1 class="fw-bold mb-0 text-primary"><i class="fas fa-history me-2"></i>Bitácora Global</h1>
                <p class="text-muted">Seguimiento detallado de todas las operaciones y eventos del sistema</p>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigateTo('inicio.php')" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active">Bitácora</li>
                </ol>
            </nav>
        </header>

        <div class="card card-premium shadow mb-5">
            <div class="card-premium-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-history me-2"></i> Historial de Actividades</h5>
                <span class="badge bg-white text-dark rounded-pill px-3 py-2 fw-bold">
                    Registros: <?php echo mysqli_num_rows(mysqli_query($conexion, ($_SESSION['tipo'] == 'admin') ? "SELECT * FROM bitacora" : "SELECT * FROM usuario_pagos WHERE usuario_id = " . $_SESSION['id'])); ?>
                </span>
            </div>
            
            <div class="card-body p-4">
                
                <!-- FILTROS PREMIUM -->
                <div class="filter-section-premium">
                    <h6 class="fw-bold mb-3 text-dark"><i class="fas fa-sliders-h me-2 text-primary"></i> Filtrar Historial</h6>
                    
                    <form method="GET" class="row g-3">
                        <?php if ($_SESSION['tipo'] == 'admin') { ?>
                            <div class="col-md-3">
                                <label class="form-label small fw-600 text-muted">Usuario Responsable</label>
                                <select name="filtro_usuario" class="form-select form-select-premium">
                                    <option value="">Todos los usuarios</option>
                                    <?php
                                    $usuarios = mysqli_query($conexion, "SELECT id_usuario, nombre FROM usuario ORDER BY nombre");
                                    while ($u = mysqli_fetch_assoc($usuarios)) {
                                        $selected = (isset($_GET['filtro_usuario']) && $_GET['filtro_usuario'] == $u['id_usuario']) ? 'selected' : '';
                                        echo "<option value='{$u['id_usuario']}' $selected>{$u['nombre']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-600 text-muted">Tipo de Cuenta</label>
                                <select name="filtro_tipo" class="form-select form-select-premium">
                                    <option value="">Todos</option>
                                    <option value="admin" <?php if(isset($_GET['filtro_tipo']) && $_GET['filtro_tipo']=='admin') echo 'selected'; ?>>Super Usuario</option>
                                    <option value="cont" <?php if(isset($_GET['filtro_tipo']) && $_GET['filtro_tipo']=='cont') echo 'selected'; ?>>Administrador</option>
                                    <option value="check" <?php if(isset($_GET['filtro_tipo']) && $_GET['filtro_tipo']=='check') echo 'selected'; ?>>Checkeador</option>
                                    <option value="upu" <?php if(isset($_GET['filtro_tipo']) && $_GET['filtro_tipo']=='upu') echo 'selected'; ?>>UPU</option>
                                </select>
                            </div>
                        <?php } ?>
                        
                        <div class="<?php echo ($_SESSION['tipo'] == 'admin') ? 'col-md-3' : 'col-md-6'; ?>">
                            <label class="form-label small fw-600 text-muted">Búsqueda por Acción</label>
                            <input type="text" name="filtro_accion" class="form-control form-control-premium" placeholder="Ej: Pago, Edición..." value="<?php echo isset($_GET['filtro_accion']) ? htmlspecialchars($_GET['filtro_accion']) : ''; ?>">
                        </div>
                        
                        <div class="<?php echo ($_SESSION['tipo'] == 'admin') ? 'col-md-2' : 'col-md-4'; ?>">
                            <label class="form-label small fw-600 text-muted">Fecha del Evento</label>
                            <input type="date" name="filtro_fecha" class="form-control form-control-premium" value="<?php echo isset($_GET['filtro_fecha']) ? $_GET['filtro_fecha'] : ''; ?>">
                        </div>
                        
                        <div class="<?php echo ($_SESSION['tipo'] == 'admin') ? 'col-md-2' : 'col-md-2'; ?> d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-apply-premium w-100" title="Aplicar Filtros">
                                <i class="fas fa-filter"></i>
                            </button>
                            <a href="bitacora.php" class="btn btn-clean-premium" title="Restablecer">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table id="datatablesSimple" class="table">
                        <thead>
                            <tr>
                                <?php if ($_SESSION['tipo'] == 'admin') { ?>
                                    <th>Usuario</th>
                                    <th>Rol</th>
                                <?php } ?>
                                <th>Dirección IP</th>
                                <th>Fecha y Hora</th>
                                <th>Información del Sistema</th>
                                <th>Actividad Registrada</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // --- Lógica de consulta PHP (Sin cambios) ---
                            if ($_SESSION['tipo'] == 'admin') {
                                $where = [];
                                if (!empty($_GET['filtro_usuario'])) $where[] = "u.id_usuario = " . intval($_GET['filtro_usuario']);
                                if (!empty($_GET['filtro_tipo'])) $where[] = "u.tipos = '" . mysqli_real_escape_string($conexion, $_GET['filtro_tipo']) . "'";
                                if (!empty($_GET['filtro_accion'])) $where[] = "b.accion LIKE '%" . mysqli_real_escape_string($conexion, $_GET['filtro_accion']) . "%'";
                                if (!empty($_GET['filtro_fecha'])) $where[] = "DATE(b.fecha) = '" . mysqli_real_escape_string($conexion, $_GET['filtro_fecha']) . "'";
                                $where_sql = count($where) ? "WHERE " . implode(" AND ", $where) : "";
                                $sql = "SELECT u.nombre, u.tipos, b.ip, b.fecha, b.system_info, b.accion FROM usuario_pagos AS up INNER JOIN usuario AS u ON up.usuario_id = u.id_usuario INNER JOIN bitacora AS b ON up.bitacora_id = b.id $where_sql GROUP BY b.id ORDER BY b.id DESC";
                            } else {
                                $where = ["u.id_usuario = " . $_SESSION['id']];
                                if (!empty($_GET['filtro_accion'])) $where[] = "b.accion LIKE '%" . mysqli_real_escape_string($conexion, $_GET['filtro_accion']) . "%'";
                                if (!empty($_GET['filtro_fecha'])) $where[] = "DATE(b.fecha) = '" . mysqli_real_escape_string($conexion, $_GET['filtro_fecha']) . "'";
                                $where_sql = count($where) ? "WHERE " . implode(" AND ", $where) : "";
                                $sql = "SELECT b.ip, b.fecha, b.system_info, b.accion FROM usuario_pagos AS up INNER JOIN usuario AS u ON up.usuario_id = u.id_usuario INNER JOIN bitacora AS b ON up.bitacora_id = b.id $where_sql GROUP BY b.id ORDER BY b.id DESC";
                            }
                            $result = mysqli_query($conexion, $sql);

                            while ($row = mysqli_fetch_assoc($result)) {
                                $accion = $row['accion'];
                                $badge_class = 'bg-secondary';
                                if (strpos($accion, 'Inicio') !== false) $badge_class = 'bg-success';
                                elseif (strpos($accion, 'Fin') !== false) $badge_class = 'bg-danger';
                                elseif (strpos($accion, 'Agregado') !== false) $badge_class = 'bg-primary';
                                elseif (strpos($accion, 'Editado') !== false) $badge_class = 'bg-warning text-dark';
                            ?>
                                <tr>
                                    <?php if ($_SESSION['tipo'] == 'admin') { ?>
                                        <td class="fw-bold text-dark"><?php echo $row['nombre']; ?></td>
                                        <td>
                                            <?php
                                            $rol_label = 'Desconocido'; $rol_class = 'bg-light text-muted';
                                            switch ($row['tipos']) {
                                                case 'admin': $rol_label = 'Super Usuario'; $rol_class = 'bg-dark text-white'; break;
                                                case 'cont': $rol_label = 'Administrador'; $rol_class = 'bg-info text-dark'; break;
                                                case 'check': $rol_label = 'Checkeador'; $rol_class = 'bg-primary text-white'; break;
                                                case 'upu': $rol_label = 'UPU'; $rol_class = 'bg-secondary text-white'; break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $rol_class; ?> rounded-pill px-2 py-1 small"><?php echo $rol_label; ?></span>
                                        </td>
                                    <?php } ?>
                                    <td class="text-muted small"><code><?php echo $row['ip']; ?></code></td>
                                    <td class="fw-semibold small"><?php echo date('d/m/Y H:i', strtotime($row['fecha'])); ?></td>
                                    <td class="small text-truncate" style="max-width: 150px;" title="<?php echo htmlspecialchars($row['system_info']); ?>"><?php echo htmlspecialchars($row['system_info']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $badge_class; ?> p-2 w-100 text-wrap shadow-sm" style="max-width: 250px;">
                                            <?php echo htmlspecialchars($accion); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if (mysqli_num_rows($result) == 0) { ?>
                    <div class="text-center py-5">
                        <div class="icon-circle-info mb-3 mx-auto" style="width: 70px; height: 70px; background: #fff7ed; color: #f97316; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                            <i class="fas fa-search"></i>
                        </div>
                        <h5 class="text-dark fw-bold">Sin resultados coincidentes</h5>
                        <p class="text-muted px-4">No encontramos registros con los filtros aplicados. Intenta con otros parámetros.</p>
                        <a href="bitacora.php" class="btn btn-outline-premium mt-2">Ver todo el historial</a>
                    </div>
                <?php } ?>
                
            </div>
        </div>
    </div>
<?php
require_once("../models/footer.php");
?>