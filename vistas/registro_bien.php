<?php
require_once("../models/header.php");
include('../acciones/controlador_categoria.php');
$categorias = obtenerCategorias($conexion);

// Obtener los bienes según la categoría seleccionada
$bienes = [];
if (isset($_POST['categoria'])) {
    $categoria_id = intval($_POST['categoria']);
    $query = "SELECT id, nombre, descripcion FROM bienes WHERE categoria_id = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $categoria_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $bienes[] = $row;
    }
}
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

    .header-info { background: linear-gradient(135deg, var(--accent-blue) 0%, #1d4ed8 100%); }
    .header-success { background: linear-gradient(135deg, var(--accent-green) 0%, #059669 100%); }
    .header-primary { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); }

    .card-premium-header h5 {
        margin: 0;
        font-weight: 700;
        letter-spacing: 0.5px;
    }

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
                <div class="card card-premium mb-4">
                    <div class="card-premium-header header-info">
                        <h5><i class="fas fa-tags me-2"></i> Paso 1: Clasificación</h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="post" action="">
                            <div class="form-group">
                                <label for="categoria" class="form-label small fw-bold text-muted mb-2">Categoría del Bien</label>
                                <select class="form-select form-select-premium" id="categoria" name="categoria" onchange="this.form.submit()">
                                    <option value="">Seleccione una categoría</option>
                                    <?php foreach ($categorias as $categoria) { ?>
                                        <option value="<?php echo $categoria['id']; ?>" <?php echo (isset($_POST['categoria']) && $_POST['categoria'] == $categoria['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($categoria['nombre']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- PASO 2: DETALLES -->
                <div class="card card-premium mb-5">
                    <div class="card-premium-header header-success">
                        <h5><i class="fas fa-edit me-2"></i> Paso 2: Información Técnica</h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="post" action="../acciones/controlador_bien.php" onsubmit="return validateFormRB()">
                            <input type="hidden" name="categoria" value="<?php echo isset($_POST['categoria']) ? $_POST['categoria'] : ''; ?>">

                            <div class="row g-4">
                                <!-- Nombre del Bien -->
                                <div class="col-12">
                                    <label for="nombre" class="form-label small fw-bold text-muted mb-2">Nombre del Activo</label>
                                    <div class="input-group">
                                        <select class="form-select form-select-premium" id="nombre" name="nombre" onchange="autocompletarDescripcion()" style="border-radius: 12px 0 0 12px !important;">
                                            <option value="">Seleccione un bien existente...</option>
                                            <?php foreach ($bienes as $bien) { ?>
                                                <option value="<?php echo $bien['id']; ?>" data-descripcion="<?php echo htmlspecialchars($bien['description'] ?? $bien['descripcion']); ?>">
                                                    <?php echo htmlspecialchars($bien['nombre']); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <button type="button" class="btn btn-success px-4" onclick="AgregarNuevoBien()" style="border-radius: 0 12px 12px 0;">
                                            <i class="fas fa-plus me-1"></i> Nuevo
                                        </button>
                                    </div>
                                    <!-- Campos ocultos para bien nuevo -->
                                    <input type="hidden" id="nuevo_nombre" name="nuevo_nombre">
                                    <input type="hidden" id="nueva_descripcion" name="nueva_descripcion">
                                </div>

                                <!-- Descripción -->
                                <div class="col-12">
                                    <label for="descripcion" class="form-label small fw-bold text-muted mb-2">Descripción Detallada</label>
                                    <textarea id="descripcion" name="descripcion" class="form-control form-control-premium" rows="3" readonly placeholder="La descripción se cargará automáticamente al seleccionar un bien"></textarea>
                                </div>

                                <!-- Serial -->
                                <div class="col-md-6">
                                    <label for="serial" class="form-label small fw-bold text-muted mb-2">Número de Serial</label>
                                    <input type="text" class="form-control form-control-premium" id="serial" name="serial" placeholder="Ej: SN-12345678">
                                </div>

                                <!-- Código Alternativo -->
                                <div class="col-md-6">
                                    <label for="codigo_alternativo" class="form-label small fw-bold text-muted mb-2">Código Interno (Auto-generado)</label>
                                    <input type="text" class="form-control form-control-premium bg-light fw-bold text-primary" id="codigo_alternativo" name="codigo_alternativo" value="BN-<?php echo strtoupper(substr(md5(uniqid(rand(), true)), 0, 8)); ?>" readonly>
                                </div>

                                <!-- Fecha de Adquisición -->
                                <div class="col-md-12">
                                    <label for="fecha_adquisicion" class="form-label small fw-bold text-muted mb-2">Fecha de Ingreso</label>
                                    <input type="text" class="form-control form-control-premium datepicker-flat" id="fecha_adquisicion" name="fecha_adquisicion" placeholder="YYYY-MM-DD">
                                </div>

                                <!-- Botón Guardar -->
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-premium-gradient w-100 py-3">
                                        <i class="fas fa-save me-2"></i> Finalizar Registro de Activo
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
require_once("../models/footer.php");
?>
