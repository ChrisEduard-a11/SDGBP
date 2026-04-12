<?php
require '../vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

try {
    // Depuración: guarda los datos recibidos por POST
    file_put_contents('debug_post.txt', print_r($_POST, true));

    // Conexión a la base de datos
    include('../conexion.php');
    if ($conexion->connect_error) {
        throw new Exception("Error de conexión: " . $conexion->connect_error);
    }

    // Recibe filtros del formulario
    $fecha_inicio = $_POST['filtro_fecha_inicio'] ?? '';
    $fecha_fin = $_POST['filtro_fecha_fin'] ?? '';
    $usuario_upu = isset($_POST['usuario_upu']) ? intval($_POST['usuario_upu']) : 0;

    // Filtro de fechas
    $filtro_fecha = "";
    if (!empty($fecha_inicio) && !empty($fecha_fin)) {
        $filtro_fecha = " AND pagos.fecha_pago BETWEEN '$fecha_inicio' AND '$fecha_fin'";
    }

    // Consulta principal
    if ($usuario_upu === 0) {
        $sql = "
            SELECT pagos.*, 
                   IFNULL(usuario.nombre, '') AS nombre_cliente
            FROM pagos
            LEFT JOIN usuario_pagos ON pagos.id = usuario_pagos.pago_id
            LEFT JOIN usuario ON usuario_pagos.usuario_id = usuario.id_usuario
            WHERE pagos.estado = 'aprobado'
            $filtro_fecha
            ORDER BY pagos.id ASC
        ";
    } else {
        $sql = "
            SELECT pagos.*, 
                   usuario.nombre AS nombre_cliente
            FROM pagos
            INNER JOIN usuario_pagos ON pagos.id = usuario_pagos.pago_id
            INNER JOIN usuario ON usuario_pagos.usuario_id = usuario.id_usuario
            WHERE usuario.id_usuario = $usuario_upu
              AND pagos.estado = 'aprobado'
              $filtro_fecha
            UNION ALL
            SELECT pagos.*, 
                    (SELECT nombre FROM usuario WHERE cliente = pagos.cliente LIMIT 1) AS nombre_cliente
                FROM pagos
                WHERE pagos.descripcion = 'Comisión por pago'
                AND pagos.estado = 'aprobado'
              AND pagos.cliente = (SELECT cliente FROM usuario WHERE id_usuario = $usuario_upu)
              AND pagos.id NOT IN (
                  SELECT pago_id FROM usuario_pagos WHERE usuario_id = $usuario_upu
              )
            $filtro_fecha
            ORDER BY id ASC
        ";
    }

    $resultado = $conexion->query($sql);
    if (!$resultado) {
        throw new Exception("Error en la consulta: " . $conexion->error . " | SQL: " . $sql);
    }

    // Obtener nombre de la UPU seleccionada
    $nombre_upu = 'Todas las UPU';
    if ($usuario_upu !== 0) {
        $res_upu = $conexion->query("SELECT nombre FROM usuario WHERE id_usuario = $usuario_upu LIMIT 1");
        if ($res_upu && $res_upu->num_rows > 0) {
            $fila_upu = $res_upu->fetch_assoc();
            $nombre_upu = $fila_upu['nombre'];
        } else {
            $nombre_upu = 'UPU desconocida';
        }
    }

    // Generar un banner vectorial 100% seguro para DOMPDF (solo shapes básicos, sin linear-gradient)
    $svg_banner = '
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 120" width="100%" height="100%" preserveAspectRatio="none">
        <!-- Ola azul oscura en el fondo -->
        <path d="M1000,40 C700,120 600,-20 0,60 L0,0 L1000,0 Z" fill="#1e293b" />
        
        <!-- Ondas naranjas estrictamente alineadas a la IZQUIERDA -->
        <path d="M0,120 L0,30 C300,100 450,150 750,120 Z" fill="#f18000" fill-opacity="0.3" />
        <path d="M0,120 L0,60 C250,100 350,130 550,120 Z" fill="#f18000" fill-opacity="0.95" />
        
        <!-- Acentos a la derecha (Puntos y lineas finas que no estorban el texto) -->
        <circle cx="880" cy="40" r="3" fill="#f18000" fill-opacity="0.8" />
        <circle cx="800" cy="90" r="2" fill="#ffffff" fill-opacity="0.15" />
        <circle cx="830" cy="65" r="1.5" fill="#f18000" fill-opacity="0.3" />
        <polyline points="800,90 830,65 850,55" fill="none" stroke="#f18000" stroke-width="1" stroke-opacity="0.3" />
    </svg>';
    $base64_banner = 'data:image/svg+xml;base64,' . base64_encode(trim($svg_banner));

    $html = '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: "Helvetica", "Arial", sans-serif; font-size: 10pt; color: #1e293b; margin: 0; padding: 0 40px; }
            @page { margin: 120px 0px 60px 0px; }
            
            header { position: fixed; top: -120px; left: 0px; right: 0px; height: 120px; background-color: #0f172a; border-bottom: 4px solid #f18000; }
            .header-content { position: relative; padding-top: 30px; padding-left: 40px; padding-right: 40px; }
            .company-name { color: #ffffffff; font-size: 22pt; font-weight: bold; margin: 0; letter-spacing: 0.5px; }
            .report-title { color: #ffffffff; font-size: 11pt; font-weight: bold; margin: 5px 0 0 0; text-transform: uppercase; letter-spacing: 1px; }
            
            footer { position: fixed; bottom: -40px; left: 40px; right: 40px; height: 30px; border-top: 1px solid #e2e8f0; padding-top: 10px; font-size: 8pt; color: #94a3b8; }
            .pagenum:before { content: counter(page); }

            .info-panel { background-color: #f8fafc; padding: 15px 20px; margin-top: 10px; margin-bottom: 25px; border-left: 5px solid #f18000; border-top: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; }
            .info-label { font-size: 8pt; color: #64748b; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; }
            .info-value { font-size: 11pt; color: #0f172a; font-weight: bold; margin-top: 3px; display: block; }
            
            .data-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; font-size: 9pt; }
            .data-table th { background-color: #0f172a; color: #ffffff; padding: 10px; font-size: 8pt; text-align: left; text-transform: uppercase; border: 1px solid #0f172a; }
            .data-table td { padding: 8px 10px; border: 1px solid #e2e8f0; vertical-align: middle; }
            .row-even { background-color: #f8fafc; }
            .row-odd { background-color: #ffffff; }
            .row-egreso { background-color: #fff1f2; }
            
            .badge-ingreso { color: #16a34a; font-weight: bold; font-size: 8pt; }
            .badge-egreso { color: #dc2626; font-weight: bold; font-size: 8pt; }
            .text-success { color: #16a34a; font-weight: bold; }
            .text-danger { color: #dc2626; font-weight: bold; }
            .text-right { text-align: right; }
            .text-center { text-align: center; }
            
            .totals-row td { background-color: #f1f5f9; font-weight: bold; font-size: 10pt; border-top: 2px solid #cbd5e1; padding: 12px 10px; }
            .saldo-final { background-color: #0f172a !important; color: #ffffff !important; font-size: 11pt !important; }
        </style>
    </head>
    <body>

    <header>
        <img src="' . $base64_banner . '" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;" alt="bg">
        <div class="header-content">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 25%;">
                        <img src="https://lh5.googleusercontent.com/p/AF1QipMIuz9nSKZaDup5Zr7LIVwhyDKheMsfdeD_55hd=w408-h408-k-no" alt="Logo" style="height: 65px; border-radius: 8px; background: white; padding: 2px;">
                    </td>
                    <td style="width: 75%; text-align: right;">
                        <h1 class="company-name ">EURIPYS 2024 C.A.</h1>
                        <p class="report-title">Registro de Ingreso y Egreso de UPU</p>
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
                        <span class="info-label">Unidad de Producción (UPU)</span>
                        <span class="info-value">' . htmlspecialchars($nombre_upu) . '</span>
                    </td>
                    <td style="width: 50%; text-align: right;">
                        <span class="info-label">Período de Reporte</span>
                        <span class="info-value">' . (empty($fecha_inicio) ? 'Todos los registros' : htmlspecialchars($fecha_inicio) . ' al ' . htmlspecialchars($fecha_fin)) . '</span>
                    </td>
                </tr>
            </table>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Nombre de la UPU</th>
                    <th>Tipo</th>
                    <th>Fecha</th>
                    <th>Referencia</th>
                    <th class="text-right">Entrada</th>
                    <th class="text-right">Salida</th>
                    <th class="text-right">Saldo</th>
                    <th>Cliente/Proveedor</th>
                </tr>
            </thead>
            <tbody>
    ';

    // Variables acumuladoras
    $total_entrada = 0;
    $total_salida = 0;
    $saldo_total = 0;
    $max_id = -1;
    $saldo_ultimo_pago = 0;

    $i = 0;
    while ($row = $resultado->fetch_assoc()) {
        if ($row['id'] > $max_id) {
            $max_id = $row['id'];
            $saldo_ultimo_pago = $row['saldo_resultante'];
        }
        
        $row_class = ($i % 2 == 0) ? 'row-even' : 'row-odd';
        if ($row['tipo'] === 'Egreso') $row_class = 'row-egreso';

        $html .= '<tr class="' . $row_class . '">';
        $html .= '<td><strong>' . htmlspecialchars($row['nombre_cliente']) . '</strong></td>';
        
        $tipo_badge = ($row['tipo'] === 'Ingreso') ? '<span class="badge-ingreso">INGRESO</span>' : '<span class="badge-egreso">EGRESO</span>';
        $html .= '<td>' . $tipo_badge . '</td>';
        
        $html .= '<td>' . htmlspecialchars($row['fecha_pago']) . '</td>';
        $html .= '<td><span style="color:#64748b;">#' . htmlspecialchars($row['referencia']) . '</span></td>';

        if ($row['tipo'] === 'Ingreso') {
            $html .= '<td class="text-right text-success">Bs +' . htmlspecialchars($row['monto']) . '</td>';
            $html .= '<td></td>';
            $total_entrada += $row['monto']; 
        } else {
            $html .= '<td></td>';
            $html .= '<td class="text-right text-danger">Bs -' . htmlspecialchars($row['monto']) . '</td>';
            $total_salida += $row['monto']; 
        }

        $html .= '<td class="text-right" style="font-weight: 600;">Bs ' . htmlspecialchars($row['saldo_resultante']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['cliente']) . '</td>';
        $html .= '</tr>';
        $i++;
    }
    
    // Fila de totales
    $saldo_periodo = number_format((float)$saldo_ultimo_pago, 2, ',', '.');

    $html .= '</tbody>';
    $html .= '<tfoot>';
    $html .= '<tr class="totals-row">';
    $html .= '<td colspan="4" class="text-right">TOTAL DEL PERÍODO</td>';
    $html .= '<td class="text-right text-success">Bs +' . number_format($total_entrada, 2, ',', '.') . '</td>';
    $html .= '<td class="text-right text-danger">Bs -' . number_format($total_salida, 2, ',', '.') . '</td>';
    $html .= '<td colspan="2" class="text-center saldo-final">SALDO TOTAL: Bs ' . $saldo_periodo . '</td>';
    $html .= '</tr>';
    $html .= '</tfoot>';
    $html .= '</table>';

    if ($resultado->num_rows == 0) {
        $html .= '<div style="height:150px; text-align:center; padding-top: 50px; color: #94a3b8;">No se encontraron registros para el período seleccionado.</div>';
    }

    $html .= '
        <table style="width: 100%; text-align: center; margin-top: 60px; page-break-inside: avoid;">
            <tr>
                <td style="width: 50%;">
                    <div style="width: 250px; border-top: 1.5px solid #0f172a; margin: 0 auto;"></div>
                    <p style="font-weight: bold; color: #0f172a; margin: 10px 0 2px 0; font-size: 10pt;">Firma del Coordinador</p>
                    <p style="color: #64748b; font-size: 8.5pt; margin: 0;">Representante de la UPU</p>
                </td>
                <td style="width: 50%;">
                    <div style="width: 250px; border-top: 1.5px solid #0f172a; margin: 0 auto;"></div>
                    <p style="font-weight: bold; color: #0f172a; margin: 10px 0 2px 0; font-size: 10pt;">Sello / Conforme</p>
                    <p style="color: #64748b; font-size: 8.5pt; margin: 0;">EURIPYS 2024 C.A.</p>
                </td>
            </tr>
        </table>
    </main>
    </body>
    </html>
    ';


    // Configuración de Dompdf
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);

    // Configurar el tamaño de la página y la orientación
    $dompdf->setPaper('A4', 'landscape');

    // Renderizar el PDF
    $dompdf->render();

    // Construir nombre del PDF dinámico
    $nombre_upu_archivo = ($usuario_upu === 0)
        ? 'Todas_las_UPU'
        : preg_replace('/[^A-Za-z0-9_\-]/', '_', $nombre_upu);

    if (!empty($fecha_inicio) && !empty($fecha_fin)) {
        $periodo_archivo = $fecha_inicio . '_al_' . $fecha_fin; // formato: 2026-04-07_al_2026-12-31
    } else {
        $periodo_archivo = 'Todos_los_registros';
    }

    $nombre_pdf = 'Reporte_Ingresos_Egresos_' . $nombre_upu_archivo . '_' . $periodo_archivo . '.pdf';

    // Mostrar el PDF en el navegador
    $dompdf->stream($nombre_pdf, ['Attachment' => 0]); 
    exit;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
?>