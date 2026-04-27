<?php
require_once("../models/header.php");

// Verificación adicional de seguridad
$tipo_usuario = $_SESSION["tipo"] ?? '';
if ($tipo_usuario !== "admin" && $tipo_usuario !== "cont") {
    $_SESSION['estatus'] = 'error';
    $_SESSION['mensaje'] = "Acceso Restringido. Esta vista es exclusiva para el equipo contable o administrativo.";
    header("Location: inicio.php");
    exit;
}

// Obtener años disponibles de los pagos para llenar el selector
$anios_query = "SELECT DISTINCT YEAR(fecha_pago) as anio FROM pagos ORDER BY anio DESC";
$res_anios = mysqli_query($conexion, $anios_query);
$anios = [];
while ($row = mysqli_fetch_assoc($res_anios)) {
    if ($row['anio']) {
        $anios[] = $row['anio'];
    }
}
$anio_actual = date('Y');
if (!in_array($anio_actual, $anios)) {
    array_unshift($anios, $anio_actual);
}

// Obtener historial de cierres
$historial_query = "
    SELECT c.*, u.nombre AS usuario_nombre 
    FROM cierres_mensuales c 
    LEFT JOIN usuario u ON c.usuario_cierre_id = u.id_usuario 
    ORDER BY c.anio DESC, c.mes DESC";
$res_historial = mysqli_query($conexion, $historial_query);

$historial_datos = [];
$meses_cerrados = [];
if ($res_historial) {
    while ($h = mysqli_fetch_assoc($res_historial)) {
        $historial_datos[] = $h;
        if (!isset($meses_cerrados[$h['anio']])) {
            $meses_cerrados[$h['anio']] = [];
        }
        $meses_cerrados[$h['anio']][] = (int)$h['mes'];
    }
}

// Nombres de meses en español
$nombres_meses = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];
?>

<div id="layoutSidenav_content">
    <div class="container-fluid px-4 py-4">
        
        <header class="page-header-standard d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 animate__animated animate__fadeIn">
            <div>
                <h1 class="fw-bold mb-0 text-primary"><i class="fas fa-calendar-check me-2"></i>Cierre de Mes</h1>
                <p class="text-muted">Bloqueo de períodos contables y consolidación de operaciones</p>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 m-0">
                    <li class="breadcrumb-item"><a href="inicio.php" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active">Cierre de Mes</li>
                </ol>
            </nav>
        </header>

        <div class="row g-4 mb-4">
            <!-- Formulario de Nuevo Cierre -->
            <div class="col-xl-4 col-lg-5">
                <div class="card border-0 shadow-sm glass-premium h-100 animate__animated animate__fadeInLeft">
                    <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0">
                        <h5 class="fw-bold text-dark"><i class="fas fa-lock text-warning me-2"></i>Ejecutar Cierre</h5>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-4">Al cerrar un mes, los usuarios UPU no podrán registrar ingresos ni egresos en ese mes. Esta acción es irreversible.</p>
                        
                        <form id="formCierre">
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-dark">Año Operativo</label>
                                <select class="form-select border-0 shadow-sm" name="anio" id="anioCierre" required style="background-color: rgba(255,255,255,0.7);">
                                    <?php foreach ($anios as $a): ?>
                                        <option value="<?php echo $a; ?>"><?php echo $a; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold small text-dark">Mes a Cerrar</label>
                                <select class="form-select border-0 shadow-sm" name="mes" id="mesCierre" required style="background-color: rgba(255,255,255,0.7);">
                                    <?php foreach ($nombres_meses as $num => $nombre): ?>
                                        <option value="<?php echo $num; ?>"><?php echo $nombre; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div id="checkPendientes" class="mb-4">
                                <!-- Se llenará dinámicamente -->
                            </div>
                            
                            <button type="submit" id="btnSellar" class="btn btn-warning w-100 fw-bold shadow-sm rounded-pill text-dark">
                                <i class="fas fa-key me-2"></i> Sellar Mes Definitivamente
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tabla de Cierres -->
            <div class="col-xl-8 col-lg-7">
                <div class="card border-0 shadow-sm glass-premium animate__animated animate__fadeInRight">
                    <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0">
                        <h5 class="fw-bold text-dark"><i class="fas fa-history text-primary me-2"></i>Historial de Cierres</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle border-light" id="tablaCierres">
                                <thead class="table-light text-muted small fw-semibold">
                                    <tr>
                                        <th>PERÍODO</th>
                                        <th>ESTADO</th>
                                        <th>FECHA DE CIERRE</th>
                                        <th>EJECUTADO POR</th>
                                        <th class="text-end">ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody class="border-top-0">
                                    <?php if (count($historial_datos) > 0): ?>
                                        <?php foreach ($historial_datos as $idx => $h): ?>
                                        <tr>
                                            <td class="fw-bold text-dark">
                                                <i class="far fa-calendar-alt text-primary me-2"></i>
                                                <?php echo $nombres_meses[$h['mes']] . ' ' . $h['anio']; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-3 py-1 rounded-pill">
                                                    <i class="fas fa-lock me-1"></i> Cerrado
                                                </span>
                                            </td>
                                            <td class="small text-muted">
                                                <?php echo date('d/m/Y h:i A', strtotime($h['fecha_cierre'])); ?>
                                            </td>
                                            <td class="small fw-semibold">
                                                <i class="fas fa-user-tie text-secondary me-1"></i>
                                                <?php echo htmlspecialchars($h['usuario_nombre'] ?? 'Sistema'); ?>
                                            </td>
                                            <td class="text-end text-nowrap">
                                                <button type="button" onclick="showPremiumReport('../dompdf/exportar_pdf_cierre.php?id=<?php echo $h['id']; ?>', 'Resumen Mensual de Cierre')" class="btn btn-sm btn-outline-primary shadow-sm rounded-pill font-monospace me-2">
                                                    <i class="fas fa-file-pdf me-1"></i> Resumen
                                                </button>
                                                <?php if ($idx < 2): ?>
                                                    <button class="btn btn-sm btn-outline-danger shadow-sm rounded-pill font-monospace" onclick="reabrirMes(<?php echo $h['id']; ?>, '<?php echo $nombres_meses[$h['mes']] . ' ' . $h['anio']; ?>')">
                                                        <i class="fas fa-lock-open me-1"></i> Re-abrir
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-outline-secondary opacity-50 shadow-sm rounded-pill font-monospace disabled" disabled title="Período blindado">
                                                        <i class="fas fa-shield-alt me-1"></i> Blindado
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">No hay periodos cerrados registrados.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        $(document).ready(function() {
            const cierresData = <?php echo json_encode($meses_cerrados); ?>;
            
            function actualizarMesesDisponibles() {
                const anioSeleccionado = $('#anioCierre').val();
                const mesesCerrados = cierresData[anioSeleccionado] || [];
                
                $('#mesCierre option').each(function() {
                    const mesValue = parseInt($(this).val());
                    if (mesesCerrados.includes(mesValue)) {
                        $(this).prop('disabled', true).addClass('d-none');
                    } else {
                        $(this).prop('disabled', false).removeClass('d-none');
                    }
                });
                
                if ($('#mesCierre option:selected').prop('disabled')) {
                    const firstAvailable = $('#mesCierre option:not(:disabled)').first().val();
                    if (firstAvailable) {
                        $('#mesCierre').val(firstAvailable).trigger('change');
                    } else {
                        $('#mesCierre').val('').trigger('change');
                    }
                }
            }
            
            function checkPendientes() {
                const anio = $('#anioCierre').val();
                const mes = $('#mesCierre').val();
                if (!anio || !mes) return;

                $.getJSON('../acciones/verificar_pendientes_cierre.php', { mes, anio }, function(data) {
                    let html = '';
                    if (data.status === 'success') {
                        if (data.total > 0) {
                            html = `<div class="alert alert-danger bg-opacity-10 border-danger d-flex align-items-center p-3 rounded-4">
                                <i class="fas fa-exclamation-circle fa-2x me-3 text-danger"></i>
                                <div>
                                    <div class="fw-bold text-danger">Acción Bloqueada</div>
                                    <div class="small text-danger opacity-75">Hay ${data.total} transacciones (Pagos/Egresos) pendientes por validar en este periodo.</div>
                                </div>
                            </div>`;
                            $('#btnSellar').prop('disabled', true).addClass('opacity-50');
                        } else {
                            html = `<div class="alert alert-success bg-opacity-10 border-success d-flex align-items-center p-3 rounded-4">
                                <i class="fas fa-shield-alt fa-2x me-3 text-success"></i>
                                <div>
                                    <div class="fw-bold text-success">Escudo Contable Activo</div>
                                    <div class="small text-success opacity-75">Periodo limpio. Listo para ser sellado.</div>
                                </div>
                            </div>`;
                            $('#btnSellar').prop('disabled', false).removeClass('opacity-50');
                        }
                    }
                    $('#checkPendientes').html(html);
                });
            }
            
            $('#anioCierre, #mesCierre').on('change', function() {
                actualizarMesesDisponibles();
                checkPendientes();
            });
            actualizarMesesDisponibles();
            checkPendientes();

            // Setup datatable if we have records
            if ($('#tablaCierres tbody tr td').length > 1) {
                $('#tablaCierres').DataTable({
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"
                    },
                    "order": [[ 2, "desc" ]],
                    "pageLength": 10,
                    "columnDefs": [ { "orderable": false, "targets": 4 } ]
                });
            }

            // Window reabrir function
            window.reabrirMes = function(id, text) {
                Swal.fire({
                    title: '¿Reabrir este mes?',
                    html: `Estás a punto de reabrir el período <b class="text-danger">${text}</b>.<br><br>Esto permitirá nuevamente la carga de pagos de este mes para todas las UPU.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-lock-open me-1"></i> Sí, Reabrir Mes',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '../acciones/reabrir_cierre.php',
                            type: 'POST',
                            data: { id: id },
                            dataType: 'json',
                            success: function(response) {
                                if (response.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Mes Reabierto',
                                        text: response.message,
                                        timer: 2000,
                                        showConfirmButton: false
                                    }).then(() => { window.location.reload(); });
                                } else {
                                    Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                                }
                            },
                            error: function() {
                                Swal.fire('Error', 'Hubo un problema de conexión al servidor.', 'error');
                            }
                        });
                    }
                });
            };

            $('#formCierre').on('submit', function(e) {
                e.preventDefault();
                
                const anio = $('#anioCierre').val();
                let mesText = $("#mesCierre option:selected").text();
                
                Swal.fire({
                    title: '¿Estás completamente seguro?',
                    html: `Vas a cerrar el mes de <b class="text-primary">${mesText} ${anio}</b>.<br><br><b>Esto bloqueará todos los movimientos contables de este mes para TODAS las UPU.</b>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ea580c',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-lock me-1"></i> Sí, Cerrar Mes',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        
                        // Enviar AJAX
                        $.ajax({
                            url: '../acciones/procesar_cierre.php',
                            type: 'POST',
                            data: $(this).serialize(),
                            dataType: 'json',
                            success: function(response) {
                                if (response.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Mes Cerrado',
                                        text: response.message,
                                        timer: 2000,
                                        showConfirmButton: false
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: response.message
                                    });
                                }
                            },
                            error: function() {
                                Swal.fire('Error de Conexión', 'Hubo un problema de red. Por favor intenta de nuevo.', 'error');
                            }
                        });
                        
                    }
                });
            });
        });
    </script>
    
<?php
require_once("../models/footer.php");
?>
