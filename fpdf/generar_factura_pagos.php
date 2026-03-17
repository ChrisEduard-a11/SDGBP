<?php
require_once("../conexion.php");
require_once("fpdf.php");

// Verificar si se recibió el ID del pago
if (!isset($_GET['id'])) {
    die("ID de pago no especificado.");
}

$id_pago = $_GET['id'];

// Obtener los datos del pago
$sql = "SELECT * FROM pagos WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_pago);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Pago no encontrado.");
}

$pago = $result->fetch_assoc();

// Crear el PDF
class PDF extends FPDF
{
    // Encabezado
    function Header()
    {
        // Logo
        $this->Image('../img/Logo-OP2_V4.png', 30, 0, 20); // Ruta, posición X, posición Y, ancho
        $this->Ln(10);
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, utf8_decode('Factura de Pago'), 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, utf8_decode('EURIPYS 2024, C.A.'), 0, 1, 'C');
        $this->Cell(0, 5, utf8_decode('RIF: G-200172169'), 0, 1, 'C');
        $this->Ln(10);
    }

    // Pie de página
    function Footer()
    {
        $this->SetY(-20);
        $this->SetFont('Arial', 'I', 6);
        $this->Cell(0, 10, utf8_decode('Gracias por su pago. Esta factura fue generada automáticamente.'), 0, 1, 'C');
    }
}

$pdf = new PDF('P', 'mm', array(80, 150)); // Tamaño pequeño (80mm x 150mm)
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

// Información del cliente
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 5, utf8_decode('Datos del Cliente:'), 0, 1);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(0, 5, utf8_decode('Nombre: ') . utf8_decode($pago['nombre_cliente']), 0, 1);
$pdf->Cell(0, 5, utf8_decode('Referencia: ') . utf8_decode($pago['referencia']), 0, 1);
$pdf->Ln(5);

// Detalles del pago
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 5, utf8_decode('Detalles del Pago:'), 0, 1);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(0, 5, utf8_decode('Monto: Bs. ') . number_format($pago['monto'], 2, ',', '.'), 0, 1);
$pdf->Cell(0, 5, utf8_decode('Banco: ') . utf8_decode($pago['metodo_pago']), 0, 1);
$pdf->Cell(0, 5, utf8_decode('Fecha de Pago: ') . utf8_decode($pago['fecha_pago']), 0, 1);
$pdf->Ln(5);

// Resumen
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 5, utf8_decode('Resumen:'), 0, 1);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(0, 5, utf8_decode('Estado: ') . utf8_decode(ucfirst($pago['estado'])), 0, 1);
$pdf->Ln(10);

// Total
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, utf8_decode('Total: Bs. ') . number_format($pago['monto'], 2, ',', '.'), 0, 1, 'C');

// Salida del PDF
$pdf->Output('I', 'Factura_' . $pago['referencia'] . '.pdf');
?>