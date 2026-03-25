<?php
require_once("../models/header.php");
include('../conexion.php');

// CONFIGURACIÓN DE USUARIOS
$superAdminId = 8; // ID del super admin (TÚ)
$loggedUserId = $_SESSION['id'] ?? 0; 
$loggedUserType = $_SESSION['tipo'] ?? ''; 

// ------------------------------------------------------------------
// LÓGICA DE ACTUALIZACIÓN DE SALDO (Solo ID 8)
// ------------------------------------------------------------------
if (isset($_POST['action']) && $_POST['action'] == 'update_saldo') {
    if ($loggedUserId == $superAdminId) {
        $idUsuarioSaldo = intval($_POST['id_usuario_saldo']);
        $nuevoSaldo = floatval($_POST['nuevo_saldo']);
        
        // Actualizamos la columna 'saldo'
        $sqlUpdate = "UPDATE usuario SET saldo = ? WHERE id_usuario = ?";
        
        if ($stmt = mysqli_prepare($conexion, $sqlUpdate)) {
            mysqli_stmt_bind_param($stmt, "di", $nuevoSaldo, $idUsuarioSaldo);
            if(mysqli_stmt_execute($stmt)){
                $_SESSION['estatus'] = 'success';
                $_SESSION['mensaje'] = 'Saldo actualizado correctamente.';
            } else {
                $_SESSION['estatus'] = 'error';
                $_SESSION['mensaje'] = 'Error al actualizar: ' . mysqli_error($conexion) ;
            }
            mysqli_stmt_close($stmt);
            echo "<script>window.location.href='usuario.php';</script>";
            exit();
        }
    } else {
            $_SESSION['estatus'] = 'error';
            $_SESSION['mensaje'] = 'No tienes permisos para realizar esta acción.';
            echo "<script>window.location.href='usuario.php';</script>";
            exit();
    }
}
?>

<style>
    :root {
        --premium-violet: #8b5cf6;
        --premium-violet-light: #a78bfa;
        --premium-indigo: #6366f1;
        --glass-bg: rgba(255, 255, 255, 0.95);
        --glass-border: rgba(139, 92, 246, 0.1);
    }

    [data-theme="dark"] {
        --glass-bg: #000000;
        --glass-border: #333;
        --premium-violet: #a78bfa;
        --card-header-bg: #111;
    }

    .page-title-icon { 
        color: var(--premium-violet);
        filter: drop-shadow(0 0 8px rgba(139, 92, 246, 0.3));
    }

    .glass-card {
        background: var(--glass-bg);
        backdrop-filter: blur(12px);
        border: 1px solid var(--glass-border);
        border-radius: 1.5rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
        color: var(--text-main);
    }

    .metric-card {
        border: none;
        border-radius: 1.25rem;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }

    .metric-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.12);
    }

    .img-circular {
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--premium-violet);
        padding: 2px;
        background: transparent;
    }

    [data-theme="dark"] .img-circular {
        background: transparent;
    }

    .table thead th { 
        background: var(--card-header-bg, #f8fafc);
        color: var(--text-main);
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        padding: 1.25rem 1rem;
        border: none;
        white-space: nowrap;
    }

    .table tbody td { 
        padding: 1rem;
        vertical-align: middle;
        border-color: var(--glass-border);
        white-space: nowrap;
    }

    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        font-weight: 700;
        font-size: 0.75rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .saldo-badge {
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
        font-weight: 800;
        padding: 0.4rem 0.8rem;
        border-radius: 0.75rem;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    [data-theme="dark"] .saldo-badge {
        background: rgba(16, 185, 129, 0.05);
        color: #34d399;
    }

    /* === BOTONES PREMIUM === */
    .btn-premium-violet {
        background: linear-gradient(135deg, #8b5cf6, #6366f1);
        color: #fff !important;
        border: none;
        font-weight: 700;
    }
    .btn-premium-violet:hover { background: linear-gradient(135deg, #7c3aed, #4f46e5); color: #fff !important; transform: translateY(-2px); }

    .btn-premium-amber {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: #fff !important;
        border: none;
        font-weight: 700;
    }
    .btn-premium-amber:hover { background: linear-gradient(135deg, #d97706, #b45309); color: #fff !important; transform: translateY(-2px); }

    /* === DARK MODE: DATATABLES === */
    [data-theme="dark"] .dataTables_wrapper,
    [data-theme="dark"] .dataTables_wrapper table,
    [data-theme="dark"] .dataTables_filter label,
    [data-theme="dark"] .dataTables_length label,
    [data-theme="dark"] .dataTables_info,
    [data-theme="dark"] .dataTables_paginate .paginate_button {
        color: var(--text-main) !important;
    }
    [data-theme="dark"] .dataTables_filter input,
    [data-theme="dark"] .dataTables_length select {
        background: rgba(30, 41, 59, 0.9) !important;
        color: var(--text-main) !important;
        border-color: rgba(255,255,255,0.15) !important;
    }
    [data-theme="dark"] .dataTables_paginate .paginate_button:not(.disabled):not(.current) {
        background: rgba(30, 41, 59, 0.8) !important;
        color: var(--text-main) !important;
        border-color: rgba(255,255,255,0.1) !important;
    }
    [data-theme="dark"] .dataTables_paginate .paginate_button.current {
        background: var(--premium-violet) !important;
        color: #fff !important;
        border-color: var(--premium-violet) !important;
    }
    [data-theme="dark"] table.dataTable tbody tr {
        background: transparent !important;
        color: var(--text-main) !important;
    }
    [data-theme="dark"] table.dataTable tbody tr:nth-child(even) {
        background: rgba(255,255,255,0.03) !important;
    }
    /* Soft button variants */
    .btn-soft-primary { background: rgba(99,102,241,0.12); color: #6366f1; border: none; }
    .btn-soft-primary:hover { background: rgba(99,102,241,0.22); color: #4f46e5; }
    .btn-soft-danger { background: rgba(239,68,68,0.12); color: #ef4444; border: none; }
    .btn-soft-danger:hover { background: rgba(239,68,68,0.22); color: #dc2626; }
    .btn-soft-success { background: rgba(16,185,129,0.12); color: #10b981; border: none; }
    .btn-soft-success:hover { background: rgba(16,185,129,0.22); color: #059669; }
    .btn-soft-warning { background: rgba(245,158,11,0.12); color: #f59e0b; border: none; }
    .btn-soft-warning:hover { background: rgba(245,158,11,0.22); color: #d97706; }
</style>

<script>
const superAdminId = <?php echo $superAdminId; ?>;
const loggedUserId = <?php echo $loggedUserId; ?>;

function openSaldoModal(id, nombre, saldoActual) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Modificar Saldo',
            html: `Usuario: <strong class="text-primary">${nombre}</strong>`,
            input: 'number',
            inputLabel: 'Nuevo Saldo Disponible ($):',
            inputValue: saldoActual,
            inputAttributes: {
                step: '0.01',
                min: '0'
            },
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-save me-1"></i> Guardar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#64748b',
            customClass: {
                popup: 'rounded-4 shadow-lg',
                input: 'text-center text-success fw-bold text-lg'
            },
            preConfirm: (nuevoSaldo) => {
                if (!nuevoSaldo || nuevoSaldo === "") {
                    Swal.showValidationMessage('Ingresa un monto válido');
                    return false;
                }
                return nuevoSaldo;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Envío dinámico del formulario
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'usuario.php';

                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'update_saldo';
                form.appendChild(actionInput);

                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id_usuario_saldo';
                idInput.value = id;
                form.appendChild(idInput);

                const saldoInput = document.createElement('input');
                saldoInput.type = 'hidden';
                saldoInput.name = 'nuevo_saldo';
                saldoInput.value = result.value;
                form.appendChild(saldoInput);

                document.body.appendChild(form);
                form.submit();
            }
        });
    } else {
        // Fallback básico si SweetAlert falla por carga
        let val = prompt(`Nuevo Saldo Disponible para ${nombre} ($):`, saldoActual);
        if(val !== null && val !== "") {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'usuario.php';
            
            form.innerHTML = `<input type="hidden" name="action" value="update_saldo">
                              <input type="hidden" name="id_usuario_saldo" value="${id}">
                              <input type="hidden" name="nuevo_saldo" value="${val}">`;
            document.body.appendChild(form);
            form.submit();
        }
    }
}

function navigateTo(url) {
    window.location.href = url;
}

function confirmDelete(url) {
    if (confirm("¿Estás seguro de que quieres eliminar este usuario? Esta acción no se puede deshacer.")) {
        window.location.href = url;
    }
}

// Nota: La función toggleBloqueo no está definida aquí, asegúrate de que esté en '../models/footer.php' o en un archivo JS aparte si la necesitas.
</script>

<div id="layoutSidenav_content">
    <!-- Modal de Saldo de Bootstrap ha sido completamente remplazado por SweetAlert Native UI en openSaldoModal() -->
    <div class="container-fluid px-4 py-4">
        
        <header class="page-header-standard d-flex justify-content-between align-items-center animate__animated animate__fadeIn">
            <div>
                <h1 class="fw-bold mb-0 text-primary"><i class="fas fa-users-cog me-2"></i>Gestión de Usuarios</h1>
                <p class="text-muted">Administración de roles, accesos y saldos del personal</p>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigateTo('inicio.php')" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active">Usuarios</li>
                </ol>
            </nav>
        </header>

        <?php if (isset($_SESSION['estatus']) && isset($_SESSION['mensaje'])): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: '<?php echo $_SESSION['estatus']; ?>',
                            title: '<?php echo $_SESSION['estatus'] === 'success' ? '¡Éxito!' : 'Error'; ?>',
                            text: '<?php echo htmlspecialchars($_SESSION['mensaje'], ENT_QUOTES); ?>',
                            confirmButtonText: 'Entendido',
                            confirmButtonColor: '#8b5cf6',
                            customClass: {
                                popup: 'rounded-4 shadow-lg'
                            }
                        });
                    } else {
                        alert('<?php echo htmlspecialchars($_SESSION['mensaje'], ENT_QUOTES); ?>');
                    }
                });
            </script>
            <?php unset($_SESSION['estatus'], $_SESSION['mensaje']); ?>
        <?php endif; ?>

        <?php
        // Consultas de totales (igual que antes)
        $sqlCheck = "SELECT COUNT(*) AS total_check FROM usuario WHERE tipos = 'inv'";
        $total_check = mysqli_fetch_assoc(mysqli_query($conexion, $sqlCheck))['total_check'];

        $sqlAdmin = "SELECT COUNT(*) AS total_admin FROM usuario WHERE tipos = 'cont'";
        $total_admin = mysqli_fetch_assoc(mysqli_query($conexion, $sqlAdmin))['total_admin'];

        $sqlUser = "SELECT COUNT(*) AS total_user FROM usuario WHERE tipos = 'upu'";
        $total_user = mysqli_fetch_assoc(mysqli_query($conexion, $sqlUser))['total_user'];

        $sqlSU = "SELECT COUNT(*) AS total_SU FROM usuario WHERE tipos = 'admin'";
        $total_SU = mysqli_fetch_assoc(mysqli_query($conexion, $sqlSU))['total_SU'];

        $sqlPendientes = "SELECT COUNT(*) AS total FROM usuario WHERE aprobado = 0";
        $totalPendientes = mysqli_fetch_assoc(mysqli_query($conexion, $sqlPendientes))['total'];
        ?>

        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="metric-card glass-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small fw-bold text-uppercase mb-1">Super Usuario</p>
                            <h2 class="mb-0 fw-800"><?php echo $total_SU; ?></h2>
                        </div>
                        <div class="p-3 rounded-4 bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-user-shield fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="metric-card glass-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small fw-bold text-uppercase mb-1">Admin</p>
                            <h2 class="mb-0 fw-800"><?php echo $total_admin; ?></h2>
                        </div>
                        <div class="p-3 rounded-4 bg-info bg-opacity-10 text-info">
                            <i class="fas fa-user-tie fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="metric-card glass-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small fw-bold text-uppercase mb-1">Inventario</p>
                            <h2 class="mb-0 fw-800"><?php echo $total_check; ?></h2>
                        </div>
                        <div class="p-3 rounded-4 bg-warning bg-opacity-10 text-warning">
                            <i class="fas fa-clipboard-check fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="metric-card glass-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small fw-bold text-uppercase mb-1">UPU</p>
                            <h2 class="mb-0 fw-800"><?php echo $total_user; ?></h2>
                        </div>
                        <div class="p-3 rounded-4 bg-success bg-opacity-10 text-success">
                            <i class="fas fa-user fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-5 d-flex flex-wrap flex-column flex-sm-row gap-3 animate__animated animate__fadeIn">
            <button class="btn btn-premium-violet px-4 py-2 rounded-4 shadow-sm fw-bold flex-grow-1 flex-sm-grow-0" onclick="navigateTo('registro_u.php')">
                <i class="fa fa-user-plus me-2"></i> Nuevo Usuario
            </button>
            <button class="btn btn-premium-amber px-4 py-2 rounded-4 shadow-sm fw-bold flex-grow-1 flex-sm-grow-0" onclick="navigateTo('usuarios_a.php')">
                <i class="fa fa-user-check me-2"></i> Aprobar Pendientes
                <span class="badge rounded-pill bg-danger ms-2"><?php echo $totalPendientes; ?></span>
            </button>
        </div>
        
        <?php
        $sql = "SELECT id_usuario, nombre, cedula, nacionalidad, usuario, correo, tipos, aprobado, foto, bloqueado, saldo, intentos, fecha_cambio_clave 
                FROM usuario ORDER BY nombre ASC";
        
        $result = mysqli_query($conexion, $sql);
        
        $superAdmin = null;
        $upuUsers = [];
        $generalUsers = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $row['saldo'] = isset($row['saldo']) ? floatval($row['saldo']) : 0.00;

            if ($row['id_usuario'] == $superAdminId) {
                $superAdmin = $row;
            } elseif ($row['tipos'] == 'upu') {
                $upuUsers[] = $row; 
            } else {
                $generalUsers[] = $row; 
            }
        }
        ?>

        <?php if ($superAdmin): ?>
            <?php
            $hoy_sa = new DateTime();
            $clave_vencida_sa = false;
            if (!empty($superAdmin['fecha_cambio_clave'])) {
                $fecha_clave_sa = new DateTime($superAdmin['fecha_cambio_clave']);
                $dias_diff_sa = $fecha_clave_sa->diff($hoy_sa)->days;
                $clave_vencida_sa = $dias_diff_sa > 180;
            }
            $bloqueado_intentos_sa = intval($superAdmin['intentos'] ?? 0) >= 3;
            ?>
            <div class="glass-card mb-5 p-4 d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-4 border-start border-4 border-primary">
                <div class="d-flex align-items-center gap-3 w-100">
                    <div class="position-relative flex-shrink-0">
                        <img src="<?php echo htmlspecialchars($superAdmin['foto']); ?>" class="img-circular" alt="foto" width="60" height="60">
                        <span class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle p-1 border border-2 border-white" style="font-size: 0.6rem;">
                            <i class="fas fa-crown"></i>
                        </span>
                    </div>
                    <div class="overflow-hidden">
                        <h5 class="mb-1 fw-bold d-flex flex-wrap align-items-center gap-2"><?php echo htmlspecialchars($superAdmin['nombre']); ?> <span class="badge bg-primary small">Super Usuario</span></h5>
                        <p class="text-muted small mb-0 text-truncate d-block" style="max-width: 100%;"><i class="fas fa-envelope me-1"></i> <?php echo htmlspecialchars($superAdmin['correo']); ?></p>
                    </div>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-3 justify-content-start justify-content-md-end w-100 w-md-auto mt-2 mt-md-0">
                    <?php if ($superAdmin['aprobado'] == 0): ?>
                        <span class="status-badge bg-secondary text-white"><i class="fas fa-clock"></i> Pendiente</span>
                    <?php elseif ($superAdmin['bloqueado']): ?>
                        <span class="status-badge bg-danger text-white"><i class="fas fa-ban"></i> Bloqueado Admin</span>
                    <?php elseif ($bloqueado_intentos_sa): ?>
                        <span class="status-badge bg-warning text-dark"><i class="fas fa-shield-alt"></i> Bloqueado Intentos</span>
                    <?php elseif ($clave_vencida_sa): ?>
                        <span class="status-badge bg-orange text-white" style="background-color: #f97316;"><i class="fas fa-key"></i> Clave Vencida</span>
                    <?php else: ?>
                        <span class="status-badge bg-success text-white"><i class="fas fa-check-circle"></i> Activo</span>
                    <?php endif; ?>

                    <?php if ($loggedUserId == $superAdmin['id_usuario']): ?>
                        <button class="btn btn-outline-primary rounded-pill btn-sm px-3" onclick="navigateTo('edit_u.php?id=<?php echo $superAdmin['id_usuario']; ?>')">
                            <i class="fas fa-user-edit me-1"></i> Mi Perfil
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="card glass-card border-0 mb-5 animate__animated animate__fadeIn">
            <div class="card-header bg-success text-white p-4 border-0">
                <h5 class="mb-0 fw-bold"><i class="fas fa-wallet me-2"></i> Clientes UPU y Saldos</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Identidad</th>
                                <th>Cédula</th>
                                <th>Correo</th>
                                <th>Saldo Disponible</th>
                                <th>Estado</th>
                                <th class="pe-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($upuUsers) > 0): ?>
                                <?php foreach ($upuUsers as $row): ?>
                                    <?php
                                        $hoy = new DateTime();
                                        $clave_vencida = false;
                                        if (!empty($row['fecha_cambio_clave'])) {
                                            $fecha_clave = new DateTime($row['fecha_cambio_clave']);
                                            $dias_diff = $fecha_clave->diff($hoy)->days;
                                            $clave_vencida = $dias_diff > 180;
                                        }
                                        $bloqueado_intentos = intval($row['intentos'] ?? 0) >= 3;
                                    ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="<?php echo htmlspecialchars($row['foto']); ?>" class="img-circular" alt="foto" width="45" height="45">
                                                <div>
                                                    <div class="fw-bold text-main"><?php echo htmlspecialchars($row['nombre']); ?></div>
                                                    <div class="text-muted small">@<?php echo htmlspecialchars($row['usuario']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['nacionalidad'] . $row['cedula']); ?></span></td>
                                        <td class="small opacity-75"><?php echo htmlspecialchars($row['correo']); ?></td>
                                        
                                        <td>
                                            <div class="d-flex align-items-center gap-3 justify-content-center">
                                                <span class="saldo-badge">$ <?php echo number_format($row['saldo'], 2); ?></span>
                                                <?php if ($loggedUserId == $superAdminId): ?>
                                                    <button class="btn btn-soft-success btn-sm rounded-pill" 
                                                            onclick="openSaldoModal(<?php echo $row['id_usuario']; ?>, '<?php echo htmlspecialchars($row['nombre']); ?>', <?php echo $row['saldo']; ?>)">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        
                                        <td>
                                            <?php if ($row['aprobado'] == 0): ?>
                                                <span class="status-badge bg-secondary text-white small"><i class="fas fa-spinner"></i> Pendiente</span>
                                            <?php elseif ($row['bloqueado']): ?>
                                                <span class="status-badge bg-danger text-white small"><i class="fas fa-user-slash"></i> Bloqueado Admin</span>
                                            <?php elseif ($bloqueado_intentos): ?>
                                                <span class="status-badge bg-warning text-dark small"><i class="fas fa-shield-virus"></i> Bloqueado Intentos</span>
                                            <?php elseif ($clave_vencida): ?>
                                                <span class="status-badge bg-orange text-white small" style="background-color: #f97316;"><i class="fas fa-key"></i> Clave Vencida</span>
                                            <?php else: ?>
                                                <span class="status-badge bg-success text-white small"><i class="fas fa-check"></i> Activo</span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="pe-4">
                                            <div class="d-flex gap-2 justify-content-center">
                                                <button class="btn btn-soft-primary btn-sm rounded-3" onclick="navigateTo('edit_u.php?id=<?php echo $row['id_usuario']; ?>')">
                                                    <i class="fas fa-pen-nib"></i>
                                                </button>
                                                <?php if ($loggedUserType == 'admin'): ?>
                                                    <?php if ($row['bloqueado']): ?>
                                                        <button class="btn btn-soft-success btn-sm rounded-3" onclick="toggleBloqueo(<?php echo $row['id_usuario']; ?>, 0)"><i class="fas fa-user-check"></i></button>
                                                    <?php else: ?>
                                                        <button class="btn btn-soft-danger btn-sm rounded-3" onclick="toggleBloqueo(<?php echo $row['id_usuario']; ?>, 1)"><i class="fas fa-user-lock"></i></button>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                <button class="btn btn-soft-danger btn-sm rounded-3" onclick="confirmDelete('../acciones/delete_u.php?id=<?php echo $row['id_usuario']; ?>')">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center text-muted p-4">No hay usuarios UPU registrados.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card glass-card border-0 animate__animated animate__fadeIn">
            <div class="card-header bg-primary text-white p-4 border-0">
                <h5 class="mb-0 fw-bold"><i class="fas fa-users me-2"></i> Lista de Personal</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="datatablesSimple" class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Identidad</th>
                                <th>Cédula</th>
                                <th>Usuario</th>
                                <th>Rol / Tipo</th>
                                <th>Estado</th>
                                <th class="pe-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($generalUsers as $row): ?>
                                <?php
                                    $hoy2 = new DateTime();
                                    $clave_vencida2 = false;
                                    if (!empty($row['fecha_cambio_clave'])) {
                                        $fecha_clave2 = new DateTime($row['fecha_cambio_clave']);
                                        $dias_diff2 = $fecha_clave2->diff($hoy2)->days;
                                        $clave_vencida2 = $dias_diff2 > 180;
                                    }
                                    $bloqueado_intentos2 = intval($row['intentos'] ?? 0) >= 3;
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="<?php echo htmlspecialchars($row['foto']); ?>" class="img-circular" alt="foto" width="45" height="45">
                                            <div>
                                                <div class="fw-bold text-main"><?php echo htmlspecialchars($row['nombre']); ?></div>
                                                <div class="small opacity-75"><?php echo htmlspecialchars($row['correo']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['nacionalidad'] . $row['cedula']); ?></span></td>
                                    <td><span class="fw-bold fs-0-9">@<?php echo htmlspecialchars($row['usuario']); ?></span></td>
                                    
                                    <td>
                                        <?php
                                        $tipo = $row['tipos'];
                                        $badgeClass = 'bg-soft-primary text-primary';
                                        $tipoTexto = strtoupper($tipo);

                                        switch ($tipo) {
                                            case 'admin': $badgeClass = 'bg-soft-danger text-danger'; $tipoTexto = 'SUPER USUARIO'; break;
                                            case 'cont':  $badgeClass = 'bg-soft-info text-info'; $tipoTexto = 'ADMINISTRADOR'; break;
                                            case 'inv':   $badgeClass = 'bg-soft-warning text-warning'; $tipoTexto = 'CHEQUEADOR'; break;
                                        }
                                        echo '<span class="badge ' . $badgeClass . ' rounded-pill px-3">' . $tipoTexto . '</span>';
                                        ?>
                                    </td>
                                    
                                    <td>
                                        <?php if ($row['aprobado'] == 0): ?>
                                            <span class="status-badge bg-secondary text-white small"><i class="fas fa-clock"></i> Pendiente</span>
                                        <?php elseif ($row['bloqueado']): ?>
                                            <span class="status-badge bg-danger text-white small"><i class="fas fa-lock"></i> Bloqueado</span>
                                        <?php elseif ($bloqueado_intentos2): ?>
                                            <span class="status-badge bg-warning text-dark small"><i class="fas fa-shield-alt"></i> Bloqueado</span>
                                        <?php elseif ($clave_vencida2): ?>
                                            <span class="status-badge bg-orange text-white small" style="background-color: #f97316;"><i class="fas fa-key"></i> Vencida</span>
                                        <?php else: ?>
                                            <span class="status-badge bg-success text-white small"><i class="fas fa-user-check"></i> Activo</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td class="pe-4">
                                        <div class="d-flex justify-content-center gap-2">
                                            <button class="btn btn-soft-primary btn-sm rounded-3" onclick="navigateTo('edit_u.php?id=<?php echo $row['id_usuario']; ?>')">
                                                <i class="fas fa-pen"></i>
                                            </button>

                                            <?php 
                                            $canDelete = true;
                                            $deleteTitle = "Borrar";
                                            if ($tipo == 'admin' && $loggedUserId != $superAdminId) {
                                                $canDelete = false;
                                                $deleteTitle = "Solo Super Admin";
                                            }
                                            if ($row['id_usuario'] == $loggedUserId) {
                                                $canDelete = false;
                                                $deleteTitle = "Tu propia cuenta";
                                            }
                                            ?>
                                            <button class="btn btn-soft-danger btn-sm rounded-3"
                                                <?php echo $canDelete ? 'onclick="confirmDelete(\'../acciones/delete_u.php?id=' . $row['id_usuario'] . '\')"' : 'disabled'; ?>
                                                title="<?php echo $deleteTitle; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>

                                            <?php if ($loggedUserType == 'admin' && $row['id_usuario'] != $loggedUserId): ?>
                                                <?php if ($row['bloqueado']): ?>
                                                    <button class="btn btn-soft-success btn-sm rounded-3" onclick="toggleBloqueo(<?php echo $row['id_usuario']; ?>, 0)"><i class="fas fa-unlock"></i></button>
                                                <?php else: ?>
                                                    <button class="btn btn-soft-warning btn-sm rounded-3" onclick="toggleBloqueo(<?php echo $row['id_usuario']; ?>, 1)"><i class="fas fa-lock"></i></button>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<?php
require_once("../models/footer.php");
?>