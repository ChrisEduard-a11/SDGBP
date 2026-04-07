<?php
require '../vendor/autoload.php';
session_start();
include('../conexion.php');
include('../models/bitacora.php');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user'])) {
    die("No autorizado.");
}

// Validar que se recibió el archivo original y los datos
if (!isset($_POST['archivo']) || empty($_POST['archivo'])) {
    $_SESSION['estatus'] = 'error';
    $_SESSION['mensaje'] = 'No se especificó un archivo para editar.';
    header("Location: ../comprobantes");
    exit();
}

$archivo_original = htmlspecialchars($_POST['archivo']);
$rutaCarpeta = "../comprobantes/";
$ruta_original = realpath($rutaCarpeta . basename($archivo_original)); // basename para evitar Directory Traversal

if (!file_exists($ruta_original)) {
    $_SESSION['estatus'] = 'error';
    $_SESSION['mensaje'] = 'El archivo especificado no existe.';
    header("Location: ../comprobantes");
    exit();
}

// Validar que los campos obligatorios estén completados
if (empty($_POST['num_comprobante']) || empty($_POST['nombre']) || empty($_POST['monto']) || empty($_POST['fecha'])) {
    $_SESSION['estatus'] = 'error';
    $_SESSION['mensaje'] = 'Los campos N° Comprobante, Nombre, Monto y Fecha son obligatorios.';
    header("Location: ../editar-comprobante?archivo=" . urlencode($archivo_original));
    exit();
}

// Obtener los nuevos datos del formulario
$num_comprobante = htmlspecialchars($_POST['num_comprobante']);
$nombre = htmlspecialchars($_POST['nombre']);
$monto = htmlspecialchars($_POST['monto']);
$fecha = htmlspecialchars($_POST['fecha']);
$descripcion = htmlspecialchars($_POST['descripcion'] ?? '');
$rif = htmlspecialchars($_POST['rif'] ?? '');
$telefono = htmlspecialchars($_POST['telefono'] ?? '');
$direccion = htmlspecialchars($_POST['direccion'] ?? '');
$ciudad = htmlspecialchars($_POST['ciudad'] ?? '');
$correo = htmlspecialchars($_POST['correo'] ?? '');
$tipo_pago = htmlspecialchars($_POST['tipo_pago'] ?? '');
$banco = htmlspecialchars($_POST['banco'] ?? '');
$cuenta = htmlspecialchars($_POST['cuenta'] ?? '');
$referencia = htmlspecialchars($_POST['referencia'] ?? '');

try {
    // Cargar el archivo Excel original
    $spreadsheet = IOFactory::load($ruta_original);
    $sheet = $spreadsheet->getActiveSheet();

    // Sobrescribir las celdas con los nuevos datos
    $sheet->setCellValue('H4', $num_comprobante);
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
    $sheet->setCellValue('B28', $tipo_pago);
    $sheet->setCellValue('B29', $banco);
    $sheet->setCellValue('B30', $cuenta);
    $sheet->setCellValue('B31', $referencia);

    // Si el número de comprobante cambió, también debería cambiar el nombre del archivo
    $nuevo_nombre_archivo = "Comprobante_de_Egreso_{$num_comprobante}.xlsm";
    $nueva_ruta = $rutaCarpeta . $nuevo_nombre_archivo;

    // Guardar el archivo modificado
    $writer = new Xlsx($spreadsheet);
    $writer->save($nueva_ruta);

    // Si el nombre del archivo cambió (porque cambió el N° de comprobante), eliminamos el viejo original
    if ($archivo_original !== $nuevo_nombre_archivo) {
        if (file_exists($ruta_original) && is_file($ruta_original)) {
            unlink($ruta_original);
        }
    }

    // Registrar en bitácora
    registrarAccion($conexion, "Editó el comprobante: $nuevo_nombre_archivo", $_SESSION['id']);

    $_SESSION['estatus'] = 'success';
    $_SESSION['mensaje'] = "El comprobante ha sido actualizado correctamente.";
    header("Location: ../vistas/listar_comprobantes.php");
    exit();

} catch (Exception $e) {
    $_SESSION['estatus'] = 'error';
    $_SESSION['mensaje'] = "Ocurrió un error al guardar el archivo Excel: " . $e->getMessage();
    header("Location: ../vistas/editar_comprobante.php?archivo=" . urlencode($archivo_original));
    exit();
}
?>
