<?php
require_once("../models/header.php");
include('../acciones/controlador_categoria.php');
$categorias = obtenerCategorias($conexion);

// Los bienes ahora se cargarán por AJAX. Ya no necesitamos procesar $_POST['categoria'] aquí.
?>
<style>
    /* =========================================
       SISTEMA SDGBP - DISEÑO ULTRA PREMIUM 2026
       REGISTRO DE BIENES NACIONALES
       ========================================= */
    :root {
        --primary: #f18000;
        --primary-dark: #d67100;
        --primary-light: rgba(241, 128, 0, 0.1);
        --bg-body: #f4f6f9;
        --text-main: #0f172a;
        --text-muted: #64748b;
        --border-color: #e2e8f0;
    }

    body {
        background-color: var(--bg-body);
        background-image: 
            radial-gradient(circle at 100% 0%, rgba(59, 130, 246, 0.04) 0%, transparent 40%), 
            radial-gradient(circle at 0% 100%, rgba(241, 128, 0, 0.04) 0%, transparent 30%);
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
        transition: transform 0.3s ease;
        overflow: visible;
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
    
    .icon-info { background: rgba(59,130,246,0.1); color: #3b82f6; }
    .icon-success { background: rgba(16,185,129,0.1); color: #10b981; }

    .form-control-premium, .form-select-premium {
        border: 1.5px solid var(--border-color) !important;
        border-radius: 12px !important;
        padding: 0.75rem 1rem !important;
        font-weight: 500 !important;
        transition: all 0.3s ease !important;
    }

    .form-control-premium:focus, .form-select-premium:focus {
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 4px var(--primary-light) !important;
    }

    .input-group-text-premium {
        background: #f8fafc !important;
        border: 1.5px solid var(--border-color) !important;
        border-right: none !important;
        border-radius: 12px 0 0 12px !important;
        color: var(--text-muted) !important;
    }

    .btn-premium-gradient {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 0.75rem 1.5rem;
        font-weight: 700;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px -1px rgba(241, 128, 0, 0.4);
    }

    .btn-premium-gradient:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(241, 128, 0, 0.5);
        color: white;
    }

    .instruction-alert {
        border: none !important;
        border-radius: 15px !important;
        background: linear-gradient(135deg, rgba(255,255,255,1) 0%, rgba(248,250,252,1) 100%);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    
    /* Los floating labels ahora utilizan el estándar form-floating de Bootstrap 5 */
    .form-floating > .form-control-premium:focus,
    .form-floating > .form-select-premium:focus {
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 4px var(--primary-light) !important;
    }
    .input-group-premium {
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        border-radius: 12px;
    }
</style>

<div id="layoutSidenav_content">
    <div class="container-fluid px-4 py-4">
        
        <header class="page-header-standard d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
            <div>
                <h1 class="fw-bold mb-0 text-primary"><i class="fas fa-plus-circle me-2"></i>Registrar Nuevo Bien</h1>
                <p class="text-muted">Incorpore activos al inventario institucional con detalles técnicos</p>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigateTo('inicio.php')" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active">Registro de Activos</li>
                </ol>
            </nav>
        </header>

        <div class="row justify-content-center">
            <div class="col-xl-8">
                <form method="post" action="../acciones/controlador_bien.php" onsubmit="return validateFormRB()" autocomplete="off">
                <!-- INSTRUCCIONES -->
                <div class="alert instruction-alert alert-dismissible fade show p-4 mb-4" role="alert">
                    <div class="d-flex">
                        <div class="me-3">
                            <i class="fas fa-info-circle fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="alert-heading fw-bold">Instrucciones de Registro</h5>
                            <p class="mb-0">Complete todos los campos obligatorios. Verifique que los números de serie y códigos coincidan con el activo físico para evitar discrepancias en la auditoría.</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>

                <!-- PASO 1: CATEGORÍA -->
                <div class="card card-premium mb-5 border-0">
                    <div class="card-premium-header d-flex align-items-center">
                        <div class="icon-glow icon-info"><i class="fas fa-tags"></i></div>
                        <h5>Paso 1: Clasificación</h5>
                    </div>
                    <div class="card-body p-4 pt-4">
                            <div class="form-group mb-3">
                                <label for="categoria" class="form-label small fw-bold text-muted mb-2"><i class="fas fa-layer-group me-1"></i> Categoría del Bien</label>
                                <select class="form-select form-select-premium shadow-sm" id="categoria" name="categoria" onchange="cargarBienesPorCategoria(this.value)">
                                    <option value="" selected>Seleccione una categoría</option>
                                    <?php foreach ($categorias as $categoria) { ?>
                                        <option value="<?php echo $categoria['id']; ?>">
                                            <?php echo htmlspecialchars($categoria['nombre']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                    </div>
                </div>

                <!-- PASO 2: DETALLES -->
                <div class="card card-premium border-0">
                    <div class="card-premium-header d-flex align-items-center">
                        <div class="icon-glow icon-success"><i class="fas fa-edit"></i></div>
                        <h5>Paso 2: Información Técnica</h5>
                    </div>
                    <div class="card-body p-4 pt-4">
                            <div class="row g-4">
                                <!-- Nombre del Bien -->
                                <div class="col-12">
                                    <div class="form-group mb-1">
                                        <label for="nombre" class="form-label small fw-bold text-muted mb-2"><i class="fas fa-box-open me-1"></i> Nombre del Activo</label>
                                        <div class="input-group input-group-premium shadow-sm">
                                            <select class="form-select form-select-premium" id="nombre" name="nombre" onchange="autocompletarDescripcion()" style="border-radius: 12px 0 0 12px !important; z-index: 1;" disabled>
                                                <option value="">Seleccione primero una categoría...</option>
                                            </select>
                                            <button type="button" class="btn btn-success px-4" onclick="AgregarNuevoBien()" data-no-preloader="true" style="border-radius: 0 12px 12px 0; z-index: 2; border: 1.5px solid var(--accent-green); border-left: none;">
                                                <i class="fas fa-plus me-1"></i> <span class="fw-bold">Añadir Nuevo</span>
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Campos ocultos para bien nuevo -->
                                    <input type="hidden" id="nuevo_nombre" name="nuevo_nombre">
                                    <input type="hidden" id="nueva_descripcion" name="nueva_descripcion">
                                </div>

                                <!-- Descripción -->
                                <div class="col-12">
                                    <div class="form-group mb-1">
                                        <label for="descripcion" class="form-label small fw-bold text-muted mb-2"><i class="fas fa-align-left me-1"></i> Descripción Detallada</label>
                                        <textarea id="descripcion" name="descripcion" class="form-control form-control-premium shadow-sm" style="height: 100px" readonly placeholder="La descripción se cargará automáticamente..."></textarea>
                                    </div>
                                </div>

                                <!-- Serial -->
                                <div class="col-md-6">
                                    <div class="form-group mb-1">
                                        <label for="serial" class="form-label small fw-bold text-muted mb-2"><i class="fas fa-barcode me-1"></i> Número de Serial</label>
                                        <input type="text" class="form-control form-control-premium shadow-sm" id="serial" name="serial" placeholder="Ej: SN-12345678" autocomplete="off">
                                    </div>
                                </div>

                                <!-- Código Alternativo -->
                                <div class="col-md-6">
                                    <div class="form-group mb-1">
                                        <label for="codigo_alternativo" class="form-label small fw-bold text-muted mb-2"><i class="fas fa-fingerprint me-1"></i> Código Interno (Generado)</label>
                                        <input type="text" class="form-control form-control-premium bg-light fw-bold text-primary shadow-sm" id="codigo_alternativo" name="codigo_alternativo" value="BN-<?php echo strtoupper(substr(md5(uniqid(rand(), true)), 0, 8)); ?>" readonly placeholder="BN-XXXX" autocomplete="off">
                                    </div>
                                </div>

                                <!-- Fecha de Adquisición -->
                                <div class="col-md-12">
                                    <div class="form-group mb-1">
                                        <label for="fecha_adquisicion" class="form-label small fw-bold text-muted mb-2"><i class="far fa-calendar-alt me-1"></i> Fecha de Ingreso</label>
                                        <input type="text" class="form-control form-control-premium datepicker-flat shadow-sm" id="fecha_adquisicion" name="fecha_adquisicion" placeholder="Seleccione la fecha de ingreso...">
                                    </div>
                                </div>

                                <!-- Botón Guardar -->
                                <div class="col-12 mt-4 d-flex justify-content-center">
                                    <button type="submit" class="btn btn-premium-gradient py-3" style="width: 80%; border-radius: 16px; font-size: 1.1rem; letter-spacing: 0.5px;">
                                        <i class="fas fa-save me-2"></i> Procesar Alta en Inventario
                                    </button>
                                </div>
                            </div>
                    </div>
                </div>
                </form>
            </div>
        </div>
<script>
// Manejar la carga por AJAX basado en la categoría seleccionada
function cargarBienesPorCategoria(categoriaId) {
    const selectNombre = document.getElementById('nombre');
    const inputDescripcion = document.getElementById('descripcion');
    
    // Limpieza inicial
    selectNombre.innerHTML = '<option value="">Seleccione un bien existente...</option>';
    inputDescripcion.value = '';
    
    // Validar si hay categoría
    if (!categoriaId) {
        selectNombre.disabled = true;
        selectNombre.innerHTML = '<option value="">Seleccione primero una categoría...</option>';
        return;
    }
    
    selectNombre.disabled = false;

    // Fetch silencioso en background
    fetch(`../acciones/obtener_bienes_por_categoria_ajax.php?categoria_id=${categoriaId}`)
        .then(response => response.json())
        .then(data => {
            if (data.length > 0) {
                data.forEach(bien => {
                    const desc = typeof bien.descripcion !== 'undefined' ? bien.descripcion : (bien.description || '');
                    const option = document.createElement('option');
                    option.value = bien.id;
                    option.setAttribute('data-descripcion', desc);
                    option.textContent = bien.nombre;
                    selectNombre.appendChild(option);
                });
            } else {
                selectNombre.innerHTML = '<option value="">No hay bienes en esta categoría, añada uno nuevo...</option>';
            }
        })
        .catch(error => {
            console.error("Error cargando los bienes:", error);
            toastr.error("Hubo un problema de conexión al cargar los activos. Refresca la ventana.");
        });
}
</script>
<?php
require_once("../models/footer.php");
?>
