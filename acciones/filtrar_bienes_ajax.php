<?php
include('../conexion.php');

$categoria_id = isset($_GET['categoria']) ? $_GET['categoria'] : '';
$nombre_bien = isset($_GET['nombre_bien']) ? $_GET['nombre_bien'] : '';

// Base Query
$sql = "SELECT b.id, b.nombre, b.descripcion, c.nombre AS categoria, b.codigo, b.serial, b.fecha_adquisicion 
        FROM bienes b 
        JOIN categorias c ON b.categoria_id = c.id
        WHERE 1=1";

$parametros = [];
$tipos = "";

// Filtro de Categoría (Opcional - podría cargar todo o solo lo necesario)
if (!empty($categoria_id)) {
    $sql .= " AND b.categoria_id = ?";
    $tipos .= "s";
    $parametros[] = $categoria_id;
}

// Filtro de Nombre de Bien (Opcional)
if (!empty($nombre_bien)) {
    $sql .= " AND b.nombre = ?";
    $tipos .= "s";
    $parametros[] = $nombre_bien;
}

$sql .= " ORDER BY b.fecha_adquisicion DESC";

// Preparamos y ejecutamos
$stmt = $conexion->prepare($sql);

if(!empty($parametros)) {
    $stmt->bind_param($tipos, ...$parametros);
}

$stmt->execute();
$result = $stmt->get_result();

$bienes = [];
while ($row = $result->fetch_assoc()) {
    $bienes[] = $row;
}

$stmt->close();
echo json_encode($bienes);
?>
