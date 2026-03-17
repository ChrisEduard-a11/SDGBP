<?php
session_start();
include('conexion.php');

$uid = $_SESSION['id'] ?? null;
if (!$uid) {
    die("Error: No has iniciado sesion. Por favor entra al sistema primero.");
}

$fecha_vieja = date('Y-m-d', strtotime('-200 days'));
$sql = "UPDATE usuario SET fecha_cambio_clave = '$fecha_vieja' WHERE id_usuario = '$uid'";

if (mysqli_query($conexion, $sql)) {
    echo "<h1>¡Exito!</h1>";
    echo "<p>He retrasado la fecha de tu contraseña a: <b>$fecha_vieja</b> (hace 200 dias).</p>";
    echo "<p>Ahora, al intentar navegar o refrescar el <a href='vistas/inicio.php'>Dashboard</a>, el sistema <b>DEBE BLOQUEARTE</b> y mandarte a cambiar la clave.</p>";
} else {
    echo "Error BD: " . mysqli_error($conexion);
}
?>
