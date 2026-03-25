<?php
require_once("../models/header.php");

// Ruta de la carpeta donde se guardan los comprobantes
$rutaCarpeta = "../comprobantes/";
// Obtener los archivos de la carpeta (excluyendo '.' y '..')
$archivos = array_diff(scandir($rutaCarpeta), array('.', '..')); 
?>

<<style>
    :root {
        --excel-green: #198754;
        --excel-green-light: #2da44e;
        --glass-bg: rgba(255, 255, 255, 0.9);
        --glass-border: rgba(255, 255, 255, 0.4);
    }

    [data-theme="dark"] {
        --glass-bg: rgba(30, 41, 59, 0.8);
        --glass-border: rgba(255, 255, 255, 0.1);
        --table-header-bg: #111827;
        --table-header-text: #94a3b8;
        --table-border: rgba(255, 255, 255, 0.05);
    }

    .page-title-icon { 
        color: var(--excel-green);
        filter: drop-shadow(0 0 8px rgba(25, 135, 84, 0.3));
    }

    .glass-card {
        background: var(--glass-bg);
        backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border);
        border-radius: 1.25rem;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        transition: all 0.3s ease;
        color: var(--text-main);
    }

    .card-header-main { 
        background: linear-gradient(135deg, var(--excel-green) 0%, var(--excel-green-light) 100%);
        color: white; 
        font-weight: 700; 
        padding: 1.25rem 1.5rem;
        border: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .table {
        margin-bottom: 0;
    }

    .table thead th {
        background: var(--table-header-bg, #f8fafc);
        color: var(--table-header-text, #475569);
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        padding: 1rem;
        border-bottom: 2px solid var(--table-border, #e2e8f0);
    }

    .table tbody td {
        padding: 1rem;
        vertical-align: middle;
        color: var(--text-main);
        border-bottom: 1px solid var(--table-border, #f1f5f9);
        transition: all 0.2s ease;
    }

    .table tbody tr:hover td {
        background-color: rgba(25, 135, 84, 0.02);
    }

    .file-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .excel-icon-wrapper {
        width: 36px;
        height: 36px;
        background: rgba(25, 135, 84, 0.1);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--excel-green);
        font-size: 1.1rem;
    }

    .btn-premium {
        border-radius: 0.75rem;
        padding: 0.5rem 1rem;
        font-weight: 600;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border: none;
    }

    .btn-premium:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .btn-download { background: #ecfdf5; color: #065f46; }
    .btn-download:hover { background: #d1fae5; color: #065f46; }

    [data-theme="dark"] .btn-download { background: rgba(16, 185, 129, 0.1); color: #4ade80; }
    [data-theme="dark"] .btn-download:hover { background: rgba(16, 185, 129, 0.2); }

    .btn-delete { background: #fef2f2; color: #991b1b; }
    .btn-delete:hover { background: #fee2e2; color: #991b1b; }

    [data-theme="dark"] .btn-delete { background: rgba(239, 68, 68, 0.1); color: #f87171; }
    [data-theme="dark"] .btn-delete:hover { background: rgba(239, 68, 68, 0.2); }

    .btn-edit { background: #eff6ff; color: #1e40af; }
    .btn-edit:hover { background: #dbeafe; color: #1e40af; }

    [data-theme="dark"] .btn-edit { background: rgba(59, 130, 246, 0.1); color: #60a5fa; }
    [data-theme="dark"] .btn-edit:hover { background: rgba(59, 130, 246, 0.2); }

    .breadcrumb-premium {
        background: white;
        padding: 1rem 1.5rem;
        border-radius: 1rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
    }

    .stat-badge {
        background: rgba(255, 255, 255, 0.2);
        padding: 0.5rem 1rem;
        border-radius: 50rem;
        font-size: 0.85rem;
        backdrop-filter: blur(4px);
    }
</style>

<div id="layoutSidenav_content">
    <div class="container-fluid px-4 py-4">
        
        <header class="page-header-standard d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
            <div>
                <h1 class="fw-bold mb-0 text-primary"><i class="fas fa-file-excel me-2"></i>Comprobantes de Egreso</h1>
                <p class="text-muted">Gestión y administración de archivos de comprobantes generados</p>
            </div>
            <button id="btnEliminarSeleccionados" class="btn btn-danger btn-premium d-none">
                <i class="fas fa-trash-alt"></i> Eliminar Selección (<span id="contadorSeleccionados">0</span>)
            </button>
            <nav aria-label="breadcrumb" class="d-none d-lg-block">
                <ol class="breadcrumb bg-transparent p-0 m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigateTo('inicio.php')" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active">Comprobantes</li>
                </ol>
            </nav>
        </header>
        

        <div class="card glass-card border-0">
            <div class="card-header card-header-main">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-table"></i> 
                    <span>Lista de Comprobantes</span>
                </div>
                <div class="stat-badge fw-bold">
                    <?php echo count($archivos); ?> Archivos totales
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="datatablesSimple" class="table align-middle">
                        <thead>
                            <tr>
                                <th style="width: 50px;" class="text-center">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>Nombre del Comprobante</th>
                                <th class="text-end" style="padding-right: 2rem;">Acciones Disponibles</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($archivos) > 0): ?>
                                <?php foreach ($archivos as $archivo): ?>
                                    <?php 
                                    $archivoUrl = str_replace('+', '%20', urlencode($archivo)); 
                                    $nombreSeguro = htmlspecialchars($archivo, ENT_QUOTES);
                                    ?>
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" class="form-check-input select-item" value="<?php echo $nombreSeguro; ?>">
                                        </td>
                                        <td>
                                            <div class="file-row">
                                                <div class="excel-icon-wrapper">
                                                    <i class="fas fa-file-csv"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold mb-0"><?php echo $nombreSeguro; ?></div>
                                                    <small class="text-muted">Formato Excel .xlsm</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end" style="padding-right: 1.5rem;">
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="editar_comprobante.php?archivo=<?php echo $archivoUrl; ?>" 
                                                   class="btn btn-premium btn-edit" title="Editar datos">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <a href="<?php echo $rutaCarpeta . $archivoUrl; ?>" 
                                                   class="btn btn-premium btn-download" 
                                                   download="<?php echo $nombreSeguro; ?>" title="Descargar">
                                                    <i class="fas fa-download"></i>
                                                </a>

                                                <button class="btn btn-premium btn-delete"  
                                                        onclick="confirmarEliminacion('<?php echo $archivoUrl; ?>', '<?php echo $nombreSeguro; ?>')"
                                                        title="Eliminar archivo">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const selectAllBtn = document.getElementById("selectAll");
            const btnEliminarSeleccionados = document.getElementById("btnEliminarSeleccionados");
            const contadorSeleccionados = document.getElementById("contadorSeleccionados");

            // Función para actualizar el botón de eliminar múltiple
            function actualizarBotonEliminar() {
                // Buscamos todos los checkboxes seleccionados en todo el DOM (incluso los de otras páginas si Datatables los mantiene, o al menos los visibles)
                const seleccionados = document.querySelectorAll(".select-item:checked").length;
                contadorSeleccionados.textContent = seleccionados;

                if (seleccionados > 0) {
                    btnEliminarSeleccionados.classList.remove("d-none");
                } else {
                    btnEliminarSeleccionados.classList.add("d-none");
                }
            }

            // Checkbox "Seleccionar Todos" (Debe delegarse por si Datatables recrea la cabecera, o escucharlo directamente si es estático)
            if (selectAllBtn) {
                selectAllBtn.addEventListener("change", function () {
                    // Seleccionar/Deseleccionar todos los checkboxes que tengan la clase .select-item
                    document.querySelectorAll(".select-item").forEach(item => {
                        item.checked = selectAllBtn.checked;
                    });
                    actualizarBotonEliminar();
                });
            }

            // Delegación de eventos para los checkboxes individuales (necesario por DataTables)
            document.body.addEventListener("change", function (e) {
                if (e.target && e.target.classList.contains("select-item")) {
                    // Verificar si están todos seleccionados para marcar el checkbox general
                    const totalItems = document.querySelectorAll(".select-item").length;
                    const seleccionados = document.querySelectorAll(".select-item:checked").length;
                    
                    if (selectAllBtn) {
                        selectAllBtn.checked = (totalItems > 0 && totalItems === seleccionados);
                    }
                    
                    actualizarBotonEliminar();
                }
            });

            // Botón "Eliminar Seleccionados"
            if (btnEliminarSeleccionados) {
                btnEliminarSeleccionados.addEventListener("click", function () {
                    const archivosSeleccionados = [];
                    document.querySelectorAll(".select-item:checked").forEach(item => {
                        archivosSeleccionados.push(item.value);
                    });

                    if (archivosSeleccionados.length === 0) return;

                    Swal.fire({
                        title: '¿Está seguro?',
                        html: `Desea eliminar <strong>${archivosSeleccionados.length}</strong> archivo(s) seleccionado(s). Esta acción no se puede deshacer.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: '<i class="fas fa-trash-alt"></i> Sí, Eliminar',
                        cancelButtonText: '<i class="fas fa-times"></i> Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Mostrar loader (opcional)
                            Swal.fire({
                                title: 'Eliminando...',
                                text: 'Por favor, espere.',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading()
                                }
                            });

                            // Enviar datos por AJAX al backend
                            fetch('../acciones/eliminar_comprobantes_bulk.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({ archivos: archivosSeleccionados })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire(
                                        '¡Eliminados!',
                                        data.message,
                                        'success'
                                    ).then(() => {
                                        window.location.reload(); // Recargar la página para ver los cambios
                                    });
                                } else {
                                    Swal.fire(
                                        'Error',
                                        data.message || 'Ocurrió un error al eliminar los archivos.',
                                        'error'
                                    );
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire(
                                    'Error',
                                    'Hubo un problema de conexión con el servidor.',
                                    'error'
                                );
                            });
                        }
                    });
                });
            }
        });

        function confirmarEliminacion(archivoUrl, nombreArchivo) {
            Swal.fire({
                title: '¿Está seguro?',
                html: `Desea eliminar el archivo: <br><strong>${nombreArchivo}</strong>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash-alt"></i> Sí, Eliminar',
                cancelButtonText: '<i class="fas fa-times"></i> Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Eliminando...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading() }
                    });

                    // Reutilizar la misma API para la eliminación individual
                    fetch('../acciones/eliminar_comprobantes_bulk.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ archivos: [nombreArchivo] }) // Enviar como array de 1 elemento
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('¡Eliminado!', `El archivo ${nombreArchivo} ha sido eliminado.`, 'success')
                                .then(() => window.location.reload());
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    })
                    .catch(err => Swal.fire('Error', 'Problema de red.', 'error'));
                }
            });
        }
    </script>
</div>
<?php
require_once("../models/footer.php");
?>