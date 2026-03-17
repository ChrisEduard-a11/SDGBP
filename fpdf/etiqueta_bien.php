<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require __DIR__ . '/fpdf.php';
require __DIR__ . '/phpqrcode/qrlib.php';
include(__DIR__ . '/../conexion.php');

class PDF extends FPDF {

    function RoundedRect($x, $y, $w, $h, $r, $style = '') {
        $k = $this->k;
        $hp = $this->h;
        if($style=='F') $op='f';
        elseif($style=='FD' || $style=='DF') $op='B';
        else $op='S';
        $MyArc = 4/3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2F %.2F m',($x+$r)*$k,($hp-$y)*$k));
        $xc = $x+$w-$r ;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l', $xc*$k,($hp-$y)*$k));

        $this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);
        $xc = $x+$w-$r ;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-$yc)*$k));
        $this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);
        $xc = $x+$r ;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',$xc*$k,($hp-($y+$h))*$k));
        $this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);
        $xc = $x+$r ;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-$yc)*$k));
        $this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
        $this->_out($op);
    }

    function _Arc($x1, $y1, $x2, $y2, $x3, $y3) {
        $h = $this->h;
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $x1*$this->k, ($h-$y1)*$this->k, 
            $x2*$this->k, ($h-$y2)*$this->k, $x3*$this->k, ($h-$y3)*$this->k));
    }

    function Header() {
        // Fondo blanco
        $this->SetFillColor(255, 255, 255);
        $this->Rect(0, 0, 62, 100, 'F');
        
        // Marco elegante (centrado)
        $this->SetDrawColor(220, 220, 220);
        $this->SetLineWidth(0.4);
        $this->RoundedRect(2, 2, 58, 96, 4, 'D');

        $logo_png = __DIR__ . '/../dompdf/Logo-OP2_V4.png';
        if (file_exists($logo_png)) {
            $this->Image($logo_png, 23.5, 5, 15);
        }

        $this->SetY(22);
        // Usamos una celda de ancho 62 para asegurar centrado total de página
        $this->SetX(0);
        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor(241, 128, 0); 
        $this->Cell(62, 5, 'EURIPYS 2024 C.A.', 0, 1, 'C');
        
        $this->SetX(0);
        $this->SetFont('Arial', '', 7);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(62, 4, 'BIENES NACIONALES', 0, 1, 'C');
        
        $this->Ln(2);
        $this->SetDrawColor(240, 240, 240);
        $this->Line(10, 33, 52, 33);
        $this->Ln(4);
    }

    function body($bien) {
        // Reducimos márgenes para que Cell(0) use todo el ancho
        $this->SetLeftMargin(0);
        $this->SetRightMargin(0);
        
        $fields = [
            'CATEGORIA' => $bien['categoria'],
            'DENOMINACION' => $bien['nombre'],
            'ANO INVENTARIO' => date('Y')
        ];

        foreach ($fields as $label => $value) {
            $this->SetFont('Arial', 'B', 6.5);
            $this->SetTextColor(160, 160, 160);
            $this->Cell(62, 3.5, mb_convert_encoding($label, 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
            
            $this->SetFont('Arial', 'B', 8.5);
            $this->SetTextColor(26, 26, 26);
            $this->MultiCell(62, 4.5, mb_convert_encoding($value, 'ISO-8859-1', 'UTF-8'), 0, 'C');
            $this->Ln(1.5);
        }
    }

    function qrCode($bien) {
        // Pill para el código (ARRIBA DEL QR)
        $this->SetFillColor(241, 245, 249);
        $this->SetDrawColor(203, 213, 225);
        $txt = 'CODIGO: ' . $bien['codigo'];
        $w = $this->GetStringWidth($txt) + 6;
        $this->SetX((62 - $w) / 2);
        $this->RoundedRect($this->GetX(), $this->GetY(), $w, 6, 1.5, 'DF');
        $this->SetFont('Arial', 'B', 7);
        $this->SetTextColor(71, 85, 105);
        $this->Cell($w, 6, $txt, 0, 1, 'C');

        $this->Ln(1);

        $codeContents = 'Serial: ' . $bien['codigo'] . "\n" .
                        'Denominacion: ' . $bien['nombre'];
        
        $qrTab = QRcode::text($codeContents, false, QR_ECLEVEL_L);
        $size = count($qrTab);
        
        // QR aún más pequeño (18mm) para que quepa todo abajo
        $target_size = 18;
        $pixel_size = $target_size / $size;
        $x_pos = (62 - $target_size) / 2; 
        $y_pos = $this->GetY();

        $this->SetFillColor(0, 0, 0);
        foreach ($qrTab as $qy => $line) {
            for ($qx = 0; $qx < $size; $qx++) {
                if ($line[$qx] == '1') {
                    $this->Rect($x_pos + ($qx * $pixel_size), $y_pos + ($qy * $pixel_size), $pixel_size, $pixel_size, 'F');
                }
            }
        }
        
        $this->SetY($y_pos + $target_size + 1.5);
        $this->SetFont('Arial', 'I', 5);
        $this->SetTextColor(170, 170, 170);
        $this->Cell(62, 4, 'SDGBP - Sistema de gestion de Bienes', 0, 1, 'C');
    }
}

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

// Inicializar FPDF con tamaño personalizado (62mm x 100mm)
$pdf = new PDF('P', 'mm', array(62, 100));
$pdf->SetAutoPageBreak(false);
$pdf->SetMargins(0, 0, 0); // Eliminamos márgenes para control total de centrado
$pdf->AddPage();
$pdf->Header();
$pdf->body($bien);
$pdf->qrCode($bien);

$pdf->Output('I', 'Etiqueta_' . $bien['codigo'] . '.pdf');
?>