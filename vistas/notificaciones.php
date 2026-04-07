<?php
require_once("../models/header.php");
require_once("../conexion.php");
require_once("../models/notificaciones.php");

$usuario_id = $_SESSION['id'] ?? null;
$tipo_usuario = $_SESSION['tipo'] ?? null;

// Preservar notificaciones locales (como las alertas de contraseña)
$notificaciones_locales = [];
if (isset($notificaciones) && is_array($notificaciones)) {
    foreach ($notificaciones as $n) {
        if (!isset($n['id'])) {
            $n['leida'] = 0; // Marcar como no leída para que resalte
            if (!isset($n['fecha'])) $n['fecha'] = date('Y-m-d H:i:s');
            $notificaciones_locales[] = $n;
        }
    }
}

$notificaciones_db = obtenerNotificaciones($conexion, $usuario_id, strtolower($tipo_usuario));
marcarNotificacionesComoLeidas($conexion, $usuario_id, strtolower($tipo_usuario));

// Unir las notificaciones locales con el historial completo de la base de datos
$notificaciones = array_merge($notificaciones_locales, $notificaciones_db);
?>
<style>
    /* =========================================
       SISTEMA SDGBP - DISEÑO ULTRA PREMIUM 2026
       CENTRO DE NOTIFICACIONES
       ========================================= */
    :root {
        --primary: #f18000;
        --primary-dark: #d67100;
        --bg-body: #f8fafc;
        --text-main: #1e293b;
        --text-muted: #64748b;
        --border-color: #e2e8f0;
        --radius-premium: 20px;
        --shadow-premium: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
        --glass: rgba(255, 255, 255, 0.8);
        --glass-border: rgba(255, 255, 255, 0.3);
    }

    .breadcrumb-premium {
        background: var(--glass) !important;
        backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border) !important;
        border-radius: 12px !important;
        box-shadow: var(--shadow-premium);
    }

    .card-premium {
        background: transparent;
        border: none !important;
        border-radius: var(--radius-premium) !important;
        overflow: hidden;
    }

    .card-premium-header {
        padding: 1.5rem 2rem;
        border: none !important;
        color: white;
    }

    .header-notif { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }

    .card-premium-header h5 {
        margin: 0;
        font-weight: 700;
        letter-spacing: 0.5px;
        font-size: 1.1rem;
    }

    .notif-item {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-bottom: 1px solid var(--border-color);
        background: #ffffff;
        position: relative;
    }
    
    .notif-item:last-child {
        border-bottom: none;
    }

    .notif-item:hover {
        background-color: #f8fafc;
        transform: scale(1.005);
        z-index: 1;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }

    .notif-unread {
        background-color: #f1f5f9;
        border-left: 4px solid var(--primary);
    }

    .avatar-icon-circle {
        width: 42px;
        height: 42px;
        min-width: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-size: 1.1rem;
        transition: transform 0.3s ease;
    }

    .notif-item:hover .avatar-icon-circle {
        transform: rotate(10deg);
    }

    /* RESPONSIVE REFINEMENTS */
    @media (max-width: 576px) {
        .card-premium-header {
            padding: 1rem;
            flex-direction: column;
            align-items: flex-start !important;
            gap: 1rem;
        }
        .notif-item {
            padding: 1.25rem 1rem !important;
            flex-direction: column;
            align-items: flex-start !important;
        }
        .notif-item-content {
            width: 100%;
        }
        .page-header-standard {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 1rem;
        }
    }

    [data-theme="dark"] .notif-item {
        background: #111111;
        border-color: #222;
    }
    
    [data-theme="dark"] .notif-item:hover {
        background: #1a1a1a;
    }
    
    [data-theme="dark"] .notif-unread {
        background: #1e1e1e;
    }
    
    [data-theme="dark"] :root {
        --border-color: #222;
    }
</style>

<div id="layoutSidenav_content">
    <div class="container-fluid px-4 py-4">
        
        <header class="page-header-standard d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
            <div>
                <h1 class="fw-bold mb-0 text-primary"><i class="fas fa-bell me-2"></i>Centro de Notificaciones</h1>
                <p class="text-muted">Revisa tus alertas y mensajes del sistema</p>
            </div>
            <nav aria-label="breadcrumb" class="d-none d-lg-block">
                <ol class="breadcrumb bg-transparent p-0 m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigateTo('inicio.php')" class="text-decoration-none fw-bold">Dashboard</a></li>
                    <li class="breadcrumb-item active fw-bold">Notificaciones</li>
                </ol>
            </nav>
        </header>

        <!-- LISTADO DE NOTIFICACIONES -->
        <div class="card card-premium shadow-lg border-0 animate__animated animate__fadeInUp">
            <div class="card-premium-header header-notif d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-inbox me-2"></i> Tus Mensajes Recientes</h5>
                <div class="d-flex align-items-center gap-2">
                    <?php if(count($notificaciones) > 0) { ?>
                        <button onclick="borrarTodasNotificaciones()" class="btn btn-sm btn-outline-light rounded-pill px-3 border-opacity-50 hover:bg-white hover:text-success transition-all">
                           <i class="fas fa-trash-alt me-1"></i> Borrar Todo
                        </button>
                    <?php } ?>
                    <span class="badge bg-white text-dark rounded-pill px-3 py-2 fw-bold">
                        Total: <?php echo count($notificaciones); ?>
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush rounded-bottom">
                    <?php if (count($notificaciones) > 0) { 
                        foreach ($notificaciones as $notificacion) { 
                            $bg_class = (isset($notificacion['leida']) && $notificacion['leida'] == 0) ? 'notif-unread' : '';
                            ?>
                            <div class="notif-item d-flex gap-3 p-4 align-items-center <?php echo $bg_class; ?>">
                                <div class="flex-shrink-0">
                                    <div class="avatar-icon-circle shadow-sm bg-<?php echo $notificacion['tipo']; ?> bg-opacity-10 text-<?php echo $notificacion['tipo']; ?>">
                                        <i class="<?php echo $notificacion['icono']; ?>"></i>
                                    </div>
                                </div>
                                <div class="notif-item-content d-flex flex-column flex-md-row gap-2 w-100 justify-content-between align-items-md-center">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold text-dark"><?php echo htmlspecialchars($notificacion['titulo']); ?></h6>
                                        <p class="mb-1 text-muted" style="font-size: 0.88rem; line-height: 1.4; letter-spacing: 0.1px;"><?php echo $notificacion['mensaje']; ?></p>
                                        <?php if ($tipo_usuario == 'admin' && !empty($notificacion['nombre_usuario'])) { ?>
                                            <div class="mt-2">
                                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 rounded-pill shadow-sm small">
                                                    <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($notificacion['nombre_usuario']); ?>
                                                </span>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <div class="opacity-75 text-nowrap mt-2 mt-md-0 d-flex align-items-center justify-content-between justify-content-md-end gap-3 small fw-semibold">
                                        <span class="d-flex align-items-center">
                                            <i class="far fa-clock me-1 text-primary"></i>
                                            <?php 
                                            $fnt = isset($notificacion['fecha']) ? $notificacion['fecha'] : date('Y-m-d H:i:s');
                                            echo date('d/m/Y h:i A', strtotime($fnt)); 
                                            ?>
                                        </span>
                                        
                                        <!-- BOTÓN ELIMINAR -->
                                        <?php if (isset($notificacion['id'])) { ?>
                                            <button class="btn btn-sm btn-light border rounded-circle shadow-sm text-danger d-flex align-items-center justify-content-center p-0" 
                                                    style="width: 32px; height: 32px;"
                                                    onclick="confirmarEliminar(<?php echo $notificacion['id']; ?>)" 
                                                    title="Eliminar notificación">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        <?php } 
                    } else { ?>
                        <div class="p-5 text-center text-muted bg-white">
                            <i class="far fa-bell-slash fa-4x mb-4 text-secondary opacity-50"></i>
                            <h4 class="fw-bold text-dark">Bandeja vacía</h4>
                            <p class="fs-6">Actualmente no tienes ninguna notificación registrada en el sistema.</p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmarEliminar(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "La notificación se eliminará permanentemente.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f18000',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1a1a1a' : '#fff',
        color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#fff' : '#1e293b'
    }).then((result) => {
        if (result.isConfirmed) {
            eliminarNotif(id);
        }
    });
}

function eliminarNotif(id) {
    $.ajax({
        url: '../acciones/eliminar_notificacion.php',
        type: 'POST',
        data: { id: id },
        success: function(response) {
           const res = JSON.parse(response);
            if (res.status === 'success') {
                Swal.fire({
                    title: 'Eliminado',
                    text: res.message,
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'No se pudo procesar la solicitud', 'error');
        }
    });
}

function borrarTodasNotificaciones() {
    Swal.fire({
        title: '¿Borrar todas las notificaciones?',
        text: "Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Sí, borrar todo',
        cancelButtonText: 'Cancelar',
        background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1a1a1a' : '#fff',
        color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#fff' : '#1e293b'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '../acciones/eliminar_notificacion.php',
                type: 'POST',
                data: { all: 'true' },
                success: function(response) {
                    const res = JSON.parse(response);
                    if (res.status === 'success') {
                        Swal.fire({
                            title: 'Completado',
                            text: res.message,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'No se pudo procesar la solicitud', 'error');
                }
            });
        }
    });
}
</script>

<?php
require_once("../models/footer.php");
?>
