<?php
session_start();
include('../conexion.php');
include('../models/notificaciones.php');
include_once('../models/bitacora.php'); // Asegúrate de incluir el archivo donde está registrarAccion
$id_personal = $_REQUEST['id'];

$nombre = $_POST['nombre'];
$nacionalidad = $_POST['nacionalidad'];
$cedula = $_POST['cedula'];
$cargo = $_POST['cargo'];
$codigo = $_POST['codigo'];
$ingreso = $_POST['ingreso'];
$nacimiento = $_POST['nacimiento'];
$activo = $_POST['activo'];

// Depuración de datos
var_dump($_POST);
exit();

// Procesar la imagen
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
    // Obtener la ruta de la imagen actual
    $sql = "SELECT foto FROM personal WHERE id='$id_personal'";
    $result = mysqli_query($conexion, $sql);
    $row = mysqli_fetch_assoc($result);

    if ($row) {
        $rutaImagenActual = $row['foto'];

        // Validar el tamaño del archivo (10 MB = 10 * 1024 * 1024 bytes)
        $imagen = $_FILES['imagen'];
        $tamañoArchivo = $imagen['size'];
        if ($tamañoArchivo > 10 * 1024 * 1024) {
            $_SESSION["estatus"] = "error";
            $_SESSION["mensaje"] = "El tamaño del archivo no debe exceder los 10 MB.";
            header("Location: ../vistas/edit_p.php");
            exit();
        }

        // Validar el tipo de archivo
        $nombreImagen = time() . '_' . $imagen['name'];
        $rutaDestino = '../img/fotos/' . $nombreImagen;
        $tipoArchivo = strtolower(pathinfo($nombreImagen, PATHINFO_EXTENSION));
        $tiposPermitidos = ['jpg', 'jpeg', 'png', 'svg'];
        if (!in_array($tipoArchivo, $tiposPermitidos)) {
            $_SESSION["estatus"] = "error";
            $_SESSION["mensaje"] = "Solo se permiten archivos JPG, JPEG, PNG y SVG.";
            header("Location: ../vistas/edit_p.php");
            exit();
        }

        if (move_uploaded_file($imagen['tmp_name'], $rutaDestino)) {
            // Eliminar la imagen anterior del servidor
            if (file_exists($rutaImagenActual)) {
                unlink($rutaImagenActual);
            }

            // Actualizar la ruta de la imagen en la base de datos
            $sql = "UPDATE personal SET nombre='$nombre', nacionalidad='$nacionalidad', cedula='$cedula', codigo='$codigo', cargo='$cargo', ingreso='$ingreso', fecha_nacimiento='$nacimiento', activo='$activo', foto='$rutaDestino' WHERE id='$id_personal'";
        } else {
            $_SESSION["estatus"] = "error";
            $_SESSION["mensaje"] = "Error al subir la nueva imagen";
            header("Location: ../vistas/edit_p.php");
            exit();
        }
    } else {
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "No se encontró el registro del personal.";
        header("Location: ../vistas/edit_p.php");
        exit();
    }
} else {
    // Si no se sube una nueva imagen, solo actualizar los demás campos
    $sql = "UPDATE personal SET nombre='$nombre', nacionalidad='$nacionalidad', cedula='$cedula', codigo='$codigo', cargo='$cargo', ingreso='$ingreso', fecha_nacimiento='$nacimiento', activo='$activo' WHERE id='$id_personal'";
}

$result = mysqli_query($conexion, $sql);

if ($result) {
    $_SESSION["estatus"] = "success";
    $_SESSION["mensaje"] = "Se Editó con Éxito...!";
    // Registrar en bitácora
    if (isset($_SESSION['id'])) {
        registrarAccion($conexion, 'Editó Personal', $_SESSION['id']);
    }

    header("Location: ../vistas/nomina.php");
} else {
    $_SESSION["estatus"] = "error";
    $_SESSION["mensaje"] = "Algo Salió Mal...! Error: En el Registro.";
    header("Location: ../vistas/edit_p.php");
}
exit();
