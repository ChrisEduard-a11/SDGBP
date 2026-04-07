<?php
require_once("../models/header.php");

// Verificación estricta de Admin
if ($_SESSION['tipo'] !== 'admin') {
    $_SESSION['estatus'] = "error";
    $_SESSION['mensaje'] = "Acceso denegado. Módulo exclusivo para administradores.";
    header("Location: inicio.php");
    exit();
}

$directorio_flyers = '../img/flyers/';
$flyers = [];

// Obtener los archivos del directorio si existe
if (is_dir($directorio_flyers)) {
    $archivos = glob($directorio_flyers . '*.{jpg,jpeg,png,webp,gif}', GLOB_BRACE);
    if ($archivos) {
        // Ordenar por fecha de modificación descendente (más recientes primero)
        usort($archivos, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        $flyers = $archivos;
    }
} else {
    // Intentar crear la carpeta de forma silenciosa
    @mkdir($directorio_flyers, 0777, true);
}

// Obtener Configuración del Sistema para el Aviso del Login
$config = [];
$res_config = mysqli_query($conexion, "SELECT clave, valor FROM config_sistema");
while($row_c = mysqli_fetch_assoc($res_config)) {
    $config[$row_c['clave']] = $row_c['valor'];
}

$aviso_texto = $config['bienvenida_login_texto'] ?? '¡Bienvenido al nuevo portal institucional!';
$aviso_fecha = $config['bienvenida_login_fecha_inicio'] ?? date('Y-m-d');
$aviso_status = ($config['bienvenida_login_status'] ?? '1') == '1';
?>

<div id="layoutSidenav_content">
    <div class="container-fluid px-4 py-4">
        
        <header class="page-header-standard d-flex justify-content-between align-items-center animate__animated animate__fadeIn mb-4">
            <div>
                <h1 class="fw-bold mb-0 text-primary"><i class="fas fa-images me-2"></i>Gestión de Flyers Promocionales</h1>
                <p class="text-muted">Administra el carrusel de bienvenida del portal de la Institución.</p>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigateTo('inicio.php')" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item active">Info Institucional</li>
                    <li class="breadcrumb-item active">Banners Informativos</li>
                </ol>
            </nav>
        </header>

        <div class="row g-4 animate__animated animate__fadeInUp">
            
            <!-- Formulario de Subida -->
            <div class="col-xl-4 col-lg-5">
                <div class="card shadow-md border-0 rounded-[1.5rem] overflow-hidden mb-4">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                        <h5 class="fw-bold"><i class="fas fa-cloud-upload-alt text-primary me-2"></i>Subir Nuevo Flyer</h5>
                        <p class="text-muted small mb-0">Sube una imagen promocional para el carrusel público de la página index.</p>
                    </div>
                    <div class="card-body p-4">
                        <form action="../acciones/subir_flyer.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate onsubmit="showPreloader('Subiendo Flyer...')">
                            
                            <!-- Input Virtual para Archivo -->
                            <div class="mb-4 text-center p-4 border border-2 border-secondary border-dashed rounded-4 bg-light bg-gradient position-relative overflow-hidden transition-all duration-300 hover:border-primary hover:bg-white popup-subida" id="drop-zone" style="border-style: dashed; transition: 0.3s ease;">
                                <i class="fas fa-file-image fa-3x text-secondary mb-3 opacity-50 drop-icon"></i>
                                <p class="mb-2 fw-bold text-dark drop-text">Arrastra o haz clic aquí</p>
                                <p class="small text-muted mb-0 drop-info">Archivos permitidos: JPG, PNG, WEBP, GIF (Máx. 5 MB)</p>
                                <input type="file" name="flyer_img" class="position-absolute w-100 h-100 top-0 start-0 opacity-0" id="flyer_img" style="cursor: pointer;" accept=".jpg,.jpeg,.png,.webp,.gif" required>
                                <div class="invalid-feedback mt-2 fw-semibold">⚠ Es obligatorio seleccionar un flyer.</div>
                            </div>

                            <!-- Vista Previa Contenedor -->
                            <div id="preview-container" class="mb-4 d-none">
                                <p class="small fw-bold text-muted mb-2"><i class="fas fa-eye me-1"></i>Vista Previa de Impresión:</p>
                                <div class="position-relative d-inline-block w-100 border border-secondary shadow-sm rounded-4 overflow-hidden mb-2 bg-dark">
                                    <img id="image-preview" src="#" alt="Vista previa del archivo" class="img-fluid" style="width: 100%; height: auto; max-height: 250px; object-fit: contain;">
                                </div>
                                <p id="file-name" class="small text-primary fw-bold mb-0 text-truncate text-center w-100"></p>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-bold shadow-lg d-flex justify-content-center align-items-center gap-2 transition-all hover:-translate-y-1 hover:shadow-xl">
                                <i class="fas fa-cloud-arrow-up fs-5"></i><span>Publicar en Plataforma</span>
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="card shadow-md border-0 rounded-[1.5rem] overflow-hidden mb-4 animate__animated animate__fadeInLeft">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                        <h5 class="fw-bold"><i class="fas fa-bullhorn text-primary me-2"></i>Aviso de Bienvenida (Login)</h5>
                        <p class="text-muted small mb-0">Gestiona el mensaje que ven los usuarios al entrar al sistema.</p>
                    </div>
                    <div class="card-body p-4">
                        <form action="../acciones/actualizar_noticia_login.php" method="POST" onsubmit="showPreloader('Actualizando Aviso...')">
                            <div class="mb-3">
                                <label class="form-label fw-600">Estado del Aviso</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="status" id="avisoStatus" <?php echo $aviso_status ? 'checked' : ''; ?> value="1">
                                    <label class="form-check-label" for="avisoStatus"><?php echo $aviso_status ? 'Activado' : 'Desactivado'; ?></label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-600">Texto del Comunicado</label>
                                <textarea name="texto" class="form-control form-control-premium" rows="3" required placeholder="Escribe el mensaje de bienvenida..."><?php echo htmlspecialchars($aviso_texto); ?></textarea>
                                <div class="form-text text-muted">Ej: ¡Bienvenido al nuevo portal institucional!</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-600">Fecha de Lanzamiento</label>
                                <input type="date" name="fecha_inicio" class="form-control form-control-premium datepicker-flat" value="<?php echo $aviso_fecha; ?>" required>
                                <div class="form-text text-info"><i class="fas fa-clock me-1"></i>Vencerá automáticamente en 90 días desde esta fecha.</div>
                            </div>

                            <button type="submit" class="btn btn-dark w-100 rounded-pill py-3 fw-bold shadow-lg d-flex justify-content-center align-items-center gap-2 transition-all hover:-translate-y-1">
                                <i class="fas fa-save fs-5"></i><span>Actualizar Aviso</span>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card bg-info bg-opacity-10 border-0 border-start border-info border-4 shadow-sm rounded-3">
                    <div class="card-body p-3 d-flex align-items-start gap-3">
                        <i class="fas fa-lightbulb fs-3 text-info"></i>
                        <div>
                            <span class="d-block fw-bold text-dark fs-6 mb-1">Recomendaciones:</span>
                            <span class="d-block small text-muted">Asegúrate de que las fotos que subes tengan un formato más ancho que largo (paisaje/panorámico). Esto previene estiramientos inesperados en las pantallas de los usuarios en el carrusel principal.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Galería -->
            <div class="col-xl-8 col-lg-7">
                <div class="card shadow-md border-0 rounded-[1.5rem] overflow-hidden h-100 bg-transparent">
                    <div class="card-header bg-white border-bottom border-light px-4 py-3 d-flex justify-content-between align-items-center shadow-sm z-1 rounded-top-[1.5rem]">
                        <div>
                            <h5 class="fw-bold text-dark mb-0"><i class="fas fa-images text-primary me-2"></i>Flyers Públicos Activos</h5>
                            <span class="text-muted small">Están rotando interactivamente en el portal externo de la app.</span>
                        </div>
                        <span class="badge bg-primary rounded-pill px-3 py-2 shadow-sm"><i class="fas fa-layer-group me-1"></i><?php echo count($flyers); ?> Publicados</span>
                    </div>
                    <div class="card-body p-4 bg-slate-50 border border-top-0 rounded-bottom-[1.5rem]" style="background-color: #f8fafc;">
                        <?php if (count($flyers) > 0) { ?>
                            <div class="row g-4">
                                <?php foreach($flyers as $flyer) { 
                                    $nombre_archivo = basename($flyer);
                                    $tamano = round(filesize($flyer) / 1024, 2); // En KB
                                ?>
                                <div class="col-md-6 col-xxl-4 animate__animated animate__zoomIn animate__faster">
                                    <div class="card overflow-hidden h-100 border-0 shadow-sm rounded-[1rem] bg-white group hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                                        
                                        <div class="ratio ratio-4x3 bg-dark bg-opacity-10 position-relative">
                                            <img src="<?php echo $flyer; ?>?v=<?php echo time(); ?>" class="card-img-top object-fit-cover w-100 h-100 transition-transform duration-500 hover:scale-105" alt="Flyer <?php echo htmlspecialchars($nombre_archivo); ?>" loading="lazy">
                                            
                                            <!-- Overlay -->
                                            <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 opacity-0 hover:opacity-100 transition-opacity d-flex align-items-center justify-content-center p-3 text-center pointer-events-none" style="backdrop-filter: blur(2px);">
                                                <span class="text-white small fw-bold lh-sm pointer-events-none"><i class="fas fa-search-plus d-block mb-1 fs-3"></i>Previsualizando Aspecto Web.</span>
                                            </div>
                                        </div>

                                        <div class="card-body px-3 py-3 position-relative bg-white border-top">
                                            <div class="d-flex w-100 justify-content-between align-items-start gap-2">
                                                <div class="overflow-hidden">
                                                    <h6 class="card-title fw-bold text-dark text-truncate mb-1 fs-6 pb-1" title="<?php echo htmlspecialchars($nombre_archivo); ?>"><?php echo htmlspecialchars($nombre_archivo); ?></h6>
                                                    <div class="card-text text-muted small d-flex flex-wrap gap-2">
                                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25"><i class="fas fa-weight-hanging me-1"></i><?php echo $tamano; ?> KB</span>
                                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 pb-1"><i class="fas fa-check-circle me-1"></i>Público</span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <form action="../acciones/eliminar_flyer.php" method="POST" id="form-delete-<?php echo md5($nombre_archivo); ?>" onsubmit="mostrarPreloader()" class="position-absolute top-0 end-0 translate-middle pointer-events-auto" style="margin-top: -15px; margin-right: -15px;">
                                                <input type="hidden" name="archivo" value="<?php echo htmlspecialchars($nombre_archivo); ?>">
                                                <button type="button" onclick="confirmarEliminarFlyer('<?php echo htmlspecialchars($nombre_archivo); ?>', 'form-delete-<?php echo md5($nombre_archivo); ?>')" class="btn btn-danger btn-sm shadow-lg rounded-circle drop-shadow-lg icon-delete hover:scale-110 transition-transform d-flex align-items-center justify-content-center border-2 border-white" style="width: 42px; height: 42px;" title="Eliminar definitivamente este flyer">
                                                    <i class="fas fa-trash-alt fs-5"></i>
                                                </button>
                                            </form>
                                            
                                        </div>
                                    </div>
                                </div>
                                <?php } ?>
                            </div>
                        <?php } else { ?>
                            <!-- Empty State Responsivo y Premium -->
                            <div class="text-center py-5 my-4 px-3 w-100 h-100 d-flex flex-column align-items-center justify-content-center">
                                <div class="bg-white rounded-circle shadow-sm d-flex align-items-center justify-content-center p-4 mb-4 border border-secondary border-opacity-25" style="width: 120px; height: 120px;">
                                    <i class="fas fa-photo-video text-secondary opacity-50 pe-none" style="font-size: 3.5rem;"></i>
                                </div>
                                <h3 class="fw-black text-dark tracking-tight">Galería Publicitaria Vacía</h3>
                                <p class="text-muted max-w-lg mx-auto">Actualmente el sistema no posee flyers promocionales subidos al ecosistema y por lo tanto **el Modal de bienvenida general automático se mantiene oculto**. ¡Publica tu primer flyer aquí para habilitar los anuncios rotativos a nivel mundial!</p>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
    
    <style>
        .group-hover:hover .hover\:scale-110 { transform: scale(1.1); }
        .group-hover:hover .hover\:opacity-100 { opacity: 1 !important; }
        .pointer-events-none { pointer-events: none; }
        .pointer-events-auto { pointer-events: auto; }
        
        #btn-eliminar-floater {
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        #btn-eliminar-floater:hover {
            transform: scale(1.15) rotate(5deg);
        }
    </style>

    <script>
        // Funcionalidad DRAG AND DROP Visual & Previsualización
        const inputFileInputs = document.getElementById('flyer_img');
        const previewBlock = document.getElementById('preview-container');
        const dropZonaArea = document.getElementById('drop-zone');
        
        if(inputFileInputs) {
            inputFileInputs.addEventListener('change', function(e) {
                const archivoBruto = e.target.files[0];
                
                if (archivoBruto) {
                    // Validar MIME image general
                    if (!archivoBruto.type.match('image.*')) {
                        Swal.fire({icon: 'error', title: 'Operación Inválida', text: 'Solo debes subir fotografías. Formatos: JPG, JPEG, PNG, WEBP o GIF.', confirmButtonColor: '#f18000'});
                        this.value = '';
                        previewBlock.classList.add('d-none');
                        dropZonaArea.style.display = 'block';
                        return;
                    }
                    
                    // Límite de Peso Estricto PHP-like
                    const limitSize = 5 * 1024 * 1024; // 5 MB
                    if (archivoBruto.size > limitSize) {
                        Swal.fire({icon: 'warning', title: 'Archivo muy pesado', text: 'Optimizate. El flyer no debe sobrepasar 5 Megabytes (MB).', confirmButtonColor: '#f18000'});
                        this.value = '';
                        previewBlock.classList.add('d-none');
                        dropZonaArea.style.display = 'block';
                        return;
                    }
                    
                    // Todo salió perfecto -> Proceder a inyectar Previsualización nativa local en el navegador base64
                    const varReaderRenderHTMLAPI = new FileReader();
                    varReaderRenderHTMLAPI.onload = function(eventoLectura) {
                        document.getElementById('image-preview').src = eventoLectura.target.result;
                        document.getElementById('file-name').textContent = "Seleccionado: " + archivoBruto.name;
                        previewBlock.classList.remove('d-none');
                        dropZonaArea.style.display = 'none'; // ocultar caja subida
                    }
                    varReaderRenderHTMLAPI.readAsDataURL(archivoBruto);
                } else {
                    // Canceló o limpió input. Restaurar estado default
                    previewBlock.classList.add('d-none');
                    dropZonaArea.style.display = 'block';
                }
            });
        }

        function confirmarEliminarFlyer(nombreSeguro, formIdText) {
            Swal.fire({
                title: 'Eliminación Inminente',
                html: "<span class='text-secondary'>El sistema destruirá físicamente el banner '<strong>"+nombreSeguro+"</strong>' y lo removerá irrevocablemente de la publicidad web de Euripys.<br/><br/>¿Proceder con la purga?</span>",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-radiation-alt me-2"></i>Eliminar Definitivo',
                cancelButtonText: 'Cancelar Abortar'
            }).then((resp) => {
                if(resp.isConfirmed) {
                    showPreloader("Eliminando Flyer...");
                    document.getElementById(formIdText).submit(); // Ejecuto Action de post local Form
                }
            });
        }
    </script>
    
<?php require_once("../models/footer.php"); ?>
