<?php
// filepath: c:\xampp\htdocs\SistemaProyectoxampp\acciones\importar_bd.php
session_start();
// Configura tus datos de conexión
$host = "localhost";
$user = "root";
$pass = ""; // Cambia por tu contraseña si tienes
$db   = "nombre_de_tu_base_de_datos"; // Cambia por el nombre real de tu base de datos

if (isset($_FILES['archivoBD']) && $_FILES['archivoBD']['error'] == 0) {
    $archivo_tmp = $_FILES['archivoBD']['tmp_name'];

    // Comando para importar
    $comando = "mysql --user={$user} --password=\"{$pass}\" --host={$host} {$db} < \"$archivo_tmp\"";
    // Ejecutar el comando
    $salida = null;
    $resultado = null;
    exec($comando, $salida, $resultado);

    if ($resultado === 0) {
        $_SESSION['mensaje'] = "Base de datos importada correctamente.";
        $_SESSION['estatus'] = "success";
    } else {
        $_SESSION['mensaje'] = "Error al importar la base de datos.";
        $_SESSION['estatus'] = "danger";
    }
} else {
    $_SESSION['mensaje'] = "No se seleccionó ningún archivo o hubo un error en la subida.";
    $_SESSION['estatus'] = "warning";
}

header("Location: ../vistas/backup_db.php");
exit;
?>