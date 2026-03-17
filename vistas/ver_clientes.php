<?php
require_once("../models/header.php");
require_once("../conexion.php");



$usuario_id = $_SESSION["id"];

$sql = "
    SELECT DISTINCT c.id_cliente, c.nombre 
    FROM cliente c
    INNER JOIN usuario_pagos up ON c.id_cliente = up.cliente_id
    WHERE up.usuario_id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<style>
    /* =========================================
       SISTEMA SDGBP - DISEÑO ULTRA PREMIUM 2026
       GESTIÓN DE CLIENTES
       ========================================= */
    :root {
        --primary: #f18000;
        --primary-dark: #d67100;
        --primary-light: rgba(241, 128, 0, 0.1);
        --success-premium: #10b981;
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
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        padding: 1.5rem 2rem;
        border: none !important;
    }

    .card-premium-header h5 {
        color: white;
        margin: 0;
        font-weight: 700;
        letter-spacing: 0.5px;
    }

    /* --- TABLE CUSTOMIZATION --- */
    #datatablesSimple {
        border-collapse: separate !important;
        border-spacing: 0 8px !important;
    }

    #datatablesSimple thead th {
        background: #f1f5f9 !important;
        color: var(--text-muted) !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        font-size: 0.75rem !important;
        padding: 1rem !important;
        border: none !important;
        letter-spacing: 1px;
    }

    #datatablesSimple tbody tr {
        background: white !important;
        box-shadow: 0 2px 5px rgba(0,0,0,0.02) !important;
        transition: all 0.2s ease;
    }

    #datatablesSimple tbody tr:hover {
        background: #f8fafc !important;
        transform: scale(1.002);
    }

    #datatablesSimple td {
        padding: 1rem !important;
        vertical-align: middle !important;
        border: none !important;
        font-weight: 500;
    }

    /* --- BUTTONS --- */
    .btn-add-premium {
        background: linear-gradient(135deg, var(--primary) 0%, #ff9800 100%) !important;
        color: white !important;
        border: none !important;
        padding: 0.75rem 1.5rem !important;
        border-radius: 12px !important;
        font-weight: 700 !important;
        box-shadow: 0 4px 12px rgba(241, 128, 0, 0.2) !important;
        transition: all 0.3s ease !important;
    }

    .btn-add-premium:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(241, 128, 0, 0.3) !important;
    }

    .btn-action {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        transition: all 0.2s ease;
        text-decoration: none !important;
    }

    .btn-action-edit {
        background: var(--primary-light);
        color: var(--primary);
    }

    .btn-action-edit:hover {
        background: var(--primary);
        color: white;
    }

    .btn-action-delete {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }

    .btn-action-delete:hover {
        background: #ef4444;
        color: white;
    }

    /* --- EMPTY STATE --- */
    .empty-state-box {
        padding: 4rem 2rem;
        background: #f8fafc;
        border-radius: 20px;
        border: 2px dashed var(--border-color);
    }

    .icon-circle-info {
        width: 80px;
        height: 80px;
        background: var(--primary-light);
        color: var(--primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        font-size: 2rem;
    }
</style>

<div id="layoutSidenav_content">
    <div class="container-fluid px-4 py-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h1 class="mt-2 mb-1 fw-bold text-dark">Mis Clientes</h1>
                <p class="text-muted mb-0">Listado de clientes registrados en el sistema</p>
            </div>
            
            <a onclick="navigateTo('agregar_cliente.php')" class="btn btn-add-premium">
                <i class="fas fa-user-plus me-2"></i> Nuevo Cliente
            </a>
        </div>
        
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-premium p-3 mb-4">
                <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigateTo('inicio.php')" class="text-primary fw-600 text-decoration-none"><i class="fas fa-home me-1"></i> Inicio</a></li>
                <li class="breadcrumb-item active text-muted"><i class="fas fa-users me-1"></i> Clientes</li>
            </ol>
        </nav>

        <div class="card card-premium shadow mb-5">
            <div class="card-premium-header">
                <h5><i class="fas fa-list-ul me-2"></i> Directorio de Clientes</h5>
            </div>
            <div class="card-body p-4">
                <?php if ($result->num_rows > 0) { ?>
                    
                    <div class="table-responsive">
                        <table id="datatablesSimple" class="table">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">REF</th>
                                    <th>Nombre Completo</th>
                                    <th class="text-center" style="width: 150px;">Operaciones</th> 
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $contador = 1;
                                while ($row = $result->fetch_assoc()) { ?>
                                    <tr>
                                        <td class="text-center"><span class="badge bg-light text-primary border rounded-pill px-3 py-2">#<?php echo str_pad($contador++, 2, "0", STR_PAD_LEFT); ?></span></td> 
                                        <td class="fw-bold"><?php echo htmlspecialchars($row['nombre']); ?></td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                <a 
                                                    onclick="navigateTo('editar_cliente.php?id=<?php echo $row['id_cliente']; ?>')" 
                                                    class="btn-action btn-action-edit" 
                                                    title="Ver Detalles"
                                                >
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                <a 
                                                    href="javascript:void(0);"
                                                    class="btn-action btn-action-delete" 
                                                    onclick="confirmarEliminacionCliente(<?php echo $row['id_cliente']; ?>)"
                                                    title="Eliminar Cliente"
                                                >
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>

                <?php } else { ?>
                    <div class="empty-state-box text-center">
                        <div class="icon-circle-info anim-bounce">
                            <i class="fas fa-users-slash"></i>
                        </div>
                        <h4 class="text-dark fw-bold mb-2">No tienes clientes registrados</h4>
                        <p class="text-muted mb-4 max-w-400 mx-auto">Parece que aún no has registrado clientes bajo tu cuenta. Comienza agregando uno nuevo.</p>
                        <a onclick="navigateTo('agregar_cliente.php')" class="btn btn-add-premium">
                             <i class="fas fa-user-plus me-2"></i> Registrar Primer Cliente
                        </a>
                    </div>
                <?php } ?>
            </div>
        </div>

    </div>

<?php
require_once("../models/footer.php");
?>