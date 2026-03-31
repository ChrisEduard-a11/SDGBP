<?php
require_once("../models/header.php");
require_once("../conexion.php");

// Solo administradores
if ($_SESSION["tipo"] != "admin") {
    echo "<script>window.location.href='denegado_a.php';</script>";
    exit();
}

// Obtener términos y estado actual
$sql = "SELECT clave, valor FROM ajustes_sistema WHERE clave IN ('terminos_condiciones', 'terminos_status')";
$result = mysqli_query($conexion, $sql);
$ajustes = [];
while ($r = mysqli_fetch_assoc($result)) {
    $ajustes[$r['clave']] = $r['valor'];
}
$terminos_actuales = $ajustes['terminos_condiciones'] ?? '';
$estado_actual = $ajustes['terminos_status'] ?? '1';

// Mensajes de éxito/error
$mensaje = $_GET['msg'] ?? '';
?>

<div id="layoutSidenav_content">
    <div class="container-fluid px-4 py-4">
        
        <header class="page-header-standard d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
            <div>
                <h1 class="fw-bold mb-0 text-primary"><i class="fas fa-file-pen me-2"></i>Gestionar Términos</h1>
                <p class="text-muted">Editor dinámico para las políticas de uso y privacidad del sistema</p>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigateTo('inicio.php')" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active">Editar Términos</li>
                </ol>
            </nav>
        </header>

        <?php if ($mensaje == 'success'): ?>
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4 animate__animated animate__headShake">
                <i class="fas fa-check-circle me-2"></i> Los términos se han actualizado correctamente. Todos los usuarios deberán aceptarlos de nuevo.
            </div>
        <?php elseif ($mensaje == 'error'): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
                <i class="fas fa-exclamation-triangle me-2"></i> Hubo un error al guardar los cambios. Inténtelo de nuevo.
            </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-xl-10">
                <div class="card card-premium shadow-lg border-0 rounded-4 overflow-hidden">
                    <div class="card-header bg-gradient-primary text-white p-4 border-0">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-edit fa-2x me-3"></i>
                            <div>
                                <h5 class="mb-0 fw-bold">Editor de Contenido Legal</h5>
                                <small class="opacity-75">Puedes usar etiquetas HTML básicas para dar formato (p, h4, ul, li, b, i)</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4 p-md-5">
                        <form action="../acciones/guardar_terminos.php" method="POST">
                            <div class="row mb-4 align-items-center">
                                <div class="col-md-8">
                                    <h6 class="fw-bold text-dark mb-1">Estado del Sistema de Términos</h6>
                                    <p class="text-muted small mb-0">Cuando está inactivó, el banner de aceptación no se mostrará y no habrá bloqueos de acceso.</p>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <div class="form-check form-switch d-inline-block">
                                        <input class="form-check-input" type="checkbox" name="status" id="terminos_status" style="width: 3.5rem; height: 1.75rem; cursor: pointer;" <?php echo $estado_actual == '1' ? 'checked' : ''; ?>>
                                        <label class="form-check-label fw-bold ms-2" for="terminos_status" id="statusLabel">
                                            <?php echo $estado_actual == '1' ? '<span class="text-success">ACTIVO</span>' : '<span class="text-danger">INACTIVO</span>'; ?>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4 opacity-25">

                            <div class="mb-4">
                                <label for="terminos" class="form-label fw-bold text-dark mb-3">Contenido de los Términos y Condiciones</label>
                                <textarea name="terminos" id="terminos" class="form-control rounded-4 shadow-sm" style="min-height: 500px; font-family: 'Consolas', monospace; font-size: 0.95rem; line-height: 1.6; padding: 1.5rem; background: #f8fafc; border: 1.5px solid #e2e8f0;"><?php echo htmlspecialchars($terminos_actuales); ?></textarea>
                            </div>

                            <div class="alert alert-warning border-0 rounded-4 p-3 mb-4 bg-opacity-10">
                                <div class="d-flex">
                                    <i class="fas fa-info-circle mt-1 me-3"></i>
                                    <div>
                                        <h6 class="fw-bold mb-1">Aviso de Seguridad</h6>
                                        <p class="mb-0 small">Al guardar, se registrará una nueva versión. Los usuarios (no administradores) verán el banner de aceptación de nuevo en su próxima sesión o al recargar la página.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="button" class="btn btn-light rounded-pill px-4 fw-bold me-md-2" onclick="history.back()">Cancelar</button>
                                <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-sm">
                                    <i class="fas fa-save me-2"></i> Guardar y Notificar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
}
.card-premium {
    background: white;
}
[data-theme="dark"] .card-premium {
    background: #111827 !important;
}
[data-theme="dark"] #terminos {
    background: #0f172a !important;
    border-color: #334155 !important;
    color: #e2e8f0 !important;
}
</style>

<script>
    document.getElementById('terminos_status').addEventListener('change', function() {
        const label = document.getElementById('statusLabel');
        if (this.checked) {
            label.innerHTML = '<span class="text-success">ACTIVO</span>';
        } else {
            label.innerHTML = '<span class="text-danger">INACTIVO</span>';
        }
    });
</script>

<?php
require_once("../models/footer.php");
?>
