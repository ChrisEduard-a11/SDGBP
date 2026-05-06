<?php
// Exportar respaldo de base de datos en PHP puro
require_once __DIR__ . '/../conexion.php';

$fecha = date("Ymd_His");
$exportType = $_GET['export_type'] ?? 'both';
$exportType = in_array($exportType, ['structure', 'data', 'both']) ? $exportType : 'both';
$tableName = trim($_GET['table_name'] ?? '');
$nombreArchivo = "respaldo_bd_{$fecha}";

$tablesToExport = [];
if ($tableName !== '') {
    $tablesToExport = [$tableName];
    $relatedMap = [];
    $relationQuery = "SELECT TABLE_NAME, REFERENCED_TABLE_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE CONSTRAINT_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL";
    $relationResult = mysqli_query($conexion, $relationQuery);
    if ($relationResult) {
        while ($rel = mysqli_fetch_assoc($relationResult)) {
            $table = $rel['TABLE_NAME'];
            $referenced = $rel['REFERENCED_TABLE_NAME'];
            $relatedMap[$table][] = $referenced;
            $relatedMap[$referenced][] = $table;
        }
    }
    $visited = [$tableName => true];
    $stack = [$tableName];
    while ($stack) {
        $current = array_pop($stack);
        if (!empty($relatedMap[$current])) {
            foreach ($relatedMap[$current] as $relatedTable) {
                if (empty($visited[$relatedTable])) {
                    $visited[$relatedTable] = true;
                    $tablesToExport[] = $relatedTable;
                    $stack[] = $relatedTable;
                }
            }
        }
    }
    $tablesToExport = array_unique($tablesToExport);
    $nombreArchivo = "respaldo_bd_{$fecha}_tabla_{$tableName}";
} else {
    $result = mysqli_query($conexion, "SHOW TABLES");
    while ($row = mysqli_fetch_row($result)) {
        $tablesToExport[] = $row[0];
    }
}

if ($exportType === 'structure') {
    $nombreArchivo .= "_estructura.sql";
} elseif ($exportType === 'data') {
    $nombreArchivo .= "_datos.sql";
} else {
    $nombreArchivo .= ".sql";
}

header("Content-Type: application/sql");
header("Content-Disposition: attachment; filename=\"$nombreArchivo\"");
header("Pragma: no-cache");
header("Expires: 0");

echo "-- Respaldo de BD generado en PHP (Compatible con InfinityFree y XAMPP)\n";
echo "-- Fecha: " . date("Y-m-d H:i:s") . "\n\n";
echo "SET FOREIGN_KEY_CHECKS=0;\n\n";

foreach ($tablesToExport as $table) {
    if ($exportType === 'structure' || $exportType === 'both') {
        $res = mysqli_query($conexion, "SHOW CREATE TABLE `$table`");
        if ($res && $row = mysqli_fetch_row($res)) {
            echo "-- Estructura de tabla `$table`\n";
            echo "DROP TABLE IF EXISTS `$table`;\n";
            echo $row[1] . ";\n\n";
            mysqli_free_result($res);
        }
    }

    if ($exportType === 'data' || $exportType === 'both') {
        // Obtenemos un registro de las columnas disponibles en esta tabla para evitar desajustes de datos
        // Usamos MYSQLI_USE_RESULT para no saturar memoria en tablas grandes
        $res = mysqli_query($conexion, "SELECT * FROM `$table`", MYSQLI_USE_RESULT);
        if ($res) {
            $num_fields = mysqli_field_count($conexion);
            $hasData = false;
            
            while ($row = mysqli_fetch_row($res)) {
                if (!$hasData) {
                    echo "-- Datos para la tabla `$table`\n";
                    $hasData = true;
                }
                echo "INSERT INTO `$table` VALUES(";
                for ($j = 0; $j < $num_fields; $j++) {
                    if (isset($row[$j])) {
                        $escaped = mysqli_real_escape_string($conexion, $row[$j]);
                        echo "'" . $escaped . "'";
                    } else {
                        echo "NULL";
                    }
                    if ($j < ($num_fields - 1)) {
                        echo ",";
                    }
                }
                echo ");\n";
            }
            if ($hasData) {
                echo "\n";
            }
            mysqli_free_result($res);
        }
    }
}
echo "SET FOREIGN_KEY_CHECKS=1;\n";
exit;
?>