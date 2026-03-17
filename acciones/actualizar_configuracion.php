<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once("../conexion.php"); // Asegúrate de tener una conexión a la base de datos
include('../models/bitacora.php');

$pregunta1 = $_POST['pregunta1']; // Pregunta de seguridad 1
$pregunta2 = $_POST['pregunta2']; // Pregunta de seguridad 2
$respuesta1 = $_POST['respuesta1']; // Respuesta a la pregunta de seguridad 1
$respuesta2 = $_POST['respuesta2']; // Respuesta a la pregunta de seguridad 2
$password_actual = $_POST['password_actual']; // Contraseña actual ingresada por el usuario
$password = $_POST['password'];
$password1 = $_POST['password1'];

// Verificar que la contraseña actual sea correcta
$sql = "SELECT clave FROM usuario WHERE id_usuario = " . $_SESSION['id'];
$result = $conexion->query($sql);

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (sha1($password_actual) !== $user['clave']) {
        $_SESSION['estatus'] = "error";
        $_SESSION['mensaje'] = "La contraseña actual es incorrecta.";
        header("Location: ../vistas/configuracion_usuario.php");
        exit();
    }
} else {
    $_SESSION['estatus'] = "error";
    $_SESSION['mensaje'] = "Error al verificar la contraseña actual.";
    header("Location: ../vistas/configuracion_usuario.php");
    exit();
}

// Verificar que las contraseñas nuevas coincidan
if (!empty($password) && $password !== $password1) {
    $_SESSION['estatus'] = "error";
    $_SESSION['mensaje'] = "Error: las contraseñas nuevas no coinciden.";
    header("Location: ../vistas/configuracion_usuario.php");
    exit();
}

// Procesar la imagen
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
    $imagen = $_FILES['imagen'];
    $nombreImagen = time() . '_' . $imagen['name'];
    $rutaDestino = '../img/fotos_perfil/' . $nombreImagen;
    $tipoArchivo = strtolower(pathinfo($nombreImagen, PATHINFO_EXTENSION));
    $tamañoArchivo = $imagen['size'];

    // Validar el tamaño del archivo (10 MB = 10 * 1024 * 1024 bytes)
    if ($tamañoArchivo > 10 * 1024 * 1024) {
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "El tamaño del archivo no debe exceder los 10 MB.";
        header("Location: ../vistas/configuracion_usuario.php");
        exit();
    }

    // Validar el tipo de archivo
    $tiposPermitidos = ['jpg', 'jpeg', 'png', 'svg'];
    if (!in_array($tipoArchivo, $tiposPermitidos)) {
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "Solo se permiten archivos JPG, JPEG, PNG y SVG.";
        header("Location: ../vistas/configuracion_usuario.php");
        exit();
    }

    // Mover la imagen a la carpeta de destino
    if (move_uploaded_file($imagen['tmp_name'], $rutaDestino)) {
        // Obtener la ruta de la imagen anterior
        $sql = "SELECT foto FROM usuario WHERE id_usuario = " . $_SESSION['id'];
        $result = mysqli_query($conexion, $sql);
        $usuario = mysqli_fetch_assoc($result);
        $rutaFotoAnterior = $usuario['foto'];

        // Eliminar la imagen anterior si existe y no es la predeterminada
        if (!empty($rutaFotoAnterior) && file_exists($rutaFotoAnterior) && $rutaFotoAnterior !== '../img/default_profile.png') {
            unlink($rutaFotoAnterior);
        }

        // Actualizar la ruta completa de la imagen en la base de datos
        $sql_imagen = ", foto='$rutaDestino'";
    } else {
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "Error al mover el archivo a la carpeta de destino.";
        header("Location: ../vistas/configuracion_usuario.php");
        exit();
    }
} else {
    $sql_imagen = "";
}

// --- MODIFICACIÓN CLAVE INICIA AQUÍ ---
$sql = "UPDATE usuario SET id_usuario = id_usuario $sql_imagen";

if (!empty($password)) {
    $hashed_password = sha1($password);
    $sql .= ", clave = '$hashed_password'";
}

// Solo si se envían respuestas nuevas
if (!empty($respuesta1) && !empty($respuesta2)) {
    $hashed_respuesta1 = sha1($respuesta1);
    $hashed_respuesta2 = sha1($respuesta2);

    $sql .= ", pregunta = '$pregunta1', respuesta = '$hashed_respuesta1', 
              pregunta2 = '$pregunta2', respuesta2 = '$hashed_respuesta2'";
}

$sql .= " WHERE id_usuario = " . $_SESSION['id'];
// --- MODIFICACIÓN CLAVE TERMINA AQUÍ ---


// Ejecutar la consulta
if ($conexion->query($sql) === TRUE) {
    // Ya no se actualiza $_SESSION['nombre'] porque el nombre no se actualiza en la BD.
    
    if (!empty($sql_imagen)) {
        $_SESSION['foto'] = $rutaDestino; // Actualizar la foto en la sesión si se cambió
    }

    // Registrar la acción en la bitácora
    registrarAccion($conexion, 'Actualización de Configuración', $_SESSION['id']);

    $_SESSION['estatus'] = "success";
    $_SESSION['mensaje'] = "Configuración actualizada con éxito.";
} else {
    $_SESSION['estatus'] = "error";
    $_SESSION['mensaje'] = "Error al actualizar la configuración: " . $conexion->error;
}

header("Location: ../vistas/configuracion_usuario.php");
exit();