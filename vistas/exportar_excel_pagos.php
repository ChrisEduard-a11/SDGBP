<?php
// Configura los encabezados para forzar la descarga del archivo Excel
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Historial_Pagos_" . date("Y-m-d") . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// Conexión a la base de datos
require_once("../conexion.php");

// Recibir variables de filtro
$estado = $_GET['estado'] ?? '';
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';
$referencia = $_GET['referencia'] ?? '';
$usuario_upu = $_GET['usuario_upu'] ?? '';

// Construir la consulta SQL con filtros (igual que en la vista principal)
$sql = "SELECT pagos.*, usuario.nombre AS nombre_cliente FROM pagos 
         INNER JOIN usuario_pagos ON pagos.id = usuario_pagos.pago_id
         INNER JOIN usuario ON usuario.id_usuario = usuario_pagos.usuario_id
         WHERE 1=1";
$params = [];

if (!empty($estado)) {
    $sql .= " AND pagos.estado = ?";
    $params[] = $estado;
}

if (!empty($fecha_inicio) && !empty($fecha_fin)) {
    $sql .= " AND pagos.fecha_pago BETWEEN ? AND ?";
    $params[] = $fecha_inicio;
    $params[] = $fecha_fin;
}

if (!empty($referencia)) {
    $sql .= " AND pagos.referencia LIKE ?";
    $params[] = "%$referencia%";
}

if (!empty($usuario_upu)) {
    $sql .= " AND usuario.id_usuario = ?";
    $params[] = $usuario_upu;
}

$sql .= " ORDER BY id DESC";

$stmt = $conexion->prepare($sql);
if (!$stmt) {
    die("Error en la preparación de la consulta: " . $conexion->error);
}

$types = str_repeat("s", count($params));

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

if (!$stmt->execute()) {
    die("Error en la ejecución de la consulta: " . $stmt->error);
}

$result = $stmt->get_result();

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

require '../vendor/autoload.php';

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Historial de Pagos y Egresos');

// Estilos de la tabla
$styleHeader = [
    'font' => [
        'bold' => true,
        'color' => ['argb' => Color::COLOR_WHITE],
        'size' => 11,
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FF10B981'] // Green Emerald 500
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['argb' => 'FF9CA3AF'],
        ],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
];

// Encabezado Global
$sheet->mergeCells('A1:L2');
$sheet->setCellValue('A1', 'REPORTE CORPORATIVO DE TRANSACCIONES Y PAGOS');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setArgb('FF1E293B');
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

$sheet->mergeCells('A3:L3');
$sheet->setCellValue('A3', 'Filtros aplicados: ' . ($estado ? "Estado: $estado " : "") . ($fecha_inicio ? "Desde: $fecha_inicio " : "") . ($fecha_fin ? "Hasta: $fecha_fin" : "Diarios"));
$sheet->getStyle('A3')->getFont()->setItalic(true)->getColor()->setArgb('FF6B7280');

// Array de cabeceras
$headers = [
    'A' => 'UPU / Socio',
    'B' => 'Tipo Tx',
    'C' => 'Fecha de Pago',
    'D' => 'Referencia',
    'E' => 'Método de Pago',
    'F' => 'Motivo de Rechazo',
    'G' => 'Concepto / Descripción',
    'H' => 'Ingreso (+ Bs)',
    'I' => 'Egreso (- Bs)',
    'J' => 'Saldo Final (Bs)',
    'K' => 'Estado Transacción',
    'L' => 'Usuario Ejecutor'
];

foreach ($headers as $col => $title) {
    // Aplicar ancho automático luego, por ahora setea valor
    $sheet->setCellValue($col . '5', $title);
}
$sheet->getStyle('A5:L5')->applyFromArray($styleHeader);
$sheet->getRowDimension(5)->setRowHeight(25);

// Llenar datos
$fila = 6;
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sheet->setCellValue('A' . $fila, $row["nombre_cliente"]);
        $sheet->setCellValue('B' . $fila, ucfirst($row["tipo"]));
        $sheet->setCellValue('C' . $fila, date('d/m/Y', strtotime($row["fecha_pago"])));
        
        // Forzar referencia como texto
        $sheet->setCellValueExplicit('D' . $fila, $row["referencia"], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        
        $sheet->setCellValue('E' . $fila, $row["metodo_pago"]);
        $sheet->setCellValue('F' . $fila, html_entity_decode($row["des_rechazo"] ?? ''));
        $sheet->setCellValue('G' . $fila, html_entity_decode($row["descripcion"]));
        
        if ($row["tipo"] == "Ingreso") {
            $sheet->setCellValue('H' . $fila, (float)$row["monto"]);
            $sheet->getStyle('H' . $fila)->getNumberFormat()->setFormatCode('#,##0.00_-"Bs"');
            $sheet->getStyle('H' . $fila)->getFont()->getColor()->setArgb('FF059669'); // Verde
        } else {
            $sheet->setCellValue('I' . $fila, (float)$row["monto"]);
            $sheet->getStyle('I' . $fila)->getNumberFormat()->setFormatCode('#,##0.00_-"Bs"');
            $sheet->getStyle('I' . $fila)->getFont()->getColor()->setArgb('FFDC2626'); // Rojo
        }
        
        if ($row['estado'] == 'aprobado' && isset($row['saldo_resultante'])) {
            $sheet->setCellValue('J' . $fila, (float)$row['saldo_resultante']);
            $sheet->getStyle('J' . $fila)->getNumberFormat()->setFormatCode('#,##0.00_-"Bs"');
            $sheet->getStyle('J' . $fila)->getFont()->setBold(true);
        }
        
        $sheet->setCellValue('K' . $fila, ucfirst($row['estado']));
        if ($row['estado'] == 'aprobado') {
            $sheet->getStyle('K' . $fila)->getFont()->getColor()->setArgb('FF059669');
        } elseif ($row['estado'] == 'rechazado') {
            $sheet->getStyle('K' . $fila)->getFont()->getColor()->setArgb('FFDC2626');
        }
        
        $sheet->setCellValue('L' . $fila, $row['cliente']);
        
        // Borde ligth para la fila
        $sheet->getStyle('A'.$fila.':L'.$fila)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_HAIR);
        
        $fila++;
    }
} else {
    $sheet->mergeCells('A6:L6');
    $sheet->setCellValue('A6', 'No se encontraron pagos con los filtros seleccionados.');
    $sheet->getStyle('A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
}

// Anchos de columna fijos o aprox
$sheet->getColumnDimension('A')->setWidth(25);
$sheet->getColumnDimension('B')->setWidth(15);
$sheet->getColumnDimension('C')->setWidth(15);
$sheet->getColumnDimension('D')->setWidth(20);
$sheet->getColumnDimension('E')->setWidth(20);
$sheet->getColumnDimension('F')->setWidth(25);
$sheet->getColumnDimension('G')->setWidth(35);
$sheet->getColumnDimension('H')->setWidth(18);
$sheet->getColumnDimension('I')->setWidth(18);
$sheet->getColumnDimension('J')->setWidth(20);
$sheet->getColumnDimension('K')->setWidth(15);
$sheet->getColumnDimension('L')->setWidth(25);

// Congelar el encabezado superior
$sheet->freezePane('A6');

// Limpiar buffers en caso de que alguien dejó un echo por ahí suelto (previene archivos excel corruptos)
if (ob_get_length()) {
    ob_end_clean();
}

$nombreArchivo = "Historial_Pagos_" . date("Y_m_d_H_i") . ".xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $nombreArchivo . '"');
header('Cache-Control: max-age=0');
header('Cache-Control: max-age=1');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); 
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); 
header('Cache-Control: cache, must-revalidate'); 
header('Pragma: public'); 

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
