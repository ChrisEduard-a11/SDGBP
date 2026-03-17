<?php
include('../conexion.php');

function obtenerCategorias($conexion) {
    $sql = "SELECT * FROM categorias";
    $result = mysqli_query($conexion, $sql);
    $categorias = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $categorias[] = $row;
    }
    return $categorias;
}
?>