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
    <x:Name>Historial Pagos</x:Name>
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
    <table>
        <thead>
            <tr class="bg-gray font-bold">
                <th>UPU</th>
                <th>Descripción</th>
                <th>Fecha</th>
                <th>Referencia</th>
                <th>Método</th>
                <th>Motivo Rechazo</th>
                <th>Concepto</th>
                <th>Entrada (Bs)</th>
                <th>Salida (Bs)</th>
                <th>Saldo Final (Bs)</th>
                <th>Estado</th>
                <th>Cliente/Proveedor</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo mb_convert_encoding(ucfirst($row["nombre_cliente"]), 'HTML-ENTITIES', 'UTF-8'); ?></td>
                        <td><?php echo mb_convert_encoding(ucfirst($row["tipo"]), 'HTML-ENTITIES', 'UTF-8'); ?></td>
                        <td class="text-center"><?php echo date('d/m/Y', strtotime($row["fecha_pago"])); ?></td>
                        <td style="mso-number-format:'@';"><?php echo $row["referencia"]; ?></td>
                        <td><?php echo mb_convert_encoding($row["metodo_pago"], 'HTML-ENTITIES', 'UTF-8'); ?></td>
                        <td><?php echo mb_convert_encoding($row["des_rechazo"], 'HTML-ENTITIES', 'UTF-8'); ?></td>
                        <td><?php echo mb_convert_encoding($row["descripcion"], 'HTML-ENTITIES', 'UTF-8'); ?></td>
                        
                        <!-- Ingreso -->
                        <td class="text-right">
                            <?php if ($row["tipo"] == "Ingreso"): ?>
                                <?php echo number_format($row["monto"], 2, ',', '.'); ?>
                            <?php
        endif; ?>
                        </td>
                        
                        <!-- Egreso -->
                        <td class="text-right">
                            <?php if ($row["tipo"] == "Egreso"): ?>
                                <?php echo number_format($row["monto"], 2, ',', '.'); ?>
                            <?php
        endif; ?>
                        </td>
                        
                        <!-- Saldo Final -->
                        <td class="text-right">
                            <?php
        if ($row['estado'] == 'aprobado') {
            echo isset($row['saldo_resultante']) ? number_format($row['saldo_resultante'], 2, ',', '.') : '';
        }
?>
                        </td>
                        
                        <td class="text-center"><?php echo mb_convert_encoding(ucfirst($row['estado']), 'HTML-ENTITIES', 'UTF-8'); ?></td>
                        <td><?php echo mb_convert_encoding($row['cliente'], 'HTML-ENTITIES', 'UTF-8'); ?></td>
                    </tr>
                <?php
    endwhile; ?>
            <?php
else: ?>
                <tr>
                    <td colspan="12" class="text-center">No se encontraron pagos con los filtros seleccionados.</td>
                </tr>
            <?php
endif; ?>
        </tbody>
    </table>
</body>
</html>
