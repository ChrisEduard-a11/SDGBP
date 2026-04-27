<?php
session_start();
require_once("../conexion.php");
require '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Verificar que el usuario sea Admin o Cont
if (!isset($_SESSION["tipo"]) || ($_SESSION["tipo"] !== "admin" && $_SESSION["tipo"] !== "cont")) {
    die("Acceso denegado. Se requieren permisos de administrador o contador.");
}

date_default_timezone_set('America/Caracas');
$fecha_emision = date("d/m/Y h:i A");

// Construir la consulta de saldos
$sql = "SELECT nombre, correo, saldo FROM usuario WHERE tipos = 'upu' ORDER BY nombre ASC";
$result = $conexion->query($sql);

$total_global = 0;
$filas_html = '';

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $total_global += $row['saldo'];
        $saldo_formateado = number_format($row['saldo'], 2, ',', '.');
        $inicial = substr($row['nombre'], 0, 1);
        
        $filas_html .= "
        <tr>
            <td style='text-align: center; font-weight: bold; color: #4f46e5;'>{$inicial}</td>
            <td style='font-weight: bold; color: #1e293b;'>{$row['nombre']}</td>
            <td style='color: #64748b;'>{$row['correo']}</td>
            <td class='text-right' style='font-weight: bold; color: #0f172a;'>{$saldo_formateado} Bs</td>
        </tr>";
    }
} else {
    $filas_html = "<tr><td colspan='4' style='text-align: center; color: #64748b;'>No hay unidades de producción registradas.</td></tr>";
}

$total_formateado = number_format($total_global, 2, ',', '.');

// HTML para el PDF
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
        .macro-box { padding: 20px; text-align: center; border: 1px solid #e2e8f0; background-color: #f8fafc; width: 100%; }
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
                    <img src="../img/Logo-OP2_V4.png" alt="Logo" style="height: 65px; border-radius: 8px; background: white; padding: 2px;">
                </td>
                <td style="width: 75%; text-align: right;">
                    <h1 class="company-name ">EURIPYS 2024 C.A.</h1>
                    <p class="report-title">Reporte General de Saldos UPU</p>
                </td>
            </tr>
        </table>
    </div>
</header>

<footer>
    <table style="width: 100%;">
        <tr>
            <td style="text-align: left;">SDGBP - SISTEMA DE GESTIÓN DE BIENES Y PAGOS</td>
            <td style="text-align: right;">Generado el ' . $fecha_emision . ' | Página <span class="pagenum"></span></td>
        </tr>
    </table>
</footer>

<main>
    <div class="info-panel">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%;">
                    <span class="info-label">FECHA DE CORTE EN SISTEMA</span>
                    <span class="info-value">' . $fecha_emision . '</span>
                </td>
                <td style="width: 50%; text-align: right;">
                    <span class="info-label">ESTADO DE ARCHIVO</span>
                    <span class="info-value text-success">Consolidado y Verificado</span>
                </td>
            </tr>
        </table>
    </div>

    <h2 class="title-section">Balance Consolidado por Unidad de Producción (UPU)</h2>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">#</th>
                <th style="width: 40%;">UNIDAD DE PRODUCCIÓN / ORIGEN</th>
                <th style="width: 35%;">CONTACTO O REFERENCIA</th>
                <th style="width: 20%;" class="text-right">SALDO DISPONIBLE</th>
            </tr>
        </thead>
        <tbody>
            ' . $filas_html . '
            <tr class="totals-row">
                <td colspan="3" class="text-right">LIQUIDEZ TOTAL DEL SISTEMA:</td>
                <td class="text-right text-blue">Bs ' . $total_formateado  . '</td>
            </tr>
        </tbody>
    </table>

    <table class="macro-panel">
        <tr>
            <td class="macro-box macro-box-central">
                <div class="macro-title">TOTAL CORPORATIVO DISPONIBLE</div>
                <div class="macro-val">Bs ' . $total_formateado . '</div>
            </td>
        </tr>
    </table>
</main>
</body>
</html>';

// Configurar y generar el PDF Optimizado
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('isFontSubsettingEnabled', true);
$options->set('dpi', 72);
$options->set('chroot', realpath(__DIR__ . '/../'));

$dompdf = new Dompdf($options);
$dompdf->setPaper('A4', 'portrait');

$dompdf->loadHtml($html);
$dompdf->render();

$attachment = (isset($_GET['download']) && $_GET['download'] == '1') ? 1 : 0;
$dompdf->stream("Reporte_Saldos_UPU.pdf", array("Attachment" => $attachment));
?>
