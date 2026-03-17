<?php
require_once("../models/header.php");
require_once("../conexion.php");

// Mostrar el saldo del usuario logueado
$saldo_usuario = 0;
if (isset($_SESSION['id'])) {
    $usuario_id = $_SESSION['id'];
    $sqlSaldo = "SELECT saldo FROM usuario WHERE id_usuario = ?";
    $stmtSaldo = $conexion->prepare($sqlSaldo);
    $stmtSaldo->bind_param("i", $usuario_id);
    $stmtSaldo->execute();
    $stmtSaldo->bind_result($saldo_usuario);
    $stmtSaldo->fetch();
    $stmtSaldo->close();
}
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --glass-bg: rgba(255, 255, 255, 0.7);
        --glass-border: rgba(255, 255, 255, 0.3);
        --glass-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.1);
        --accent-success: #2ec4b6;
        --text-main: #1e293b;
        --text-muted: #64748b;
    }

    [data-theme="dark"] {
        --glass-bg: rgba(30, 41, 59, 0.7);
        --glass-border: rgba(255, 255, 255, 0.1);
        --glass-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
        --text-main: #f8fafc;
        --text-muted: #94a3b8;
    }

    body { font-family: 'Inter', sans-serif; }

    #layoutSidenav_content { background: transparent; }

    /* Glassmorphism Containers */
    .glass-card {
        background: var(--glass-bg);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border);
        border-radius: 24px;
        box-shadow: var(--glass-shadow);
    }

    .form-section-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .form-section-title i { color: var(--accent-success); }

    .input-group-text {
        background: transparent;
        border-color: var(--glass-border);
        color: var(--text-muted);
    }
    .form-control, .form-select {
        background: rgba(255, 255, 255, 0.05);
        border-color: var(--glass-border);
        color: var(--text-main);
        border-radius: 12px;
    }
    [data-theme="dark"] .form-control, [data-theme="dark"] .form-select {
        background: rgba(0, 0, 0, 0.2);
    }
    .form-control:focus, .form-select:focus {
        background: rgba(255, 255, 255, 0.1);
        border-color: var(--accent-success);
        box-shadow: 0 0 0 4px rgba(46, 196, 182, 0.1);
        color: var(--text-main);
    }

    /* Saldo Badge Moderno */
    .saldo-badge {
        background: linear-gradient(135deg, #2ec4b6 0%, #0891b2 100%);
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 50px;
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        box-shadow: 0 4px 15px rgba(46, 196, 182, 0.2);
    }

    /* Animations */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-up { animation: fadeInUp 0.5s ease forwards; }

    .btn-register {
        background: linear-gradient(135deg, #2ec4b6 0%, #2193b0 100%);
        border: none;
        color: white;
        font-weight: 700;
        border-radius: 15px;
        padding: 1rem;
        transition: all 0.3s ease;
    }
    .btn-register:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(46, 196, 182, 0.3);
        color: white;
    }
</style>

<div id="layoutSidenav_content">
    <div class="container-fluid px-4 py-4">
        
        <header class="d-flex justify-content-between align-items-center mb-4 animate-up">
            <div>
                <h1 class="fw-bold mb-0">Registrar Ingreso</h1>
                <p class="text-muted small">Reporta tus pagos recibidos para su verificación</p>
            </div>
            <div class="breadcrumb-container d-none d-md-block">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigateTo('inicio.php')" class="text-decoration-none text-muted"><i class="fas fa-home me-1"></i> Inicio</a></li>
                    <li class="breadcrumb-item active fw-bold text-success">Nuevo Ingreso</li>
                </ol>
            </div>
        </header>

        <div class="row g-4">
            <!-- Sidebar Info -->
            <div class="col-lg-4 animate-up" style="animation-delay: 0.1s;">
                <div class="glass-card p-4 mb-4">
                    <div class="form-section-title">
                        <i class="fas fa-wallet"></i> Balance Actual
                    </div>
                    <div class="saldo-badge w-100">
                        <div class="bg-white bg-opacity-20 p-2 rounded-circle">
                            <i class="fas fa-university fs-5"></i>
                        </div>
                        <div>
                            <div class="small opacity-80">Saldo disponible</div>
                            <div class="fw-bold fs-4">Bs. <?php echo number_format($saldo_usuario, 2, ',', '.'); ?></div>
                        </div>
                    </div>
                </div>

                <div class="glass-card p-4 border-start border-4 border-success">
                    <div class="form-section-title">
                        <i class="fas fa-info-circle text-success"></i> Instrucciones
                    </div>
                    <ul class="small text-muted ps-3 mb-0">
                        <li class="mb-2">Asegúrate de que la <b>referencia</b> sea exacta (últimos 6 dígitos).</li>
                        <li class="mb-2">El comprobante debe ser legible si decides adjuntarlo.</li>
                        <li class="mb-0">Verifica el <b>monto</b> antes de confirmar la operación.</li>
                    </ul>
                </div>
            </div>

            <!-- Main Form -->
            <div class="col-lg-8 animate-up" style="animation-delay: 0.2s;">
                <div class="glass-card p-4 p-md-5">
                    <form method="post" action="../acciones/controlador_pago.php" onsubmit="return validateFormRegistroP()" enctype="multipart/form-data">
                        
                        <div class="form-section-title">
                            <i class="fas fa-receipt"></i> Detalles de la Transacción
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label class="small fw-bold mb-2">Usuario Asociado</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <?php if ($_SESSION["tipo"] == "admin") { ?>
                                        <select class="form-select" id="nombre_cliente" name="nombre_cliente" onchange="actualizarUsuarioId(this)" required>
                                            <option value="">Seleccione una UPU</option>
                                            <?php
                                            $sql = "SELECT id_usuario, nombre FROM usuario WHERE tipos = 'upu' AND aprobado = 1";
                                            $result = $conexion->query($sql);
                                            while ($row = $result->fetch_assoc()) {
                                                echo "<option value='" . $row['nombre'] . "' data-id='" . $row['id_usuario'] . "'>" . $row['nombre'] . "</option>";
                                            }
                                            ?>
                                        </select>
                                        <input type="hidden" id="usuario_id" name="usuario_id" value="">
                                        <script>
                                            function actualizarUsuarioId(select) {
                                                const selectedOption = select.options[select.selectedIndex];
                                                const usuarioId = selectedOption.getAttribute('data-id');
                                                document.getElementById('usuario_id').value = usuarioId;
                                            }
                                        </script>
                                    <?php } else { ?>
                                        <input type="hidden" id="usuario_id" name="usuario_id" value="<?php echo $_SESSION['id']; ?>">
                                        <input type="text" class="form-control" value="<?php echo $_SESSION['nombre']; ?>" readonly>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="small fw-bold mb-2">Cliente / Proveedor</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-building"></i></span>
                                    <select class="form-select" id="cliente" name="cliente">
                                        <option value="">Seleccione cliente...</option>
                                        <?php
                                        $sqlClientes = "SELECT DISTINCT c.id_cliente, c.nombre FROM cliente c INNER JOIN usuario_pagos up ON c.id_cliente = up.cliente_id WHERE up.usuario_id = ?";
                                        $stmtClientes = $conexion->prepare($sqlClientes);
                                        $stmtClientes->bind_param("i", $_SESSION['id']);
                                        $stmtClientes->execute();
                                        $resultClientes = $stmtClientes->get_result();
                                        while ($rowCliente = $resultClientes->fetch_assoc()) {
                                            echo "<option value='" . $rowCliente['id_cliente'] . "'>" . $rowCliente['nombre'] . "</option>";
                                        }
                                        $stmtClientes->close();
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="small fw-bold mb-2">Monto del Ingreso (Bs)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-money-bill-wave"></i></span>
                                    <input type="text" class="form-control campo-monto" id="monto" name="monto" placeholder="0,00" maxlength="15" required>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="small fw-bold mb-2">Banco de Origen</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-university"></i></span>
                                    <select class="form-select" id="metodo_pago" name="metodo_pago">
                                        <option value="">Seleccione banco...</option>
                                        <optgroup label="Bancos Principales">
                                            <option value="Banco de Venezuela">Banco de Venezuela</option>
                                            <option value="Banesco">Banesco</option>
                                            <option value="Mercantil">Banco Mercantil</option>
                                            <option value="Provincial">Banco Provincial</option>
                                        </optgroup>
                                        <optgroup label="Otros Bancos">
                                            <option value="BNC">BNC</option>
                                            <option value="Bancamiga">Bancamiga</option>
                                            <option value="Banplus">Banplus</option>
                                            <option value="Exterior">Banco Exterior</option>
                                        </optgroup>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="small fw-bold mb-2">Referencia (Últimos 6)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                    <input type="text" class="form-control" id="codigo_pago" name="referencia" placeholder="000000">
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="small fw-bold mb-2">Fecha del Pago</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                    <input type="text" class="form-control datepicker-flat" id="fecha_pago" name="fecha_pago" placeholder="YYYY-MM-DD">
                                </div>
                            </div>

                            <?php if ($_SESSION['tipo'] === 'upu'): ?>
                            <div class="col-12 mb-3">
                                <label class="small fw-bold mb-2">Adjuntar Comprobante (Opcional)</label>
                                <div class="input-group">
                                    <input type="file" class="form-control" id="comprobante" name="comprobante" accept="image/*,.pdf">
                                </div>
                                <div class="extra-small text-muted mt-1"><i class="fas fa-info-circle me-1"></i> Formatos JPG, PNG o PDF. Máximo 15 días de persistencia.</div>
                            </div>
                            <?php endif; ?>

                            <div class="col-12 mb-4">
                                <label class="small fw-bold mb-2">Descripción / Motivo</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" maxlength="50" placeholder="Ej: Pago de factura #123" style="height: 100px;"></textarea>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-register btn-lg w-100 shadow-sm mt-2">
                            <i class="fas fa-check-circle me-2"></i> Registrar Pago
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php
require_once("../models/footer.php");
?>