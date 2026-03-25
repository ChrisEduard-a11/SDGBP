<?php
require_once("../models/header.php");
include('../conexion.php');

// Obtener usuarios no aprobados
$sql = "SELECT id_usuario, nombre, usuario, correo, foto, tipos, cedula, nacionalidad FROM usuario WHERE aprobado = 0";
$result = mysqli_query($conexion, $sql);
if (!$result) {
    die("Error al obtener usuarios no aprobados: " . mysqli_error($conexion));
}
$usuarios_no_aprobados = mysqli_fetch_all($result, MYSQLI_ASSOC);
$totalPendientes = count($usuarios_no_aprobados);
?>

<style>
    :root {
        --premium-violet: #8b5cf6;
        --glass-bg: rgba(255, 255, 255, 0.95);
        --glass-border: rgba(139, 92, 246, 0.12);
    }
    [data-theme="dark"] {
        --glass-bg: rgba(30, 41, 59, 0.75);
        --glass-border: rgba(255, 255, 255, 0.08);
    }
    .pending-card {
        background: var(--glass-bg);
        border: 1px solid var(--glass-border);
        border-radius: 1.5rem;
        backdrop-filter: blur(12px);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
        color: var(--text-main);
    }
    .pending-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 40px rgba(139, 92, 246, 0.15);
    }
    .user-photo-ring {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 50%;
        border: 3px solid var(--premium-violet);
        padding: 3px;
        background: var(--glass-bg);
    }
    .info-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.6rem 1rem;
        border-bottom: 1px solid var(--glass-border);
        font-size: 0.85rem;
    }
    .info-item:last-child { border-bottom: none; }
    .info-item i { color: var(--premium-violet); width: 16px; text-align: center; }
    .btn-aprobar {
        background: linear-gradient(135deg, #10b981, #059669);
        color: #fff; border: none; border-radius: 0.75rem;
        padding: 0.6rem 1.2rem; font-weight: 700;
        transition: all 0.25s ease;
    }
    .btn-aprobar:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(16,185,129,0.35); color: #fff; }
    .btn-rechazar {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: #fff; border: none; border-radius: 0.75rem;
        padding: 0.6rem 1.2rem; font-weight: 700;
        transition: all 0.25s ease;
    }
    .btn-rechazar:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(239,68,68,0.35); color: #fff; }
    .empty-state {
        background: var(--glass-bg);
        border: 1px solid var(--glass-border);
        border-radius: 1.5rem;
        padding: 4rem 2rem;
        text-align: center;
        color: var(--text-main);
    }
</style>

<script>
    function confirmarRechazo(id) {
        Swal.fire({
            title: '¿Rechazar usuario?',
            text: 'Esta acción eliminará al usuario pendiente. No se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fas fa-times-circle me-1"></i> Sí, Rechazar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '../acciones/rechazar.php?id=' + id;
            }
        });
    }
</script>

<div id="layoutSidenav_content">
    <div class="container-fluid px-4 py-4">

        <header class="page-header-standard d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
            <div>
                <h1 class="fw-bold mb-0 text-primary"><i class="fas fa-user-clock me-2"></i>Aprobar Usuarios</h1>
                <p class="text-muted">Gestión de solicitudes de acceso y validación de nuevos miembros del sistema</p>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigateTo('inicio.php')" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigateTo('usuario.php')" class="text-decoration-none">Usuarios</a></li>
                    <li class="breadcrumb-item active">Aprobaciones</li>
                </ol>
            </nav>
        </header>

        <!-- Counter badge -->
        <div class="d-flex align-items-center gap-3 mb-4">
            <div class="d-flex align-items-center gap-2 px-4 py-2 rounded-4 shadow-sm" style="background: var(--glass-bg); border: 1px solid var(--glass-border);">
                <i class="fas fa-list-check" style="color: var(--premium-violet, #8b5cf6); font-size: 1.2rem;"></i>
                <span class="fw-bold" style="color: var(--text-main);">Solicitudes pendientes:</span>
                <span class="badge rounded-pill bg-danger ms-1 fs-6"><?php echo $totalPendientes; ?></span>
            </div>
            <button class="btn btn-outline-secondary rounded-4 px-4" onclick="navigateTo('usuario.php')">
                <i class="fas fa-arrow-left me-2"></i> Volver
            </button>
        </div>

        <div class="row g-4">
            <?php if (empty($usuarios_no_aprobados)): ?>

                <div class="col-12">
                    <div class="empty-state animate__animated animate__fadeIn">
                        <i class="fas fa-check-circle fa-4x mb-3" style="color: #10b981;"></i>
                        <h4 class="fw-bold mb-2">¡Todo Despejado!</h4>
                        <p class="text-muted mb-4">No hay usuarios pendientes de aprobación en este momento.</p>
                        <button class="btn btn-aprobar px-4 py-2" onclick="navigateTo('usuario.php')">
                            <i class="fas fa-users me-2"></i> Volver a Usuarios
                        </button>
                    </div>
                </div>

            <?php else: ?>

                <?php foreach ($usuarios_no_aprobados as $usuario): ?>
                    <?php
                        $tipo = $usuario['tipos'];
                        $badgeClass = 'bg-secondary';
                        $tipoTexto = 'Usuario';
                        switch ($tipo) {
                            case 'admin': $badgeClass = 'text-bg-primary'; $tipoTexto = 'SUPER USUARIO'; break;
                            case 'cont':  $badgeClass = 'text-bg-info'; $tipoTexto = 'ADMINISTRADOR'; break;
                            case 'inv':   $badgeClass = 'text-bg-warning'; $tipoTexto = 'CHEQUEADOR'; break;
                            case 'upu':   $badgeClass = 'text-bg-success'; $tipoTexto = 'UPU'; break;
                        }
                    ?>
                    <div class="col-xl-3 col-md-4 col-sm-6 animate__animated animate__fadeInUp">
                        <div class="pending-card h-100 d-flex flex-column">

                            <!-- Header: avatar + name -->
                            <div class="text-center pt-4 pb-2 px-3">
                                <img
                                    src="<?php echo htmlspecialchars($usuario['foto'] ?? '../assets/img/default-user.png'); ?>"
                                    alt="Foto de <?php echo htmlspecialchars($usuario['nombre']); ?>"
                                    class="user-photo-ring mb-3"
                                >
                                <h5 class="fw-bold mb-1" style="color: var(--text-main);">
                                    <?php echo htmlspecialchars($usuario['nombre']); ?>
                                </h5>
                                <span class="badge <?php echo $badgeClass; ?> rounded-pill px-3 mb-2"><?php echo $tipoTexto; ?></span>
                            </div>

                            <!-- Info list -->
                            <div class="px-1 flex-grow-1">
                                <div class="info-item">
                                    <i class="fas fa-id-card"></i>
                                    <div><span class="text-muted small">Cédula</span><br><strong><?php echo htmlspecialchars($usuario['nacionalidad'] . $usuario['cedula']); ?></strong></div>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-user-tag"></i>
                                    <div><span class="text-muted small">Usuario</span><br><strong>@<?php echo htmlspecialchars($usuario['usuario']); ?></strong></div>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-envelope"></i>
                                    <div><span class="text-muted small">Correo</span><br><strong class="small"><?php echo htmlspecialchars($usuario['correo']); ?></strong></div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="p-3 d-flex gap-2">
                                <form method="POST" action="../acciones/aprobar.php" class="flex-grow-1">
                                    <input type="hidden" name="usuario_id" value="<?php echo $usuario['id_usuario']; ?>">
                                    <button type="submit" name="aprobar" class="btn-aprobar w-100">
                                        <i class="fas fa-check-circle me-1"></i> Aprobar
                                    </button>
                                </form>
                                <button class="btn-rechazar flex-grow-1" onclick="confirmarRechazo(<?php echo $usuario['id_usuario']; ?>)">
                                    <i class="fas fa-times-circle me-1"></i> Rechazar
                                </button>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>

            <?php endif; ?>
        </div>

    </div>
<?php
require_once("../models/footer.php");
?>