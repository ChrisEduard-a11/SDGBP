<?php
// Configura los encabezados para forzar la descarga del archivo Excel
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Resumen_Saldos_UPU_" . date("Y-m-d") . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// Conexión a la base de datos
require_once("../conexion.php");

// Consulta SQL para obtener los datos de los usuarios UPU
$sql = "SELECT nombre, correo, saldo FROM usuario WHERE tipos = 'upu'";
$result = $conexion->query($sql);

if (!$result) {
    die("Error al consultar la base de datos: " . $conexion->error);
}

// BOM para UTF-8
echo "\xEF\xBB\xBF";
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <!--[if gte mso 9]>
    <xml>
    <x:ExcelWorkbook>
    <x:ExcelWorksheets>
    <x:ExcelWorksheet>
    <x:Name>Resumen Saldos</x:Name>
    <x:WorksheetOptions>
    <x:DisplayGridlines/>
    </x:WorksheetOptions>
    </x:ExcelWorksheet>
    </x:ExcelWorksheets>
    </x:ExcelWorkbook>
    </xml>
    <![endif]-->
    <style>
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bg-gray { background-color: #f0f0f0; }
        .font-bold { font-weight: bold; }
        table { border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 5px; }
    </style>
</head>
<body>
    <table border="1">
        <thead>
            <tr class="bg-gray font-bold">
                <th>Nombre UPU</th>
                <th>Correo</th>
                <th>Saldo Disponible (Bs)</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo mb_convert_encoding($row['nombre'], 'HTML-ENTITIES', 'UTF-8'); ?></td>
                        <td><?php echo $row['correo']; ?></td>
                        <td class="text-right"><?php echo number_format($row['saldo'], 2, ',', '.'); ?></td>
                    </tr>
                <?php
    endwhile; ?>
            <?php
else: ?>
                <tr>
                    <td colspan="3" class="text-center">No se encontraron usuarios UPU.</td>
                </tr>
            <?php
endif; ?>
        </tbody>
    </table>
</body>
</html>
