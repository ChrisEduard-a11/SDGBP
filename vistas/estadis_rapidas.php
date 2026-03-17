<?php
require_once("../models/header.php");
require_once("../conexion.php"); // Archivo de conexión a la base de datos

// Inicializar los datos
$meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
$pagosAprobados = array_fill(0, 12, 0); // Inicializar con 0 para cada mes
$pagosRechazados = array_fill(0, 12, 0); // Inicializar con 0 para cada mes

// Consultar pagos aprobados y rechazados por mes
$query = "SELECT MONTH(fecha_pago) AS mes, estado, COUNT(*) AS total 
          FROM pagos 
          GROUP BY MONTH(fecha_pago), estado";
$result = mysqli_query($conexion, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $mes = (int)$row['mes'] - 1; // Restar 1 porque los índices del array comienzan en 0
        if ($row['estado'] === 'aprobado') {
            $pagosAprobados[$mes] = (int)$row['total'];
        } elseif ($row['estado'] === 'rechazado') {
            $pagosRechazados[$mes] = (int)$row['total'];
        }
    }
}

// Consultas para obtener los datos reales
$totalUsuarios = $totalBienes = $totalPagos = 0; // Valores iniciales

// Total de usuarios registrados
$queryUsuarios = "SELECT COUNT(*) AS total FROM usuario";
$resultUsuarios = mysqli_query($conexion, $queryUsuarios);
if ($resultUsuarios) {
    $rowUsuarios = mysqli_fetch_assoc($resultUsuarios);
    $totalUsuarios = $rowUsuarios['total'];
}

// Total de bienes registrados
$queryBienes = "SELECT COUNT(*) AS total FROM bienes";
$resultBienes = mysqli_query($conexion, $queryBienes);
if ($resultBienes) {
    $rowBienes = mysqli_fetch_assoc($resultBienes);
    $totalBienes = $rowBienes['total'];
}

// Total de pagos realizados
$queryPagos = "SELECT COUNT(*) AS total FROM pagos";
$resultPagos = mysqli_query($conexion, $queryPagos);
if ($resultPagos) {
    $rowPagos = mysqli_fetch_assoc($resultPagos);
    $totalPagos = $rowPagos['total'];
}
?>
<div id="layoutSidenav_content">
    <div class="container-fluid px-4">
        <h1 class="mt-4 text-center">Estadisticas Rapidas</h1>
            <ol class="breadcrumb mb-4 bg-light p-3 rounded">
                <li class="breadcrumb-item"><a onclick="navigateTo('inicio.php')" class="text-decoration-none">Inicio</a></li>
                <li class="breadcrumb-item active">Estadisticas Rapidas</li>
            </ol>
        <div class="row">
            <!-- Tarjeta de Usuarios Registrados -->
            <div class="col-xl-3 col-md-6">
                <div class="card bg-primary text-white mb-4">
                    <div class="card-body">
                        <i class="fas fa-users"></i> Usuarios Registrados
                        <h3 class="mt-2"><?php echo $totalUsuarios; ?></h3>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a class="small text-white stretched-link" href="javascript:void(0);" onclick="navigateTo('usuario.php')">Ver Detalles</a>
                        <div class="small text-white"><i class="fas fa-arrow-circle-right"></i></div>
                    </div>
                </div>
            </div>
            <!-- Tarjeta de Bienes Registrados -->
            <div class="col-xl-3 col-md-6">
                <div class="card bg-warning text-white mb-4">
                    <div class="card-body">
                        <i class="fas fa-box"></i> Bienes Registrados
                        <h3 class="mt-2"><?php echo $totalBienes; ?></h3>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a class="small text-white stretched-link" href="javascript:void(0);" onclick="navigateTo('lista_bienes.php')">Ver Detalles</a>
                        <div class="small text-white"><i class="fas fa-arrow-circle-right"></i></div>
                    </div>
                </div>
            </div>
            <!-- Tarjeta de Pagos Realizados -->
            <div class="col-xl-3 col-md-6">
                <div class="card bg-success text-white mb-4">
                    <div class="card-body">
                        <i class="fas fa-file-invoice-dollar"></i> Pagos Realizados
                        <h3 class="mt-2"><?php echo $totalPagos; ?></h3>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a class="small text-white stretched-link" href="javascript:void(0);" onclick="navigateTo('ver_pagos_cont.php')">Ver Detalles</a>
                        <div class="small text-white"><i class="fas fa-arrow-circle-right"></i></div>
                    </div>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar"></i> Estadísticas de Pagos por Mes
                </div>
                <div class="card-body">
                    <canvas id="graficoPagos"></canvas>
                </div>
            </div>
            <script>
                const ctx = document.getElementById('graficoPagos').getContext('2d');
                const graficoPagos = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: <?php echo json_encode($meses); ?>, // Etiquetas de los meses
                        datasets: [
                            {
                                label: 'Pagos Aprobados',
                                data: <?php echo json_encode($pagosAprobados); ?>, // Datos de pagos aprobados
                                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Pagos Rechazados',
                                data: <?php echo json_encode($pagosRechazados); ?>, // Datos de pagos rechazados
                                backgroundColor: 'rgba(255, 99, 132, 0.5)',
                                borderColor: 'rgba(255, 99, 132, 1)',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            </script>
        </div>
    </div>
<?php require_once("../models/footer.php"); ?>