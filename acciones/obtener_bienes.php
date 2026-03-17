<?php
include('../conexion.php');

$categoria_id = $_GET['categoria_id'];
$nombre_bien = $_GET['nombre_bien'];

$sql = "SELECT b.id, b.nombre, b.descripcion, c.nombre AS categoria, b.codigo, b.serial, b.fecha_adquisicion 
        FROM bienes b 
        JOIN categorias c ON b.categoria_id = c.id
        WHERE b.categoria_id = '$categoria_id' AND b.nombre = '$nombre_bien'";

$result = mysqli_query($conexion, $sql);

$bienes = [];
while ($row = mysqli_fetch_assoc($result)) {
    $bienes[] = $row;
}

echo json_encode($bienes);
?>