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

    // Banner superior corporativo y estilos
    $html = '
    <style>
        body { font-family: "Helvetica", "Arial", sans-serif; font-size: 10pt; color: #333; }
        .header-table { width: 100%; border-bottom: 3px solid #f18000; padding-bottom: 10px; margin-bottom: 20px; }
        .logo-cell { width: 30%; vertical-align: middle; }
        .title-cell { width: 70%; text-align: right; vertical-align: middle; }
        .company-name { font-size: 22pt; font-weight: bold; color: #0f172a; margin: 0; letter-spacing: 1px; }
        .report-title { font-size: 12pt; color: #f18000; font-weight: bold; margin: 5px 0 0 0; text-transform: uppercase; }
        
        .info-panel { background-color: #f8fafc; border-left: 4px solid #0f172a; padding: 10px 15px; margin-bottom: 25px; border-radius: 0 4px 4px 0; }
        .info-panel p { margin: 5px 0; font-size: 9.5pt; color: #475569; }
        .info-panel strong { color: #0f172a; }

        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 9pt; }
        .data-table th { background-color: #0f172a; color: #ffffff; padding: 10px; text-align: left; text-transform: uppercase; font-size: 8pt; letter-spacing: 0.5px; border: 1px solid #0f172a; }
        .data-table td { padding: 8px 10px; border: 1px solid #e2e8f0; vertical-align: middle; }
        .row-even { background-color: #f8fafc; }
        .row-odd { background-color: #ffffff; }
        .row-egreso { background-color: #fef2f2; }
        
        .text-success { color: #16a34a; font-weight: bold; }
        .text-danger { color: #dc2626; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .totals-row td { background-color: #f1f5f9; font-weight: bold; font-size: 10pt; border-top: 2px solid #cbd5e1; }
        .saldo-final { background-color: #0f172a !important; color: #ffffff !important; }
        
        .footer-signatures { width: 100%; margin-top: 50px; page-break-inside: avoid; }
        .sig-line { width: 250px; border-top: 1px solid #475569; margin: 0 auto; margin-bottom: 5px; }
        .sig-name { font-weight: bold; color: #0f172a; font-size: 10pt; margin: 0; }
        .sig-title { color: #64748b; font-size: 8.5pt; margin: 0; }
        
        .page-footer { position: fixed; bottom: -30px; left: 0; right: 0; height: 30px; text-align: center; font-size: 8pt; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 10px; }
    </style>

    <div class="page-footer">
        Generado por SDGBP - Sistema de Gestión de Bienes y Pagos el ' . date('d/m/Y h:i A') . '
    </div>

    <table class="header-table">
        <tr>
            <td class="logo-cell">
                <img src="https://lh5.googleusercontent.com/p/AF1QipMIuz9nSKZaDup5Zr7LIVwhyDKheMsfdeD_55hd=w408-h408-k-no" alt="Logo" style="height: 60px;">
            </td>
            <td class="title-cell">
                <h1 class="company-name">EURIPYS 2024 C.A.</h1>
                <p class="report-title">Registro de Ingreso y Egreso de UPU</p>
            </td>
        </tr>
    </table>

    <div class="info-panel">
        <p><strong>Filtros aplicados:</strong></p>
        <p><strong>Unidad de Producción (UPU):</strong> ' . htmlspecialchars($nombre_upu) . '</p>
        <p><strong>Período:</strong> ' . (empty($fecha_inicio) ? 'Todos los registros' : htmlspecialchars($fecha_inicio) . ' al ' . htmlspecialchars($fecha_fin)) . '</p>
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
        // Obtenemos el saldo del pago más reciente (el de mayor ID en la BD)
        if ($row['id'] > $max_id) {
            $max_id = $row['id'];
            $saldo_ultimo_pago = $row['saldo_resultante'];
        }
        
        $row_class = ($i % 2 == 0) ? 'row-even' : 'row-odd';
        if ($row['tipo'] === 'Egreso') $row_class = 'row-egreso';

        $html .= '<tr class="' . $row_class . '">';
        $html .= '<td>' . htmlspecialchars($row['nombre_cliente']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['tipo'] === 'Ingreso' ? 'Ingreso' : 'Egreso') . '</td>';
        $html .= '<td>' . htmlspecialchars($row['fecha_pago']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['referencia']) . '</td>';

        if ($row['tipo'] === 'Ingreso') {
            $html .= '<td class="text-right text-success">Bs +' . htmlspecialchars($row['monto']) . '</td>';
            $html .= '<td></td>';
            $total_entrada += $row['monto']; 
            $saldo_total += $row['monto'];
        } else {
            $html .= '<td></td>';
            $html .= '<td class="text-right text-danger">Bs -' . htmlspecialchars($row['monto']) . '</td>';
            $total_salida += $row['monto']; 
            $saldo_total += $row['monto'];
        }

        $html .= '<td class="text-right">Bs ' . htmlspecialchars($row['saldo_resultante']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['cliente']) . '</td>';
        $html .= '</tr>';
        $i++;
    }
    // Fila de totales
    // Usamos el saldo del último pago registrado encontrado (mayor ID)
    $saldo_periodo = number_format((float)$saldo_ultimo_pago, 2, ',', '.');

    $html .= '<tr class="totals-row">';
    $html .= '<td colspan="4" class="text-right">TOTAL DEL PERÍODO</td>';
    $html .= '<td class="text-right text-success">Bs +' . number_format($total_entrada, 2, ',', '.') . '</td>';
    $html .= '<td class="text-right text-danger">Bs -' . number_format($total_salida, 2, ',', '.') . '</td>';
    $html .= '<td colspan="2" class="text-center saldo-final">SALDO TOTAL: Bs ' . $saldo_periodo . '</td>';
    $html .= '</tr>';

    $html .= '</tbody>';
    $html .= '</table>';

    // Si no hay registros
    if ($resultado->num_rows == 0) {
        $html .= '<div style="height:250px;"></div>';
    }

    $html .= '
    <table class="footer-signatures">
        <tr>
            <td class="text-center">
                <div class="sig-line"></div>
                <p class="sig-name">Firma del Coordinador</p>
                <p class="sig-title">Representante de la UPU</p>
            </td>
            <td class="text-center">
                <div class="sig-line"></div>
                <p class="sig-name">Sello / Conforme</p>
                <p class="sig-title">EURIPYS 2024 C.A.</p>
            </td>
        </tr>
    </table>
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

    // Mostrar el PDF en el navegador
    $dompdf->stream('REGISTRO_DE_INGRESO_Y_EGRESO.pdf', ['Attachment' => 0]); 
    exit;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
?>