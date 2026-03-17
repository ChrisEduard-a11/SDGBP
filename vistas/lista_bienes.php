<?php
require_once("../models/header.php");
include('../conexion.php');
include('../acciones/controlador_categoria.php');

// Obtener todas las categorías
// Nota: Se asume que obtenerCategorias($conexion) devuelve una lista de categorías.
$categorias = obtenerCategorias($conexion);

// Lógica para preselección y carga inicial (Mantenida del código original)
$categoria_seleccionada = isset($_POST['categoria']) ? $_POST['categoria'] : '';
$nombre_bien_seleccionado = isset($_POST['nombre_bien']) ? $_POST['nombre_bien'] : '';

$bienes = [];
// Solo cargamos datos si ambos filtros están presentes (asumiendo que esta lógica es para la carga inicial/re-envío del form)
if ($categoria_seleccionada && $nombre_bien_seleccionado) {
    // NOTA: La columna 'Serial' y la de 'Etiqueta' no estaban en el SELECT de PHP original,
    // pero se incluyen aquí y en la tabla para mantener la estructura completa de la tabla HTML esperada.
    $sql = "SELECT b.id, b.nombre, b.descripcion, c.nombre AS categoria, b.codigo, b.serial, b.fecha_adquisicion 
            FROM bienes b 
            JOIN categorias c ON b.categoria_id = c.id
            WHERE b.categoria_id = '$categoria_seleccionada' AND b.nombre = '$nombre_bien_seleccionado'";

    $result = mysqli_query($conexion, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        $bienes[] = $row;
    }
}

// Obtener los nombres de los bienes según la categoría seleccionada para precargar el segundo select
$nombres_bienes = [];
if ($categoria_seleccionada) {
    $sql_nombres = "SELECT DISTINCT nombre FROM bienes WHERE categoria_id = '$categoria_seleccionada' ORDER BY nombre ASC";
    $result_nombres = mysqli_query($conexion, $sql_nombres);
    while ($row_nombres = mysqli_fetch_assoc($result_nombres)) {
        $nombres_bienes[] = $row_nombres['nombre'];
    }
    }
?>
<style>
    /* =========================================
       SISTEMA SDGBP - DISEÑO ULTRA PREMIUM 2026
       INVENTARIO DE BIENES NACIONALES
       ========================================= */
    :root {
        --primary: #f18000;
        --primary-dark: #d67100;
        --primary-light: rgba(241, 128, 0, 0.1);
        --accent-blue: #3b82f6;
        --accent-green: #10b981;
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
        box-shadow: var(--shadow-premium);
    }

    .card-premium {
        background: #ffffff;
        border: none !important;
        border-radius: var(--radius-premium) !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
        overflow: hidden;
    }

    .card-premium-header {
        padding: 1.5rem 2rem;
        border: none !important;
        color: white;
    }

    .header-filter { background: linear-gradient(135deg, var(--accent-blue) 0%, #1d4ed8 100%); }
    .header-list { background: linear-gradient(135deg, var(--accent-green) 0%, #059669 100%); }

    .card-premium-header h5 {
        margin: 0;
        font-weight: 700;
        letter-spacing: 0.5px;
    }

    .form-select-premium {
        border: 1.5px solid var(--border-color) !important;
        border-radius: 12px !important;
        padding: 0.75rem 1rem !important;
        font-weight: 500 !important;
        transition: all 0.3s ease !important;
    }

    .form-select-premium:focus {
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 4px var(--primary-light) !important;
    }

    /* --- TABLE CUSTOMIZATION --- */
    #datatablesSimple {
        border-collapse: separate !important;
        border-spacing: 0 8px !important;
    }

    #datatablesSimple thead th {
        background: #f8fafc !important;
        color: var(--text-muted) !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        font-size: 0.75rem !important;
        padding: 1.25rem 1rem !important;
        border: none !important;
        letter-spacing: 1px;
    }

    #datatablesSimple tbody tr {
        background: white !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        transition: all 0.2s ease;
    }

    #datatablesSimple tbody tr:hover {
        background: #f1f5f9 !important;
        transform: scale(1.002);
    }

    #datatablesSimple td {
        padding: 1rem !important;
        vertical-align: middle !important;
        border: none !important;
    }

    .badge-premium {
        padding: 0.5rem 0.75rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.75rem;
    }

    .btn-action-premium {
        width: 35px;
        height: 35px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        transition: all 0.2s ease;
        border: none;
    }

    .btn-edit { background: #eff6ff; color: #1d4ed8; }
    .btn-delete { background: #fef2f2; color: #dc2626; }
    .btn-print { background: #f0fdf4; color: #16a34a; }

    .btn-edit:hover { background: #1d4ed8; color: white; }
    .btn-delete:hover { background: #dc2626; color: white; }
    .btn-print:hover { background: #16a34a; color: white; }
</style>

<div id="layoutSidenav_content">
    <div class="container-fluid px-4 py-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h1 class="mt-2 mb-1 fw-bold text-dark">Inventario de Bienes</h1>
                <p class="text-muted mb-0">Gestión y control de activos nacionales</p>
            </div>
            <div class="d-none d-md-block text-end">
                <span class="badge bg-white text-dark border rounded-pill px-3 py-2 shadow-sm">
                    <i class="fas fa-calendar-alt text-primary me-2"></i> <?php echo date('d M, Y'); ?>
                </span>
            </div>
        </div>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-premium p-3 mb-4">
                <li class="breadcrumb-item"><a href="inicio.php" class="text-primary fw-600 text-decoration-none"><i class="fas fa-home me-1"></i> Inicio</a></li>
                <li class="breadcrumb-item active text-muted"><i class="fas fa-boxes me-1"></i> Bienes Nacionales</li>
            </ol>
        </nav>

        <!-- FILTROS DE BÚSQUEDA -->
        <div class="card card-premium mb-4 shadow-lg border-0">
            <div class="card-premium-header header-filter">
                <h5><i class="fas fa-filter me-2"></i> Opciones de Búsqueda</h5>
            </div>
            <div class="card-body p-4">
                <form id="form-filtros" method="POST" action="lista_bienes.php" class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label for="categoria" class="form-label small fw-bold text-muted mb-1"><i class="fas fa-tags me-1"></i> Categoría del Bien</label>
                        <select class="form-select form-select-premium" id="categoria" name="categoria" required onchange="this.form.submit()">
                            <option value="">Seleccione una categoría</option>
                            <?php foreach ($categorias as $categoria) { ?>
                                <option 
                                    value="<?php echo $categoria['id']; ?>" 
                                    <?php echo ($categoria['id'] == $categoria_seleccionada) ? 'selected' : ''; ?>
                                >
                                    <?php echo htmlspecialchars($categoria['nombre']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    
                    <div class="col-md-5">
                        <label for="nombre_bien" class="form-label small fw-bold text-muted mb-1"><i class="fas fa-box me-1"></i> Nombre Específico</label>
                        <select class="form-select form-select-premium" id="nombre_bien" name="nombre_bien" <?php echo empty($categoria_seleccionada) ? 'disabled' : ''; ?> required onchange="this.form.submit()">
                            <option value="">Seleccione un nombre</option>
                            <?php foreach ($nombres_bienes as $nombre_bien_opcion) { ?>
                                <option 
                                    value="<?php echo htmlspecialchars($nombre_bien_opcion); ?>"
                                    <?php echo ($nombre_bien_opcion == $nombre_bien_seleccionado) ? 'selected' : ''; ?>
                                >
                                    <?php echo htmlspecialchars($nombre_bien_opcion); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <a href="lista_bienes.php" class="btn btn-outline-secondary w-100 rounded-pill py-2 fw-600" style="border: 1.5px solid #e2e8f0;">
                            <i class="fas fa-undo me-1"></i> Limpiar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- LISTADO DE RESULTADOS -->
        <div class="card card-premium shadow-lg border-0">
            <div class="card-premium-header header-list d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-list-ul me-2"></i> Bienes Registrados</h5>
                <span class="badge bg-white text-dark rounded-pill px-3 py-2 fw-bold">
                    Total: <?php echo count($bienes); ?>
                </span>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <?php if (count($bienes) > 0) { ?>
                        <table id="datatablesSimple" class="table">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">REF</th>
                                    <th>Nombre y Descripción</th>
                                    <th>Categoría</th>
                                    <th>Código / Serial</th>
                                    <th>Adquisición</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="bienes-list">
                                <?php 
                                $contador = 1;
                                foreach ($bienes as $bien) { ?>
                                    <tr>
                                        <td class="text-center">
                                            <span class="badge bg-light text-muted border px-2 py-1"><?php echo str_pad($contador++, 2, "0", STR_PAD_LEFT); ?></span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($bien['nombre']); ?></div>
                                            <div class="small text-muted text-truncate" style="max-width: 300px;"><?php echo htmlspecialchars($bien['descripcion']); ?></div>
                                        </td>
                                        <td><span class="badge-premium" style="background: #f1f5f9; color: #475569;"><?php echo htmlspecialchars($bien['categoria']); ?></span></td>
                                        <td>
                                            <div class="badge bg-primary-light text-primary px-2 py-1 mb-1 d-inline-block rounded-pill fw-bold" style="font-size: 0.7rem;">
                                                <i class="fas fa-barcode me-1"></i> <?php echo htmlspecialchars($bien['codigo']); ?>
                                            </div>
                                            <div class="small text-muted font-monospace" style="font-size: 0.7rem;">SN: <?php echo htmlspecialchars($bien['serial'] ?? 'N/A'); ?></div>
                                        </td>
                                        <td class="small fw-semibold"><?php echo date('d/m/Y', strtotime($bien['fecha_adquisicion'])); ?></td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                <button onclick="printEtiqueta(<?php echo $bien['id']; ?>)" class="btn-action-premium btn-print" title="Generar Etiqueta">
                                                    <i class="fas fa-print"></i>
                                                </button>
                                                <button onclick="confirmDeleteBien(<?php echo $bien['id']; ?>)" class="btn-action-premium btn-delete" title="Eliminar">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    <?php } else { ?>
                        <div class="text-center py-5">
                            <div class="opacity-50 mb-3">
                                <i class="fas fa-boxes fa-3x text-muted"></i>
                            </div>
                            <h6 class="text-muted fw-bold">No se han encontrado bienes con los filtros seleccionados</h6>
                            <p class="text-muted small">Seleccione una categoría y un nombre para ver los detalles.</p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

<!-- MODAL PARA VISTA PREVIA DE ETIQUETA -->
<div class="modal fade" id="etiquetaModal" tabindex="-1" aria-labelledby="etiquetaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header border-0 bg-success text-white p-4">
                <h5 class="modal-title fw-bold" id="etiquetaModalLabel">
                    <i class="fas fa-print me-2"></i> Vista Previa de Etiqueta
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 bg-light">
                <iframe id="etiquetaFrame" src="" style="width: 100%; height: 500px; border: none;"></iframe>
            </div>
            <div class="modal-footer border-0 p-3 bg-white">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-success rounded-pill px-4" onclick="document.getElementById('etiquetaFrame').contentWindow.print()">
                    <i class="fas fa-print me-1"></i> Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function printEtiqueta(id) {
        const frame = document.getElementById('etiquetaFrame');
        if (frame) {
            frame.src = '../fpdf/etiqueta_bien.php?id=' + id;
            const myModal = new bootstrap.Modal(document.getElementById('etiquetaModal'));
            myModal.show();
        }
    }

    function confirmDeleteBien(id) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede deshacer",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
                borderRadius: '15px'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '../acciones/eliminar_bien.php?id=' + id;
                }
            });
        } else {
            if (confirm('¿Estás seguro de que deseas eliminar este bien?')) {
                window.location.href = '../acciones/eliminar_bien.php?id=' + id;
            }
        }
    }
</script>

<?php
require_once("../models/footer.php");
?>
