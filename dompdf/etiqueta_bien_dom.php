<?php
require '../vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

include(__DIR__ . '/../conexion.php');
require_once __DIR__ . '/phpqrcode/qrlib.php';

$id = isset($_GET['id']) ? $_GET['id'] : null;
if (!$id) {
    die('ID de bien no especificado.');
}

$sql = "SELECT b.id, b.nombre, b.descripcion, c.nombre AS categoria, b.codigo, b.fecha_adquisicion 
        FROM bienes b 
        JOIN categorias c ON b.categoria_id = c.id
        WHERE b.id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$bien = $result->fetch_assoc();
$stmt->close();

if (!$bien) {
    die('Bien no encontrado.');
}

// Generar QR como Matriz de Texto
$codeContents = 'Serial: ' . $bien['codigo'] . "\n" .
                'Denominacion: ' . $bien['nombre'];
$qrTab = QRcode::text($codeContents, false, QR_ECLEVEL_L);
$qrSize = count($qrTab);

// Convertir QR a tabla HTML
$qrHtml = '<table style="border-collapse: collapse; margin: 0 auto; line-height: 0; border: 2mm solid white; background: white;">';
foreach ($qrTab as $line) {
    $qrHtml .= '<tr style="height: 0.8mm;">';
    for ($i = 0; $i < $qrSize; $i++) {
        $color = ($line[$i] == '1') ? '#000000' : '#ffffff';
        $qrHtml .= '<td style="width: 0.8mm; background-color: ' . $color . '; padding: 0;"></td>';
    }
    $qrHtml .= '</tr>';
}
$qrHtml .= '</table>';

// Logo en Base64 para evitar problemas de ruta con Dompdf
$logoPath = __DIR__ . '/Logo-OP2_V4.png';
$logoBase64 = '';
if (file_exists($logoPath)) {
    $logoData = file_get_contents($logoPath);
    $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
}

// HTML de la Etiqueta
$html = '
<!DOCTYPE html>
<html>
<head>
    <style>
        @page {
            margin: 0;
            size: 62mm 100mm;
        }
        body {
            font-family: "Helvetica", sans-serif;
            margin: 0;
            padding: 2mm;
            color: #333;
            text-align: center;
        }
        .container {
            border: 0.5mm solid #ccc;
            height: 94mm;
            border-radius: 4mm;
            overflow: hidden;
            background: #fff;
            position: relative;
        }
        .header {
            padding-top: 2mm;
            border-bottom: 0.2mm solid #eee;
            margin-bottom: 2mm;
            text-align: center;
        }
        .logo {
            width: 15mm;
            display: block;
            margin: 0 auto 1mm auto;
        }
        .company-name {
            font-size: 3.5mm;
            font-weight: bold;
            color: #f18000;
            margin: 0;
            width: 100%;
            text-align: center;
        }
        .subtitle {
            font-size: 2.5mm;
            color: #666;
            margin: 0;
            letter-spacing: 0.5mm;
            width: 100%;
            text-align: center;
        }
        .content {
            padding: 0 4mm;
            text-align: left;
        }
        .field {
            margin-bottom: 2mm;
        }
        .label {
            font-size: 2.2mm;
            color: #999;
            text-transform: uppercase;
            font-weight: bold;
            display: block;
        }
        .value {
            font-size: 3.2mm;
            font-weight: bold;
            color: #1a1a1a;
            word-wrap: break-word;
            display: block;
        }
        .qr-section {
            position: absolute;
            bottom: 3mm;
            left: 0;
            right: 0;
            text-align: center;
        }
        .footer-text {
            font-size: 2mm;
            color: #bbb;
            margin-top: 1mm;
        }
        .code-pill {
            background: #f1f5f9;
            color: #475569;
            padding: 1mm 3mm;
            border-radius: 2mm;
            font-size: 2.8mm;
            display: inline-block;
            margin-bottom: 2mm;
            border: 0.1mm solid #cbd5e1;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="' . $logoBase64 . '" class="logo">
            <p class="company-name">EURIPYS 2024 C.A.</p>
            <p class="subtitle">BIENES NACIONALES</p>
        </div>
        
        <div class="content">
            <div class="field">
                <span class="label">Categoría</span>
                <span class="value">' . htmlspecialchars($bien['categoria']) . '</span>
            </div>
            <div class="field">
                <span class="label">Denominación</span>
                <span class="value">' . htmlspecialchars($bien['nombre']) . '</span>
            </div>
            <div class="field">
                <span class="label">Año Inventario</span>
                <span class="value">' . date('Y') . '</span>
            </div>
        </div>

        <div class="qr-section">
            <div class="code-pill">
                CÓDIGO: ' . htmlspecialchars($bien['codigo']) . '
            </div>
            ' . $qrHtml . '
            <p class="footer-text">SDGBP - Sistema de Gestión de Bienes</p>
        </div>
    </div>
</body>
</html>';

// Configuración de Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper(array(0, 0, 175.75, 283.46), 'portrait'); // 62mm x 100mm en puntos (1mm = 2.83465pt)

$dompdf->render();
$dompdf->stream('Etiqueta_' . $bien['codigo'] . '.pdf', array('Attachment' => 0));
exit();
?>
