<?php
session_start();
require_once("../conexion.php");
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

// Validar Permisos
if (!isset($_SESSION["tipo"]) || ($_SESSION["tipo"] !== "admin" && $_SESSION["tipo"] !== "cont")) {
    die("Acceso denegado.");
}

date_default_timezone_set('America/Caracas');
$fecha_hoy = date("d/m/Y");

// Iniciar Libro
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Saldos Disponibles UPU');

// -- ESTILOS --
$styleHeader = [
    'font' => [
        'bold' => true,
        'color' => ['argb' => Color::COLOR_WHITE],
        'size' => 11,
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FF4F46E5'] // Indigo Hex #4f46e5
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['argb' => 'FFCBD5E1'],
        ],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
];

$styleTitle = [
    'font' => [
        'bold' => true,
        'size' => 16,
        'color' => ['argb' => 'FF1E293B'] // Slate 800
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
];

// -- TÍTULO DEL REPORTE --
$sheet->mergeCells('A1:C2');
$sheet->setCellValue('A1', 'REPORTE DE FONDOS DISPONIBLES (UPU)');
$sheet->getStyle('A1:C2')->applyFromArray($styleTitle);
$sheet->getRowDimension(1)->setRowHeight(20);
$sheet->getRowDimension(2)->setRowHeight(20);

$sheet->setCellValue('A3', 'Generado automáticamente por el Sistema el: ' . $fecha_hoy);
$sheet->mergeCells('A3:C3');
$sheet->getStyle('A3')->getFont()->setItalic(true);
$sheet->getStyle('A3')->getFont()->getColor()->setArgb('FF64748B');

// -- CABECERAS DE COLUMNAS --
$sheet->setCellValue('A5', 'Nombre de la Unidad / Origen');
$sheet->setCellValue('B5', 'Correo Electrónico');
$sheet->setCellValue('C5', 'Saldo Disponible (Bs)');

$sheet->getStyle('A5:C5')->applyFromArray($styleHeader);
$sheet->getRowDimension(5)->setRowHeight(25);

// -- OBTENER DATOS --
$sql = "SELECT nombre, correo, saldo FROM usuario WHERE tipos = 'upu' ORDER BY nombre ASC";
$result = $conexion->query($sql);

$fila_actual = 6;
$total_saldos = 0;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sheet->setCellValue('A' . $fila_actual, $row['nombre']);
        $sheet->setCellValue('B' . $fila_actual, $row['correo']);
        
        // Saldos en numérico para Excel
        $sheet->setCellValue('C' . $fila_actual, (float)$row['saldo']);
        $sheet->getStyle('C'.$fila_actual)->getNumberFormat()->setFormatCode('#,##0.00_-"Bs"');
        
        $total_saldos += $row['saldo'];
        
        // Estilos básicos de bordes para la fila
        $sheet->getStyle('A'.$fila_actual.':C'.$fila_actual)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A'.$fila_actual.':C'.$fila_actual)->getBorders()->getAllBorders()->getColor()->setArgb('FFE2E8F0');
        
        $fila_actual++;
    }
} else {
    $sheet->mergeCells('A6:C6');
    $sheet->setCellValue('A6', 'No hay registros encontrados.');
    $sheet->getStyle('A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
}

// -- PIE DE TABLA (TOTAL) --
$sheet->setCellValue('B' . $fila_actual, 'TOTAL GENERAL (LIQUIDEZ)');
$sheet->setCellValue('C' . $fila_actual, (float)$total_saldos);
$sheet->getStyle('B'.$fila_actual)->getFont()->setBold(true);
$sheet->getStyle('B'.$fila_actual)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
$sheet->getStyle('C'.$fila_actual)->getFont()->setBold(true);
$sheet->getStyle('C'.$fila_actual)->getNumberFormat()->setFormatCode('#,##0.00_-"Bs"');
$sheet->getStyle('C'.$fila_actual)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setArgb('FFF1F5F9');

// -- AUTO-AJUSTAR ANCHO DE COLUMNAS --
$sheet->getColumnDimension('A')->setWidth(40);
$sheet->getColumnDimension('B')->setWidth(35);
$sheet->getColumnDimension('C')->setWidth(25);

// Bloquear encabezados flotantes
$sheet->freezePane('A6');

// -- ENVIAR HOJA AL NAVEGADOR --
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Saldos_UPU_' . date('Y_m_d_H_i') . '.xlsx"');
header('Cache-Control: max-age=0');
// IE 9
header('Cache-Control: max-age=1');
// SSL Headers
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header('Pragma: public'); // HTTP/1.0

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
