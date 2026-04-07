<?php
require '../vendor/autoload.php'; // Ajusta la ruta si es necesario
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

session_start();

// Validar que los datos del formulario existan y no estén vacíos
if (!isset($_POST['num_comprobante'], $_POST['nombre'], $_POST['monto'], $_POST['fecha'], $_POST['descripcion'], $_POST['rif'], $_POST['telefono'], $_POST['direccion'], $_POST['ciudad'], $_POST['correo'], 
          $_POST['tipo_pago'], $_POST['banco'], $_POST['cuenta'], $_POST['referencia']) || 
    empty($_POST['num_comprobante']) || empty($_POST['nombre']) || empty($_POST['monto']) || empty($_POST['fecha']) || empty($_POST['tipo_pago']) || empty($_POST['banco']) || empty($_POST['cuenta']) || empty($_POST['referencia'])) {
    $_SESSION["estatus"] = "error";
    $_SESSION["mensaje"] = "Todos los campos obligatorios deben ser completados correctamente.";
    header("Location: ../vistas/formulario_comprobante.php");
    exit();
}

// Obtener datos del formulario
$num_comprobante = htmlspecialchars($_POST['num_comprobante']); // Nuevo campo
$nombre = htmlspecialchars($_POST['nombre']);
$monto = htmlspecialchars($_POST['monto']);
$fecha = htmlspecialchars($_POST['fecha']);
$descripcion = htmlspecialchars($_POST['descripcion']);
$rif = htmlspecialchars($_POST['rif']);
$telefono = htmlspecialchars($_POST['telefono']);
$direccion = htmlspecialchars($_POST['direccion']);
$ciudad = htmlspecialchars($_POST['ciudad']);
$correo = htmlspecialchars($_POST['correo']);
$tipo_pago = htmlspecialchars($_POST['tipo_pago']);
$banco = htmlspecialchars($_POST['banco']);
$cuenta = htmlspecialchars($_POST['cuenta']);
$referencia = htmlspecialchars($_POST['referencia']);

// Verificar que la plantilla exista
$plantilla = "plantillas/comprobante_egreso.xlsm"; // Ruta de la plantilla
if (!file_exists($plantilla)) {
    $_SESSION["estatus"] = "error";
    $_SESSION["mensaje"] = "La plantilla Excel no se encuentra en el servidor.";
    header("Location: ../vistas/formulario_comprobante.php");
    exit();
}

// Cargar plantilla
$spreadsheet = IOFactory::load($plantilla);
$sheet = $spreadsheet->getActiveSheet();

// Llenar las celdas correspondientes con las coordenadas correctas
$sheet->setCellValue('H4', $num_comprobante); // Número de Comprobante
$sheet->setCellValue('B8', $nombre);
$sheet->setCellValue('B9', $rif);
$sheet->setCellValue('E9', $telefono);
$sheet->setCellValue('B10', $direccion);
$sheet->setCellValue('E10', $ciudad);
$sheet->setCellValue('B11', $correo);
$sheet->setCellValue('H15', $monto);
$sheet->setCellValue('C15', $descripcion);
$sheet->setCellValue('G9', $fecha);

// Datos bancarios
$sheet->setCellValue('B28', $tipo_pago);  // Tipo de Cuenta
$sheet->setCellValue('B29', $banco);      // Banco
$sheet->setCellValue('B30', $cuenta);     // Número de Cuenta
$sheet->setCellValue('B31', $referencia); // Referencia

// Generar el nombre del archivo dinámicamente
$nombreArchivo = "Comprobante_de_Egreso_{$num_comprobante}.xlsm";

// Ruta donde se guardará el archivo
$rutaCarpeta = "../comprobantes/";
if (!file_exists($rutaCarpeta)) {
    mkdir($rutaCarpeta, 0777, true); // Crear la carpeta si no existe
}
$rutaArchivo = $rutaCarpeta . $nombreArchivo;

// Guardar el archivo en la carpeta
$writer = new Xlsx($spreadsheet);
$writer->save($rutaArchivo);

// Descargar el archivo directamente
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=\"$nombreArchivo\"");
header('Cache-Control: max-age=0');
readfile($rutaArchivo);

// Redirigir al usuario a la lista de comprobantes después de la descarga
echo "<script>
    window.location.href = '../comprobantes';
</script>";
exit();
?>