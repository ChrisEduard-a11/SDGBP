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
            ORDER BY pagos.id DESC
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

    // Banner superior corporativo
    $html = '
    <div style="width:100%; background:#f18000; padding:15px; color:#fff; font-family:sans-serif; display:flex; align-items:center; justify-content:space-between; border-bottom:5px solid #c75b00;">
        <div style="flex:0 0 auto; margin-left:20px;">
            <img src="https://lh5.googleusercontent.com/p/AF1QipMIuz9nSKZaDup5Zr7LIVwhyDKheMsfdeD_55hd=w408-h408-k-no" alt="Logo" style="height:80px;">
        </div>
        <div style="flex:1; text-align:right; margin-right:20px;">
            <h1 style="margin:0; font-size:2em; font-weight:bold;">EURIPYS 2024 C.A.</h1>
            <p style="margin:0; font-size:1.1em; font-weight:500;">REGISTRO DE INGRESO Y EGRESO DE UPU</p>
        </div>
    </div>

    <!-- CONTENEDOR PRINCIPAL -->
    <div style="width:100%; padding:0 20px; box-sizing:border-box;">
        <div style="margin-top:20px; font-family:sans-serif; font-size:0.9em;">
            <strong>Filtros aplicados:</strong><br>
            Fecha Inicio: ' . htmlspecialchars($fecha_inicio) . ' | Fecha Fin: ' . htmlspecialchars($fecha_fin) . ' | UPU: ' . htmlspecialchars($nombre_upu) . '
    ';

    // Tabla con bordes visibles y estilo profesional
    $html .= '<table style="width:100%; border-collapse:collapse; margin-top:20px; font-family:sans-serif; font-size:0.9em;">';
    $html .= '<thead>'; 
    $html .= '<tr style="background-color:#f18000; color:#fff;">';
    $html .= '<th style="padding:8px; border:1px solid #ccc;">Nombre de la UPU</th>';
    $html .= '<th style="border:1px solid #ccc;">Descripción</th>';
    $html .= '<th style="border:1px solid #ccc;">Fecha</th>';
    $html .= '<th style="border:1px solid #ccc;">Referencia</th>';
    $html .= '<th style="border:1px solid #ccc;">Entrada</th>';
    $html .= '<th style="border:1px solid #ccc;">Salida</th>';
    $html .= '<th style="border:1px solid #ccc;">Saldo</th>';
    $html .= '<th style="border:1px solid #ccc;">Cliente/Proveedor</th>';
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';

    // Variables acumuladoras
    $total_entrada = 0;
    $total_salida = 0;
    $saldo_total = 0;

    $i = 0;
    while ($row = $resultado->fetch_assoc()) {
        $ultimo_pago = $row; // Guardar el último pago iterado (último según ORDER BY)
        $row_color = ($i % 2 == 0) ? '#f9f9f9' : '#ffffff';
        $color_fondo = ($row['tipo'] === 'Egreso') ? 'background-color:#f8d7da;' : 'background-color:' . $row_color . ';';
        $html .= '<tr style="' . $color_fondo . '">';
        $html .= '<td style="border:1px solid #ccc;">' . htmlspecialchars($row['nombre_cliente']) . '</td>';
        $html .= '<td style="border:1px solid #ccc;">' . htmlspecialchars($row['tipo'] === 'Ingreso' ? 'Ingreso' : 'Egreso') . '</td>';
        $html .= '<td style="border:1px solid #ccc;">' . htmlspecialchars($row['fecha_pago']) . '</td>';
        $html .= '<td style="border:1px solid #ccc;">' . htmlspecialchars($row['referencia']) . '</td>';

        if ($row['tipo'] === 'Ingreso') {
            $html .= '<td style="color:green; border:1px solid #ccc;">Bs +' . htmlspecialchars($row['monto']) . '</td>';
            $html .= '<td style="border:1px solid #ccc;"></td>';
            $total_entrada += $row['monto']; 
            $saldo_total += $row['monto'];
        } else {
            $html .= '<td style="border:1px solid #ccc;"></td>';
            $html .= '<td style="color:red; border:1px solid #ccc;">Bs -' . htmlspecialchars($row['monto']) . '</td>';
            $total_salida += $row['monto']; 
            $saldo_total += $row['monto'];
        }

        $html .= '<td style="border:1px solid #ccc;">Bs ' . htmlspecialchars($row['saldo_resultante']) . '</td>';
        $html .= '<td style="border:1px solid #ccc;">' . htmlspecialchars($row['cliente']) . '</td>';
        $html .= '</tr>';
        $i++;
    }
    // Fila de totales
    // Calculamos la diferencia real para el Saldo Final
    $saldo_calculado = $total_entrada - $total_salida; 

    // Obtener el saldo_resultante del último pago filtrado
    if (isset($ultimo_pago) && isset($ultimo_pago['saldo_resultante'])) {
        $saldo_periodo = number_format((float)$ultimo_pago['saldo_resultante'], 2, ',', '.');
    } else {
        $saldo_periodo = number_format(0, 2, ',', '.');
    }

    $html .= '<tr style="background-color:#eaeaea; font-weight:bold;">';
    $html .= '<td colspan="4" style="text-align:right; border:1px solid #ccc;">TOTAL</td>';
    // Aquí se muestra la suma de todos los ingresos
    $html .= '<td style="color:green; border:1px solid #ccc;">Bs +' . number_format($total_entrada, 2, ',', '.') . '</td>';
    // Aquí se muestra la suma de todos los egresos (lo que salió)
    $html .= '<td style="color:red; border:1px solid #ccc;">Bs -' . number_format($total_salida, 2, ',', '.') . '</td>';
    // Mostrar el saldo_resultante del último pago filtrado como "Saldo del periodo"
    $html .= '<td colspan="2" style="border:1px solid #ccc; font-weight:bold;">Saldo del periodo: Bs ' . $saldo_periodo . '</td>';
    $html .= '</tr>';

    $html .= '</tbody>';
    $html .= '</table>';

    // Si no hay registros
    if ($resultado->num_rows == 0) {
        $html .= '<div style="height:250px;"></div>';
    }

    // Pie de página profesional
    date_default_timezone_set('America/Caracas'); // Hora venezolana
    $fecha_fecha = date('d/m/Y');
    $fecha_hora = date('h:i A');

    $html .= '
    <div style="margin-top:50px; font-family:sans-serif; font-size:0.9em; text-align:center; border-top:2px solid #ccc; padding-top:20px;">
        <table style="width:100%;">
            <tr>
                <td style="width:50%; text-align:center;">
                    <p style="margin-bottom:40px;">___________________________</p>
                    <p>Coordinador de la UPU</p>
                </td>
                <td style="width:50%; text-align:center;">
                    <p style="margin-bottom:40px;">___________________________</p>
                    <p>Responsable por EURIPYS 2024 C.A.</p>
                </td>
            </tr>
        </table>
        <p style="margin-top:30px; font-size:0.8em; color:#555; font-style:italic;">
            Generado por el SDGBP - Sistema de Gestión de Bienes y Pagos<br>
            Fecha: ' . $fecha_fecha . ' | Hora: ' . $fecha_hora . '
        </p>
    </div>
    </div>
    </div>
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