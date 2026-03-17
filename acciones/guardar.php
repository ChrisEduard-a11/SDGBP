<?php
session_start();
include('../conexion.php');
include('../models/notificaciones.php');
include_once('../models/bitacora.php'); // Asegúrate de incluir el archivo donde está registrarAccion

$nombre = $_POST['nombre'];
$nacionalidad = $_POST['nacionalidad'];
$cedula = $_POST['cedula'];
$cargo = $_POST['cargo'];
$codigo = $_POST["codigo"];
$ingreso = $_POST['ingreso'];
$nacimiento = $_POST['nacimiento'];
$activo = $_POST['activo'];

// Verificar si ya existe un Director o SubDirector
if ($cargo == 'Director' || $cargo == 'SubDirector') {
    $sql_check = "SELECT COUNT(*) as count FROM personal WHERE cargo = '$cargo'";
    $result_check = mysqli_query($conexion, $sql_check);
    $row_check = mysqli_fetch_assoc($result_check);

    if ($row_check['count'] > 0) {
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "Solo puede existir un $cargo registrado.";
        header("Location: ../vistas/registro_p.php");
        exit();
    }
}

// Procesar la imagen
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
    $imagen = $_FILES['imagen'];
    $nombreImagen = time() . '_' . $imagen['name'];
    $rutaDestino = '../img/fotos/' . $nombreImagen;
    $tipoArchivo = strtolower(pathinfo($nombreImagen, PATHINFO_EXTENSION));
    $tamañoArchivo = $imagen['size'];

    // Validar el tamaño del archivo (10 MB = 10 * 1024 * 1024 bytes)
    if ($tamañoArchivo > 10 * 1024 * 1024) {
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "El tamaño del archivo no debe exceder los 10 MB.";
        header("Location: ../vistas/registro_p.php");
        exit();
    }

    // Validar el tipo de archivo
    $tiposPermitidos = ['jpg', 'jpeg', 'png', 'svg'];
    if (!in_array($tipoArchivo, $tiposPermitidos)) {
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "Solo se permiten archivos JPG, JPËG, PNG y SVG.";
        header("Location: ../vistas/registro_p.php");
        exit();
    }

    // Mover la imagen a la carpeta de destino
    if (move_uploaded_file($imagen['tmp_name'], $rutaDestino)) {
        // Guardar la ruta de la imagen en la base de datos
        $sql = "INSERT INTO personal(nombre, nacionalidad, cedula, codigo, cargo, ingreso, fecha_nacimiento, activo, foto) VALUES ('$nombre', '$nacionalidad', '$cedula', '$codigo', '$cargo', '$ingreso', '$nacimiento', '$activo', '$rutaDestino')";
        $result = mysqli_query($conexion, $sql);

        if ($result) {
            $_SESSION["estatus"] = "success";
            $_SESSION["mensaje"] = "Registro Exitoso...!";
            // Registrar en bitácora
            if (isset($_SESSION['id'])) {
                registrarAccion($conexion, 'Registro de Personal', $_SESSION['id']);
            }
            header("Location: ../vistas/nomina.php");
        } else {
            $_SESSION["estatus"] = "error";
            $_SESSION["mensaje"] = "Error al Registrar... DNI(Cedula), ya existe en la base de datos.";
            header("Location: ../vistas/registro_p.php");
        }
    } else {
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "Error al subir la imagen";
        header("Location: ../vistas/registro_p.php");
    }
} else {
    $_SESSION["estatus"] = "error";
    $_SESSION["mensaje"] = "No se ha seleccionado ninguna imagen o ha ocurrido un error. Error: " . $_FILES['imagen']['error'];
    header("Location: ../vistas/registro_p.php");
}
exit();
?>
