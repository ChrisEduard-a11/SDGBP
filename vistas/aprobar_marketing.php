<?php
include_once("../models/header.php");
require_once("../conexion.php");

// Verificar si el usuario tiene permisos de administración
if ($_SESSION["tipo"] != "cont" && $_SESSION["tipo"] != "admin") {
    echo "<script>window.location.href='inicio.php';</script>";
    exit();
}

$sql = "SELECT * FROM pagos_productos WHERE estado = 'Pendiente' ORDER BY fecha_pago ASC, id ASC";
$result = $conexion->query($sql);

if (!$result) {
    die("<div class='container mt-5'><div class='alert alert-danger'>
        <h4><i class='fas fa-exclamation-triangle'></i> Error de Base de Datos</h4>
        <p>No se pudo consultar la tabla `pagos_productos`. Es muy probable que falten columnas o la tabla no exista en el servidor remoto.</p>
        <p><strong>Detalle:</strong> " . $conexion->error . "</p>
        <hr>
        <p class='mb-0'>Por favor, ejecuta el script de migración SQL para actualizar la estructura de la base de datos.</p>
    </div></div>");
}

$metricSql = "SELECT COUNT(*) as pending_count, SUM(monto) as pending_usd FROM pagos_productos WHERE estado = 'Pendiente'";
$metricRes = $conexion->query($metricSql);
$metrics = ($metricRes) ? $metricRes->fetch_assoc() : ['pending_count' => 0, 'pending_usd' => 0];
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
    [data-theme="dark"] {
        --glass-bg: rgba(30, 41, 59, 0.7);
        --glass-border: rgba(255, 255, 255, 0.1);
    }

    body {
        font-family: 'Inter', sans-serif;
    }

    .glass-card {
        background: var(--glass-bg, rgba(255, 255, 255, 0.9));
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border, rgba(0, 0, 0, 0.1));
        border-radius: 1.5rem;
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
    }
    
    .metric-card {
        border: none;
        border-radius: 1.25rem;
        color: white;
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .metric-card:hover {
        transform: translateY(-8px);
    }
    .bg-gradient-warning { background: linear-gradient(135deg, #f6d365 0%, #fda085 100%); }
    
    .custom-table th {
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        white-space: nowrap;
    }
    .custom-table td {
        vertical-align: middle;
        white-space: nowrap;
    }
</style>

<div id="layoutSidenav_content">
    
    <?php if (isset($_SESSION['estatus']) && isset($_SESSION['mensaje'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: '<?php echo $_SESSION['estatus']; ?>',
                        title: '<?php echo $_SESSION['estatus'] === 'success' ? '¡Operación Exitosa!' : 'Alerta'; ?>',
                        text: '<?php echo htmlspecialchars($_SESSION['mensaje'], ENT_QUOTES); ?>',
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#8b5cf6',
                        customClass: { popup: 'rounded-4 shadow-lg' }
                    });
                }
            });
        </script>
        <?php unset($_SESSION['estatus'], $_SESSION['mensaje']); ?>
    <?php endif; ?>

    <div class="container-fluid px-4 py-4">
        
        <header class="page-header-standard d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
            <div>
                <h1 class="fw-bold mb-0 text-success"><i class="fas fa-shopping-cart me-2"></i>Aprobar Ventas Store</h1>
                <p class="text-muted">Aprobación de pagos y validación de despachos del catálogo de marketing</p>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigateTo('inicio.php')" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active">Aprobación de Ventas</li>
                </ol>
            </nav>
        </header>

        <div class="row g-4 mb-5 animate__animated animate__fadeInUp">
            <div class="col-xl-6 col-md-12">
                <div class="card metric-card bg-gradient-warning shadow-lg p-4">
                    <div class="card-body p-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-2 opacity-75 fw-bold text-dark">Ventas por Procesar</h6>
                            <h2 class="display-6 fw-bold mb-0 text-dark">
                                <?php echo $metrics['pending_count'] ?? 0; ?> <small class="fs-4">Órdenes</small>
                            </h2>
                        </div>
                        <i class="fas fa-boxes fa-3x text-dark opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-md-12">
                <div class="card metric-card bg-primary shadow-lg p-4">
                    <div class="card-body p-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-2 opacity-75 fw-bold">Liquidez Potencial</h6>
                            <h2 class="display-6 fw-bold mb-0">
                                $<?php echo number_format($metrics['pending_usd'] ?? 0, 2); ?>
                            </h2>
                        </div>
                        <i class="fas fa-dollar-sign fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="card glass-card border-0 animate__animated animate__fadeInUp" style="animation-delay: 0.1s;">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h4 class="fw-bold mb-0 text-dark dark:text-white"><i class="fas fa-clipboard-check me-2 text-warning"></i> Solicitudes de Compra Pendientes</h4>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive" style="min-height: 400px;">
                    <table id="datatablesSimple" class="table table-hover custom-table w-100 align-middle">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th>#ID</th>
                                <th>Comprador</th>
                                <th>Reporte Pago</th>
                                <th>Fecha</th>
                                <th>Contacto</th>
                                <th class="text-center">Carrito</th>
                                <th class="text-center">Decisión</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): 
                                    $carritoData = json_encode($row['carrito_json'] ? json_decode($row['carrito_json'], true) : []);
                                    // Escapar data para HTML
                                    $carritoDataEscaped = htmlspecialchars($carritoData, ENT_QUOTES, 'UTF-8');
                                ?>
                                    <tr>
                                        <td><span class="badge bg-secondary">V-<?php echo $row['id']; ?></span></td>
                                        <td>
                                            <div class="fw-bold text-primary"><?php echo htmlspecialchars($row['nombre_comprador']); ?></div>
                                            <div class="small text-muted"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($row['correo_comprador']); ?></div>
                                            <div class="small text-muted">CI/Doc: <?php echo htmlspecialchars($row['cedula']); ?></div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column gap-1 align-items-start">
                                                <span class="badge bg-success text-white border border-success px-2 py-1 fs-6 shadow-sm">
                                                    <i class="fas fa-money-bill-wave me-1"></i>Bs <?php echo number_format($row['monto_bs_pago'], 2, ',', '.'); ?>
                                                </span>
                                                <span class="badge bg-primary text-white border border-primary px-2 py-1 shadow-sm">
                                                    <i class="fas fa-dollar-sign me-1"></i>USD <?php echo number_format($row['monto'], 2, '.', ','); ?>
                                                </span>
                                            </div>
                                            <div class="mt-2">
                                                <span class="small fw-semibold text-dark"><i class="fas fa-university me-1 text-secondary"></i><?php echo htmlspecialchars($row['banco'] . ' (' . $row['metodo_pago'] . ')'); ?></span>
                                                <br>
                                                <span class="small text-muted fw-bold">REF: <?php echo htmlspecialchars($row['referencia']); ?></span>
                                            </div>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($row['fecha_pago'])); ?></td>
                                        <td>
                                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $row['telefono']); ?>" target="_blank" class="text-success text-decoration-none fw-bold">
                                                <i class="fab fa-whatsapp fs-5"></i> Chat
                                            </a>
                                            <div class="small text-muted"><?php echo htmlspecialchars($row['telefono']); ?></div>
                                        </td>
                                        <td class="text-center">
                                            <button onclick="verCarrito('<?php echo $row['id']; ?>', '<?php echo $carritoDataEscaped; ?>', '<?php echo number_format($row['monto_bs_pago'], 2, ',', '.'); ?>')" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-bold shadow-sm">
                                                <i class="fas fa-box-open me-1"></i> Ver Productos
                                            </button>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <form action="../acciones/aprobar_marketing_pago.php" method="POST" id="form-aprobar-<?php echo $row['id']; ?>">
                                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                    <input type="hidden" name="accion" value="aprobar">
                                                    <button type="button" class="btn btn-success rounded-circle shadow-sm" style="width: 40px; height:40px;" onclick="confirmarAprobacion('<?php echo $row['id']; ?>')" title="Aprobar y Descontar Stock">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>

                                                <form action="../acciones/aprobar_marketing_pago.php" method="POST" id="form-rechazar-<?php echo $row['id']; ?>">
                                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                    <input type="hidden" name="accion" value="rechazar">
                                                    <button type="button" class="btn btn-danger rounded-circle shadow-sm" style="width: 40px; height:40px;" onclick="confirmarRechazoMarketing('<?php echo $row['id']; ?>')" title="Rechazar y Cerrar">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function verCarrito(id, jsonString, montoBs) {
            try {
                let carrito = JSON.parse(jsonString);
                let html = '<div class="table-responsive"><table class="table table-sm table-bordered mt-3 text-start align-middle">';
                html += '<thead class="bg-light"><tr><th>Producto</th><th class="text-center">Cant.</th><th class="text-end">Subtotal</th></tr></thead><tbody>';
                
                if (carrito.length === 0) {
                    html += '<tr><td colspan="3" class="text-center text-muted p-4"><i class="fas fa-box-open fa-2x mb-2 d-block"></i>No hay detalles del carrito disponibles (ventas antiguas).</td></tr>';
                } else {
                    let total = 0;
                    carrito.forEach(item => {
                        let sub = parseFloat(item.precio) * parseInt(item.cantidad);
                        total += sub;
                        html += `<tr>
                                    <td class="fw-bold text-dark border-bottom"><i class="fas fa-tag text-primary me-2"></i>${item.nombre}</td>
                                    <td class="text-center border-bottom fw-bold">${item.cantidad}</td>
                                    <td class="text-end border-bottom">$${sub.toFixed(2)}</td>
                                 </tr>`;
                    });
                    html += `<tr class="table-light border-0"><td colspan="2" class="text-end fw-bold text-muted border-0 pt-3">Subtotal Mercancía:</td><td class="text-end fw-bold text-dark border-0 pt-3">$${total.toFixed(2)} USD</td></tr>`;
                    html += `<tr class="table-success border-success"><td colspan="2" class="text-end fw-black fs-5 border-success align-middle"><i class="fas fa-university me-2 text-success"></i>Pago Declarado en Banco:</td><td class="text-end fw-black text-success fs-4 border-success">Bs ${montoBs}</td></tr>`;
                }
                html += '</tbody></table></div>';
                
                Swal.fire({
                    title: '<span class="fw-bold text-dark">Ticket de Despacho <span class="text-primary">#V-' + id + '</span></span>',
                    html: html,
                    width: 600,
                    showCloseButton: true,
                    showConfirmButton: false
                });
            } catch (e) {
                Swal.fire('Error', 'El detalle del carrito está corrupto o formato no válido.', 'error');
            }
        }

        function confirmarAprobacion(id) {
            Swal.fire({
                title: '¿Confirmar Envío y Aprobar Pago?',
                text: "Esta acción marcará el pago como Aprobado y realizará automáticamente la reducción matemática del STOCK en inventario. Asegúrate de haber verificado la cuenta de banco.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-check-circle me-1"></i> Sí, Aprobar Venta',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({title: 'Auditando Stock...', didOpen: () => {Swal.showLoading()}});
                    document.getElementById('form-aprobar-' + id).submit();
                }
            })
        }

        function confirmarRechazoMarketing(id) {
            Swal.fire({
                title: '¿Rechazar esta orden?',
                text: "El pago será rechazado. El stock físico del sistema no será alterado en absoluto.",
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Rechazar Permanentemente',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('form-rechazar-' + id).submit();
                }
            })
        }
    </script>

<?php require_once("../models/footer.php"); ?>
