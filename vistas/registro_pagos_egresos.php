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

// Generar token de seguridad (idempotencia)
if (!isset($_SESSION['form_tokens'])) {
    $_SESSION['form_tokens'] = [];
}
$idempotency_token = bin2hex(random_bytes(16));
$_SESSION['form_tokens'][$idempotency_token] = time();

// Limpiar tokens viejos (más de 1 hora)
foreach ($_SESSION['form_tokens'] as $token => $time) {
    if (time() - $time > 3600) {
        unset($_SESSION['form_tokens'][$token]);
    }
}
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --glass-bg: rgba(255, 255, 255, 0.7);
        --glass-border: rgba(255, 255, 255, 0.3);
        --glass-shadow: 0 8px 32px 0 rgba(135, 31, 31, 0.1);
        --accent-danger: #ef4444;
        --accent-warning: #f59e0b;
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
    .form-section-title i { color: var(--accent-danger); }

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
        border-color: var(--accent-danger);
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
        color: var(--text-main);
    }

    .saldo-badge {
        background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 50px;
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        box-shadow: 0 4px 15px rgba(239, 68, 68, 0.2);
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-up { animation: fadeInUp 0.5s ease forwards; }

    .btn-register-egreso {
        background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
        border: none;
        color: white;
        font-weight: 700;
        border-radius: 15px;
        padding: 1rem;
        transition: all 0.3s ease;
    }
    .btn-register-egreso:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(239, 68, 68, 0.3);
        color: white;
    }
</style>

<div id="layoutSidenav_content">
    <div class="container-fluid px-4 py-4">
        
        <header class="page-header-standard d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
            <div>
                <h1 class="fw-bold mb-0 text-primary"><i class="fas fa-minus-circle me-2"></i>Registrar Egreso</h1>
                <p class="text-muted">Reporta tus salidas de dinero para control administrativo y balance</p>
            </div>
            <nav aria-label="breadcrumb" class="d-none d-lg-block">
                <ol class="breadcrumb bg-transparent p-0 m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigateTo('inicio.php')" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active">Nuevo Egreso</li>
                </ol>
            </nav>
        </header>

        <div class="row g-4">
            <!-- Sidebar Info -->
            <div class="col-lg-4 animate-up" style="animation-delay: 0.1s;">
                <div class="glass-card p-4 mb-4">
                    <div class="form-section-title">
                        <i class="fas fa-wallet"></i> Balance Actual
                    </div>
                    <?php if ($_SESSION["tipo"] == "upu") { ?>
                    <div class="saldo-badge w-100">
                        <div class="bg-white bg-opacity-20 p-2 rounded-circle">
                            <i class="fas fa-university fs-5"></i>
                        </div>
                        <div>
                            <div class="small opacity-80">Saldo disponible</div>
                            <div class="fw-bold fs-4">Bs. <?php echo number_format($saldo_usuario, 2, ',', '.'); ?></div>
                        </div>
                    </div>
                    <?php } else { ?>
                        <div class="alert alert-light bg-opacity-10 py-2 border-0 mb-0">
                            <small class="text-muted"><i class="fas fa-user-shield me-1"></i> Sesión administrativa</small>
                        </div>
                    <?php } ?>
                </div>

                <div class="glass-card p-4 border-start border-4 border-warning">
                    <div class="form-section-title">
                        <i class="fas fa-exclamation-triangle text-warning"></i> Guía de Egresos
                    </div>
                    <ul class="small text-muted ps-3 mb-0">
                        <li class="mb-2">Confirma que el <b>monto</b> no exceda tu disponibilidad si eres UPU.</li>
                        <li class="mb-2">La <b>referencia</b> es obligatoria para el rastreo del gasto.</li>
                        <li class="mb-0">Describe claramente el <b>motivo</b> para facilitar la aprobación.</li>
                    </ul>
                </div>
            </div>

            <!-- Main Form -->
            <div class="col-lg-8 animate-up" style="animation-delay: 0.2s;">
                <div class="glass-card p-4 p-md-5">
                    <form method="post" id="formRegistroEgreso" action="../acciones/controlador_pago_egreso.php" onsubmit="return validateFormRegistroEgreso()" enctype="multipart/form-data">
                        <input type="hidden" name="idempotency_token" value="<?php echo $idempotency_token; ?>">
                        
                        <div class="form-section-title">
                            <i class="fas fa-minus-circle"></i> Detalles del Egreso
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label class="small fw-bold mb-2">Usuario Asociado</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <?php if ($_SESSION["tipo"] == "admin" || $_SESSION["tipo"] == "cont") { ?>
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
                                    <?php if ($_SESSION['tipo'] === 'admin' || $_SESSION['tipo'] === 'cont'): ?>
                                        <span class="input-group-text"><i class="fas fa-ban"></i></span>
                                        <input type="text" class="form-control bg-light" value="No Aplica" readonly>
                                        <input type="hidden" name="cliente" id="cliente" value="No Aplica">
                                    <?php else: ?>
                                        <span class="input-group-text"><i class="fas fa-building"></i></span>
                                        <select class="form-select" id="cliente" name="cliente" required>
                                            <option value="">Seleccione...</option>
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
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="small fw-bold mb-2">Banco de Destino / Método</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-university"></i></span>
                                    <select class="form-select" id="metodo_pago" name="metodo_pago" required>
                                        <option value="">Seleccione el banco...</option>
                                        <optgroup label="Bancos Públicos">
                                            <option value="Banco de Venezuela">Banco de Venezuela (BDV)</option>
                                            <option value="Banco del Tesoro">Banco del Tesoro</option>
                                            <option value="BDT - Banco Digital de los Trabajadores">BDT - Banco Digital de los Trabajadores</option>
                                            <option value="Banco Agrícola de Venezuela">Banco Agrícola de Venezuela</option>
                                            <option value="BANFANB">BANFANB</option>
                                        </optgroup>
                                        <optgroup label="Bancos Privados">
                                            <option value="Banesco">Banesco</option>
                                            <option value="Banco Mercantil">Banco Mercantil</option>
                                            <option value="Provincial">Provincial (BBVA)</option>
                                            <option value="BNC - Banco Nacional de Crédito">BNC - Banco Nacional de Crédito</option>
                                            <option value="Bancamiga">Bancamiga</option>
                                            <option value="Banplus">Banplus</option>
                                            <option value="Banco Exterior">Banco Exterior</option>
                                            <option value="BFC Banco Fondo Común">BFC Banco Fondo Común</option>
                                            <option value="Banco Caroní">Banco Caroní</option>
                                            <option value="Banco Activo">Banco Activo</option>
                                            <option value="Banco Plaza">Banco Plaza</option>
                                            <option value="100% Banco">100% Banco</option>
                                            <option value="DelSur">DelSur</option>
                                            <option value="Bancentro">Bancentro</option>
                                            <option value="Mi Banco">Mi Banco</option>
                                            <option value="Banco Venezolano de Crédito">Banco Venezolano de Crédito</option>
                                            <option value="Otro/Efectivo">Otro / Efectivo / Divisa</option>
                                        </optgroup>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="small fw-bold mb-2">Monto del Egreso (Bs)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-money-bill-wave"></i></span>
                                    <input type="text" class="form-control campo-monto" id="monto" name="monto" placeholder="1.234,56" maxlength="15" required>
                                </div>
                                <div class="form-text small opacity-70"><i class="fas fa-info-circle me-1"></i> Formato sugerido: 1.500,00 (Punto para miles, coma para decimales).</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="small fw-bold mb-2">Referencia / Cheque</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                    <input type="text" class="form-control" id="codigo_pago" name="referencia" placeholder="Nro de Operación" required>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="small fw-bold mb-2">Fecha del Egreso</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                    <input type="text" class="form-control datepicker-flat" id="fecha_pago" name="fecha_pago" placeholder="YYYY-MM-DD">
                                </div>
                            </div>

                            <?php if ($_SESSION['tipo'] === 'upu'): ?>
                            <div class="col-md-6 mb-3">
                                <label class="small fw-bold mb-2">Comprobante (Opcional)</label>
                                <div class="input-group">
                                    <input type="file" class="form-control" id="comprobante" name="comprobante" accept="image/*,.pdf">
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="col-12 mb-4">
                                <label class="small fw-bold mb-2">Descripción / Motivo</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" maxlength="50" placeholder="Motivo detallado (máx 50 caracteres)" style="height: 100px;" required></textarea>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-register-egreso btn-lg w-100 shadow-sm mt-2">
                            <i class="fas fa-check-circle me-2"></i> Confirmar Egreso
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php
require_once("../models/footer.php");
?>