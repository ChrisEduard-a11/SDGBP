<?php
require '../vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

try {
    session_start();
    if (empty($_SESSION['id']) || ($_SESSION['tipo'] !== 'admin' && $_SESSION['tipo'] !== 'cont')) {
        die("Acceso Restringido. Esta vista es exclusiva para el equipo contable o administrativo.");
    }

    include('../conexion.php');
    if ($conexion->connect_error) {
        throw new Exception("Error de conexión: " . $conexion->connect_error);
    }

    $cierre_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($cierre_id <= 0) {
        die("Identificador de cierre inválido.");
    }

    // Obtener información del cierre
    $sql_cierre = "SELECT mes, anio, fecha_cierre FROM cierres_mensuales WHERE id = ?";
    $stmt_c = $conexion->prepare($sql_cierre);
    $stmt_c->bind_param("i", $cierre_id);
    $stmt_c->execute();
    $res_c = $stmt_c->get_result();
    
    if ($res_c->num_rows === 0) {
        die("No se encontró el período cerrado especificado.");
    }
    
    $cierre_info = $res_c->fetch_assoc();
    $mes = $cierre_info['mes'];
    $anio = $cierre_info['anio'];
    $fecha_corte = date('d/m/Y h:i A', strtotime($cierre_info['fecha_cierre']));
    
    $nombres_meses = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    ];
    $nombre_mes = mb_strtoupper($nombres_meses[$mes], 'UTF-8');

    // Consulta para agrupar ingresos y egresos por UPU en ese mes
    // Comisiones no suelen estar en usuario_pagos, sino vinculadas por p.cliente.
    // Traemos todos los pagos aprobados de ese mes, ordenados cronológicamente
    $sql_pagos = "
        SELECT 
            p.*,
            IFNULL(u.id_usuario, (SELECT id_usuario FROM usuario WHERE cliente = p.cliente AND tipos = 'upu' LIMIT 1)) AS id_upu_real,
            IFNULL(u.nombre, IFNULL((SELECT nombre FROM usuario WHERE cliente = p.cliente AND tipos = 'upu' LIMIT 1), 'Pagos y Comisiones (General)')) AS upu_nombre
        FROM pagos p
        LEFT JOIN usuario_pagos up ON p.id = up.pago_id
        LEFT JOIN usuario u ON up.usuario_id = u.id_usuario AND u.tipos = 'upu'
        WHERE p.estado = 'aprobado' 
          AND MONTH(p.fecha_pago) = $mes
          AND YEAR(p.fecha_pago) = $anio
        ORDER BY p.fecha_pago ASC, p.id ASC
    ";
    
    $resultado = $conexion->query($sql_pagos);
    if (!$resultado) {
        throw new Exception("Error en la consulta: " . $conexion->error);
    }

    $upus_data = [];
    $macro_ingresos = 0;
    $macro_egresos = 0;

    // Precargar TODAS las UPU y su saldo histórico en caso de que no tengan movimientos en el mes actual
    $sql_todas_upus = "SELECT id_usuario, nombre FROM usuario WHERE tipos = 'upu'";
    $res_todas = $conexion->query($sql_todas_upus);
    if ($res_todas) {
        while ($ru = $res_todas->fetch_assoc()) {
            $id_upu_temp = $ru['id_usuario'];
            $nombre_temp = $ru['nombre'];
            
            // Buscar saldo histórico más reciente HASTA el final de ese mes
            $sql_saldo = "
                SELECT p.saldo_resultante 
                FROM pagos p
                LEFT JOIN usuario_pagos up ON p.id = up.pago_id
                WHERE p.estado = 'aprobado' 
                  AND (YEAR(p.fecha_pago) < $anio OR (YEAR(p.fecha_pago) = $anio AND MONTH(p.fecha_pago) <= $mes))
                  AND (
                      (up.usuario_id = $id_upu_temp) 
                      OR (p.cliente = (SELECT cliente FROM usuario WHERE id_usuario = $id_upu_temp LIMIT 1))
                  )
                ORDER BY p.fecha_pago DESC, p.id DESC
                LIMIT 1
            ";
            $res_saldo = $conexion->query($sql_saldo);
            $saldo_historico = ($res_saldo && $res_saldo->num_rows > 0) ? (float)$res_saldo->fetch_assoc()['saldo_resultante'] : 0;
            
            $upus_data[$id_upu_temp] = [
                'nombre' => $nombre_temp,
                'ingresos' => 0,
                'egresos' => 0,
                'saldo_final' => $saldo_historico
            ];
        }
    }

    while ($row = $resultado->fetch_assoc()) {
        $id_upu = $row['id_upu_real'] ?: 'general';
        if (!isset($upus_data[$id_upu])) {
            $upus_data[$id_upu] = [
                'nombre' => $row['upu_nombre'],
                'ingresos' => 0,
                'egresos' => 0,
                'saldo_final' => 0
            ];
        }
        
        $monto = (float)$row['monto'];
        if ($row['tipo'] === 'Ingreso') {
            $upus_data[$id_upu]['ingresos'] += $monto;
            $macro_ingresos += $monto;
        } else {
            $upus_data[$id_upu]['egresos'] += $monto;
            $macro_egresos += $monto;
        }
        
        // El saldo final siempre se actualiza al último registro válido procesado (cierre de mes)
        $upus_data[$id_upu]['saldo_final'] = (float)$row['saldo_resultante'];
    }
    
    // Sort logic to order by ingresos DESC to match original behavior
    usort($upus_data, function($a, $b) {
        if ($b['ingresos'] == $a['ingresos']) {
            return strcmp($a['nombre'], $b['nombre']);
        }
        return $b['ingresos'] <=> $a['ingresos'];
    });

    // ... generamos el HTML ...
    $svg_banner = '
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 120" width="100%" height="100%" preserveAspectRatio="none">
        <path d="M1000,40 C700,120 600,-20 0,60 L0,0 L1000,0 Z" fill="#1e293b" />
        <path d="M0,120 L0,30 C300,100 450,150 750,120 Z" fill="#f18000" fill-opacity="0.3" />
        <path d="M0,120 L0,60 C250,100 350,130 550,120 Z" fill="#f18000" fill-opacity="0.95" />
        <circle cx="880" cy="40" r="3" fill="#f18000" fill-opacity="0.8" />
        <circle cx="800" cy="90" r="2" fill="#ffffff" fill-opacity="0.15" />
        <circle cx="830" cy="65" r="1.5" fill="#f18000" fill-opacity="0.3" />
        <polyline points="800,90 830,65 850,55" fill="none" stroke="#f18000" stroke-width="1" stroke-opacity="0.3" />
    </svg>';
    $base64_banner = 'data:image/svg+xml;base64,' . base64_encode(trim($svg_banner));

    // Logo Corporativo en Base64 para máxima compatibilidad en InfinityFree
    $logo_path = '../img/Logo-OP2_V4.png';
    $logo_base64 = '';
    if (file_exists($logo_path)) {
        $logo_data = base64_encode(file_get_contents($logo_path));
        $logo_base64 = 'data:image/png;base64,' . $logo_data;
    }

    $html = '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: "Helvetica", "Arial", sans-serif; font-size: 10pt; color: #1e293b; margin: 0; padding: 0 40px; }
            @page { margin: 120px 0px 60px 0px; }
            
            header { position: fixed; top: -120px; left: 0px; right: 0px; height: 120px; background-color: #0f172a; border-bottom: 4px solid #f18000; }
            .header-content { position: relative; padding-top: 30px; padding-left: 40px; padding-right: 40px; }
            .company-name { color: #ffffff; font-size: 22pt; font-weight: bold; margin: 0; letter-spacing: 0.5px; }
            .report-title { color: #ffffff; font-size: 11pt; font-weight: bold; margin: 5px 0 0 0; text-transform: uppercase; letter-spacing: 1px; }
            
            footer { position: fixed; bottom: -40px; left: 40px; right: 40px; height: 30px; border-top: 1px solid #e2e8f0; padding-top: 10px; font-size: 8pt; color: #94a3b8; }
            .pagenum:before { content: counter(page); }

            .info-panel { background-color: #f8fafc; padding: 15px 20px; margin-top: 10px; margin-bottom: 25px; border-left: 5px solid #f18000; border: 1px solid #e2e8f0; }
            .info-label { font-size: 8pt; color: #64748b; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; }
            .info-value { font-size: 11pt; color: #0f172a; font-weight: bold; margin-top: 3px; display: block; }
            
            .data-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; font-size: 9pt; }
            .data-table th { background-color: #0f172a; color: #ffffff; padding: 10px; font-size: 8pt; text-align: left; text-transform: uppercase; border: 1px solid #0f172a; }
            .data-table td { padding: 10px 10px; border: 1px solid #e2e8f0; vertical-align: middle; }
            .row-even { background-color: #f8fafc; }
            .row-odd { background-color: #ffffff; }
            
            .text-success { color: #16a34a; font-weight: bold; }
            .text-danger { color: #dc2626; font-weight: bold; }
            .text-right { text-align: right; }
            .text-center { text-align: center; }
            .text-blue { color: #2563eb; font-weight: bold; }
            
            .totals-row td { background-color: #f1f5f9; font-weight: bold; font-size: 10pt; border-top: 2px solid #cbd5e1; padding: 12px 10px; }
            
            .macro-panel { width: 100%; border-collapse: collapse; margin-top: 20px; }
            .macro-box { padding: 20px; text-align: center; border: 1px solid #e2e8f0; background-color: #f8fafc; width: 33%; }
            .macro-box-central { background-color: #0f172a; color: #ffffff; border: 1px solid #0f172a;}
            .macro-title { font-size: 9pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px;}
            .macro-val { font-size: 16pt; font-weight: bold; }
            .macro-box-central .macro-title { color: #94a3b8; }
            .macro-box-central .macro-val { color: #f8fafc; }
            
            .title-section { font-size: 14pt; color: #0f172a; font-weight: bold; margin-bottom: 15px; border-bottom: 2px solid #f18000; padding-bottom: 5px;}
        </style>
    </head>
    <body>

    <header>
        <img src="' . $base64_banner . '" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;" alt="bg">
        <div class="header-content">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 25%;">

                        <img src="' . $logo_base64 . '" alt="Logo" style="height: 65px; border-radius: 88x; background: white; padding: 2px;">
                    </td>
                    <td style="width: 75%; text-align: right;">
                        <h1 class="company-name ">EURIPYS 2024 C.A.</h1>
                        <p class="report-title">Resumen de Cierre Mensual Consolidado</p>
                    </td>
                </tr>
            </table>
        </div>
    </header>

    <footer>
        <table style="width: 100%;">
            <tr>
                <td style="text-align: left;">SDGBP - SISTEMA DE GESTIÓN DE BIENES Y PAGOS</td>
                <td style="text-align: right;">Generado el ' . date('d/m/Y h:i A') . ' | Página <span class="pagenum"></span></td>
            </tr>
        </table>
    </footer>

    <main>
        <div class="info-panel">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 50%;">
                        <span class="info-label">PERÍODO AUDITADO</span>
                        <span class="info-value">' . $nombre_mes . ' ' . $anio . '</span>
                    </td>
                    <td style="width: 50%; text-align: right;">
                        <span class="info-label">FECHA DE CORTE EN SISTEMA</span>
                        <span class="info-value">' . $fecha_corte . '</span>
                    </td>
                </tr>
            </table>
        </div>

        <div class="title-section">Balance Consolidado por Unidad de Producción (UPU)</div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Unidad de Producción / Origen</th>
                    <th class="text-right">Total Ingresos cargados</th>
                    <th class="text-right">Total Egresos cargados</th>
                    <th class="text-right">Saldo de Cierre (Mes)</th>
                </tr>
            </thead>
            <tbody>
    ';

    $i = 0;
    foreach ($upus_data as $data) {
        $row_class = ($i % 2 == 0) ? 'row-even' : 'row-odd';
        $ing = (float)$data['ingresos'];
        $egr = (float)$data['egresos'];
        
        // Emplea el saldo_final extraído cronológicamente como el resultado final del mes
        $saldo_final = (float)$data['saldo_final'];
        $saldo_class = ($saldo_final >= 0) ? 'text-blue' : 'text-danger';
        $saldo_sign = ($saldo_final > 0) ? '+' : '';

        $html .= '<tr class="' . $row_class . '">';
        $html .= '<td><strong>' . htmlspecialchars($data['nombre']) . '</strong></td>';
        $html .= '<td class="text-right text-success">Bs ' . number_format($ing, 2, ',', '.') . '</td>';
        $html .= '<td class="text-right text-danger">Bs ' . number_format($egr, 2, ',', '.') . '</td>';
        $html .= '<td class="text-right ' . $saldo_class . '">Bs ' . $saldo_sign . number_format($saldo_final, 2, ',', '.') . '</td>';
        $html .= '</tr>';
        $i++;
    }
    
    $macro_neto = $macro_ingresos - $macro_egresos;

    $html .= '</tbody>';
    $html .= '<tfoot>';
    $html .= '<tr class="totals-row">';
    $html .= '<td class="text-right">SUMATORIA DE UPU ACTIVAS</td>';
    $html .= '<td class="text-right text-success">Bs ' . number_format($macro_ingresos, 2, ',', '.') . '</td>';
    $html .= '<td class="text-right text-danger">Bs ' . number_format($macro_egresos, 2, ',', '.') . '</td>';
    $html .= '<td class="text-right" style="color:#0f172a;">Bs ' . number_format($macro_neto, 2, ',', '.') . '</td>';
    $html .= '</tr>';
    $html .= '</tfoot>';
    $html .= '</table>';

    if ($resultado->num_rows == 0) {
        $html .= '<div style="height:150px; text-align:center; padding-top: 50px; color: #94a3b8;">No se registraron movimientos en este período mensual.</div>';
    }

    $capital_movilizado = $macro_ingresos + $macro_egresos;

    $html .= '
        <div style="page-break-inside: avoid;">
            <div class="title-section" style="margin-top: 40px;">Panel Macro Corporativo</div>
            <table class="macro-panel">
                <tr>
                    <td class="macro-box">
                        <div class="macro-title text-success">INGRESOS TOTALES</div>
                        <div class="macro-val text-success">Bs ' . number_format($macro_ingresos, 2, ',', '.') . '</div>
                    </td>
                    <td class="macro-box macro-box-central">
                        <div class="macro-title">Capital General Movilizado</div>
                        <div class="macro-val">Bs ' . number_format($capital_movilizado, 2, ',', '.') . '</div>
                    </td>
                    <td class="macro-box">
                        <div class="macro-title text-danger">EGRESOS TOTALES</div>
                        <div class="macro-val text-danger">Bs ' . number_format($macro_egresos, 2, ',', '.') . '</div>
                    </td>
                </tr>
            </table>
            
            <table style="width: 100%; text-align: center; margin-top: 60px;">
                <tr>
                    <td style="width: 50%;">
                        <div style="width: 250px; border-top: 1.5px solid #0f172a; margin: 0 auto;"></div>
                        <p style="font-weight: bold; color: #0f172a; margin: 10px 0 2px 0; font-size: 10pt;">Verificación Administrativa</p>
                        <p style="color: #64748b; font-size: 8.5pt; margin: 0;">Firma y Sello</p>
                    </td>
                    <td style="width: 50%;">
                        <div style="width: 250px; border-top: 1.5px solid #0f172a; margin: 0 auto;"></div>
                        <p style="font-weight: bold; color: #0f172a; margin: 10px 0 2px 0; font-size: 10pt;">Revisión Contable</p>
                        <p style="color: #64748b; font-size: 8.5pt; margin: 0;">EURIPYS 2024 C.A.</p>
                    </td>
                </tr>
            </table>
        </div>
    </main>
    </body>
    </html>
    ';

    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $options->set('isFontSubsettingEnabled', true);
    $options->set('dpi', 72);
    $options->set('chroot', realpath(__DIR__ . '/../'));

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $attachment = (isset($_GET['download']) && $_GET['download'] == '1') ? 1 : 0;
    $dompdf->stream($nombre_pdf, ['Attachment' => $attachment]); 
    exit;
} catch (Exception $e) {
    echo "Error interno al generar documento PDF: " . $e->getMessage();
    exit;
}
