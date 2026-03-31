<?php
require_once("../models/header.php");
?>

<div id="layoutSidenav_content">
    <div class="container-fluid px-4 py-4">
        
        <header class="page-header-standard d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
            <div>
                <h1 class="fw-bold mb-0 text-primary"><i class="fas fa-file-contract me-2"></i>Términos y Condiciones</h1>
                <p class="text-muted">Políticas de uso y privacidad del Sistema SDGBP v2.0</p>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigateTo('inicio.php')" class="text-decoration-none text-primary">Dashboard</a></li>
                    <li class="breadcrumb-item active">Términos</li>
                </ol>
            </nav>
        </header>

        <div class="row justify-content-center pt-2">
            <div class="col-xl-9 col-lg-10">
                <!-- Content Card -->
                <div class="card card-premium shadow-lg border-0 rounded-4 overflow-hidden animate__animated animate__fadeInUp">
                    <div class="card-body p-4 p-md-5">
                        
                        <div class="legal-content text-dark" style="line-height: 1.8;">
                            <?php
                            $sql_public = "SELECT valor, ultima_actualizacion FROM ajustes_sistema WHERE clave = 'terminos_condiciones'";
                            $res_public = mysqli_query($conexion, $sql_public);
                            $row_public = mysqli_fetch_assoc($res_public);
                            echo $row_public['valor'] ?? '<p class="text-muted">No se han definido términos y condiciones aún.</p>';
                            ?>
                            
                            <div class="alert alert-info border-0 rounded-4 p-4 mt-5 bg-opacity-10">
                                <div class="d-flex align-items-center">
                                    <div class="icon-box me-3">
                                        <i class="fas fa-history fa-2x"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-1">Última Actualización</h6>
                                        <p class="mb-0 text-muted">
                                            <?php 
                                            if (isset($row_public['ultima_actualizacion'])) {
                                                echo date("d/m/Y - H:i", strtotime($row_public['ultima_actualizacion']));
                                            } else {
                                                echo "No registrada";
                                            }
                                            ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-5">
                            <a href="javascript:void(0);" onclick="history.back();" class="btn btn-primary rounded-pill px-5 py-3 fw-bold shadow-sm">
                                <i class="fas fa-arrow-left me-2"></i> Volver al Sistema
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<style>
.card-premium {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.3) !important;
}

[data-theme="dark"] .card-premium {
    background: rgba(18, 18, 18, 0.8) !important;
    border-color: rgba(255, 255, 255, 0.05) !important;
}

.legal-content h4 {
    letter-spacing: -0.01em;
}

.animate__animated {
    animation-duration: 0.8s;
}
</style>

<?php
require_once("../models/footer.php");
?>
