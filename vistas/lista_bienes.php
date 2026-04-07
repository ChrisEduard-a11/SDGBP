<?php
require_once("../models/header.php");
include('../conexion.php');
include('../acciones/controlador_categoria.php');

// Obtener todas las categorías
// Nota: Se asume que obtenerCategorias($conexion) devuelve una lista de categorías.
$categorias = obtenerCategorias($conexion);

// Los filtros ahora se manejan asíncronamente con JavaScript, por lo que no se inicializan en PHP aquí.
?>
<style>
    /* =========================================
       SISTEMA SDGBP - DISEÑO ULTRA PREMIUM 2026
       INVENTARIO DE BIENES NACIONALES
       ========================================= */
    :root {
        --primary: #f18000;
        --primary-light: rgba(241, 128, 0, 0.1);
        --bg-body: #f4f6f9;
        --text-main: #0f172a;
        --text-muted: #64748b;
        --border-color: #e2e8f0;
    }

    body {
        background-color: var(--bg-body);
        background-image: 
            radial-gradient(circle at 0% 0%, rgba(59, 130, 246, 0.04) 0%, transparent 40%), 
            radial-gradient(circle at 100% 100%, rgba(241, 128, 0, 0.04) 0%, transparent 30%);
        color: var(--text-main);
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    /* CARD SAAS */
    .card-premium {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255,255,255,0.8) !important;
        border-radius: 24px !important;
        box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.04), 0 1px 3px rgba(0,0,0,0.02) !important;
        overflow: hidden;
        transition: transform 0.3s ease;
    }

    .card-premium:hover {
        transform: translateY(-2px);
    }

    .card-premium-header {
        background: transparent !important;
        color: var(--text-main) !important;
        padding: 1.75rem 2rem 1rem 2rem !important;
        border-bottom: 1px solid #f1f5f9 !important;
    }

    .card-premium-header h5 {
        margin: 0;
        font-weight: 800;
        letter-spacing: -0.5px;
        font-size: 1.35rem;
    }

    .icon-glow {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 12px;
        margin-right: 12px;
    }
    
    .icon-filter { background: rgba(59,130,246,0.1); color: #3b82f6; }
    .icon-list { background: rgba(16,185,129,0.1); color: #10b981; }

    /* CONTROLES */
    .form-select-premium {
        border: 1.5px solid var(--border-color) !important;
        border-radius: 14px !important;
        padding: 0.85rem 1.25rem !important;
        font-weight: 600 !important;
        color: #334155 !important;
        background-color: #f8fafc !important;
        transition: all 0.3s ease !important;
        box-shadow: none !important;
    }

    .form-select-premium:focus {
        border-color: var(--primary) !important;
        background-color: #ffffff !important;
        box-shadow: 0 0 0 4px var(--primary-light) !important;
    }

    /* --- TABLE CUSTOMIZATION SAAS --- */
    #tablaBienesAjax {
        border-collapse: separate !important;
        border-spacing: 0 10px !important;
    }

    #tablaBienesAjax thead th {
        background: transparent !important;
        color: #94a3b8 !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        font-size: 0.75rem !important;
        padding: 0 1.25rem 1rem 1.25rem !important;
        border: none !important;
        letter-spacing: 1px;
    }

    #tablaBienesAjax tbody tr {
        background: #ffffff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.01), 0 1px 2px rgba(0,0,0,0.02);
        border-radius: 16px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    #tablaBienesAjax tbody tr:hover {
        transform: scale(1.005) translateY(-3px);
        box-shadow: 0 15px 25px -5px rgba(0,0,0,0.04), 0 8px 10px -6px rgba(0,0,0,0.02);
        position: relative;
        z-index: 2;
    }

    #tablaBienesAjax td {
        padding: 1.25rem !important;
        vertical-align: middle !important;
        border: top 1px solid transparent !important;
        border-bottom: 1px solid transparent !important;
    }

    #tablaBienesAjax td:first-child { border-top-left-radius: 16px; border-bottom-left-radius: 16px; border-left: 1px solid #f1f5f9; }
    #tablaBienesAjax td:last-child { border-top-right-radius: 16px; border-bottom-right-radius: 16px; border-right: 1px solid #f1f5f9; }

    .badge-premium {
        padding: 0.5rem 0.85rem;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.75rem;
        letter-spacing: 0.3px;
    }

    .btn-action-premium {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        transition: all 0.2s ease;
        border: none;
    }

    .btn-print { background: #f0fdf4; color: #16a34a; }
    .btn-delete { background: #fef2f2; color: #ef4444; }
    .btn-print:hover { background: #16a34a; color: white; transform: translateY(-2px); }
    .btn-delete:hover { background: #ef4444; color: white; transform: translateY(-2px); }

    /* Ultra Premium Upgrades */
    .table-container {
        position: relative;
        background: transparent;
        padding: 0 1rem;
    }
    
    .fade-in-up {
        animation: fadeInUp 0.4s ease-out forwards;
        opacity: 0;
        transform: translateY(15px);
    }

    @keyframes fadeInUp { to { opacity: 1; transform: translateY(0); } }
</style>

<div id="layoutSidenav_content">
    <div class="container-fluid px-4 py-4">
        
        <header class="page-header-standard d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
            <div>
                <h1 class="fw-bold mb-0 text-primary"><i class="fas fa-boxes me-2"></i>Inventario de Bienes</h1>
                <p class="text-muted">Gestión y control detallado de activos nacionales y mobiliario</p>
            </div>
            <div class="d-none d-md-block text-end">
                <span class="badge bg-white text-dark border rounded-pill px-3 py-2 shadow-sm">
                    <i class="fas fa-calendar-alt text-primary me-2"></i> <?php echo date('d M, Y'); ?>
                </span>
            </div>
            <nav aria-label="breadcrumb" class="d-none d-lg-block">
                <ol class="breadcrumb bg-transparent p-0 m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigateTo('inicio.php')" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active">Bienes Nacionales</li>
                </ol>
            </nav>
        </header>

        <!-- FILTROS DE BÚSQUEDA -->
        <div class="card card-premium mb-5 border-0">
            <div class="card-premium-header d-flex align-items-center">
                <div class="icon-glow icon-filter"><i class="fas fa-filter"></i></div>
                <h5>Filtrado Dinámico</h5>
            </div>
            <div class="card-body p-4 pt-1">
                <form id="form-filtros" class="row g-3 align-items-end" onsubmit="event.preventDefault(); return false;">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="categoria" class="form-label small fw-bold text-muted mb-2"><i class="fas fa-tags me-1"></i> Filtro por Categoría</label>
                            <select class="form-select form-select-premium shadow-sm" id="categoria" name="categoria" onchange="cargarNombresyFiltrar(this.value)">
                                <option value="" selected>Todas las categorías</option>
                                <?php foreach ($categorias as $categoria) { ?>
                                    <option value="<?php echo $categoria['id']; ?>">
                                        <?php echo htmlspecialchars($categoria['nombre']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="nombre_bien" class="form-label small fw-bold text-muted mb-2"><i class="fas fa-box me-1"></i> Filtro Específico</label>
                            <select class="form-select form-select-premium shadow-sm" id="nombre_bien" name="nombre_bien" disabled onchange="filtrarTabla()">
                                <option value="">Seleccione una categoría primero</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <button type="button" onclick="limpiarFiltros()" data-no-preloader="true" class="btn btn-outline-secondary w-100 rounded-pill py-2 fw-600 shadow-sm transition-all" style="border: 1.5px solid #e2e8f0;">
                            <i class="fas fa-undo me-1"></i> Reiniciar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- LISTADO DE RESULTADOS -->
        <div class="card card-premium mb-5 border-0">
            <div class="card-premium-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="icon-glow icon-list"><i class="fas fa-list-ul"></i></div>
                    <h5>Bienes Registrados</h5>
                </div>
                <span class="badge" style="background:#e2e8f0; color:#334155; font-size:15px; border-radius:12px; padding: 8px 15px;" id="count-bienes">
                    Total: <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                </span>
            </div>
                <div class="table-container table-responsive outline-0" style="min-height: 300px; position: relative;" id="datatable-wrapper">
                    <div id="loader-overlay" class="position-absolute w-100 h-100 d-flex justify-content-center align-items-center bg-white bg-opacity-75" style="z-index: 5; display: none; border-radius: 16px;">
                        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                    <!-- La tabla se generará completamente desde JS dentro de #datatable-wrapper -->
                    <table id="tablaBienesAjax" class="table mb-0 w-100">
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
                        <tbody id="bienes-list"></tbody>
                    </table>
                    
                    <div id="empty-state" class="text-center py-5" style="display: none;">
                        <div class="opacity-50 mb-3">
                            <i class="fas fa-boxes fa-3x text-muted"></i>
                        </div>
                        <h6 class="text-muted fw-bold">No hay bienes con los parámetros seleccionados</h6>
                        <p class="text-muted small">Intente cambiando su categoría o limpieza de los filtros de búsqueda.</p>
                    </div>
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
<script>
    let tableInstance = null; // Variable para almacenar la instancia de simple-datatables
    
    document.addEventListener("DOMContentLoaded", function() {
        filtrarTabla(); // Carga inicial
    });

    function cargarNombresyFiltrar(categoriaId) {
        const selectNombre = document.getElementById('nombre_bien');
        selectNombre.innerHTML = '<option value="">Todos los nombres</option>';
        
        if (!categoriaId) {
            selectNombre.disabled = true;
            filtrarTabla();
            return;
        }

        selectNombre.disabled = false;
        
        // Obtener solo nombres únicos de la categoría elegida
        fetch(`../acciones/obtener_nombres_bienes.php?categoria_id=${categoriaId}`)
            .then(res => res.json())
            .then(data => {
                data.forEach(nombre => {
                    const option = document.createElement('option');
                    option.value = nombre;
                    option.textContent = nombre;
                    selectNombre.appendChild(option);
                });
            })
            .catch(err => console.error("Error obteniendo nombres:", err));
        
        filtrarTabla(); // Actualizar la tabla
    }

    function limpiarFiltros() {
        document.getElementById('categoria').value = '';
        const selectNombre = document.getElementById('nombre_bien');
        selectNombre.value = '';
        selectNombre.disabled = true;
        selectNombre.innerHTML = '<option value="">Seleccione una categoría primero</option>';
        filtrarTabla();
    }

    function filtrarTabla() {
        const categoria_id = document.getElementById('categoria').value;
        const nombre_bien = document.getElementById('nombre_bien').value;
        const emptyState = document.getElementById('empty-state');
        const loader = document.getElementById('loader-overlay');
        const countBadge = document.getElementById('count-bienes');
        const wrapper = document.getElementById('datatable-wrapper');
        
        loader.style.setProperty('display', 'flex', 'important');
        emptyState.style.display = 'none';

        // Destruir limpiar la tabla existente para recrearla limpia
        if (tableInstance) {
            tableInstance.destroy();
            tableInstance = null;
        }
        
        // Remover la tabla vieja
        const oldTable = document.getElementById('tablaBienesAjax');
        if (oldTable) oldTable.remove();

        fetch(`../acciones/filtrar_bienes_ajax.php?categoria=${encodeURIComponent(categoria_id)}&nombre_bien=${encodeURIComponent(nombre_bien)}`)
            .then(res => res.json())
            .then(data => {
                loader.style.setProperty('display', 'none', 'important');
                
                if (countBadge) countBadge.innerHTML = 'Total: ' + data.length;

                if (data.length === 0) {
                    emptyState.style.display = 'block';
                    return;
                }

                // Crear tabla HTML limpia
                let tableHTML = `
                <table id="tablaBienesAjax" class="table mb-0 w-100">
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
                    <tbody id="bienes-list">`;

                data.forEach((bien, i) => {
                    const fechaParts = bien.fecha_adquisicion ? bien.fecha_adquisicion.split('-') : [];
                    const fechaFormateada = fechaParts.length === 3 ? `${fechaParts[2]}/${fechaParts[1]}/${fechaParts[0]}` : 'N/A';
                    const numCounter = (i + 1).toString().padStart(2, '0');

                    tableHTML += `
                        <tr class="fade-in-up" style="animation-delay: ${i * 0.05}s">
                            <td class="text-center">
                                <span class="badge bg-light text-muted border px-2 py-1 shadow-sm">${numCounter}</span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">${bien.nombre}</div>
                                <div class="small text-muted text-truncate" style="max-width: 300px;">${bien.descripcion}</div>
                            </td>
                            <td><span class="badge-premium shadow-sm" style="background: #f1f5f9; color: #475569;">${bien.categoria}</span></td>
                            <td>
                                <div class="badge bg-primary-light text-primary px-2 py-1 mb-1 d-inline-block rounded-pill fw-bold shadow-sm" style="font-size: 0.7rem;">
                                    <i class="fas fa-barcode me-1"></i> ${bien.codigo}
                                </div>
                                <br>
                                <span class="small text-muted font-monospace" style="font-size: 0.7rem;">SN: ${bien.serial || 'N/A'}</span>
                            </td>
                            <td class="small fw-semibold text-secondary">${fechaFormateada}</td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <button onclick="printEtiqueta(${bien.id})" data-no-preloader="true" class="btn-action-premium btn-print shadow-sm" title="Generar Etiqueta">
                                        <i class="fas fa-print"></i>
                                    </button>
                                    <button onclick="confirmDeleteBien(${bien.id})" data-no-preloader="true" class="btn-action-premium btn-delete shadow-sm" title="Eliminar">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>`;
                });

                tableHTML += `</tbody></table>`;
                
                // Insertar tabla antes del empty-state
                emptyState.insertAdjacentHTML('beforebegin', tableHTML);

                // Inicializar dataTable en la tabla recién recreada
                if (typeof simpleDatatables !== 'undefined') {
                    tableInstance = new simpleDatatables.DataTable("#tablaBienesAjax", {
                        searchable: true,
                        fixedHeight: false,
                        labels: {
                            placeholder: "Buscar...",
                            searchTitle: "Buscar dentro de la tabla",
                            pageTitle: "Página {page}",
                            perPage: "registros por página",
                            noRows: "No hay entradas encontradas",
                            info: "Mostrando {start} a {end} de {rows} registros",
                            noResults: "No hay resultados coinciden con su búsqueda",
                        }
                    });
                }
            })
            .catch(error => {
                console.error("Error filtrando", error);
                loader.style.setProperty('display', 'none', 'important');
            });
    }

    function printEtiqueta(id) {
        const frame = document.getElementById('etiquetaFrame');
        if (frame) {
            const labelUrl = '../fpdf/etiqueta_bien.php?id=' + id;
            console.log("Intentando cargar etiqueta desde:", labelUrl);
            frame.src = labelUrl;
            const myModal = new bootstrap.Modal(document.getElementById('etiquetaModal'));
            myModal.show();
        }
    }

    function confirmDeleteBien(id) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '<strong style="color: #0f172a">¿Pulverizar Activo?</strong>',
                html: "<p class='text-muted' style='font-size: 0.95rem'>Esta acción borrará este bien de la base de datos de manera permanente. No podrás revertirlo.</p>",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: '<i class="fas fa-trash-alt me-1"></i> Sí, Eliminar',
                cancelButtonText: 'Mejor no',
                reverseButtons: true,
                customClass: { 
                    popup: 'border-0',
                    confirmButton: 'rounded-pill px-4',
                    cancelButton: 'rounded-pill px-4'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Mostrar modal de procesamiento
                    Swal.fire({ 
                        title: 'Eliminando...', 
                        timerProgressBar: true, 
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading() } 
                    });

                    // Petición AJAX POST
                    fetch('../acciones/eliminar_bien.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: id })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({ 
                                icon: 'success', 
                                title: '<strong style="color: #0f172a">¡Operación Exitosa!</strong>', 
                                text: 'El activo desapareció completamente de la bóveda.', 
                                showConfirmButton: false, 
                                timer: 2000 
                            });
                            filtrarTabla(); // Remueve el ítem de la tabla asíncronamente
                        } else {
                            Swal.fire('Error al eliminar', data.message || 'No se pudo retirar el documento', 'error');
                        }
                    })
                    .catch(e => {
                        console.error('Network Error:', e);
                        Swal.fire('Error Fatal', 'Fallo en la comunicación con el servidor', 'error');
                    });
                }
            });
        } else {
            console.error('No se pudo invocar SweetAlert2. Procediendo normal...');
            if (confirm('¿Estás seguro de que deseas eliminar permanentemente este bien?')) {
                // Caída atrás de seguridad, reintento tradicional
                window.location.href = '../acciones/eliminar_bien.php?id=' + id;
            }
        }
    }
</script>

<?php
require_once("../models/footer.php");
?>
